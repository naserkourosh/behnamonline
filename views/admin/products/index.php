<?php
/** @var list<array<string,mixed>> $items @var int $total @var int $page @var int $pages @var string $search */
?>
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <form method="get" action="<?= e(url('/admin/products')) ?>" class="flex items-center gap-2">
        <input name="q" value="<?= e($search) ?>" placeholder="جستجوی نام یا SKU…" class="w-56 rounded-xl2 border border-line bg-white px-4 py-2 text-[13px] outline-none focus:border-secondary">
        <button class="rounded-xl2 bg-surface px-4 py-2 text-[13px] font-semibold text-secondary">جستجو</button>
        <span class="text-[12px] text-[#999] nums"><?= fa($total) ?> محصول</span>
    </form>
    <a href="<?= e(url('/admin/products/create')) ?>" class="btn-primary px-5 py-2.5 text-[13px]">+ افزودن محصول</a>
</div>

<div class="overflow-hidden rounded-2xl border border-line2 bg-white">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[720px] text-[12.5px]">
            <thead>
                <tr class="border-b border-line bg-surface text-[#888]">
                    <th class="p-3 text-right font-semibold">محصول</th>
                    <th class="p-3 font-semibold">دسته</th>
                    <th class="p-3 font-semibold">برند</th>
                    <th class="p-3 font-semibold">قیمت</th>
                    <th class="p-3 font-semibold">موجودی</th>
                    <th class="p-3 font-semibold">وضعیت</th>
                    <th class="p-3 font-semibold">عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $p): ?>
                    <tr class="border-b border-line2 last:border-0 hover:bg-surface/50">
                        <td class="p-3">
                            <div class="flex items-center gap-3">
                                <img src="<?= e(asset((string) ($p['image'] ?: 'assets/images/placeholder-product.svg'))) ?>" alt="" class="h-11 w-11 flex-none rounded-lg object-cover">
                                <a href="<?= e(url('/admin/products/' . $p['id'] . '/edit')) ?>" class="font-semibold text-[#333] hover:text-secondary"><?= e($p['name']) ?></a>
                            </div>
                        </td>
                        <td class="p-3 text-center text-[#666]"><?= e($p['category_name'] ?: '—') ?></td>
                        <td class="p-3 text-center text-[#666]"><?= e($p['brand_name'] ?: '—') ?></td>
                        <td class="p-3 text-center font-bold text-secondary nums"><?= money((int) $p['price']) ?></td>
                        <td class="p-3 text-center"><span class="nums <?= (int) $p['stock'] <= 0 ? 'text-danger' : ((int) $p['stock'] <= 5 ? 'text-warning' : 'text-[#444]') ?>"><?= fa((int) $p['stock']) ?></span></td>
                        <td class="p-3 text-center">
                            <?php if ((int) $p['is_active']): ?><span class="rounded-lg bg-[#E7F7F0] px-2 py-1 text-[10px] font-bold text-success">فعال</span><?php else: ?><span class="rounded-lg bg-surface px-2 py-1 text-[10px] font-bold text-[#999]">پیش‌نویس</span><?php endif; ?>
                        </td>
                        <td class="p-3">
                            <div class="flex items-center justify-center gap-2">
                                <a href="<?= e(url('/admin/products/' . $p['id'] . '/edit')) ?>" class="text-secondary hover:underline">ویرایش</a>
                                <form method="post" action="<?= e(url('/admin/products/' . $p['id'] . '/delete')) ?>" class="js-confirm inline" data-confirm="حذف این محصول؟">
                                    <?= csrf_field() ?><button class="text-danger hover:underline">حذف</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($items === []): ?>
                    <tr><td colspan="7" class="p-8 text-center text-[#999]">محصولی یافت نشد.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $this->partial('admin/pagination', ['page' => $page, 'pages' => $pages, 'baseUrl' => url('/admin/products') . '?q=' . urlencode($search) . '&']); ?>
