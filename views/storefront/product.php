<?php
/** @var array<string,mixed> $product */
/** @var list<array<string,mixed>> $images */
/** @var list<array<string,mixed>> $attributes */
/** @var list<array<string,mixed>> $variants */
/** @var list<array<string,mixed>> $reviews */
/** @var list<array<string,mixed>> $related */
/** @var list<array<string,mixed>> $fbt */
/** @var list<array<string,mixed>> $recently */

$p         = $product;
$price     = (int) $p['price'];
$old       = (int) $p['old_price'];
$discount  = discount_percent($old, $price);
// Per-product stock model: اتمام موجودی is absolute; otherwise کنترل موجودی
// makes the numeric count govern (shown + zero blocks); else free selling.
// A zero/unset price also blocks purchasing (WooCommerce-style).
$oos       = !empty($p['is_out_of_stock']) || $price <= 0;
$tracked   = !$oos && !empty($p['track_stock']);
$available = $oos ? 0 : ($tracked ? (int) $p['stock'] - (int) $p['reserved'] : 9999);
$showQty   = $tracked;
$lowAt     = (int) setting('low_stock_threshold', 5);
if ($oos) {
    // The manual flag wins: variants must not re-enable the buy button.
    foreach ($variants as &$vv) {
        $vv['stock'] = 0;
    }
    unset($vv);
} elseif (!$tracked) {
    // Untracked stock: variants must never render as unavailable either.
    foreach ($variants as &$vv) {
        $vv['stock'] = 9999;
    }
    unset($vv);
}

$images    = $images !== [] ? $images : [['path' => 'assets/images/placeholder-product.svg', 'alt' => $p['name'], 'title' => $p['name']]];
$mainImg   = $images[0];

$shortText = trim(strip_tags((string) $p['short_desc']));
$this->meta([
    'title'       => ($p['seo_title'] ?: $p['name'] . ' | بهنام'),
    'description' => ($p['seo_description'] ?: $shortText ?: $p['name']),
    'og_image'    => asset((string) ($p['og_image'] ?: $mainImg['path'])),
]);

