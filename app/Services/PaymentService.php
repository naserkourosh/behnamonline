<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use App\Repositories\CouponRepository;
use App\Repositories\OrderRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\ProductRepository;
use App\Services\Payment\MockGateway;
use App\Services\Payment\PaymentGateway;
use App\Services\Payment\ZarinpalGateway;
use App\Services\Sms\SmsManager;

/**
 * Orchestrates the payment lifecycle: choose a gateway for the order's
 * payment method, initiate a transaction, verify the callback, and on
 * success settle the order (stock, tracking code, SMS).
 */
final class PaymentService
{
    private OrderRepository $orders;
    private PaymentRepository $payments;
    private ProductRepository $products;

    public function __construct()
    {
        $this->orders   = new OrderRepository();
        $this->payments = new PaymentRepository();
        $this->products = new ProductRepository();
    }

    /** Card-to-card is a manual, non-redirect method handled separately. */
    public function isManual(string $method): bool
    {
        return $method === 'card';
    }

    /**
     * Resolve the gateway for an order's chosen payment method. Falls back
     * to the mock gateway for methods without configured credentials.
     */
    public function gatewayFor(string $method): PaymentGateway
    {
        if ($method === 'zarinpal') {
            $merchant = (string) Config::get('payment.gateways.zarinpal.merchant_id', '');
            $driver   = (string) Config::get('payment.driver', 'mock');
            if ($driver === 'zarinpal' && $merchant !== '') {
                return new ZarinpalGateway();
            }
            return new MockGateway('زرین‌پال (آزمایشی)', 'zarinpal');
        }

        $labels = ['snappay' => 'اسنپ‌پی', 'digipay' => 'دیجی‌پی'];
        return new MockGateway(($labels[$method] ?? 'درگاه') . ' (آزمایشی)', $method);
    }

    /**
     * Begin a payment for an order.
     * @param array<string,mixed> $order
     * @return array{ok:bool,redirect_url?:string,error?:string}
     */
    public function begin(array $order, string $callbackUrl): array
    {
        $gateway = $this->gatewayFor((string) $order['payment_method']);
        $amount  = (int) $order['total'];

        $paymentId = $this->payments->create((int) $order['id'], $gateway->key(), $amount);

        $result = $gateway->initiate(
            (int) $order['id'],
            $amount,
            'سفارش ' . $order['order_number'] . ' — فروشگاه بهنام',
            $callbackUrl
        );

        if (!$result['ok']) {
            $this->payments->markFailed($paymentId);
            return ['ok' => false, 'error' => $result['error'] ?? 'خطا در ایجاد تراکنش.'];
        }

        if (!empty($result['authority'])) {
            $this->payments->setAuthority($paymentId, (string) $result['authority']);
        }

        return ['ok' => true, 'redirect_url' => (string) $result['redirect_url']];
    }

    /**
     * Verify a gateway callback and settle the order on success.
     * @param array<string,mixed> $order
     * @param array<string,mixed> $params
     * @return array{ok:bool,tracking?:string,error?:string}
     */
    public function complete(array $order, array $params): array
    {
        $payment = $this->payments->latestForOrder((int) $order['id']);
        if ($payment === null) {
            return ['ok' => false, 'error' => 'تراکنشی برای این سفارش یافت نشد.'];
        }

        // Already settled (double callback / refresh) — treat as success.
        if ((string) $order['payment_status'] === 'paid') {
            return ['ok' => true, 'tracking' => (string) $order['tracking_code']];
        }

        $gateway = $this->gatewayFor((string) $order['payment_method']);
        $result  = $gateway->verify($params, (int) $order['total']);

        if (!$result['ok']) {
            $this->payments->markFailed((int) $payment['id']);
            return ['ok' => false, 'error' => $result['error'] ?? 'پرداخت ناموفق بود.'];
        }

        $this->payments->markPaid((int) $payment['id'], (string) ($result['ref_id'] ?? ''));
        $this->settle((int) $order['id'], $order);

        // No tracking code yet — it is issued by the admin after the parcel ships.
        return ['ok' => true, 'tracking' => ''];
    }

    /**
     * Finalize a paid order: decrement stock, mark paid+processing, notify by
     * SMS. The postal tracking code is NOT generated here — the admin sends it
     * once the parcel actually ships.
     */
    private function settle(int $orderId, array $order): void
    {
        // Claim the unpaid→paid transition FIRST: if another callback (or a
        // concurrent admin confirm) already settled this order, do nothing —
        // otherwise stock would be decremented twice.
        if ($this->orders->markPaidProcessing($orderId) === 0) {
            return;
        }

        foreach ($this->orders->items($orderId) as $item) {
            if ($item['product_id'] !== null) {
                $this->products->decrementStock((int) $item['product_id'], (int) $item['qty']);
            }
            if ($item['variant_id'] !== null) {
                $this->products->decrementVariantStock((int) $item['variant_id'], (int) $item['qty']);
            }
        }

        $this->finalizePromotions(array_merge($order, ['id' => $orderId]));

        (new SmsManager())->sendTemplate(
            (string) $order['mobile'],
            'order_paid',
            ['order' => (string) $order['order_number'], 'tracking' => ''],
            "بهنام\nسفارش {$order['order_number']} با موفقیت پرداخت شد. ✅\nپس از ارسال مرسوله، کد رهگیری پستی برای شما پیامک می‌شود."
        );
    }

    /**
     * Record coupon consumption and award loyalty points once an order is
     * paid. Idempotent per order — safe from both the gateway callback and a
     * manual (card-to-card) confirmation in the admin panel.
     * @param array<string,mixed> $order  Must contain id, coupon_code, coupon_discount, user_id, subtotal.
     */
    public function finalizePromotions(array $order): void
    {
        $orderId = (int) ($order['id'] ?? 0);
        if ($orderId === 0) {
            return;
        }

        $code     = (string) ($order['coupon_code'] ?? '');
        $discount = (int) ($order['coupon_discount'] ?? 0);
        if ($code !== '' && $discount > 0) {
            $coupons = new CouponRepository();
            if (!$coupons->hasUsageForOrder($orderId)) {
                $coupon = $coupons->findByCode($code);
                if ($coupon !== null) {
                    $coupons->recordUsage(
                        (int) $coupon['id'],
                        !empty($order['user_id']) ? (int) $order['user_id'] : null,
                        $orderId,
                        $discount
                    );
                }
            }
        }
    }
}
