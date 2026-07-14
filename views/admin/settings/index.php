<?php
/** @var array<string,string> $fields */
$inp = 'w-full rounded-xl2 border border-line bg-white px-3.5 py-2.5 text-[13px] outline-none focus:border-secondary';
$lbl = 'mb-1.5 block text-[12px] font-semibold text-[#666]';
$labels = [
    'brand_name'              => 'نام فروشگاه',
    'announcement_text'       => 'متن نوار اعلان بالای سایت',
    'show_announcement'       => 'نمایش نوار اعلان',
    'free_shipping_threshold' => 'حداقل مبلغ ارسال رایگان (تومان)',
    'low_stock_threshold'     => 'آستانه هشدار «تنها N عدد» (برای محصولات با کنترل موجودی)',
    'show_ratings'            => 'نمایش امتیاز ستاره‌ای محصولات',
    'about_text'              => 'متن صفحهٔ «درباره ما»',
    'contact_phone'           => 'تلفن تماس',
    'contact_email'           => 'ایمیل',
    'contact_instagram'       => 'اینستاگرام (نام کاربری)',
    'contact_address'         => 'آدرس فروشگاه',
    'flash_sale_ends_at'      => 'پایان پیشنهاد شگفت‌انگیز (YYYY-MM-DD HH:MM:SS)',
    'chat_enabled'            => 'فعال‌سازی گفتگوی آنلاین (چت پشتیبانی)',
    'trust_badge_1_code'      => 'نماد اعتماد ۱ — کد HTML (مثلاً کد اینماد را اینجا بچسبانید)',
    'trust_badge_2_code'      => 'نماد اعتماد ۲ — کد HTML (مثلاً ساماندهی)',
    'trust_badge_3_code'      => 'نماد اعتماد ۳ — کد HTML (اختیاری)',
    'trust_badge_4_code'      => 'نماد اعتماد ۴ — کد HTML (اختیاری)',
];
?>
<form method="post" action="<?= e(url('/admin/settings')) ?>" class="max-w-2xl">
    <?= csrf_field() ?>
    <div class="mb-4 flex items-center justify-between">
        <h2 class="text-[15px] font-bold text-[#333]">تنظیمات عمومی</h2>
        <button class="btn-primary px-6 py-2.5 text-[13px]">ذخیره تنظیمات</button>
    </div>
    <div class="space-y-4 rounded-2xl border border-line2 bg-white p-5">
        <?php foreach ($fields as $key => $type):
            $current = setting($key, ''); ?>
            <?php if ($type === 'bool'): ?>
                <label class="flex items-center justify-between">
                    <span class="text-[13px] text-[#555]"><?= e($labels[$key] ?? $key) ?></span>
                    <input type="checkbox" name="<?= e($key) ?>" value="1" class="h-5 w-5 accent-secondary" <?= $current ? 'checked' : '' ?>>
                </label>
            <?php else: ?>
                <div>
                    <label class="<?= $lbl ?>"><?= e($labels[$key] ?? $key) ?></label>
                    <?php if ($key === 'announcement_text' || $type === 'text'): ?>
                        <textarea name="<?= e($key) ?>" rows="<?= $type === 'text' ? 4 : 2 ?>" class="<?= $inp ?>"><?= e((string) $current) ?></textarea>
                    <?php elseif ($key === 'flash_sale_ends_at'): ?>
                        <input name="<?= e($key) ?>" value="<?= e((string) $current) ?>" dir="ltr" class="js-jdatetime <?= $inp ?> text-left">
                    <?php else: ?>
                        <input name="<?= e($key) ?>" value="<?= e((string) $current) ?>" class="<?= $inp ?> <?= $type === 'int' ? 'text-left' : '' ?>" <?= $type === 'int' ? 'dir="ltr"' : '' ?>>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</form>
