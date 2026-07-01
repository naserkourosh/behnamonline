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

        $ship = $this->shipping->resolve((string) $address['city'], $shippingKey, (int) $summary['subtotal']);
        if ($ship === null) {
            return ['ok' => false, 'message' => 'روش ارسال نامعتبر است.'];
        }

        $subtotal = (int) $summary['subtotal'];
        $total    = $subtotal + $ship['cost'];

        $orderId = $this->orders->create([
            'order_number'   => 'BH-TMP',
            'user_id'        => $userId,
            'status'         => 'processing',
            'subtotal'       => $subtotal,
            'discount'       => (int) $summary['savings'],
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

            if ($item['variant_id'] !== null) {
                $this->products->decrementVariantStock((int) $item['variant_id'], (int) $item['qty']);
            }
            $this->products->decrementStock((int) $item['product_id'], (int) $item['qty']);
        }

        // Mock payment gateway — auto-approve.
        $this->orders->markPaid($orderId);
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
}
