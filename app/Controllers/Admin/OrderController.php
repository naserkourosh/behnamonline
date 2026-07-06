<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Repositories\UserRepository;
use App\Services\Sms\SmsManager;

final class OrderController extends AdminController
{
    private OrderRepository $orders;

    public function __construct()
    {
        $this->orders = new OrderRepository();
    }

    public function index(Request $request): Response
    {
        if ($r = $this->guard('orders')) {
            return $r;
        }
        $filters = [
            'status'         => (string) $request->query('status', ''),
            'payment_status' => (string) $request->query('payment_status', ''),
            'search'         => trim((string) $request->query('q', '')),
        ];
        $perPage = 20;
        $page    = max(1, (int) $request->query('page', 1));
        $total   = $this->orders->adminCount($filters);

        return $this->adminView('admin/orders/index', [
            'items'   => $this->orders->adminList($filters, $perPage, ($page - 1) * $perPage),
            'filters' => $filters,
            'total'   => $total,
            'page'    => $page,
            'pages'   => (int) ceil($total / $perPage),
        ], 'سفارش‌ها');
    }

    public function show(Request $request): Response
    {
        if ($r = $this->guard('orders')) {
            return $r;
        }
        $order = $this->orders->findAny((int) $request->param('id'));
        if ($order === null) {
            return $this->notFound();
        }
        $customer = $order['user_id'] ? (new UserRepository())->find((int) $order['user_id']) : null;

        return $this->adminView('admin/orders/show', [
            'order'    => $order,
            'items'    => $this->orders->items((int) $order['id']),
            'customer' => $customer,
        ], 'سفارش ' . $order['order_number']);
    }

    public function update(Request $request): Response
    {
        if ($r = $this->guard('orders')) {
            return $r;
        }
        $id    = (int) $request->param('id');
        $order = $this->orders->findAny($id);
        if ($order === null) {
            return $this->notFound();
        }

        $validStatus  = ['pending', 'processing', 'shipped', 'delivered', 'canceled'];
        $validPayment = ['unpaid', 'paid', 'failed'];
        $status   = in_array($request->input('status'), $validStatus, true) ? (string) $request->input('status') : (string) $order['status'];
        $payment  = in_array($request->input('payment_status'), $validPayment, true) ? (string) $request->input('payment_status') : (string) $order['payment_status'];
        $tracking = trim((string) $request->input('tracking_code', '')) ?: ($order['tracking_code'] ?? null);

        $sms = new SmsManager();

        // Confirming payment (e.g. card-to-card) → decrement stock, notify.
        // markPaidProcessing() atomically claims the transition; 0 rows means
        // the gateway already settled it concurrently — skip the side effects.
        if ($payment === 'paid' && $this->orders->markPaidProcessing($id) > 0) {
            $products = new ProductRepository();
            foreach ($this->orders->items($id) as $it) {
                if ($it['product_id'] !== null) {
                    $products->decrementStock((int) $it['product_id'], (int) $it['qty']);
                }
                if ($it['variant_id'] !== null) {
                    $products->decrementVariantStock((int) $it['variant_id'], (int) $it['qty']);
                }
            }
            // Consume coupon + award loyalty points (idempotent per order).
            (new \App\Services\PaymentService())->finalizePromotions($order);
            // No tracking code at payment time — it is sent when the parcel ships.
            $sms->sendTemplate(
                (string) $order['mobile'],
                'payment_confirmed',
                ['order' => (string) $order['order_number'], 'tracking' => ''],
                "بهنام\nپرداخت سفارش {$order['order_number']} تایید شد. ✅\nسفارش شما در حال آماده‌سازی است."
            );
        }

        // Shipping notification.
        if ((string) $order['status'] !== 'shipped' && $status === 'shipped') {
            $sms->sendTemplate(
                (string) $order['mobile'],
                'order_shipped',
                ['order' => (string) $order['order_number'], 'tracking' => (string) ($tracking ?? '')],
                "بهنام\nسفارش {$order['order_number']} ارسال شد. 🚚" . ($tracking ? "\nکد رهگیری: {$tracking}" : '')
            );
        }

        $this->orders->adminUpdate($id, $status, $payment, $tracking ?: null);
        $this->audit($request, 'update', 'order', $id, "status={$status} payment={$payment}");
        Session::flash('success', 'سفارش به‌روزرسانی شد.');
        return $this->redirect(url('/admin/orders/' . $id));
    }
}