// Product + Breadcrumb structured data
$this->push('json_ld', '<script type="application/ld+json">' . json_encode([
    '@context'    => 'https://schema.org',
    '@type'       => 'Product',
    'name'        => $p['name'],
    'sku'         => $p['sku'],
    'gtin'        => $p['barcode'],
    'brand'       => ['@type' => 'Brand', 'name' => $p['brand_name']],
    'image'       => asset((string) $mainImg['path']),
    'description' => $shortText,
    'aggregateRating' => (int) $p['rating_count'] > 0 ? [
        '@type' => 'AggregateRating',
        'ratingValue' => (float) $p['rating_avg'],
        'reviewCount' => (int) $p['rating_count'],
    ] : null,
    'offers' => [
        '@type'         => 'Offer',
        'priceCurrency' => 'IRR',
        'price'         => $price * 10, // Toman → Rial for schema
        'availability'  => $available > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
        'url'           => abs_url('product/' . $p['slug']),
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>');

// BreadcrumbList schema (خانه ← دسته ← محصول) for Google rich results.
$crumbs = [['@type' => 'ListItem', 'position' => 1, 'name' => 'خانه', 'item' => base_url() . '/']];
if (!empty($p['category_slug'])) {
    $crumbs[] = ['@type' => 'ListItem', 'position' => 2, 'name' => (string) $p['category_name'], 'item' => abs_url('category/' . $p['category_slug'])];
}
$crumbs[] = ['@type' => 'ListItem', 'position' => count($crumbs) + 1, 'name' => (string) $p['name'], 'item' => abs_url('product/' . $p['slug'])];
$this->push('json_ld', '<script type="application/ld+json">' . json_encode([
    '@context' => 'https://schema.org',
    '@type'    => 'BreadcrumbList',
    'itemListElement' => $crumbs,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>');

// Torob (ترب) crawler markup — lets Torob read this product reliably when it
// visits the page (complements the /torob.json feed). Prices in Toman.
if (setting('torob_enabled', true)) {
    $this->push('json_ld', '<script type="application/json" id="torob-product">' . json_encode([
        'product_id'   => (string) $p['id'],
        'page_url'     => abs_url('product/' . $p['slug']),
        'title'        => (string) $p['name'],
        'price'        => $price,
        'old_price'    => $old ?: 0,
        'availability' => $available > 0 ? 'instock' : 'outofstock',
        'image_link'   => base_url() . asset((string) $mainImg['path']),
        'category_name'=> (string) ($p['category_name'] ?? ''),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>');
}

// default variant = the one matching base price (override NULL), else first
$defaultVariantId = 0;
$defaultPrice     = $price;
$defaultStock     = $available;
if ($variants !== []) {
    $defaultVariantId = (int) $variants[0]['id'];
    $defaultPrice     = $variants[0]['price_override'] !== null ? (int) $variants[0]['price_override'] : $price;
    $defaultStock     = (int) $variants[0]['stock'];
    foreach ($variants as $v) {
        if ($v['price_override'] === null) {
            $defaultVariantId = (int) $v['id'];
            $defaultPrice     = $price;
            $defaultStock     = (int) $v['stock'];
            break;
        }
    }
}

$stockBadge = static function (int $avail) use ($showQty, $lowAt): array {
    if ($avail <= 0) {
        return ['اتمام موجودی', 'text-danger'];
    }
    if ($showQty && $avail <= $lowAt) {
        return ['تنها ' . fa($avail) . ' عدد در انبار', 'text-warning'];
    }
    return ['موجود در انبار', 'text-success'];
};
[$stockText, $stockColor] = $stockBadge($defaultStock);
?>

<div class="container-page py-5 md:py-8">
    <nav class="mb-4 text-[11px] text-mauve">
        <a href="<?= e(url('/')) ?>" class="hover:text-secondary">خانه</a>
        <span class="mx-1">/</span>
        <?php if (!empty($p['category_slug'])): ?>
            <a href="<?= e(url('/category/' . $p['category_slug'])) ?>" class="hover:text-secondary"><?= e($p['category_name']) ?></a>
            <span class="mx-1">/</span>
        <?php endif; ?>
        <span class="text-[#777]"><?= e($p['name']) ?></span>
    </nav>

    <div id="js-pdp" data-id="<?= (int) $p['id'] ?>" data-showqty="<?= $showQty ? 1 : 0 ?>" class="grid gap-7 md:grid-cols-2 md:gap-12">
        <!-- gallery -->
        <div>
            <div class="relative aspect-square overflow-hidden rounded-3xl bg-white">
                <img id="js-gallery-main" src="<?= e(asset((string) $mainImg['path'])) ?>" alt="<?= e($mainImg['alt'] ?: $p['name']) ?>" title="<?= e($mainImg['title'] ?: $p['name']) ?>" class="h-full w-full cursor-zoom-in object-contain js-zoom">
                <?php if ($discount > 0): ?>
                    <span class="badge-discount absolute right-3.5 top-3.5 text-[11px]">٪<?= fa($discount) ?></span>
                <?php endif; ?>
                <span class="badge-verified absolute left-3.5 top-3.5">اصل · دارای کد رهگیری</span>
            </div>
            <div class="mt-3 flex gap-2.5">
                <?php foreach (array_slice($images, 0, 4) as $i => $img): ?>
                    <button type="button" class="js-thumb aspect-square w-1/4 overflow-hidden rounded-xl2 border-2 <?= $i === 0 ? 'border-secondary' : 'border-transparent' ?>"
                            data-src="<?= e(asset((string) $img['path'])) ?>" data-alt="<?= e($img['alt'] ?: $p['name']) ?>">
                        <img src="<?= e(asset((string) $img['path'])) ?>" alt="<?= e($img['alt'] ?: $p['name']) ?>" loading="lazy" class="h-full w-full bg-white object-contain">
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- info -->
        <div>
            <div class="flex items-center justify-between">
                <a href="<?= e(url('/category/' . ($p['category_slug'] ?? ''))) ?>" class="text-[11px] font-medium text-mauve"><?= e($p['brand_name']) ?></a>
            </div>
            <h1 class="mb-2 mt-2.5 text-[20px] font-bold leading-relaxed text-[#2a2a2a] md:text-[26px]"><?= e($p['name']) ?></h1>
            <?php if ((int) $p['rating_count'] > 0 && (bool) setting('show_ratings', true)): ?>
                <div class="flex items-center gap-2">
                    <span class="text-[13px] text-star"><?= str_repeat('★', (int) round((float) $p['rating_avg'])) . str_repeat('☆', 5 - (int) round((float) $p['rating_avg'])) ?></span>
                    <span class="text-[11px] text-[#888] nums"><?= fa(number_format((float) $p['rating_avg'], 1)) ?></span>
                    <span class="text-[11px] text-mauve">(<?= fa((int) $p['rating_count']) ?> دیدگاه)</span>
                </div>
            <?php endif; ?>

            <?php if ($shortText !== ''): ?>
                <div class="rich mt-4 border-s-2 border-primary ps-3 text-[#666]"><?= html_clean((string) $p['short_desc']) ?></div>
            <?php endif; ?>

            <?php if ($variants !== []): ?>
                <div class="mt-5">
                    <div class="mb-2.5 text-[12px] text-[#666]">حجم / گزینه:</div>
                    <div class="flex flex-wrap gap-2.5">
                        <?php foreach ($variants as $v):
                            $vPrice = $v['price_override'] !== null ? (int) $v['price_override'] : $price;
                            $on = (int) $v['id'] === $defaultVariantId; ?>
                            <button type="button" class="js-variant rounded-xl2 border px-4 py-2.5 text-[12px] font-semibold transition <?= $on ? 'border-secondary bg-secondary text-white' : 'border-line bg-white text-secondary' ?>"
                                    data-id="<?= (int) $v['id'] ?>" data-price="<?= $vPrice ?>" data-stock="<?= (int) $v['stock'] ?>"><?= e($v['label']) ?></button>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (flash_active($p)): ?>
                <div class="mt-5 flex items-center justify-between rounded-2xl border border-[#F4D9DC] bg-gradient-to-l from-[#FBF1F2] to-[#F8E7E9] px-4 py-3">
                    <span class="text-[12.5px] font-extrabold text-secondary">⚡ پیشنهاد شگفت‌انگیز</span>
                    <span class="js-flash-cd flex items-center gap-1 rounded-lg bg-secondary px-3 py-1.5 text-[13px] font-bold text-white" data-countdown="<?= (int) strtotime((string) $p['flash_sale_ends_at']) ?>">
                        <span class="js-cd-text nums" dir="ltr">--:--:--</span>
                    </span>
                </div>
            <?php endif; ?>

            <!-- price + stock -->
            <div class="mt-5 flex items-center justify-between rounded-2xl bg-surface p-4">
                <div>
                    <?php if ($discount > 0): ?>
                        <div class="text-[11px] text-[#bbb] line-through nums"><?= money($old) ?> تومان</div>
                    <?php endif; ?>
                    <div><span class="js-pdp-price text-[22px] font-extrabold text-secondary nums"><?= money($defaultPrice) ?></span> <span class="text-[11px] text-[#999]">تومان</span></div>
                </div>
                <div class="text-start md:text-end">
                    <div class="js-pdp-stock text-[12px] font-bold <?= $stockColor ?>"><?= e($stockText) ?></div>
                    <div class="mt-1 text-[10px] text-[#999]">برای ارسال امروز سفارش دهید</div>
                </div>
            </div>

            <button type="button" class="js-compare mt-3 flex items-center gap-2 text-[12.5px] font-semibold text-secondary" data-id="<?= (int) $p['id'] ?>">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M7 16V4M7 4L3 8M7 4l4 4M17 8v12M17 20l4-4M17 20l-4-4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span class="js-compare-label"><?= (new \App\Services\CompareService())->has((int) $p['id']) ? 'در لیست مقایسه ✓' : 'افزودن به مقایسه' ?></span>
            </button>

            <!-- desktop inline add-to-cart -->
            <div class="mt-5 hidden items-center gap-3 md:flex">
                <button type="button" class="js-wishlist flex h-12 w-12 flex-none items-center justify-center rounded-xl2 border border-primary text-secondary" data-id="<?= (int) $p['id'] ?>" aria-label="علاقه‌مندی">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <div class="flex flex-none items-center overflow-hidden rounded-xl2 bg-surface">
                    <button type="button" class="js-qty-dec flex h-12 w-10 items-center justify-center text-[20px] text-secondary">−</button>
                    <span class="js-qty w-8 text-center font-bold text-[#333] nums" data-qty="1">۱</span>
                    <button type="button" class="js-qty-inc flex h-12 w-10 items-center justify-center text-[20px] text-secondary">+</button>
                </div>
                <button type="button" class="js-pdp-add btn-primary flex-1 py-3.5 text-[14px]" <?= $available <= 0 ? 'disabled' : '' ?>><?= $available <= 0 ? 'اتمام موجودی' : 'افزودن به سبد' ?></button>
            </div>
        </div>
    </div>

    <!-- Aparat video -->
    <?php if (!empty($p['aparat_embed'])): ?>
        <section class="mt-9">
            <h2 class="section-title mb-4">ویدیو معرفی محصول</h2>
            <div class="aspect-video w-full max-w-3xl overflow-hidden rounded-2xl bg-[#2a2230]">
                <iframe src="<?= e($p['aparat_embed']) ?>" class="h-full w-full" allowfullscreen loading="lazy" referrerpolicy="strict-origin" title="ویدیو معرفی <?= e($p['name']) ?>"></iframe>
            </div>
        </section>
    <?php endif; ?>

    <!-- full description -->
    <?php if (trim((string) $p['description']) !== ''): ?>
        <section class="mt-9">
            <h2 class="section-title mb-4">توضیحات محصول</h2>
            <div class="rich max-w-3xl"><?= html_clean((string) $p['description']) ?></div>
        </section>
    <?php endif; ?>

    <!-- technical specs (below the description) -->
    <section class="mt-9">
        <h2 class="section-title mb-4">مشخصات فنی</h2>
        <div class="max-w-2xl overflow-hidden rounded-2xl border border-line">
            <?php foreach ($attributes as $i => $attr): ?>
                <div class="flex justify-between px-4 py-3 text-[12.5px] <?= $i % 2 ? 'bg-surface' : 'bg-white' ?>">
                    <span class="text-[#999]"><?= e($attr['attr_key']) ?></span>
                    <span class="font-semibold text-[#333]"><?= e($attr['attr_value']) ?></span>
                </div>
            <?php endforeach; ?>
            <div class="flex justify-between px-4 py-3 text-[12.5px] <?= count($attributes) % 2 ? 'bg-surface' : 'bg-white' ?>">
                <span class="text-[#999]">موجودی</span>
                <span class="font-semibold text-[#333] nums"><?= $showQty ? fa($available) . ' عدد' : ($available > 0 ? 'موجود' : 'ناموجود') ?></span>
            </div>
        </div>
    </section>

    <!-- frequently bought together -->
    <?php if ($fbt !== []): ?>
        <section class="mt-9">
            <h2 class="section-title mb-4">معمولاً با هم خریداری می‌شوند</h2>
            <div class="hscroll -mx-4 flex gap-3.5 overflow-x-auto px-4 pb-2 md:mx-0 md:px-0">
                <?php foreach ($fbt as $product): ?>
                    <div class="w-[150px] flex-none md:w-[210px]"><?php $this->partial('product-card', ['product' => $product]); ?></div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <!-- related -->
    <?php if ($related !== []): ?>
        <section class="mt-9">
            <h2 class="section-title mb-4">محصولات مرتبط</h2>
            <div class="hscroll -mx-4 flex gap-3.5 overflow-x-auto px-4 pb-2 md:mx-0 md:px-0">
                <?php foreach ($related as $product): ?>
                    <div class="w-[150px] flex-none md:w-[210px]"><?php $this->partial('product-card', ['product' => $product]); ?></div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <!-- reviews -->
    <section class="mt-9" id="reviews">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="section-title">دیدگاه مشتریان</h2>
            <?php if ((int) $p['rating_count'] > 0 && (bool) setting('show_ratings', true)): ?>
                <div class="flex items-center gap-1.5">
                    <span class="text-[20px] font-extrabold text-secondary nums"><?= fa(number_format((float) $p['rating_avg'], 1)) ?></span>
                    <span class="text-[12px] text-star">★★★★★</span>
                </div>
            <?php endif; ?>
        </div>
        <?php if ($reviews === []): ?>
            <p class="text-[13px] text-[#999]">هنوز دیدگاهی ثبت نشده است. اولین نفر باشید!</p>
        <?php else: ?>
            <div class="grid gap-3 md:grid-cols-2">
                <?php foreach ($reviews as $r): ?>
                    <div class="rounded-2xl border border-line2 p-4">
                        <div class="mb-2.5 flex items-center gap-2.5">
                            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-pink text-[13px] font-bold text-secondary"><?= e(mb_substr(trim((string) $r['author_name']), 0, 1)) ?></div>
                            <div class="flex-1">
                                <div class="text-[12.5px] font-bold text-[#333]"><?= e($r['author_name']) ?></div>
                                <div class="text-[11px] text-star"><?= str_repeat('★', (int) $r['rating']) . str_repeat('☆', 5 - (int) $r['rating']) ?></div>
                            </div>
                            <?php if (!empty($r['is_verified'])): ?><span class="badge-verified">خریدار این محصول</span><?php endif; ?>
                        </div>
                        <p class="text-[12px] leading-7 text-[#666]"><?= e($r['body']) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- submit a review -->
        <?php if (\App\Services\AuthService::check()): ?>
            <form method="post" action="<?= e(url('/product/' . $p['slug'] . '/review')) ?>" class="mt-6 max-w-lg rounded-2xl border border-line2 bg-white p-5">
                <?= csrf_field() ?>
                <h3 class="mb-3 text-[14px] font-bold text-[#333]">دیدگاه خود را بنویسید</h3>
                <div class="mb-3">
                    <label class="mb-1.5 block text-[12px] font-semibold text-[#666]">امتیاز شما</label>
                    <select name="rating" class="w-full rounded-xl2 border border-line bg-white px-3.5 py-2.5 text-[13px] outline-none focus:border-secondary">
                        <option value="5">★★★★★ — عالی</option>
                        <option value="4">★★★★ — خوب</option>
                        <option value="3">★★★ — متوسط</option>
                        <option value="2">★★ — ضعیف</option>
                        <option value="1">★ — خیلی ضعیف</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="mb-1.5 block text-[12px] font-semibold text-[#666]">متن دیدگاه</label>
                    <textarea name="body" rows="3" required class="w-full rounded-xl2 border border-line bg-white px-3.5 py-2.5 text-[13px] outline-none focus:border-secondary" placeholder="تجربهٔ شما از این محصول…"></textarea>
                </div>
                <button class="btn-primary w-full py-2.5 text-[13px]">ثبت دیدگاه</button>
                <p class="mt-2 text-[10.5px] text-[#aaa]">دیدگاه شما پس از تایید مدیر نمایش داده می‌شود.</p>
            </form>
        <?php else: ?>
            <p class="mt-5 text-[12.5px] text-[#888]">
                برای ثبت دیدگاه <a href="<?= e(url('/login?redirect=' . rawurlencode('/product/' . $p['slug']))) ?>" class="font-bold text-secondary underline">وارد حساب خود شوید</a>.
            </p>
        <?php endif; ?>
    </section>

    <!-- recently viewed -->
    <?php if ($recently !== []): ?>
        <section class="mt-9">
            <h2 class="section-title mb-4">بازدیدهای اخیر</h2>
            <div class="hscroll -mx-4 flex gap-3.5 overflow-x-auto px-4 pb-2 md:mx-0 md:px-0">
                <?php foreach ($recently as $product): ?>
                    <div class="w-[150px] flex-none md:w-[200px]"><?php $this->partial('product-card', ['product' => $product]); ?></div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</div>

<!-- mobile sticky add-to-cart -->
<div class="fixed bottom-0 left-1/2 z-50 flex w-full max-w-mobile -translate-x-1/2 items-center gap-3 border-t border-line bg-white px-4 pb-4 pt-3 shadow-nav md:hidden">
    <button type="button" class="js-wishlist flex h-12 w-12 flex-none items-center justify-center rounded-xl2 border border-primary text-secondary" aria-label="علاقه‌مندی">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </button>
    <div class="flex flex-none items-center overflow-hidden rounded-xl2 bg-surface">
        <button type="button" class="js-qty-dec flex h-12 w-9 items-center justify-center text-[20px] text-secondary">−</button>
        <span class="js-qty w-7 text-center font-bold text-[#333] nums" data-qty="1">۱</span>
        <button type="button" class="js-qty-inc flex h-12 w-9 items-center justify-center text-[20px] text-secondary">+</button>
    </div>
    <button type="button" class="js-pdp-add btn-primary flex-1 py-3.5 text-[14px]" <?= $available <= 0 ? 'disabled' : '' ?>><?= $available <= 0 ? 'اتمام موجودی' : 'افزودن به سبد' ?></button>
</div>
