<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;

/**
 * Turns the current cart into a paid order: snapshots totals & address,
 * decrements stock, (mock) settles payment, and clears the cart.
 * Real payment gateways arrive in Phase 3 — here payment is auto-approved.
 */
final class CheckoutService
{
    private OrderRepository $orders;
    private ProductRepository $products;
    private CartService $cart;
    private ShippingService $shipping;

    public function __construct()
    {
        $this->orders   = new OrderRepository();
        $this->products = new ProductRepository();
        $this->cart     = new CartService();
        $this->shipping = new ShippingService();
    }

    /**
     * @param array<string,mixed> $address  receiver_name, mobile, province, city, address, postal_code
     * @return array{ok:bool,message?:string,order?:array<string,mixed>}
     */
    public function place(int $userId, array $address, string $shippingKey, string $paymentMethod): array
    {
        $summary = $this->cart->summary();
        if ((int) $summary['count'] === 0) {
            return ['ok' => false, 'message' => 'سبد خرید شما خالی است.'];
        }

        $ship = $this->shipping->resolve(
            (string) ($address['province'] ?? ''),
            (string) $address['city'],
            $shippingKey,
            (int) $summary['subtotal'],
            $this->cart->parcel()
        );
        if ($ship === null) {
            return ['ok' => false, 'message' => 'روش ارسال نامعتبر است.'];
        }

        $subtotal = (int) $summary['subtotal'];

        // Re-validate any applied coupon now that the customer is known
        // (enforces per-user limits). Drop it silently if no longer valid.
        $couponCode     = $this->cart->appliedCouponCode();
        $couponDiscount = 0;
        if ($couponCode !== null) {
            $res = (new CouponService())->validate($couponCode, $subtotal, $userId);
            if ($res['ok']) {
                $couponDiscount = (int) $res['discount'];
            } else {
                $couponCode = null;
            }
        }

        $total = max(0, $subtotal - $couponDiscount + $ship['cost']);

        // Create the order as PENDING/UNPAID. Stock is decremented and the
        // order settled only after the payment gateway confirms (PaymentService).
        $orderId = $this->orders->create([
            'order_number'   => 'BH-TMP',
            'user_id'        => $userId,
            'status'         => 'pending',
            'subtotal'       => $subtotal,
            'discount'       => (int) $summary['savings'],
            'coupon_code'    => $couponCode,
            'coupon_discount'=> $couponDiscount,
            'shipping_cost'  => $ship['cost'],
            'total'          => $total,
            'shipping_method'=> $ship['label'],
            'payment_method' => $paymentMethod,
            'payment_status' => 'unpaid',
            'receiver_name'  => $address['receiver_name'],
            'mobile'         => $address['mobile'],
            'province'       => $address['province'],
            'city'           => $address['city'],
            'address'        => $address['address'],
            'postal_code'    => $address['postal_code'] ?? null,
            'note'           => $address['note'] ?? null,
        ]);

        $this->orders->setNumber($orderId, 'BH-' . (10000 + $orderId));

        foreach ($summary['items'] as $item) {
            $this->orders->addItem($orderId, [
                'product_id'    => $item['product_id'],
                'variant_id'    => $item['variant_id'],
                'name'          => $item['name'],
                'variant_label' => $item['variant_label'],
                'qty'           => $item['qty'],
                'unit_price'    => $item['unit_price'],
                'line_total'    => $item['line_total'],
            ]);
        }

        // Notify the customer that the order is placed and awaiting payment,
        // embedding their name and the cart items (before the cart is cleared).
        $this->sendReadySms($address, $summary['items'], 'BH-' . (10000 + $orderId));

        // The cart has been converted into an order; clear it. Payment retries
        // operate on the order, not the cart.
        $this->cart->clear();

        return [
            'ok'    => true,
            'order' => [
                'id'     => $orderId,
                'number' => 'BH-' . (10000 + $orderId),
                'total'  => $total,
            ],
        ];
    }

    /**
     * SMS the customer that their order is placed and ready for payment.
     * @param array<string,mixed> $address
     * @param list<array<string,mixed>> $items
     */
    private function sendReadySms(array $address, array $items, string $orderNumber): void
    {
        $name = trim((string) ($address['receiver_name'] ?? '')) ?: 'مشتری';

        $names = [];
        foreach ($items as $it) {
            $names[] = (string) $it['name'] . ' ×' . fa((int) $it['qty']);
        }
        $products = implode('، ', $names);

        $message = (new \App\Repositories\SmsTemplateRepository())->render(
            'order_ready',
            ['name' => $name, 'order' => $orderNumber, 'products' => $products],
            "کاربر گرامی {$name}،\nسفارش شما شامل {$products} ثبت شد و آمادهٔ پرداخت است.\nبهنام"
        );

        (new \App\Services\Sms\SmsManager())->send((string) ($address['mobile'] ?? ''), $message, 'order');
    }
}
