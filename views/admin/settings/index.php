<?php
/** @var array<string,string> $fields */
$inp = 'w-full rounded-xl2 border border-line bg-white px-3.5 py-2.5 text-[13px] outline-none focus:border-secondary';
$lbl = 'mb-1.5 block text-[12px] font-semibold text-[#666]';
$labels = [
    'brand_name'              => 'نام فروشگاه',
    'announcement_text'       => 'متن نوار اعلان بالای سایت',
    'show_announcement'       => 'نمایش نوار اعلان',
    'free_shipping_threshold' => 'حداقل مبلغ ارسال رایگان (تومان)',
    'show_stock_qty'          => 'نمایش تعداد موجودی به مشتری',
    'low_stock_threshold'     => 'آستانه نمایش «تنها N عدد»',
    'flash_sale_ends_at'      => 'پایان پیشنهاد شگفت‌انگیز (YYYY-MM-DD HH:MM:SS)',
    'points_enabled'          => 'فعال‌سازی باشگاه مشتریان (امتیازدهی)',
    'points_earn_percent'     => 'درصد امتیاز از مبلغ هر سفارش',
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
                    <?php if ($key === 'announcement_text'): ?>
                        <textarea name="<?= e($key) ?>" rows="2" class="<?= $inp ?>"><?= e((string) $current) ?></textarea>
                    <?php else: ?>
                        <input name="<?= e($key) ?>" value="<?= e((string) $current) ?>" class="<?= $inp ?> <?= $type === 'int' ? 'text-left' : '' ?>" <?= $type === 'int' ? 'dir="ltr"' : '' ?>>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</form>
