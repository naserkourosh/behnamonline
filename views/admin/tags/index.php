<?php /** @var list<array<string,mixed>> $items @var list<string> $groups */
$inp = 'rounded-xl2 border border-line bg-white px-3.5 py-2.5 text-[13px] outline-none focus:border-secondary';

// Group tags by their tag_group for display.
$grouped = [];
foreach ($items as $t) {
    $g = trim((string) ($t['tag_group'] ?? '')) ?: 'بدون گروه';
    $grouped[$g][] = $t;
}
?>
<datalist id="tag-groups"><?php foreach ($groups as $g): ?><option value="<?= e($g) ?>"></option><?php endforeach; ?></datalist>

<div class="grid gap-5 lg:grid-cols-3">
    <!-- Add form -->
    <div class="lg:col-span-1">
        <form method="post" action="<?= e(url('/admin/tags')) ?>" class="rounded-2xl border border-line2 bg-white p-5">
            <?= csrf_field() ?>
            <h3 class="mb-3 text-[14px] font-bold text-[#333]">افزودن برچسب</h3>
            <input name="name" placeholder="نام برچسب" class="<?= $inp ?> mb-3 w-full" required>
            <input name="tag_group" list="tag-groups" placeholder="گروه/دسته برچسب (اختیاری)" class="<?= $inp ?> mb-3 w-full">
            <p class="mb-3 text-[11px] leading-6 text-[#aaa]">با گروه‌بندی برچسب‌ها (مثلاً «نوع پوست»، «برند»، «رنگ») هنگام زیاد شدن، سریع‌تر پیدا می‌شوند.</p>
            <button class="btn-primary w-full py-2.5 text-[13px]">افزودن</button>
        </form>
    </div>

    <!-- Grouped list -->
    <div class="space-y-4 lg:col-span-2">
        <?php if ($items === []): ?>
            <div class="rounded-2xl border border-line2 bg-white p-8 text-center text-[#999]">برچسبی وجود ندارد.</div>
        <?php endif; ?>
        <?php foreach ($grouped as $groupName => $rows): ?>
            <div class="overflow-hidden rounded-2xl border border-line2 bg-white">
                <div class="flex items-center justify-between border-b border-line bg-surface px-4 py-2.5">
                    <span class="text-[12.5px] font-bold text-secondary"><?= e($groupName) ?></span>
                    <span class="text-[11px] text-[#999] nums"><?= fa(count($rows)) ?> برچسب</span>
                </div>
                <table class="w-full text-[12.5px]">
                    <tbody>
                        <?php foreach ($rows as $t): ?>
                            <tr class="border-b border-line2 last:border-0">
                                <td class="p-3">
                                    <form method="post" action="<?= e(url('/admin/tags/' . $t['id'])) ?>" class="flex flex-wrap items-center gap-2">
                                        <?= csrf_field() ?>
                                        <input name="name" value="<?= e($t['name']) ?>" class="<?= $inp ?> w-36 py-1.5">
                                        <input name="tag_group" list="tag-groups" value="<?= e($t['tag_group'] ?? '') ?>" placeholder="گروه" class="<?= $inp ?> w-28 py-1.5">
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
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>
    </div>
</div>
