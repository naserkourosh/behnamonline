<?php
/** @var array<string,mixed>|null $item */
$isEdit = $item !== null;
$action = $isEdit ? url('/admin/shipping/' . $item['id']) : url('/admin/shipping');
$inp = 'w-full rounded-xl2 border border-line bg-white px-3.5 py-2.5 text-[13px] outline-none focus:border-secondary';
$lbl = 'mb-1.5 block text-[12px] font-semibold text-[#666]';
$v = static fn (string $k, $d = '') => e($item[$k] ?? $d);
$ltr = ' dir="ltr" ';
$cityVal = (string) ($item['city'] ?? '');
$isDefault = $cityVal === '*';
?>
<form method="post" action="<?= e($action) ?>" class="max-w-2xl">
    <?= csrf_field() ?>
    <div class="mb-4 flex items-center justify-between">
        <a href="<?= e(url('/admin/shipping')) ?>" class="text-[12px] text-mauve">‹ بازگشت</a>
        <button class="btn-primary px-6 py-2.5 text-[13px]"><?= $isEdit ? 'ذخیره تغییرات' : 'ایجاد روش' ?></button>
    </div>

    <div class="space-y-4 rounded-2xl border border-line2 bg-white p-5">
        <div>
            <label class="<?= $lbl ?>">شهر</label>
            <input name="city" value="<?= $isDefault ? '' : e($cityVal) ?>" class="<?= $inp ?>" placeholder="خالی = سراسری (همه شهرها)">
            <p class="mt-1 text-[10.5px] text-[#999]">برای روش سراسری این فیلد را خالی بگذارید. برای روش ویژه‌ی یک شهر، نام دقیق شهر را وارد کنید (مثلاً گرگان).</p>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div><label class="<?= $lbl ?>">نام روش *</label><input name="method_label" value="<?= $v('method_label') ?>" class="<?= $inp ?>" placeholder="پست پیشتاز" required></div>
            <div><label class="<?= $lbl ?>">کلید (لاتین) *</label><input name="method_key" value="<?= $v('method_key') ?>" <?= $ltr ?> class="<?= $inp ?> text-left" placeholder="post" required></div>
        </div>
        <div><label class="<?= $lbl ?>">توضیح</label><input name="note" value="<?= $v('note') ?>" class="<?= $inp ?>" placeholder="۲ تا ۳ روز کاری"></div>
        <div class="grid grid-cols-2 gap-4">
            <div><label class="<?= $lbl ?>">هزینه (تومان)</label><input name="cost" value="<?= $v('cost', '0') ?>" <?= $ltr ?> class="<?= $inp ?> text-left"></div>
            <div><label class="<?= $lbl ?>">ارسال رایگان از (تومان)</label><input name="free_over" value="<?= $v('free_over') ?>" <?= $ltr ?> class="<?= $inp ?> text-left" placeholder="خالی = بدون آستانه"></div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div><label class="<?= $lbl ?>">ترتیب</label><input name="sort" value="<?= $v('sort', '0') ?>" <?= $ltr ?> class="<?= $inp ?> text-left"></div>
            <label class="flex items-end gap-2 pb-2.5"><input type="checkbox" name="is_active" value="1" class="h-5 w-5 accent-secondary" <?= ($item['is_active'] ?? 1) ? 'checked' : '' ?>><span class="text-[13px] text-[#555]">فعال</span></label>
        </div>
    </div>
</form>
