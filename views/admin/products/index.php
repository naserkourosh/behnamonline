<?php
/** @var list<array<string,mixed>> $items @var int $total @var int $page @var int $pages */
/** @var array{q:string,category:int,brand:int,tag:int,stock:string} $filters */
/** @var list<array<string,mixed>> $categories @var list<array<string,mixed>> $brands @var list<array<string,mixed>> $tags */
$sel = 'rounded-xl2 border border-line bg-white px-3 py-2 text-[12.5px] outline-none focus:border-secondary';
$hasFilter = $filters['q'] !== '' || $filters['category'] || $filters['brand'] || $filters['tag'] || $filters['stock'] !== '';
?>
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <span class="text-[13px] font-semibold text-[#555]"><?= fa($total) ?> محصول</span>
    <a href="<?= e(url('/admin/products/create')) ?>" class="btn-primary px-5 py-2.5 text-[13px]">+ افزودن محصول</a>
</div>

<form method="get" action="<?= e(url('/admin/products')) ?>" class="mb-4 flex flex-wrap items-center gap-2 rounded-2xl border border-line2 bg-white p-3">
    <input name="q" value="<?= e($filters['q']) ?>" placeholder="جستجوی نام یا SKU…" class="w-48 rounded-xl2 border border-line bg-white px-3.5 py-2 text-[13px] outline-none focus:border-secondary">
    <select name="category" class="<?= $sel ?>">
        <option value="">همهٔ دسته‌ها</option>
        <?php foreach ($categories as $c): ?>
            <option value="<?= (int) $c['id'] ?>" <?= $filters['category'] === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
        <?php endforeach; ?>
    </select>
    <select name="brand" class="<?= $sel ?>">
        <option value="">همهٔ برندها</option>
        <?php foreach ($brands as $b): ?>
            <option value="<?= (int) $b['id'] ?>" <?= $filters['brand'] === (int) $b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option>
        <?php endforeach; ?>
    </select>
    <?php if ($tags !== []): ?>
    <select name="tag" class="<?= $sel ?>">
        <option value="">همهٔ برچسب‌ها</option>
        <?php foreach ($tags as $t): ?>
            <option value="<?= (int) $t['id'] ?>" <?= $filters['tag'] === (int) $t['id'] ? 'selected' : '' ?>><?= e($t['name']) ?></option>
        <?php endforeach; ?>
    </select>
    <?php endif; ?>
    <select name="stock" class="<?= $sel ?>">
        <option value="">موجودی: همه</option>
        <option value="in"  <?= $filters['stock'] === 'in'  ? 'selected' : '' ?>>موجود</option>
        <option value="out" <?= $filters['stock'] === 'out' ? 'selected' : '' ?>>ناموجود</option>
    </select>
    <button class="rounded-xl2 bg-secondary px-4 py-2 text-[12.5px] font-semibold text-white">اعمال فیلتر</button>
    <?php if ($hasFilter): ?>
        <a href="<?= e(url('/admin/products')) ?>" class="rounded-xl2 bg-surface px-4 py-2 text-[12.5px] font-semibold text-[#777]">حذف فیلترها</a>
    <?php endif; ?>
</form>

<div class="overflow-hidden rounded-2xl border border-line2 bg-white">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[820px] text-[12.5px]">
            <thead>
                <tr class="border-b border-line bg-surface text-[#888]">
                    <th class="p-3 text-right font-semibold">محصول</th>
                    <th class="p-3 font-semibold">دسته</th>
                    <th class="p-3 font-semibold">برند</th>
                    <th class="p-3 font-semibold">قیمت</th>
                    <th class="p-3 font-semibold">موجودی</th>
                    <th class="p-3 font-semibold">ترتیب</th>
                    <th class="p-3 font-semibold">وضعیت</th>
                    <th class="p-3 font-semibold">عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $p): ?>
                    <tr class="border-b border-line2 last:border-0 hover:bg-surface/50">
                        <td class="p-3">
                            <div class="flex items-center gap-3">
                                <img src="<?= e(asset((string) ($p['image'] ?: 'assets/images/placeholder-product.svg'))) ?>" alt="" class="h-11 w-11 flex-none rounded-lg bg-white object-contain">
                                <a href="<?= e(url('/admin/products/' . $p['id'] . '/edit')) ?>" class="font-semibold text-[#333] hover:text-secondary"><?= e($p['name']) ?></a>
                            </div>
                        </td>
                        <td class="p-3 text-center text-[#666]"><?= e($p['category_name'] ?: '—') ?></td>
                        <td class="p-3 text-center text-[#666]"><?= e($p['brand_name'] ?: '—') ?></td>
                        <td class="p-3 text-center font-bold text-secondary nums"><?= money((int) $p['price']) ?></td>
                        <td class="p-3 text-center">
                            <?php if (!empty($p['is_out_of_stock'])): ?>
                                <span class="rounded-lg bg-[#FDECEC] px-2 py-1 text-[10px] font-bold text-danger">اتمام موجودی</span>
                            <?php elseif (!empty($p['track_stock'])): ?>
                                <span class="nums <?= (int) $p['stock'] <= 0 ? 'font-bold text-danger' : ((int) $p['stock'] <= 5 ? 'text-warning' : 'text-[#444]') ?>" title="کنترل موجودی فعال"><?= fa((int) $p['stock']) ?></span>
                            <?php else: ?>
                                <span class="text-[13px] text-[#bbb]" title="بدون کنترل موجودی — فروش آزاد">∞</span>
                            <?php endif; ?>
                        </td>
                        <td class="p-3 text-center">
                            <input type="number" value="<?= (int) $p['sort'] ?>" data-url="<?= e(url('/admin/products/' . $p['id'] . '/sort')) ?>" class="js-sort-input w-16 rounded-lg border border-line bg-surface px-2 py-1 text-center text-[12px] nums outline-none focus:border-secondary" title="ترتیب نمایش">
                        </td>
                        <td class="p-3 text-center">
                            <?php if ((int) $p['is_active']): ?><span class="rounded-lg bg-[#E7F7F0] px-2 py-1 text-[10px] font-bold text-success">فعال</span><?php else: ?><span class="rounded-lg bg-surface px-2 py-1 text-[10px] font-bold text-[#999]">پیش‌نویس</span><?php endif; ?>
                        </td>
                        <td class="p-3">
                            <div class="flex items-center justify-center gap-2.5">
                                <a href="<?= e(url('/product/' . $p['slug'])) ?>" target="_blank" rel="noopener" class="text-mauve hover:underline" title="مشاهده در سایت">نمایش ↗</a>
                                <a href="<?= e(url('/admin/products/' . $p['id'] . '/edit')) ?>" class="text-secondary hover:underline">ویرایش</a>
                                <form method="post" action="<?= e(url('/admin/products/' . $p['id'] . '/delete')) ?>" class="js-confirm inline" data-confirm="حذف این محصول؟">
                                    <?= csrf_field() ?><button class="text-danger hover:underline">حذف</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($items === []): ?>
                    <tr><td colspan="8" class="p-8 text-center text-[#999]">محصولی یافت نشد.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
$qs = http_build_query(array_filter([
    'q' => $filters['q'], 'category' => $filters['category'] ?: '', 'brand' => $filters['brand'] ?: '',
    'tag' => $filters['tag'] ?: '', 'stock' => $filters['stock'],
]));
$this->partial('admin/pagination', ['page' => $page, 'pages' => $pages, 'baseUrl' => url('/admin/products') . '?' . $qs . '&']);
?>
