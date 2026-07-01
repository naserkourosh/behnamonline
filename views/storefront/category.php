<?php
/** @var array<string,mixed>|null $category */
/** @var list<array<string,mixed>> $allCategories */
/** @var list<array<string,mixed>> $brands */
/** @var list<array<string,mixed>> $products */
/** @var int $total */
/** @var int $page */
/** @var bool $hasMore */
/** @var string $sort */
/** @var array<string,mixed> $filters */

$title    = $category['name'] ?? ((!empty($filters['search'])) ? 'جستجو: ' . $filters['search'] : 'همه محصولات');
$action   = $category ? url('/category/' . $category['slug']) : url('/category');
$basePath = '/' . trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/', '/');
$activeFilterCount = (int) (!empty($filters['min_price']) || !empty($filters['max_price']))
    + count((array) ($filters['brand_ids'] ?? []))
    + (int) !empty($filters['in_stock'])
    + (int) !empty($filters['min_rating']);

$this->meta([
    'title'       => $title . ' | بهنام',
    'description' => ($category['seo_description'] ?? null) ?: ('خرید ' . $title . ' اصل با ضمانت اصالت و ارسال سریع از فروشگاه بهنام.'),
]);

$sorts = [
    'default'     => 'پیشنهاد بهنام',
    'bestselling' => 'پرفروش‌ترین',
    'newest'      => 'جدیدترین',
    'price_asc'   => 'ارزان‌ترین',
    'price_desc'  => 'گران‌ترین',
    'rating'      => 'بیشترین امتیاز',
];

// Build hidden fields preserving filters when changing sort.
$hidden = '';
foreach (['q' => $filters['search'] ?? '', 'min_price' => $filters['min_price'] ?? '', 'max_price' => $filters['max_price'] ?? '', 'in_stock' => !empty($filters['in_stock']) ? '1' : '', 'min_rating' => $filters['min_rating'] ?? ''] as $k => $v) {
    if ($v !== '' && $v !== null) {
        $hidden .= '<input type="hidden" name="' . e($k) . '" value="' . e($v) . '">';
    }
}
foreach ((array) ($filters['brand_ids'] ?? []) as $bid) {
    $hidden .= '<input type="hidden" name="brand[]" value="' . (int) $bid . '">';
}
?>

