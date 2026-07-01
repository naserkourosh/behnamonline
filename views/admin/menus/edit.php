<?php
/** @var array<string,mixed> $menu @var list<array<string,mixed>> $items @var list<array<string,mixed>> $categories */
$inp = 'w-full rounded-xl2 border border-line bg-white px-3.5 py-2.5 text-[13px] outline-none focus:border-secondary';
$lbl = 'mb-1.5 block text-[12px] font-semibold text-[#666]';
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
        <div class="mb-4"><label class="<?= $lbl ?>">لینک (URL)</label><input name="url" class="js-menu-url <?= $inp ?> text-left" dir="ltr" placeholder="/category/makeup یا https://…"></div>
        <button class="btn-primary w-full py-2.5 text-[13px]">افزودن به منو</button>
    </form>

    <!-- Items list -->
    <div class="rounded-2xl border border-line2 bg-white p-5">
        <h3 class="mb-3 text-[14px] font-bold text-[#333]">آیتم‌های منو</h3>
        <?php if ($items === []): ?>
            <p class="text-[12px] text-[#999]">هنوز آیتمی اضافه نشده است.</p>
        <?php else: ?>
            <div class="space-y-2">
                <?php foreach ($items as $it): ?>
                    <div class="flex items-center justify-between rounded-xl2 border border-line px-3.5 py-2.5">
                        <div>
                            <div class="text-[13px] font-semibold text-[#333]"><?= e($it['label']) ?></div>
                            <div class="text-[11px] text-[#999]" dir="ltr"><?= e($it['url']) ?></div>
                        </div>
                        <form method="post" action="<?= e(url('/admin/menus/items/' . $it['id'] . '/delete')) ?>" class="js-confirm" data-confirm="حذف این آیتم؟"><?= csrf_field() ?><button class="text-[11.5px] text-danger hover:underline">حذف</button></form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
