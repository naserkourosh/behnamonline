<?php
/** @var array<string,mixed>|null $coupon */
$isEdit = $coupon !== null;
$action = $isEdit ? url('/admin/coupons/' . $coupon['id']) : url('/admin/coupons');
$inp = 'w-full rounded-xl2 border border-line bg-white px-3.5 py-2.5 text-[13px] outline-none focus:border-secondary';
$lbl = 'mb-1.5 block text-[12px] font-semibold text-[#666]';
$v = static fn (string $k, $d = '') => e($coupon[$k] ?? $d);
$ltr = ' dir="ltr" ';
?>
<form method="post" action="<?= e($action) ?>">
    <?= csrf_field() ?>
    <div class="mb-4 flex items-center justify-between">
        <a href="<?= e(url('/admin/coupons')) ?>" class="text-[12px] text-mauve">‹ بازگشت</a>
        <button class="btn-primary px-6 py-2.5 text-[13px]"><?= $isEdit ? 'ذخیره تغییرات' : 'ایجاد کد' ?></button>
    </div>

    <div class="grid gap-5 lg:grid-cols-2">
        <div class="rounded-2xl border border-line2 bg-white p-5">
            <h3 class="mb-4 text-[14px] font-bold text-[#333]">تخفیف</h3>
            <div class="mb-3"><label class="<?= $lbl ?>">کد تخفیف *</label><input name="code" value="<?= $v('code') ?>" <?= $ltr ?> class="<?= $inp ?> text-left uppercase" placeholder="مثلاً NOWRUZ1404" required></div>
            <div class="mb-3"><label class="<?= $lbl ?>">توضیح (اختیاری)</label><input name="description" value="<?= $v('description') ?>" class="<?= $inp ?>"></div>
            <div class="mb-3 grid grid-cols-2 gap-3">
                <div><label class="<?= $lbl ?>">نوع</label>
                    <select name="type" class="<?= $inp ?>">
                        <option value="percent" <?= ($coupon['type'] ?? 'percent') === 'percent' ? 'selected' : '' ?>>درصدی</option>
                        <option value="fixed" <?= ($coupon['type'] ?? '') === 'fixed' ? 'selected' : '' ?>>مبلغ ثابت (تومان)</option>
                    </select>
                </div>
                <div><label class="<?= $lbl ?>">مقدار *</label><input name="value" value="<?= $v('value') ?>" <?= $ltr ?> class="<?= $inp ?> text-left" placeholder="درصد یا تومان" required></div>
            </div>
            <div><label class="<?= $lbl ?>">حداکثر تخفیف (سقف، برای درصدی)</label><input name="max_discount" value="<?= $v('max_discount') ?>" <?= $ltr ?> class="<?= $inp ?> text-left" placeholder="تومان — خالی = بدون سقف"></div>
        </div>

        <div class="rounded-2xl border border-line2 bg-white p-5">
            <h3 class="mb-4 text-[14px] font-bold text-[#333]">شرایط و محدودیت</h3>
            <div class="mb-3"><label class="<?= $lbl ?>">حداقل مبلغ سبد (تومان)</label><input name="min_cart" value="<?= $v('min_cart', '0') ?>" <?= $ltr ?> class="<?= $inp ?> text-left"></div>
            <div class="mb-3 grid grid-cols-2 gap-3">
                <div><label class="<?= $lbl ?>">سقف کل استفاده</label><input name="usage_limit" value="<?= $v('usage_limit') ?>" <?= $ltr ?> class="<?= $inp ?> text-left" placeholder="خالی = نامحدود"></div>
                <div><label class="<?= $lbl ?>">سقف هر کاربر</label><input name="per_user_limit" value="<?= $v('per_user_limit') ?>" <?= $ltr ?> class="<?= $inp ?> text-left" placeholder="خالی = نامحدود"></div>
            </div>
            <div class="mb-3 grid grid-cols-2 gap-3">
                <div><label class="<?= $lbl ?>">شروع <span class="font-normal text-[#aaa]">(خالی = بدون محدودیت)</span></label><input name="starts_at" value="<?= $v('starts_at') ?>" <?= $ltr ?> class="js-jdate <?= $inp ?> text-left"></div>
                <div><label class="<?= $lbl ?>">پایان</label><input name="ends_at" value="<?= $v('ends_at') ?>" <?= $ltr ?> class="js-jdate <?= $inp ?> text-left"></div>
            </div>
            <label class="flex items-center justify-between pt-1"><span class="text-[12.5px] text-[#555]">فعال</span><input type="checkbox" name="is_active" value="1" class="h-5 w-5 accent-secondary" <?= ($coupon['is_active'] ?? 1) ? 'checked' : '' ?>></label>
        </div>
    </div>
</form>
