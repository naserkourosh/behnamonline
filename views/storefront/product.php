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
$available = (int) $p['stock'] - (int) $p['reserved'];
$showQty   = (bool) setting('show_stock_qty', true);
$lowAt     = (int) setting('low_stock_threshold', 5);

$images    = $images !== [] ? $images : [['path' => 'assets/images/placeholder-product.svg', 'alt' => $p['name'], 'title' => $p['name']]];
$mainImg   = $images[0];

$this->meta([
    'title'       => ($p['seo_title'] ?: $p['name'] . ' | بهنام'),
    'description' => ($p['seo_description'] ?: $p['short_desc'] ?: $p['name']),
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
    'description' => $p['short_desc'],
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
        return ['ناموجود', 'text-danger'];
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

    <div id="js-pdp" data-id="<?= (int) $p['id'] ?>" class="grid gap-7 md:grid-cols-2 md:gap-12">
        <!-- gallery -->
        <div>
            <div class="relative aspect-square overflow-hidden rounded-3xl bg-[#F3EBE2]">
                <img id="js-gallery-main" src="<?= e(asset((string) $mainImg['path'])) ?>" alt="<?= e($mainImg['alt'] ?: $p['name']) ?>" title="<?= e($mainImg['title'] ?: $p['name']) ?>" class="h-full w-full cursor-zoom-in object-cover js-zoom">
                <?php if ($discount > 0): ?>
                    <span class="badge-discount absolute right-3.5 top-3.5 text-[11px]">٪<?= fa($discount) ?></span>
                <?php endif; ?>
                <span class="badge-verified absolute left-3.5 top-3.5">اصل · دارای کد رهگیری</span>
            </div>
            <div class="mt-3 flex gap-2.5">
                <?php foreach (array_slice($images, 0, 4) as $i => $img): ?>
                    <button type="button" class="js-thumb aspect-square w-1/4 overflow-hidden rounded-xl2 border-2 <?= $i === 0 ? 'border-secondary' : 'border-transparent' ?>"
                            data-src="<?= e(asset((string) $img['path'])) ?>" data-alt="<?= e($img['alt'] ?: $p['name']) ?>">
                        <img src="<?= e(asset((string) $img['path'])) ?>" alt="<?= e($img['alt'] ?: $p['name']) ?>" loading="lazy" class="h-full w-full object-cover">
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
            <div class="flex items-center gap-2">
                <span class="text-[13px] text-star"><?= str_repeat('★', (int) round((float) $p['rating_avg'])) . str_repeat('☆', 5 - (int) round((float) $p['rating_avg'])) ?></span>
                <span class="text-[11px] text-[#888] nums"><?= fa(number_format((float) $p['rating_avg'], 1)) ?></span>
                <span class="text-[11px] text-mauve">(<?= fa((int) $p['rating_count']) ?> دیدگاه)</span>
            </div>

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

            <!-- desktop inline add-to-cart -->
            <div class="mt-5 hidden items-center gap-3 md:flex">
                <button type="button" class="js-wishlist flex h-12 w-12 flex-none items-center justify-center rounded-xl2 border border-primary text-secondary" data-id="<?= (int) $p['id'] ?>" aria-label="علاقه‌مندی">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 21s-7-4.4-9.4-8.6C1 9.3 2.6 5.6 6 5.6c2 0 3 1.1 4 2.6 1-1.5 2-2.6 4-2.6 3.4 0 5 3.7 3.4 6.8C19 16.6 12 21 12 21z"/></svg>
                </button>
                <div class="flex flex-none items-center overflow-hidden rounded-xl2 bg-surface">
                    <button type="button" class="js-qty-dec flex h-12 w-10 items-center justify-center text-[20px] text-secondary">−</button>
                    <span class="js-qty w-8 text-center font-bold text-[#333] nums" data-qty="1">۱</span>
                    <button type="button" class="js-qty-inc flex h-12 w-10 items-center justify-center text-[20px] text-secondary">+</button>
                </div>
                <button type="button" class="js-pdp-add btn-primary flex-1 py-3.5 text-[14px]" <?= $available <= 0 ? 'disabled' : '' ?>>افزودن به سبد</button>
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

    <!-- description / specs tabs -->
    <section class="mt-9">
        <div class="mb-4 flex gap-6 border-b border-line">
            <button type="button" class="js-tab -mb-px border-b-2 border-secondary pb-2.5 text-[13px] font-bold text-secondary" data-tab="desc">توضیحات</button>
            <button type="button" class="js-tab -mb-px border-b-2 border-transparent pb-2.5 text-[13px] font-bold text-[#bbb]" data-tab="specs">مشخصات فنی</button>
        </div>
        <div class="js-tab-panel" data-panel="desc">
            <div class="max-w-3xl text-[13px] leading-8 text-[#555]"><?= html_clean((string) $p['description']) ?></div>
        </div>
        <div class="js-tab-panel hidden" data-panel="specs">
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
        </div>
    </section>

    <!-- frequently bought together -->
    <?php if ($fbt !== []): ?>
        <section class="mt-9 rounded-3xl bg-surface p-5 md:p-7">
            <h2 class="section-title mb-5">معمولاً با هم خریداری می‌شوند</h2>
            <div class="flex flex-wrap items-stretch gap-3.5">
                <?php foreach (array_merge([$p], $fbt) as $item):
                    $img = $item['image'] ?? ($mainImg['path']); ?>
                    <div class="w-[140px] flex-none">
                        <?php // lightweight tile for the bundle ?>
                        <div class="overflow-hidden rounded-xl2 border border-line2 bg-white">
                            <div class="aspect-square bg-[#F3EBE2]"><img src="<?= e(asset((string) ($item['image'] ?? $mainImg['path']))) ?>" alt="<?= e($item['name']) ?>" loading="lazy" class="h-full w-full object-cover"></div>
                            <div class="p-2 text-center text-[11px] font-bold text-secondary nums"><?= money((int) $item['price']) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php $bundleTotal = (int) $p['price'] + array_sum(array_map(static fn ($i) => (int) $i['price'], $fbt)); ?>
            <button type="button" class="js-add-bundle btn-primary mt-5 w-full py-3.5 text-[13px] md:w-auto md:px-8"
                    data-ids="<?= e(implode(',', array_merge([(int) $p['id']], array_map(static fn ($i) => (int) $i['id'], $fbt)))) ?>">
                افزودن همه به سبد · <?= money($bundleTotal) ?> تومان
            </button>
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
    <section class="mt-9">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="section-title">دیدگاه مشتریان</h2>
            <div class="flex items-center gap-1.5">
                <span class="text-[20px] font-extrabold text-secondary nums"><?= fa(number_format((float) $p['rating_avg'], 1)) ?></span>
                <span class="text-[12px] text-star">★★★★★</span>
            </div>
        </div>
        <?php if ($reviews === []): ?>
            <p class="text-[13px] text-[#999]">هنوز دیدگاهی ثبت نشده است.</p>
        <?php else: ?>
            <div class="grid gap-3 md:grid-cols-2">
                <?php foreach ($reviews as $r): ?>
                    <div class="rounded-2xl border border-line2 p-4">
                        <div class="mb-2.5 flex items-center gap-2.5">
                            <div class="h-9 w-9 rounded-full bg-[#F3EBE2]"></div>
                            <div class="flex-1">
                                <div class="text-[12.5px] font-bold text-[#333]"><?= e($r['author_name']) ?></div>
                                <div class="text-[11px] text-star"><?= str_repeat('★', (int) $r['rating']) . str_repeat('☆', 5 - (int) $r['rating']) ?></div>
                            </div>
                            <?php if (!empty($r['is_verified'])): ?><span class="badge-verified">خرید تاییدشده</span><?php endif; ?>
                        </div>
                        <p class="text-[12px] leading-7 text-[#666]"><?= e($r['body']) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
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
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 21s-7-4.4-9.4-8.6C1 9.3 2.6 5.6 6 5.6c2 0 3 1.1 4 2.6 1-1.5 2-2.6 4-2.6 3.4 0 5 3.7 3.4 6.8C19 16.6 12 21 12 21z"/></svg>
    </button>
    <div class="flex flex-none items-center overflow-hidden rounded-xl2 bg-surface">
        <button type="button" class="js-qty-dec flex h-12 w-9 items-center justify-center text-[20px] text-secondary">−</button>
        <span class="js-qty w-7 text-center font-bold text-[#333] nums" data-qty="1">۱</span>
        <button type="button" class="js-qty-inc flex h-12 w-9 items-center justify-center text-[20px] text-secondary">+</button>
    </div>
    <button type="button" class="js-pdp-add btn-primary flex-1 py-3.5 text-[14px]" <?= $available <= 0 ? 'disabled' : '' ?>>افزودن به سبد</button>
</div>
