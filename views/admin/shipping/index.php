<?php
/** @var list<array<string,mixed>> $items */
/** @var array<string,mixed> $settings */
$inp = 'w-full rounded-xl2 border border-line bg-white px-3.5 py-2.5 text-[13px] outline-none focus:border-secondary';
$lbl = 'mb-1.5 block text-[12px] font-semibold text-[#666]';
?>
<!-- Shipping settings -->
<form method="post" action="<?= e(url('/admin/shipping/settings')) ?>" class="mb-5 rounded-2xl border border-line2 bg-white p-5">
    <?= csrf_field() ?>
    <div class="mb-4 flex items-center justify-between">
        <h2 class="text-[14px] font-bold text-[#333]">تنظیمات ارسال</h2>
        <button class="btn-primary px-5 py-2 text-[12.5px]">ذخیره تنظیمات</button>
    </div>
    <div class="grid gap-4 md:grid-cols-2">
        <div class="space-y-3">
            <label class="flex items-center justify-between rounded-xl2 border border-line2 px-3.5 py-2.5">
                <span class="text-[12.5px] text-[#555]">ارسال با پست فعال باشد</span>
                <input type="checkbox" name="shipping_post_enabled" value="1" class="h-5 w-5 accent-secondary" <?= $settings['post_enabled'] ? 'checked' : '' ?>>
            </label>
            <label class="flex items-center justify-between rounded-xl2 border border-line2 px-3.5 py-2.5">
                <span class="text-[12.5px] text-[#555]">پس‌کرایه (هزینهٔ پست هنگام تحویل)</span>
                <input type="checkbox" name="shipping_collect_enabled" value="1" class="h-5 w-5 accent-secondary" <?= $settings['collect_enabled'] ? 'checked' : '' ?>>
            </label>
            <label class="flex items-center justify-between rounded-xl2 border border-line2 px-3.5 py-2.5">
                <span class="text-[12.5px] text-[#555]">نمایش پیش‌بینی زمان تحویل</span>
                <input type="checkbox" name="shipping_eta_enabled" value="1" class="h-5 w-5 accent-secondary" <?= $settings['eta_enabled'] ? 'checked' : '' ?>>
            </label>
        </div>
        <div class="space-y-3">
            <div><label class="<?= $lbl ?>">زمان تحویل گرگان (پیک)</label><input name="shipping_eta_gorgan" value="<?= e((string) $settings['eta_gorgan']) ?>" class="<?= $inp ?>" placeholder="کمتر از یک روز کاری"></div>
            <div><label class="<?= $lbl ?>">زمان تحویل سایر شهرها (پست)</label><input name="shipping_eta_default" value="<?= e((string) $settings['eta_default']) ?>" class="<?= $inp ?>" placeholder="۲ تا ۴ روز کاری"></div>
        </div>
    </div>
    <p class="mt-3 text-[11px] leading-6 text-[#999]">«ارسال با پست» روش پیش‌پرداخت (پست پیشتاز) و «پس‌کرایه» روش پرداخت هنگام تحویل است. این دو مستقل‌اند و می‌توانند هم‌زمان فعال باشند؛ برای شهرهای غیر از گرگان حداقل یکی باید فعال باشد تا مشتری بتواند سفارش را ثبت کند. ارسال پستی فقط یک روش دارد (پست پیشتاز).</p>
</form>

<div class="mb-4 flex items-center justify-between">
    <span class="text-[13px] font-semibold text-[#555] nums"><?= fa(count($items)) ?> روش پیک شهری</span>
    <a href="<?= e(url('/admin/shipping/create')) ?>" class="btn-primary px-5 py-2.5 text-[13px]">+ روش جدید</a>
</div>

<div class="mb-4 rounded-2xl border border-line2 bg-[#EEF2FF] px-4 py-3 text-[12px] leading-6 text-[#4b5563]">
    هزینهٔ پست به‌صورت خودکار از وب‌سرویس پست ملی محاسبه می‌شود. روش‌های زیر اضافه می‌شوند: با «شهر خاص» فقط برای همان شهر و به‌جای پست نمایش داده می‌شوند (مثل پیک ویژهٔ گرگان)، و با «سراسری» (فیلد شهر خالی) در کنار پست برای همهٔ شهرها نشان داده می‌شوند.
</div>

<div class="overflow-hidden rounded-2xl border border-line2 bg-white">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[760px] text-[12.5px]">
            <thead>
                <tr class="border-b border-line bg-surface text-[#888]">
                    <th class="p-3 text-right font-semibold">شهر</th>
                    <th class="p-3 text-right font-semibold">روش ارسال</th>
                    <th class="p-3 font-semibold">هزینه</th>
                    <th class="p-3 font-semibold">رایگان از</th>
                    <th class="p-3 font-semibold">وضعیت</th>
                    <th class="p-3 font-semibold">عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $z): $isDefault = (string) $z['city'] === '*'; ?>
                    <tr class="border-b border-line2 last:border-0 hover:bg-surface/50">
                        <td class="p-3">
                            <?php if ($isDefault): ?><span class="rounded-lg bg-[#EEF2FF] px-2 py-1 text-[11px] font-bold text-secondary">سراسری</span>
                            <?php else: ?><span class="font-semibold text-[#444]"><?= e($z['city']) ?></span><?php endif; ?>
                        </td>
                        <td class="p-3">
                            <a href="<?= e(url('/admin/shipping/' . $z['id'] . '/edit')) ?>" class="font-bold text-secondary"><?= e($z['method_label']) ?></a>
                            <div class="text-[10.5px] text-[#999]" dir="ltr"><?= e($z['method_key']) ?><?= !empty($z['note']) ? ' — ' . e((string) $z['note']) : '' ?></div>
                        </td>
                        <td class="p-3 text-center font-semibold text-[#333] nums"><?= (int) $z['cost'] > 0 ? money((int) $z['cost']) . ' ت' : 'رایگان' ?></td>
                        <td class="p-3 text-center text-[#666] nums"><?= $z['free_over'] !== null ? money((int) $z['free_over']) . ' ت' : '—' ?></td>
                        <td class="p-3 text-center">
                            <?php if ((int) $z['is_active']): ?><span class="rounded-lg bg-[#E7F7F0] px-2 py-1 text-[10px] font-bold text-success">فعال</span>
                            <?php else: ?><span class="rounded-lg bg-[#FDECEC] px-2 py-1 text-[10px] font-bold text-danger">غیرفعال</span><?php endif; ?>
                        </td>
                        <td class="p-3">
                            <div class="flex items-center justify-center gap-2.5">
                                <a href="<?= e(url('/admin/shipping/' . $z['id'] . '/edit')) ?>" class="text-secondary hover:underline">ویرایش</a>
                                <form method="post" action="<?= e(url('/admin/shipping/' . $z['id'] . '/delete')) ?>" class="js-confirm inline" data-confirm="حذف این روش ارسال؟"><?= csrf_field() ?><button class="text-danger hover:underline">حذف</button></form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($items === []): ?><tr><td colspan="6" class="p-8 text-center text-[#999]">هنوز روشی تعریف نشده است.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
