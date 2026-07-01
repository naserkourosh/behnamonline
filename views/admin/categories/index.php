<?php /** @var list<array<string,mixed>> $items */ ?>
<div class="mb-4 flex items-center justify-between">
    <span class="text-[12px] text-[#999] nums"><?= fa(count($items)) ?> دسته‌بندی</span>
    <a href="<?= e(url('/admin/categories/create')) ?>" class="btn-primary px-5 py-2.5 text-[13px]">+ افزودن دسته</a>
</div>
<div class="overflow-hidden rounded-2xl border border-line2 bg-white">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[600px] text-[12.5px]">
            <thead>
                <tr class="border-b border-line bg-surface text-[#888]">
                    <th class="p-3 text-right font-semibold">نام</th>
                    <th class="p-3 font-semibold">نامک</th>
                    <th class="p-3 font-semibold">والد</th>
                    <th class="p-3 font-semibold">محصولات</th>
                    <th class="p-3 font-semibold">وضعیت</th>
                    <th class="p-3 font-semibold">عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $c): ?>
                    <tr class="border-b border-line2 last:border-0">
                        <td class="p-3">
                            <div class="flex items-center gap-3">
                                <img src="<?= e(asset((string) ($c['image'] ?: 'assets/images/placeholder-category.svg'))) ?>" alt="" class="h-9 w-9 flex-none rounded-full object-cover">
                                <span class="font-semibold text-[#333]"><?= e($c['name']) ?></span>
                            </div>
                        </td>
                        <td class="p-3 text-center text-[#999]" dir="ltr"><?= e($c['slug']) ?></td>
                        <td class="p-3 text-center text-[#666]"><?= e($c['parent_name'] ?: '—') ?></td>
                        <td class="p-3 text-center nums"><?= fa((int) $c['product_count']) ?></td>
                        <td class="p-3 text-center"><?= (int) $c['is_active'] ? '<span class="rounded-lg bg-[#E7F7F0] px-2 py-1 text-[10px] font-bold text-success">فعال</span>' : '<span class="rounded-lg bg-surface px-2 py-1 text-[10px] font-bold text-[#999]">غیرفعال</span>' ?></td>
                        <td class="p-3">
                            <div class="flex items-center justify-center gap-2">
                                <a href="<?= e(url('/admin/categories/' . $c['id'] . '/edit')) ?>" class="text-secondary hover:underline">ویرایش</a>
                                <form method="post" action="<?= e(url('/admin/categories/' . $c['id'] . '/delete')) ?>" class="js-confirm inline" data-confirm="حذف این دسته؟"><?= csrf_field() ?><button class="text-danger hover:underline">حذف</button></form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($items === []): ?><tr><td colspan="6" class="p-8 text-center text-[#999]">دسته‌بندی‌ای وجود ندارد.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
