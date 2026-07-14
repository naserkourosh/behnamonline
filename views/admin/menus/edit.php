<?php
/** @var array<string,mixed> $menu @var list<array<string,mixed>> $items @var list<array<string,mixed>> $categories */
$inp = 'w-full rounded-xl2 border border-line bg-white px-3.5 py-2.5 text-[13px] outline-none focus:border-secondary';
$lbl = 'mb-1.5 block text-[12px] font-semibold text-[#666]';

// Group items by parent for the nested list + parent options.
$byParent = [];
foreach ($items as $i) {
    $byParent[(int) ($i['parent_id'] ?? 0)][] = $i;
}
$parentOptions = [];
$collect = function (int $pid, int $depth) use (&$collect, &$parentOptions, $byParent): void {
    foreach ($byParent[$pid] ?? [] as $i) {
        $parentOptions[] = ['id' => (int) $i['id'], 'label' => str_repeat('— ', $depth) . $i['label']];
        if ($depth < 1) { // items up to level 2 can be parents (max 3 levels)
            $collect((int) $i['id'], $depth + 1);
        }
    }
};
$collect(0, 0);
?>
<a href="<?= e(url('/admin/menus')) ?>" class="mb-4 inline-block text-[12px] text-mauve">‹ بازگشت به منوها</a>
<div class="grid gap-5 lg:grid-cols-2">
    <!-- Add item -->
    <form method="post" action="<?= e(url('/admin/menus/' . $menu['id'] . '/items')) ?>" class="rounded-2xl border border-line2 bg-white p-5">
        <?= csrf_field() ?>
        <h3 class="mb-3 text-[14px] font-bold text-[#333]">افزودن آیتم</h3>
        <div class="mb-3"><label class="<?= $lbl ?>">افزودن سریع از دسته‌بندی</label>
            <select class="js-menu-cat <?= $inp ?>"><option value="">— انتخاب دسته —</option>
                <?php foreach ($categories as $c): ?><option value="/category/<?= e($c['slug']) ?>" data-label="<?= e($c['name']) ?>"><?= e($c['name']) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3"><label class="<?= $lbl ?>">عنوان *</label><input name="label" class="js-menu-label <?= $inp ?>" required></div>
        <div class="mb-3"><label class="<?= $lbl ?>">لینک (URL)</label><input name="url" class="js-menu-url <?= $inp ?> text-left" dir="ltr" placeholder="/category/makeup یا https://…"></div>
        <div class="mb-3">
            <label class="<?= $lbl ?>">زیرمجموعهٔ (والد)</label>
            <select name="parent_id" class="<?= $inp ?>">
                <option value="0">— آیتم سطح اول —</option>
                <?php foreach ($parentOptions as $po): ?>
                    <option value="<?= $po['id'] ?>"><?= e($po['label']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <label class="mb-4 flex items-center justify-between rounded-xl2 border border-line px-3.5 py-2.5">
            <span class="text-[12.5px] text-[#555]">مگا منو <span class="block text-[10.5px] text-[#aaa]">فقط برای آیتم سطح اول — زیرمنوها به‌صورت پنل عریض ستونی نمایش داده می‌شوند</span></span>
            <input type="checkbox" name="is_mega" value="1" class="h-5 w-5 flex-none accent-secondary">
        </label>
        <button class="btn-primary w-full py-2.5 text-[13px]">افزودن به منو</button>
        <p class="mt-2 text-[10.5px] leading-5 text-[#aaa]">ساختار: آیتم سطح اول ← زیرمنو (سطح دوم) ← زیرِ زیرمنو (سطح سوم، لینک‌های ستون‌های مگا منو).</p>
    </form>

    <!-- Items list (nested) -->
    <div class="rounded-2xl border border-line2 bg-white p-5">
        <h3 class="mb-3 text-[14px] font-bold text-[#333]">آیتم‌های منو</h3>
        <?php if ($items === []): ?>
            <p class="text-[12px] text-[#999]">هنوز آیتمی اضافه نشده است.</p>
        <?php else: ?>
            <div class="space-y-2">
                <?php
                $renderItem = function (array $it, int $depth) use (&$renderItem, $byParent): void { ?>
                    <div class="flex items-center justify-between rounded-xl2 border border-line px-3.5 py-2.5" style="margin-inline-start: <?= $depth * 22 ?>px">
                        <div>
                            <div class="text-[13px] font-semibold text-[#333]">
                                <?= $depth > 0 ? '<span class="text-[#bbb]">↳</span> ' : '' ?><?= e($it['label']) ?>
                                <?php if (!empty($it['is_mega'])): ?><span class="ms-1 rounded-lg bg-pink px-2 py-0.5 text-[10px] font-bold text-secondary">مگا</span><?php endif; ?>
                            </div>
                            <div class="text-[11px] text-[#999]" dir="ltr"><?= e($it['url']) ?></div>
                        </div>
                        <form method="post" action="<?= e(url('/admin/menus/items/' . $it['id'] . '/delete')) ?>" class="js-confirm" data-confirm="حذف این آیتم (زیرمنوهایش هم حذف می‌شوند)؟"><?= csrf_field() ?><button class="text-[11.5px] text-danger hover:underline">حذف</button></form>
                    </div>
                    <?php foreach ($byParent[(int) $it['id']] ?? [] as $child) { $renderItem($child, $depth + 1); }
                };
                foreach ($byParent[0] ?? [] as $top) { $renderItem($top, 0); }
                ?>
            </div>
        <?php endif; ?>
    </div>
</div>
