<?php

declare(strict_types=1);

namespace App\Controllers\Storefront;

use App\Controllers\Controller;
use App\Core\Config;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Repositories\AddressRepository;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use App\Services\CartService;
use App\Services\CheckoutService;
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

    /**
     * Place the order directly from the shipping form — no OTP. Registration
     * is optional: a customer account is created/looked-up by mobile and the
     * customer is signed in so they can view the order + their panel. The
     * separate /login flow (OTP) is for deliberate registration/sign-in.
     */
    public function place(Request $request): Response
    {
        $data = $this->collect($request);

        $validator = new Validator();
        $rules = [
            'first_name'      => 'required',
            'last_name'       => 'required',
            'province'        => 'required',
            'city'            => 'required',
            'address'         => 'required',
            'mobile'          => 'required|mobile',
            'shipping_method' => 'required',
            'payment_method'  => 'required',
        ];
        if (!$validator->validate($data, $rules)) {
            return $this->json(['ok' => false, 'error' => 'لطفاً همه‌ی فیلدهای الزامی را کامل کنید.', 'fields' => $validator->errors()], 422);
        }

        $cart    = new CartService();
        $summary = $cart->summary();
        if ((int) $summary['count'] === 0) {
            return $this->json(['ok' => false, 'error' => 'سبد خرید شما خالی است.'], 422);
        }

        // Validate the chosen shipping method against destination + parcel.
        $ship = (new ShippingService())->resolve(
            $data['province'],
            $data['city'],
            $data['shipping_method'],
            (int) $summary['subtotal'],
            $cart->parcel()
        );
        if ($ship === null) {
            return $this->json(['ok' => false, 'error' => 'روش ارسال نامعتبر است.'], 422);
        }

        // Find or create the customer account by mobile (no OTP).
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

        // Create the order (also SMS-notifies "ready for payment").
        $result = (new CheckoutService())->place($userId, [
            'receiver_name' => trim($data['first_name'] . ' ' . $data['last_name']),
            'mobile'        => $data['mobile'],
            'province'      => $data['province'],
            'city'          => $data['city'],
            'address'       => $data['address'],
            'postal_code'   => $data['postal_code'] ?? null,
            'note'          => $data['note'] ?? null,
        ], $data['shipping_method'], $data['payment_method']);

        if (!$result['ok']) {
            return $this->json(['ok' => false, 'error' => $result['message']], 422);
        }

        // Sign the customer in (1-year session) so they can track the order.
        AuthService::login($userId);

        // Hand off to the payment step (gateway redirect or card-to-card page).
        return $this->json([
            'ok'          => true,
            'payment_url' => url('/pay/' . $result['order']['id']),
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
            'note'            => trim((string) $request->input('note', '')) ?: null,
            'shipping_method' => trim((string) $request->input('shipping_method', '')),
            'payment_method'  => trim((string) $request->input('payment_method', 'zarinpal')),
        ];
    }
}
