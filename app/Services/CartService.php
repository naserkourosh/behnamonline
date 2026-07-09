<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Session;
use App\Repositories\CartRepository;
use App\Repositories\ProductRepository;

/**
 * Guest cart keyed by a random token stored in an http-only cookie.
 * Handles add/update/remove with stock clamping and computes the
 * money summary (gross, savings, shipping, total, free-ship progress).
 */
final class CartService
{
    private const COOKIE       = 'behnam_cart';
    private const COUPON_KEY   = 'cart_coupon';

    private CartRepository $carts;
    private ProductRepository $products;

    public function __construct()
    {
        $this->carts    = new CartRepository();
        $this->products = new ProductRepository();
    }

    /** @return array{ok:bool,message?:string,summary:array<string,mixed>} */
    public function add(int $productId, ?int $variantId, int $qty): array
    {
        $qty     = max(1, $qty);
        $product = $this->products->findActive($productId);
        if ($product === null) {
            return ['ok' => false, 'message' => 'محصول یافت نشد.', 'summary' => $this->summary()];
        }

        // Manual «اتمام موجودی» flag blocks the sale unconditionally.
        if (!empty($product['is_out_of_stock'])) {
            return ['ok' => false, 'message' => 'موجودی این محصول به پایان رسیده است.', 'summary' => $this->summary()];
        }

        // Counts only govern when this product's «کنترل موجودی انبار» is on.
        $tracked   = !empty($product['track_stock']);
        $unitPrice = (int) $product['price'];
        $available = $tracked ? (int) $product['stock'] - (int) $product['reserved'] : PHP_INT_MAX;

        if ($variantId !== null) {
            $variant = $this->products->findVariant($variantId, $productId);
            if ($variant === null) {
                return ['ok' => false, 'message' => 'گزینه انتخابی نامعتبر است.', 'summary' => $this->summary()];
            }
            if ($variant['price_override'] !== null) {
                $unitPrice = (int) $variant['price_override'];
            }
            $available = $tracked ? (int) $variant['stock'] : PHP_INT_MAX;
        }

        if ($available <= 0) {
            return ['ok' => false, 'message' => 'موجودی این محصول به پایان رسیده است.', 'summary' => $this->summary()];
        }

        $cartId = $this->resolveCartId(true);
        $line   = $this->carts->findLine($cartId, $productId, $variantId);
        $target = ($line ? (int) $line['qty'] : 0) + $qty;
        $target = min($target, $available);

        if ($line) {
            $this->carts->setQty((int) $line['id'], $cartId, $target);
        } else {
            $this->carts->addLine($cartId, $productId, $variantId, $target, $unitPrice);
        }
        $this->carts->touch($cartId);

        return ['ok' => true, 'message' => 'به سبد خرید اضافه شد.', 'summary' => $this->summary()];
    }

    /** @return array{ok:bool,summary:array<string,mixed>} */
    public function updateQty(int $lineId, int $qty): array
    {
        $cartId = $this->resolveCartId(false);
        if ($cartId === 0) {
            return ['ok' => false, 'summary' => $this->summary()];
        }

        $qty = max(0, $qty);
        foreach ($this->carts->items($cartId) as $item) {
            if ((int) $item['id'] !== $lineId) {
                continue;
            }
            if ($qty === 0) {
                $this->carts->removeLine($lineId, $cartId);
                break;
            }
            $available = (empty($item['track_stock']) || $item['variant_id'] !== null)
                ? PHP_INT_MAX // untracked product / variant validated on add
                : (int) $item['stock'] - (int) $item['reserved'];
            $this->carts->setQty($lineId, $cartId, min($qty, max(1, $available)));
            break;
        }
        $this->carts->touch($cartId);

        return ['ok' => true, 'summary' => $this->summary()];
    }

    /** @return array{ok:bool,summary:array<string,mixed>} */
    public function remove(int $lineId): array
    {
        $cartId = $this->resolveCartId(false);
        if ($cartId !== 0) {
            $this->carts->removeLine($lineId, $cartId);
            $this->carts->touch($cartId);
        }
        return ['ok' => true, 'summary' => $this->summary()];
    }

