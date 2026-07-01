<?php /** @var list<array<string,mixed>> $items */ ?>
<div class="mb-4 flex items-center justify-between">
    <span class="text-[12px] text-[#999] nums"><?= fa(count($items)) ?> برند</span>
    <a href="<?= e(url('/admin/brands/create')) ?>" class="btn-primary px-5 py-2.5 text-[13px]">+ افزودن برند</a>
</div>
<div class="overflow-hidden rounded-2xl border border-line2 bg-white">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[520px] text-[12.5px]">
            <thead>
                <tr class="border-b border-line bg-surface text-[#888]">
                    <th class="p-3 text-right font-semibold">برند</th>
                    <th class="p-3 font-semibold">نامک</th>
                    <th class="p-3 font-semibold">محصولات</th>
                    <th class="p-3 font-semibold">وضعیت</th>
                    <th class="p-3 font-semibold">عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $b): ?>
                    <tr class="border-b border-line2 last:border-0">
                        <td class="p-3">
                            <div class="flex items-center gap-3">
                                <?php if (!empty($b['logo'])): ?><img src="<?= e(asset((string) $b['logo'])) ?>" alt="" class="h-9 w-9 flex-none rounded-lg object-contain"><?php else: ?><span class="flex h-9 w-9 flex-none items-center justify-center rounded-lg bg-surface text-[12px] text-secondary"><?= e(mb_substr($b['name'], 0, 1)) ?></span><?php endif; ?>
                                <span class="font-semibold text-[#333]"><?= e($b['name']) ?></span>
                            </div>
                        </td>
                        <td class="p-3 text-center text-[#999]" dir="ltr"><?= e($b['slug']) ?></td>
                        <td class="p-3 text-center nums"><?= fa((int) $b['product_count']) ?></td>
                        <td class="p-3 text-center"><?= (int) $b['is_active'] ? '<span class="rounded-lg bg-[#E7F7F0] px-2 py-1 text-[10px] font-bold text-success">فعال</span>' : '<span class="rounded-lg bg-surface px-2 py-1 text-[10px] font-bold text-[#999]">غیرفعال</span>' ?></td>
                        <td class="p-3">
                            <div class="flex items-center justify-center gap-2">
                                <a href="<?= e(url('/admin/brands/' . $b['id'] . '/edit')) ?>" class="text-secondary hover:underline">ویرایش</a>
                                <form method="post" action="<?= e(url('/admin/brands/' . $b['id'] . '/delete')) ?>" class="js-confirm inline" data-confirm="حذف این برند؟"><?= csrf_field() ?><button class="text-danger hover:underline">حذف</button></form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($items === []): ?><tr><td colspan="5" class="p-8 text-center text-[#999]">برندی وجود ندارد.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
