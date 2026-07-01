<?php

declare(strict_types=1);

namespace App\Controllers\Storefront;

use App\Controllers\Controller;
use App\Core\Config;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\OrderRepository;
use App\Repositories\PaymentRepository;
use App\Services\AuthService;
use App\Services\PaymentService;

final class PaymentController extends Controller
{
    /** GET /pay/{order} — start payment for a pending order. */
    public function start(Request $request): Response
    {
        [$order] = $this->loadOrder($request);
        if ($order === null) {
            return $this->notFound();
        }
        if ((string) $order['payment_status'] === 'paid') {
            return $this->redirect(url('/checkout/success/' . $order['id']));
        }

        $method = (string) $order['payment_method'];

        // Card-to-card is a manual transfer — show instructions instead of redirecting.
        if ((new PaymentService())->isManual($method)) {
            return $this->view('storefront/pay/card', [
                'order' => $order,
                'card'  => Config::get('payment.gateways.card_to_card', []),
            ]);
        }

        $callback = abs_url('pay/callback?order=' . $order['id']);
        $result   = (new PaymentService())->begin($order, $callback);

        if (!$result['ok']) {
            Session::flash('error', $result['error'] ?? 'خطا در ایجاد تراکنش.');
            return $this->redirect(url('/checkout/failed/' . $order['id']));
        }

        return $this->redirect($result['redirect_url']);
    }

    /** GET /pay/mock — internal test gateway (dev only). */
    public function mock(Request $request): Response
    {
        [$order] = $this->loadOrder($request, 'order');
        if ($order === null) {
            return $this->notFound();
        }

        return $this->view('storefront/pay/mock', [
            'order'     => $order,
            'authority' => (string) $request->query('authority', ''),
            'gateway'   => (string) $request->query('gateway', 'mock'),
        ]);
    }

    /** GET /pay/callback — verify the gateway result and settle the order. */
    public function callback(Request $request): Response
    {
        [$order] = $this->loadOrder($request, 'order');
        if ($order === null) {
            return $this->notFound();
        }

        $params = $request->queryAll();
        $result = (new PaymentService())->complete($order, $params);

        if ($result['ok']) {
            return $this->redirect(url('/checkout/success/' . $order['id']));
        }

        Session::flash('error', $result['error'] ?? 'پرداخت ناموفق بود.');
        return $this->redirect(url('/checkout/failed/' . $order['id']));
    }

    /** POST /pay/card/{order} — submit a card-to-card transfer reference. */
    public function cardConfirm(Request $request): Response
    {
        [$order] = $this->loadOrder($request);
        if ($order === null) {
            return $this->notFound();
        }

        $ref = en_num((string) $request->input('reference', ''));
        if (strlen($ref) < 4) {
            Session::flash('error', 'شماره پیگیری/مرجع تراکنش را وارد کنید.');
            return $this->redirect(url('/pay/' . $order['id']));
        }

        (new PaymentRepository())->recordManual((int) $order['id'], (int) $order['total'], $ref);
        Session::flash('success', 'رسید شما ثبت شد. پس از تایید پرداخت، سفارش پردازش می‌شود.');
        return $this->redirect(url('/account/orders/' . $order['id']));
    }

    /** GET /checkout/success/{order} */
    public function success(Request $request): Response
    {
        [$order] = $this->loadOrder($request);
        if ($order === null) {
            return $this->notFound();
        }
        return $this->view('storefront/checkout-result', ['order' => $order, 'success' => true]);
    }

    /** GET /checkout/failed/{order} */
    public function failed(Request $request): Response
    {
        [$order] = $this->loadOrder($request);
        if ($order === null) {
            return $this->notFound();
        }
        return $this->view('storefront/checkout-result', ['order' => $order, 'success' => false]);
    }

    /**
     * Load an order owned by the current user. $source picks where the id
     * comes from: 'param' (route {order}) or a query key.
     * @return array{0:array<string,mixed>|null}
     */
    private function loadOrder(Request $request, string $source = 'param'): array
    {
        $userId = (int) AuthService::id();
        $id = $source === 'param'
            ? (int) $request->param('order')
            : (int) $request->query($source, 0);

        return [(new OrderRepository())->find($id, $userId)];
    }
}
