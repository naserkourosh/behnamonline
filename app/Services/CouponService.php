<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\CouponRepository;

/**
 * Validates discount codes and computes the money they take off a cart.
 * Stateless: the applied code lives in the session (CartService reads it);
 * everything here re-validates against the live cart total and the user.
 */
final class CouponService
{
    private CouponRepository $coupons;

    public function __construct()
    {
        $this->coupons = new CouponRepository();
    }

    /**
     * Validate a code against a subtotal (and optionally a user) and compute
     * the discount. When $userId is null, per-user limits are not enforced yet
     * (checkout re-validates once the customer is known).
     *
     * @return array{ok:bool,discount:int,message:string,coupon?:array<string,mixed>}
     */
    public function validate(string $code, int $subtotal, ?int $userId = null): array
    {
        $code   = strtoupper(trim($code));
        $coupon = $code !== '' ? $this->coupons->findByCode($code) : null;

        if ($coupon === null) {
            return ['ok' => false, 'discount' => 0, 'message' => 'کد تخفیف نامعتبر است.'];
        }
        if ((int) $coupon['is_active'] !== 1) {
            return ['ok' => false, 'discount' => 0, 'message' => 'این کد تخفیف غیرفعال است.'];
        }

        $now = date('Y-m-d H:i:s');
        if (!empty($coupon['starts_at']) && $now < $coupon['starts_at']) {
            return ['ok' => false, 'discount' => 0, 'message' => 'زمان استفاده از این کد هنوز فرا نرسیده است.'];
        }
        if (!empty($coupon['ends_at']) && $now > $coupon['ends_at']) {
            return ['ok' => false, 'discount' => 0, 'message' => 'مهلت استفاده از این کد به پایان رسیده است.'];
        }
        if ($coupon['usage_limit'] !== null && (int) $coupon['used_count'] >= (int) $coupon['usage_limit']) {
            return ['ok' => false, 'discount' => 0, 'message' => 'ظرفیت استفاده از این کد تکمیل شده است.'];
        }
        if ((int) $coupon['min_cart'] > 0 && $subtotal < (int) $coupon['min_cart']) {
            $need = money((int) $coupon['min_cart']);
            return ['ok' => false, 'discount' => 0, 'message' => "حداقل مبلغ سبد برای این کد {$need} تومان است."];
        }
        if ($userId !== null && $coupon['per_user_limit'] !== null
            && $this->coupons->usageCountForUser((int) $coupon['id'], $userId) >= (int) $coupon['per_user_limit']) {
            return ['ok' => false, 'discount' => 0, 'message' => 'شما پیش‌تر از این کد استفاده کرده‌اید.'];
        }

        $discount = $this->discountFor($coupon, $subtotal);
        if ($discount <= 0) {
            return ['ok' => false, 'discount' => 0, 'message' => 'این کد برای سبد فعلی تخفیفی ندارد.'];
        }

        return [
            'ok'       => true,
            'discount' => $discount,
            'message'  => 'کد تخفیف اعمال شد.',
            'coupon'   => $coupon,
        ];
    }

    /** @param array<string,mixed> $coupon */
    public function discountFor(array $coupon, int $subtotal): int
    {
        if ((string) $coupon['type'] === 'percent') {
            $discount = (int) floor($subtotal * (int) $coupon['value'] / 100);
            if ($coupon['max_discount'] !== null && (int) $coupon['max_discount'] > 0) {
                $discount = min($discount, (int) $coupon['max_discount']);
            }
        } else {
            $discount = (int) $coupon['value'];
        }
        return max(0, min($discount, $subtotal));
    }
}
