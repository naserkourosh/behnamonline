<?php
/** @var list<array<string,mixed>> $items */
$freqLabel = ['once_session' => 'یک‌بار در نشست', 'once_day' => 'روزی یک‌بار', 'always' => 'همیشه'];
?>
<div class="mb-4 flex items-center justify-between">
    <span class="text-[13px] font-semibold text-[#555] nums"><?= fa(count($items)) ?> پاپ‌آپ</span>
    <a href="<?= e(url('/admin/popups/create')) ?>" class="btn-primary px-5 py-2.5 text-[13px]">+ پاپ‌آپ جدید</a>
</div>

<div class="overflow-hidden rounded-2xl border border-line2 bg-white">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[680px] text-[12.5px]">
            <thead>
                <tr class="border-b border-line bg-surface text-[#888]">
                    <th class="p-3 text-right font-semibold">عنوان</th>
                    <th class="p-3 font-semibold">نمایش در</th>
                    <th class="p-3 font-semibold">تکرار</th>
                    <th class="p-3 font-semibold">تأخیر</th>
                    <th class="p-3 font-semibold">وضعیت</th>
                    <th class="p-3 font-semibold">عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $p): ?>
                    <tr class="border-b border-line2 last:border-0 hover:bg-surface/50">
                        <td class="p-3"><a href="<?= e(url('/admin/popups/' . $p['id'] . '/edit')) ?>" class="font-semibold text-[#333] hover:text-secondary"><?= e($p['title']) ?></a></td>
                        <td class="p-3 text-center text-[#666]" dir="ltr"><?= e($p['target']) ?></td>
                        <td class="p-3 text-center text-[#666]"><?= e($freqLabel[$p['frequency']] ?? $p['frequency']) ?></td>
                        <td class="p-3 text-center text-[#666] nums"><?= fa((int) $p['delay_seconds']) ?> ثانیه</td>
                        <td class="p-3 text-center"><?= (int) $p['is_active'] ? '<span class="rounded-lg bg-[#E7F7F0] px-2 py-1 text-[10px] font-bold text-success">فعال</span>' : '<span class="rounded-lg bg-surface px-2 py-1 text-[10px] font-bold text-[#999]">غیرفعال</span>' ?></td>
                        <td class="p-3">
                            <div class="flex items-center justify-center gap-2.5">
                                <a href="<?= e(url('/admin/popups/' . $p['id'] . '/edit')) ?>" class="text-secondary hover:underline">ویرایش</a>
                                <form method="post" action="<?= e(url('/admin/popups/' . $p['id'] . '/delete')) ?>" class="js-confirm inline" data-confirm="حذف این پاپ‌آپ؟"><?= csrf_field() ?><button class="text-danger hover:underline">حذف</button></form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($items === []): ?><tr><td colspan="6" class="p-8 text-center text-[#999]">هنوز پاپ‌آپی تعریف نشده است.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
