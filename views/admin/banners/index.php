<?php
/** @var list<array<string,mixed>> $items */
$placeLabels = ['hero' => 'اسلایدر اصلی', 'promo' => 'باکس تبلیغاتی', 'strip' => 'نوار'];
?>
<div class="mb-4 flex items-center justify-between">
    <span class="text-[13px] font-semibold text-[#555] nums"><?= fa(count($items)) ?> بنر</span>
    <a href="<?= e(url('/admin/banners/create')) ?>" class="btn-primary px-5 py-2.5 text-[13px]">+ بنر جدید</a>
</div>

<div class="overflow-hidden rounded-2xl border border-line2 bg-white">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[720px] text-[12.5px]">
            <thead>
                <tr class="border-b border-line bg-surface text-[#888]">
                    <th class="p-3 text-right font-semibold">بنر</th>
                    <th class="p-3 font-semibold">جایگاه</th>
                    <th class="p-3 font-semibold">ترتیب</th>
                    <th class="p-3 font-semibold">زمان‌بندی</th>
                    <th class="p-3 font-semibold">وضعیت</th>
                    <th class="p-3 font-semibold">عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $b):
                    $expired = !empty($b['ends_at']) && $b['ends_at'] < date('Y-m-d H:i:s'); ?>
                    <tr class="border-b border-line2 last:border-0 hover:bg-surface/50">
                        <td class="p-3">
                            <div class="flex items-center gap-3">
                                <div class="h-11 w-16 flex-none overflow-hidden rounded-lg bg-surface" <?= empty($b['image']) && !empty($b['bg_color']) ? 'style="background:' . e((string) $b['bg_color']) . '"' : '' ?>>
                                    <?php if (!empty($b['image'])): ?><img src="<?= e(asset((string) $b['image'])) ?>" alt="" class="h-full w-full object-cover"><?php endif; ?>
                                </div>
                                <div>
                                    <a href="<?= e(url('/admin/banners/' . $b['id'] . '/edit')) ?>" class="font-bold text-secondary"><?= e($b['title']) ?></a>
                                    <?php if (!empty($b['subtitle'])): ?><div class="text-[10.5px] text-[#999]"><?= e($b['subtitle']) ?></div><?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td class="p-3 text-center text-[#666]"><?= e($placeLabels[(string) $b['placement']] ?? $b['placement']) ?></td>
                        <td class="p-3 text-center text-[#666] nums"><?= fa((int) $b['sort']) ?></td>
                        <td class="p-3 text-center text-[10.5px] text-[#999] nums"><?= $b['ends_at'] ? jdate((string) $b['ends_at']) : 'بدون محدودیت' ?></td>
                        <td class="p-3 text-center">
                            <?php if ($expired): ?><span class="rounded-lg bg-surface px-2 py-1 text-[10px] font-bold text-[#999]">منقضی</span>
                            <?php elseif ((int) $b['is_active']): ?><span class="rounded-lg bg-[#E7F7F0] px-2 py-1 text-[10px] font-bold text-success">فعال</span>
                            <?php else: ?><span class="rounded-lg bg-[#FDECEC] px-2 py-1 text-[10px] font-bold text-danger">غیرفعال</span><?php endif; ?>
                        </td>
                        <td class="p-3">
                            <div class="flex items-center justify-center gap-2.5">
                                <a href="<?= e(url('/admin/banners/' . $b['id'] . '/edit')) ?>" class="text-secondary hover:underline">ویرایش</a>
                                <form method="post" action="<?= e(url('/admin/banners/' . $b['id'] . '/delete')) ?>" class="js-confirm inline" data-confirm="حذف این بنر؟"><?= csrf_field() ?><button class="text-danger hover:underline">حذف</button></form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($items === []): ?><tr><td colspan="6" class="p-8 text-center text-[#999]">هنوز بنری تعریف نشده است.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
