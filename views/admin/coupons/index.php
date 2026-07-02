<?php
/** @var list<array<string,mixed>> $items */
?>
<div class="mb-4 flex items-center justify-between">
    <span class="text-[13px] font-semibold text-[#555] nums"><?= fa(count($items)) ?> کد تخفیف</span>
    <a href="<?= e(url('/admin/coupons/create')) ?>" class="btn-primary px-5 py-2.5 text-[13px]">+ کد جدید</a>
</div>

<div class="overflow-hidden rounded-2xl border border-line2 bg-white">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[760px] text-[12.5px]">
            <thead>
                <tr class="border-b border-line bg-surface text-[#888]">
                    <th class="p-3 text-right font-semibold">کد</th>
                    <th class="p-3 font-semibold">تخفیف</th>
                    <th class="p-3 font-semibold">حداقل سبد</th>
                    <th class="p-3 font-semibold">استفاده</th>
                    <th class="p-3 font-semibold">مهلت</th>
                    <th class="p-3 font-semibold">وضعیت</th>
                    <th class="p-3 font-semibold">عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $c):
                    $val = (string) $c['type'] === 'percent' ? fa((int) $c['value']) . '٪' : money((int) $c['value']) . ' ت';
                    $limit = $c['usage_limit'] !== null ? fa((int) $c['used_count']) . ' / ' . fa((int) $c['usage_limit']) : fa((int) $c['used_count']) . ' / ∞';
                    $expired = !empty($c['ends_at']) && $c['ends_at'] < date('Y-m-d H:i:s');
                    ?>
                    <tr class="border-b border-line2 last:border-0 hover:bg-surface/50">
                        <td class="p-3">
                            <a href="<?= e(url('/admin/coupons/' . $c['id'] . '/edit')) ?>" class="font-bold text-secondary" dir="ltr"><?= e($c['code']) ?></a>
                            <?php if (!empty($c['description'])): ?><div class="text-[10.5px] text-[#999]"><?= e($c['description']) ?></div><?php endif; ?>
                        </td>
                        <td class="p-3 text-center font-semibold text-[#333] nums"><?= $val ?><?php if ((string) $c['type'] === 'percent' && $c['max_discount']): ?><div class="text-[9.5px] text-[#aaa]">سقف <?= money((int) $c['max_discount']) ?></div><?php endif; ?></td>
                        <td class="p-3 text-center text-[#666] nums"><?= (int) $c['min_cart'] > 0 ? money((int) $c['min_cart']) : '—' ?></td>
                        <td class="p-3 text-center text-[#666] nums"><?= $limit ?></td>
                        <td class="p-3 text-center text-[10.5px] text-[#999] nums"><?= $c['ends_at'] ? jdate((string) $c['ends_at']) : 'بدون محدودیت' ?></td>
                        <td class="p-3 text-center">
                            <?php if ($expired): ?><span class="rounded-lg bg-surface px-2 py-1 text-[10px] font-bold text-[#999]">منقضی</span>
                            <?php elseif ((int) $c['is_active']): ?><span class="rounded-lg bg-[#E7F7F0] px-2 py-1 text-[10px] font-bold text-success">فعال</span>
                            <?php else: ?><span class="rounded-lg bg-[#FDECEC] px-2 py-1 text-[10px] font-bold text-danger">غیرفعال</span><?php endif; ?>
                        </td>
                        <td class="p-3">
                            <div class="flex items-center justify-center gap-2.5">
                                <a href="<?= e(url('/admin/coupons/' . $c['id'] . '/edit')) ?>" class="text-secondary hover:underline">ویرایش</a>
                                <form method="post" action="<?= e(url('/admin/coupons/' . $c['id'] . '/delete')) ?>" class="js-confirm inline" data-confirm="حذف این کد؟"><?= csrf_field() ?><button class="text-danger hover:underline">حذف</button></form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($items === []): ?><tr><td colspan="7" class="p-8 text-center text-[#999]">هنوز کد تخفیفی تعریف نشده است.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
