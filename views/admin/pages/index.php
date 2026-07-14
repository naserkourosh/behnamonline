<?php
/** @var list<array<string,mixed>> $items */
?>
<div class="mb-4 flex items-center justify-between">
    <span class="text-[13px] font-semibold text-[#555] nums"><?= fa(count($items)) ?> صفحه</span>
    <a href="<?= e(url('/admin/pages/create')) ?>" class="btn-primary px-5 py-2.5 text-[13px]">+ صفحه جدید</a>
</div>

<div class="overflow-hidden rounded-2xl border border-line2 bg-white">
    <div class="overflow-x-auto">
        <table class="w-full text-[12.5px]">
            <thead class="bg-surface text-[11.5px] text-[#888]">
                <tr>
                    <th class="p-3 text-right font-semibold">عنوان</th>
                    <th class="p-3 text-center font-semibold">آدرس</th>
                    <th class="p-3 text-center font-semibold">لینک فوتر</th>
                    <th class="p-3 text-center font-semibold">وضعیت</th>
                    <th class="p-3 text-center font-semibold">عملیات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line2">
                <?php if ($items === []): ?>
                    <tr><td colspan="5" class="p-6 text-center text-[#999]">هنوز صفحه‌ای ساخته نشده است.</td></tr>
                <?php endif; ?>
                <?php foreach ($items as $pg): ?>
                    <tr>
                        <td class="p-3 font-semibold text-[#333]"><?= e($pg['title']) ?></td>
                        <td class="p-3 text-center text-[#777]" dir="ltr">/page/<?= e($pg['slug']) ?></td>
                        <td class="p-3 text-center"><?= !empty($pg['show_in_footer']) ? '<span class="rounded-lg bg-pink px-2 py-0.5 text-[10.5px] font-bold text-secondary">در فوتر</span>' : '<span class="text-[#bbb]">—</span>' ?></td>
                        <td class="p-3 text-center">
                            <?php if (!empty($pg['is_active'])): ?>
                                <span class="rounded-lg bg-[#E8F6EE] px-2 py-0.5 text-[10.5px] font-bold text-[#1F9254]">فعال</span>
                            <?php else: ?>
                                <span class="rounded-lg bg-[#F5F5F5] px-2 py-0.5 text-[10.5px] font-bold text-[#999]">غیرفعال</span>
                            <?php endif; ?>
                        </td>
                        <td class="p-3 text-center">
                            <div class="flex items-center justify-center gap-3">
                                <a href="<?= e(url('/page/' . $pg['slug'])) ?>" target="_blank" class="text-[11.5px] text-mauve hover:underline">نمایش</a>
                                <a href="<?= e(url('/admin/pages/' . $pg['id'] . '/edit')) ?>" class="text-[11.5px] text-secondary hover:underline">ویرایش</a>
                                <form method="post" action="<?= e(url('/admin/pages/' . $pg['id'] . '/delete')) ?>" class="js-confirm inline" data-confirm="حذف این صفحه؟"><?= csrf_field() ?><button class="text-[11.5px] text-danger hover:underline">حذف</button></form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
