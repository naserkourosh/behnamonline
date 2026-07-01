<?php

declare(strict_types=1);

namespace App\Controllers\Storefront;

use App\Controllers\Controller;
use App\Core\Config;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Repositories\AddressRepository;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\OtpService;
use App\Services\ShippingService;

final class CheckoutController extends Controller
{
    public function index(Request $request): Response
    {
        $summary = (new CartService())->summary();
        if ((int) $summary['count'] === 0) {
            return $this->redirect(url('/cart'));
        }

        $prefill = [];
        if (AuthService::check()) {
            $user    = AuthService::user();
            $default = (new AddressRepository())->defaultFor((int) $user['id']);
            $prefill = [
                'first_name'    => $user['first_name'] ?? '',
                'last_name'     => $user['last_name'] ?? '',
                'mobile'        => $user['mobile'] ?? '',
                'province'      => $default['province'] ?? '',
                'city'          => $default['city'] ?? '',
                'address'       => $default['address'] ?? '',
                'postal_code'   => $default['postal_code'] ?? '',
            ];
        }

        return $this->view('storefront/checkout', [
            'summary'   => $summary,
            'provinces' => Config::get('geo', []),
            'prefill'   => $prefill,
        ], 'storefront');
    }

    public function sendOtp(Request $request): Response
    {
        $data = $this->collect($request);

        $validator = new Validator();
        $rules = [
            'first_name' => 'required',
            'last_name'  => 'required',
            'province'   => 'required',
            'city'       => 'required',
            'address'    => 'required',
            'mobile'     => 'required|mobile',
            'shipping_method' => 'required',
            'payment_method'  => 'required',
        ];
        if (!$validator->validate($data, $rules)) {
            return $this->json(['ok' => false, 'error' => 'لطفاً همه‌ی فیلدهای الزامی را کامل کنید.', 'fields' => $validator->errors()], 422);
        }

        // Validate the chosen shipping method against the city.
        $summary = (new CartService())->summary();
        if ((int) $summary['count'] === 0) {
            return $this->json(['ok' => false, 'error' => 'سبد خرید شما خالی است.'], 422);
        }
        $ship = (new ShippingService())->resolve($data['city'], $data['shipping_method'], (int) $summary['subtotal']);
        if ($ship === null) {
            return $this->json(['ok' => false, 'error' => 'روش ارسال نامعتبر است.'], 422);
        }

        Session::set('checkout', $data);

        $result = (new OtpService())->send($data['mobile'], 'checkout');
        return $this->json($result, $result['ok'] ? 200 : 429);
    }

    public function verify(Request $request): Response
    {
        $data = Session::get('checkout');
        if (!is_array($data)) {
            return $this->json(['ok' => false, 'error' => 'نشست پرداخت منقضی شده است. دوباره تلاش کنید.'], 419);
        }

        $code = en_num((string) $request->input('code', ''));
        $otp  = (new OtpService())->verify($data['mobile'], $code, 'checkout');
        if (!$otp['ok']) {
            return $this->json(['ok' => false, 'error' => $otp['message']], 422);
        }

        // Find or create the customer account, filling in name if missing.
        $users = new UserRepository();
        $user  = $users->findByMobile($data['mobile']);
        if ($user === null) {
            $userId = $users->create($data['mobile'], $data['first_name'], $data['last_name']);
        } else {
            $userId = (int) $user['id'];
            if (empty($user['first_name'])) {
                $users->updateProfile($userId, $data['first_name'], $data['last_name']);
            }
        }

        // Persist the shipping address.
        $addresses = new AddressRepository();
        $addresses->create($userId, [
            'receiver_name' => trim($data['first_name'] . ' ' . $data['last_name']),
            'mobile'        => $data['mobile'],
            'province'      => $data['province'],
            'city'          => $data['city'],
            'address'       => $data['address'],
            'postal_code'   => $data['postal_code'] ?? null,
            'is_default'    => $addresses->count($userId) === 0 ? 1 : 0,
        ]);

        $result = (new CheckoutService())->place($userId, [
            'receiver_name' => trim($data['first_name'] . ' ' . $data['last_name']),
            'mobile'        => $data['mobile'],
            'province'      => $data['province'],
            'city'          => $data['city'],
            'address'       => $data['address'],
            'postal_code'   => $data['postal_code'] ?? null,
        ], $data['shipping_method'], $data['payment_method']);

        if (!$result['ok']) {
            return $this->json(['ok' => false, 'error' => $result['message']], 422);
        }

        AuthService::login($userId);
        Session::forget('checkout');

        return $this->json([
            'ok'    => true,
            'order' => [
                'number' => $result['order']['number'],
                'total'  => $result['order']['total'],
            ],
        ]);
    }

    /** @return array<string,mixed> */
    private function collect(Request $request): array
    {
        return [
            'first_name'      => trim((string) $request->input('first_name', '')),
            'last_name'       => trim((string) $request->input('last_name', '')),
            'province'        => trim((string) $request->input('province', '')),
            'city'            => trim((string) $request->input('city', '')),
            'address'         => trim((string) $request->input('address', '')),
            'postal_code'     => en_num((string) $request->input('postal_code', '')),
            'mobile'          => en_num((string) $request->input('mobile', '')),
            'shipping_method' => trim((string) $request->input('shipping_method', '')),
            'payment_method'  => trim((string) $request->input('payment_method', 'zarinpal')),
        ];
    }
}