<!-- Breadcrumb JSON-LD -->
<?php $this->push('json_ld', '<script type="application/ld+json">' . json_encode([
    '@context' => 'https://schema.org',
    '@type'    => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'خانه', 'item' => base_url() . '/'],
        ['@type' => 'ListItem', 'position' => 2, 'name' => $title],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>'); ?>

<div class="container-page py-5 md:py-8">
    <!-- breadcrumb + title -->
    <nav class="mb-3 text-[11px] text-mauve">
        <a href="<?= e(url('/')) ?>" class="hover:text-secondary">خانه</a>
        <span class="mx-1">/</span>
        <span class="text-[#777]"><?= e($title) ?></span>
    </nav>
    <div class="mb-4 flex items-end justify-between">
        <h1 class="text-[18px] font-bold text-secondary md:text-[24px]"><?= e($title) ?></h1>
        <span class="text-[11px] text-[#999] nums"><?= fa($total) ?> کالا</span>
    </div>

    <!-- category chips -->
    <div class="hscroll -mx-4 mb-5 flex gap-2 overflow-x-auto px-4 md:mx-0 md:px-0">
        <a href="<?= e(url('/category')) ?>" class="flex-none rounded-full border px-4 py-2 text-[12px] font-semibold <?= $category === null ? 'border-secondary bg-secondary text-white' : 'border-line bg-white text-secondary' ?>">همه</a>
        <?php foreach ($allCategories as $c): $on = $category && (int) $category['id'] === (int) $c['id']; ?>
            <a href="<?= e(url('/category/' . $c['slug'])) ?>" class="flex-none whitespace-nowrap rounded-full border px-4 py-2 text-[12px] font-semibold <?= $on ? 'border-secondary bg-secondary text-white' : 'border-line bg-white text-secondary' ?>"><?= e($c['name']) ?></a>
        <?php endforeach; ?>
    </div>

    <!-- toolbar -->
    <div class="mb-5 flex items-center gap-2.5">
        <button type="button" class="js-filter-open flex flex-1 items-center justify-center gap-2 rounded-xl2 border border-line py-2.5 text-[12.5px] font-semibold text-secondary md:hidden">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 6h16M7 12h10M10 18h4" stroke-linecap="round"/></svg>
            فیلترها
            <?php if ($activeFilterCount > 0): ?><span class="rounded-lg bg-secondary px-1.5 text-[9px] text-white nums"><?= fa($activeFilterCount) ?></span><?php endif; ?>
        </button>
        <form method="get" action="<?= e($action) ?>" class="flex flex-1 items-center gap-2 md:flex-none">
            <?= $hidden ?>
            <label class="flex w-full items-center gap-2 rounded-xl2 border border-line px-3 py-2 text-[12.5px] font-semibold text-secondary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M3 6h12M3 12h9M3 18h6" stroke-linecap="round"/></svg>
                <select name="sort" class="js-sort w-full bg-transparent outline-none">
                    <?php foreach ($sorts as $val => $label): ?>
                        <option value="<?= e($val) ?>" <?= $sort === $val ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </form>
    </div>

    <div class="md:flex md:gap-8">
        <!-- desktop sidebar -->
        <aside class="hidden w-64 flex-none md:block">
            <div class="sticky top-44 rounded-2xl border border-line2 bg-white p-5">
                <div class="mb-4 text-[15px] font-bold text-secondary">فیلترها</div>
                <?php $this->partial('category-filters', ['brands' => $brands, 'filters' => $filters, 'action' => $action, 'sort' => $sort]); ?>
            </div>
        </aside>

        <!-- grid -->
        <div class="flex-1">
            <?php if ($products === []): ?>
                <div class="rounded-2xl border border-line2 bg-surface py-16 text-center text-[13px] text-[#999]">محصولی با این فیلترها یافت نشد.</div>
            <?php else: ?>
                <div id="js-product-grid" class="grid grid-cols-2 gap-3.5 md:grid-cols-3 lg:grid-cols-4">
                    <?php foreach ($products as $product): ?>
                        <?php $this->partial('product-card', ['product' => $product]); ?>
                    <?php endforeach; ?>
                </div>
                <?php if ($hasMore): ?>
                    <div class="py-6 text-center">
                        <button type="button"
                                class="js-load-more btn-outline px-8 py-3 text-[12.5px]"
                                data-base="<?= e($basePath) ?>"
                                data-query="<?= e(http_build_query(array_diff_key($_GET, ['page' => '']))) ?>"
                                data-page="<?= (int) $page ?>">نمایش محصولات بیشتر</button>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- mobile filter sheet -->
<div class="js-filter-overlay fixed inset-0 z-[70] hidden bg-black/45 md:hidden"></div>
<div class="js-filter-sheet fixed bottom-0 left-1/2 z-[71] hidden max-h-[82vh] w-full max-w-mobile -translate-x-1/2 animate-sheetUp overflow-y-auto rounded-t-3xl bg-white pb-6 md:hidden">
    <div class="sticky top-0 flex items-center justify-between bg-white px-5 pb-3 pt-4">
        <span class="text-[16px] font-bold text-secondary">فیلترها</span>
        <button type="button" class="js-filter-close text-2xl text-[#999]" aria-label="بستن">&times;</button>
    </div>
    <div class="px-5">
        <?php $this->partial('category-filters', ['brands' => $brands, 'filters' => $filters, 'action' => $action, 'sort' => $sort]); ?>
    </div>
</div>