    /** @return array<string,mixed> */
    public function summary(): array
    {
        $cartId = $this->resolveCartId(false);
        $items  = $cartId === 0 ? [] : $this->carts->items($cartId);

        $gross   = 0;
        $net     = 0;
        $savings = 0;
        $count   = 0;
        $lines   = [];

        foreach ($items as $it) {
            $qty       = (int) $it['qty'];
            $unitPrice = (int) $it['unit_price'];
            $lineNet   = $unitPrice * $qty;
            $orig      = (int) ($it['old_price'] ?? 0);
            $base      = $orig > $unitPrice ? $orig : $unitPrice;

            $net     += $lineNet;
            $gross   += $base * $qty;
            $savings += ($base - $unitPrice) * $qty;
            $count   += $qty;

            $lines[] = [
                'id'            => (int) $it['id'],
                'product_id'    => (int) $it['product_id'],
                'variant_id'    => $it['variant_id'] !== null ? (int) $it['variant_id'] : null,
                'name'          => (string) $it['name'],
                'slug'          => (string) $it['slug'],
                'brand_name'    => (string) ($it['brand_name'] ?? ''),
                'variant_label' => $it['variant_label'] !== null ? (string) $it['variant_label'] : null,
                'image'         => (string) ($it['image'] ?? 'assets/images/placeholder-product.svg'),
                'image_alt'     => (string) ($it['image_alt'] ?? $it['name']),
                'qty'           => $qty,
                'unit_price'    => $unitPrice,
                'line_total'    => $lineNet,
            ];
        }

        // Shipping is NOT priced in the cart — it depends on the delivery
        // address (destination + parcel weight) and is computed at checkout.
        $threshold = (int) \setting('free_shipping_threshold', (int) \config('shipping.free_shipping_threshold', 500000));
        $shipping   = 0;
        $remaining  = max(0, $threshold - $net);
        $progress   = $threshold > 0 ? min(100, (int) round($net / $threshold * 100)) : 100;

        // Apply a session-stored discount code against the current subtotal.
        $couponCode     = null;
        $couponDiscount = 0;
        $couponError    = '';
        $applied = Session::get(self::COUPON_KEY);
        if (is_string($applied) && $applied !== '' && $count > 0) {
            $res = (new CouponService())->validate($applied, $net, AuthService::id());
            if ($res['ok']) {
                $couponCode     = $applied;
                $couponDiscount = (int) $res['discount'];
            } else {
                $couponError = $res['message'];
            }
        }

        return [
            'items'             => $lines,
            'count'             => $count,
            'gross'             => $gross,
            'savings'           => $savings,
            'subtotal'          => $net,
            'shipping'          => $shipping,
            'shipping_pending'  => $count > 0,
            'coupon_code'       => $couponCode,
            'coupon_discount'   => $couponDiscount,
            'coupon_error'      => $couponError,
            'total'             => max(0, $net - $couponDiscount),
            'free_threshold'    => $threshold,
            'free_remaining'    => $remaining,
            'free_progress'     => $progress,
            'qualifies_free'    => $count > 0 && $net >= $threshold,
        ];
    }

    /**
     * Store & validate a discount code for the current cart.
     * @return array{ok:bool,message:string,summary:array<string,mixed>}
     */
    public function applyCoupon(string $code): array
    {
        $subtotal = (int) $this->summary()['subtotal'];
        $res = (new CouponService())->validate($code, $subtotal, AuthService::id());
        if ($res['ok']) {
            Session::set(self::COUPON_KEY, strtoupper(trim($code)));
        }
        return ['ok' => $res['ok'], 'message' => $res['message'], 'summary' => $this->summary()];
    }

    /** @return array{ok:bool,summary:array<string,mixed>} */
    public function removeCoupon(): array
    {
        Session::forget(self::COUPON_KEY);
        return ['ok' => true, 'summary' => $this->summary()];
    }

    public function appliedCouponCode(): ?string
    {
        $c = Session::get(self::COUPON_KEY);
        return is_string($c) && $c !== '' ? strtoupper($c) : null;
    }

    public function clearCoupon(): void
    {
        Session::forget(self::COUPON_KEY);
    }

    public function count(): int
    {
        $summary = $this->summary();
        return (int) $summary['count'];
    }

    /**
     * Aggregate the cart into a shippable parcel for the postal-fee service.
     * Billable weight = max(actual weight, volumetric weight), where
     * volumetric grams = Σ(L×W×H cm)/6 (i.e. cm³ ÷ 6000 kg × 1000 g).
     *
     * @return array{weight_g:int,volumetric_g:int,billable_g:int,items:int}
     */
    public function parcel(): array
    {
        $cartId = $this->resolveCartId(false);
        $items  = $cartId === 0 ? [] : $this->carts->items($cartId);

        $weight = 0;
        $volume = 0;
        $count  = 0;
        foreach ($items as $it) {
            $qty = (int) $it['qty'];
            $weight += max(0, (int) ($it['weight_grams'] ?? 0)) * $qty;
            $l = max(0, (int) ($it['length_cm'] ?? 0));
            $w = max(0, (int) ($it['width_cm'] ?? 0));
            $h = max(0, (int) ($it['height_cm'] ?? 0));
            $volume += (int) round($l * $w * $h / 6) * $qty; // cm³ → volumetric grams
            $count  += $qty;
        }

        return [
            'weight_g'     => $weight,
            'volumetric_g' => $volume,
            'billable_g'   => max($weight, $volume),
            'items'        => $count,
        ];
    }

    /** Empty the current cart (after a successful checkout). */
    public function clear(): void
    {
        $cartId = $this->resolveCartId(false);
        if ($cartId !== 0) {
            $this->carts->clear($cartId);
        }
        $this->clearCoupon();
    }

    /**
     * Resolve the current cart id from the cookie. When $create is true a
     * new cart + cookie are issued if none exists. Returns 0 when absent.
     */
    private function resolveCartId(bool $create): int
    {
        $token = isset($_COOKIE[self::COOKIE]) && preg_match('/^[a-f0-9]{32}$/', (string) $_COOKIE[self::COOKIE])
            ? (string) $_COOKIE[self::COOKIE]
            : null;

        if ($token !== null) {
            $cart = $this->carts->findByToken($token);
            if ($cart !== null) {
                return (int) $cart['id'];
            }
        }

        if (!$create) {
            return 0;
        }

        $token = bin2hex(random_bytes(16));
        $this->setCookie($token);
        return $this->carts->create($token);
    }

    private function setCookie(string $token): void
    {
        $_COOKIE[self::COOKIE] = $token;
        setcookie(self::COOKIE, $token, [
            'expires'  => time() + 60 * 60 * 24 * 30,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
            'secure'   => (bool) \config('app.session.secure', false),
        ]);
    }
}
