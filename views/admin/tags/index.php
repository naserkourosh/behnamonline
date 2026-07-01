<?php /** @var list<array<string,mixed>> $items */
$inp = 'rounded-xl2 border border-line bg-white px-3.5 py-2.5 text-[13px] outline-none focus:border-secondary';
?>
<div class="grid gap-5 lg:grid-cols-3">
    <!-- Add form -->
    <div class="lg:col-span-1">
        <form method="post" action="<?= e(url('/admin/tags')) ?>" class="rounded-2xl border border-line2 bg-white p-5">
            <?= csrf_field() ?>
            <h3 class="mb-3 text-[14px] font-bold text-[#333]">افزودن برچسب</h3>
            <input name="name" placeholder="نام برچسب" class="<?= $inp ?> mb-3 w-full" required>
            <button class="btn-primary w-full py-2.5 text-[13px]">افزودن</button>
        </form>
    </div>
    <!-- List -->
    <div class="lg:col-span-2">
        <div class="overflow-hidden rounded-2xl border border-line2 bg-white">
            <table class="w-full text-[12.5px]">
                <thead>
                    <tr class="border-b border-line bg-surface text-[#888]">
                        <th class="p-3 text-right font-semibold">نام</th>
                        <th class="p-3 font-semibold">نامک</th>
                        <th class="p-3 font-semibold">محصولات</th>
                        <th class="p-3 font-semibold">عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $t): ?>
                        <tr class="border-b border-line2 last:border-0">
                            <td class="p-3">
                                <form method="post" action="<?= e(url('/admin/tags/' . $t['id'])) ?>" class="flex items-center gap-2">
                                    <?= csrf_field() ?>
                                    <input name="name" value="<?= e($t['name']) ?>" class="<?= $inp ?> w-40 py-1.5">
                                    <button class="text-[11px] text-secondary hover:underline">ذخیره</button>
                                </form>
                            </td>
                            <td class="p-3 text-center text-[#999]" dir="ltr"><?= e($t['slug']) ?></td>
                            <td class="p-3 text-center nums"><?= fa((int) $t['product_count']) ?></td>
                            <td class="p-3 text-center">
                                <form method="post" action="<?= e(url('/admin/tags/' . $t['id'] . '/delete')) ?>" class="js-confirm inline" data-confirm="حذف این برچسب؟"><?= csrf_field() ?><button class="text-danger hover:underline">حذف</button></form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if ($items === []): ?><tr><td colspan="4" class="p-8 text-center text-[#999]">برچسبی وجود ندارد.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
