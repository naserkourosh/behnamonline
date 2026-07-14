<?php
/** @var list<array<string,mixed>> $categories */
/** @var list<array<string,mixed>> $flashSale */
/** @var list<array<string,mixed>> $sections */
/** @var list<array<string,mixed>> $brands */
/** @var list<array<string,mixed>> $reviews */
/** @var list<array<string,mixed>> $posts */

$brand = (string) setting('brand_name', 'بهنام');
$this->meta([
    'title'       => $brand . ' | فروشگاه لوکس آرایشی، بهداشتی و شوینده',
    'description' => 'خرید اینترنتی محصولات آرایشی، مراقبت پوست، عطر و مواد بهداشتی و شوینده اصل با ضمانت اصالت و ارسال سریع از فروشگاه ' . $brand . '.',
]);
$this->push('json_ld', '<script type="application/ld+json">' . json_encode([
    '@context' => 'https://schema.org',
    '@type'    => 'WebSite',
    'name'     => $brand,
    'url'      => base_url(),
    'potentialAction' => [
        '@type'       => 'SearchAction',
        'target'      => base_url() . '/category?q={search_term_string}',
        'query-input' => 'required name=search_term_string',
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>');

/** @var list<array<string,mixed>> $heroBanners */
/** @var list<array<string,mixed>> $promoBanners */
$heroBanners = $heroBanners ?? [];
$promoBanners = $promoBanners ?? [];

// Admin-managed hero banners take priority; otherwise use the built-in slides.
if ($heroBanners !== []) {
    $slides = [];
    foreach ($heroBanners as $b) {
        $slides[] = [
            'kicker' => (string) ($b['kicker'] ?? ''),
            't1'     => (string) $b['title'],
            't2'     => (string) ($b['subtitle'] ?? ''),
            'sub'    => (string) ($b['subtitle'] ?? ''),
            'cta'    => (string) ($b['cta_label'] ?? 'خرید اکنون') ?: 'خرید اکنون',
            'href'   => (string) ($b['link_url'] ?? '/category') ?: '/category',
            'image'  => !empty($b['image']) ? asset((string) $b['image']) : null,
            'grad'   => (string) ($b['bg_color'] ?? '') ?: 'linear-gradient(155deg,#F4E4E6 0%,#EAD0D4 55%,#E8C5C8 100%)',
        ];
    }
} else {
    $slides = [
        ['kicker' => 'مجموعه‌ی جدید بهار', 't1' => 'درخششی که', 't2' => 'شایسته‌ی شماست', 'sub' => 'محصولات اصل آرایشی و مراقبتی، با ضمانت اصالت', 'grad' => 'linear-gradient(155deg,#F4E4E6 0%,#EAD0D4 55%,#E8C5C8 100%)', 'cta' => 'خرید اکنون', 'href' => '/category', 'image' => null],
        ['kicker' => 'عطرهای لوکس', 't1' => 'رایحه‌ای', 't2' => 'به‌یادماندنی', 'sub' => 'مجموعه‌ی عطرهای زنانه‌ی اصل و ماندگار', 'grad' => 'linear-gradient(155deg,#EFE6DC 0%,#E4D3C4 55%,#D8BFA8 100%)', 'cta' => 'خرید اکنون', 'href' => '/category', 'image' => null],
        ['kicker' => 'مراقبت پوست', 't1' => 'پوستی سالم،', 't2' => 'احساسی تازه', 'sub' => 'با برندهای معتبر جهانی و قیمت ویژه', 'grad' => 'linear-gradient(155deg,#E9E0E6 0%,#D9C7D2 55%,#C9AEC0 100%)', 'cta' => 'خرید اکنون', 'href' => '/category', 'image' => null],
    ];
}

// Preload the first hero image (the likely LCP element) when one is set.
if (($slides[0]['image'] ?? null)) {
    $this->push('head', '<link rel="preload" as="image" href="' . e((string) $slides[0]['image']) . '" fetchpriority="high">');
}

/** @var ?string $flashEndsAt */
$flashEndsAt = $flashEndsAt ?? null;
if ($flashEndsAt !== null) {
    // Real engine: count down to the soonest-ending active flash sale.
    $remaining = max(0, strtotime($flashEndsAt) - time());
} else {
    $flashEnds = (string) setting('flash_sale_ends_at', date('Y-m-d H:i:s', time() + 7200));
    $remaining = max(0, strtotime($flashEnds) - time());
    if ($remaining <= 0) {
        // Campaign expired/misaligned — roll to end of today so the countdown stays live.
        $remaining = strtotime('tomorrow') - time();
    }
}
$h = str_pad((string) intdiv($remaining, 3600), 2, '0', STR_PAD_LEFT);
$m = str_pad((string) intdiv($remaining % 3600, 60), 2, '0', STR_PAD_LEFT);
$s = str_pad((string) ($remaining % 60), 2, '0', STR_PAD_LEFT);
?>

<!-- ── Hero + side promos ─────────────────────────────────── -->
<section class="container-page pt-4 md:pt-7">
    <div class="grid gap-5 md:grid-cols-[1fr_360px]">
        <div class="js-hero relative h-[368px] overflow-hidden rounded-[22px] md:h-[440px] md:rounded-4xl" data-autoplay="1">
            <?php foreach ($slides as $i => $sl): ?>
                <a href="<?= e(url((string) $sl['href'])) ?>"
                   class="hero-slide absolute inset-0 flex flex-col justify-end bg-cover bg-center p-6 transition-opacity duration-700 md:justify-center md:px-14 <?= $i === 0 ? 'opacity-100' : 'opacity-0' ?>"
                   style="background:<?= !empty($sl['image']) ? "url('" . e((string) $sl['image']) . "') center/cover no-repeat" : $sl['grad'] ?>" <?= $i === 0 ? '' : 'aria-hidden="true"' ?>>
                    <div class="absolute inset-0 bg-gradient-to-t from-secondary/50 to-transparent md:bg-gradient-to-l md:from-transparent md:to-secondary/10"></div>
                    <div class="relative max-w-[460px]">
                        <?php if ($sl['kicker'] !== ''): ?><div class="mb-2.5 pr-[0.32em] text-[10px] tracking-[0.32em] text-white/90 md:mb-4 md:text-[12px]"><?= e($sl['kicker']) ?></div><?php endif; ?>
                        <h2 class="mb-2 text-[30px] font-light leading-snug text-white md:text-[46px]"><?= e($sl['t1']) ?><?php if (($sl['t2'] ?? '') !== '' && $sl['t2'] !== $sl['sub']): ?> <span class="font-bold"><?= e($sl['t2']) ?></span><?php endif; ?></h2>
                        <?php if (($sl['sub'] ?? '') !== ''): ?><p class="mb-4 text-[12.5px] leading-7 text-white/90 md:mb-7 md:text-[15px]"><?= e($sl['sub']) ?></p><?php endif; ?>
                        <span class="inline-block rounded-full bg-white px-7 py-3 text-[13px] font-bold text-secondary shadow-card md:text-[14px]"><?= e((string) ($sl['cta'] ?? 'خرید اکنون')) ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
            <div class="absolute bottom-3.5 left-1/2 flex -translate-x-1/2 gap-1.5 md:left-auto md:right-14 md:translate-x-0">
                <?php foreach ($slides as $i => $sl): ?>
                    <button type="button" class="js-hero-dot h-1.5 rounded-full transition-all <?= $i === 0 ? 'w-5 bg-secondary' : 'w-1.5 bg-[#E0CDD3]' ?>" data-index="<?= $i ?>" aria-label="اسلاید <?= fa($i + 1) ?>"></button>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="hidden grid-rows-2 gap-5 md:grid">
            <?php if ($promoBanners !== []): ?>
                <?php foreach (array_slice($promoBanners, 0, 2) as $pb):
                    $bg = !empty($pb['image']) ? "url('" . e(asset((string) $pb['image'])) . "') center/cover no-repeat" : ((string) ($pb['bg_color'] ?? '') ?: 'linear-gradient(140deg,#EFE3D4,#E2CBB4)'); ?>
                    <a href="<?= e(url((string) ($pb['link_url'] ?? '/category') ?: '/category')) ?>" class="card-rise relative flex flex-col justify-center overflow-hidden rounded-4xl p-6 text-white" style="background:<?= $bg ?>">
                        <?php if (!empty($pb['image'])): ?><div class="absolute inset-0 bg-secondary/25"></div><?php endif; ?>
                        <div class="relative">
                            <?php if (!empty($pb['kicker'])): ?><div class="mb-2 text-[11px] font-bold text-white/90"><?= e($pb['kicker']) ?></div><?php endif; ?>
                            <div class="text-[22px] font-bold leading-relaxed"><?= e($pb['title']) ?></div>
                            <div class="mt-2 text-[12px] text-white/90"><?= e((string) ($pb['cta_label'] ?? 'مشاهده') ?: 'مشاهده') ?> ›</div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <a href="<?= e(url('/category/perfume')) ?>" class="card-rise relative flex flex-col justify-center overflow-hidden rounded-4xl p-6" style="background:linear-gradient(140deg,#EFE3D4,#E2CBB4)">
                    <div class="mb-2 text-[11px] font-bold text-[#8a6a4a]">عطر و ادکلن</div>
                    <div class="text-[22px] font-bold leading-relaxed text-[#5a3a1e]">تا ۴۰٪ تخفیف</div>
                    <div class="mt-2 text-[12px] text-[#8a6a4a]">مشاهده ›</div>
                </a>
                <a href="<?= e(url('/category/skincare')) ?>" class="card-rise relative flex flex-col justify-center overflow-hidden rounded-4xl p-6" style="background:linear-gradient(140deg,#E9DDE5,#D4BCCB)">
                    <div class="mb-2 text-[11px] font-bold text-[#7c5070]">مراقبت پوست</div>
                    <div class="text-[22px] font-bold leading-relaxed text-secondary">کالکشن جدید</div>
                    <div class="mt-2 text-[12px] text-[#7c5070]">مشاهده ›</div>
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ── Featured categories ────────────────────────────────── -->
<section class="container-page pt-8 md:pt-12">
    <div class="mb-4 flex items-baseline justify-between md:mb-7">
        <h2 class="section-title">خرید بر اساس دسته‌بندی</h2>
        <a href="<?= e(url('/category')) ?>" class="text-[11.5px] text-mauve md:text-[13px]">مشاهده همه ›</a>
    </div>
    <div class="hscroll flex gap-4 overflow-x-auto pb-2 md:grid md:grid-cols-6 md:gap-5 md:overflow-visible">
        <?php foreach (array_slice($categories, 0, 6) as $cat): ?>
            <a href="<?= e(url('/category/' . $cat['slug'])) ?>" class="w-[70px] flex-none text-center md:w-auto">
                <div class="cat-circle mx-auto mb-2 aspect-square w-[70px] overflow-hidden rounded-full border border-line md:mb-3.5 md:w-auto">
                    <img src="<?= e(asset((string) ($cat['image'] ?: 'assets/images/placeholder-category.svg'))) ?>" alt="<?= e($cat['name']) ?>" loading="lazy" class="h-full w-full object-cover">
                </div>
                <div class="text-[11.5px] font-medium text-[#4a4a4a] md:text-[14px] md:font-semibold"><?= e($cat['name']) ?></div>
                <div class="mt-1 hidden text-[11px] text-mauve nums md:block"><?= fa((int) $cat['product_count']) ?> کالا</div>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- ── Promo strip banners (نوار تبلیغاتی، admin-managed) ──── -->
<?php $stripBanners = $stripBanners ?? []; ?>
<?php if ($stripBanners !== []): ?>
<section class="container-page pt-8 md:pt-12">
    <div class="grid gap-4 <?= count($stripBanners) > 1 ? 'md:grid-cols-2' : '' ?>">
        <?php foreach ($stripBanners as $sb):
            $bg = !empty($sb['image'])
                ? "url('" . e(asset((string) $sb['image'])) . "') center/cover no-repeat"
                : ((string) ($sb['bg_color'] ?? '') ?: 'linear-gradient(120deg,#5C2D46,#7c4862)'); ?>
            <a href="<?= e(url((string) ($sb['link_url'] ?? '/category') ?: '/category')) ?>"
               class="card-rise relative flex items-center justify-between gap-4 overflow-hidden rounded-2xl px-5 py-4 md:px-8 md:py-5" style="background:<?= $bg ?>">
                <?php if (!empty($sb['image'])): ?><div class="absolute inset-0 bg-secondary/40"></div><?php endif; ?>
                <div class="relative min-w-0">
                    <?php if (!empty($sb['kicker'])): ?><div class="mb-1 text-[10px] font-bold tracking-wide text-white/80"><?= e($sb['kicker']) ?></div><?php endif; ?>
                    <div class="truncate text-[14px] font-extrabold text-white md:text-[17px]"><?= e($sb['title']) ?></div>
                    <?php if (!empty($sb['subtitle'])): ?><div class="mt-0.5 truncate text-[11px] text-white/85"><?= e($sb['subtitle']) ?></div><?php endif; ?>
                </div>
                <span class="relative flex-none rounded-full bg-white px-4 py-2 text-[11.5px] font-bold text-secondary md:px-5"><?= e((string) ($sb['cta_label'] ?? 'مشاهده') ?: 'مشاهده') ?> ›</span>
            </a>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- ── Flash sale ─────────────────────────────────────────── -->
<?php if ($flashSale !== []): ?>
<section class="container-page pt-8 md:pt-12">
    <div class="rounded-4xl border border-[#F4D9DC] bg-gradient-to-br from-[#FBF1F2] to-[#F8E7E9] p-4 md:p-7">
        <div class="mb-5 flex items-center justify-between md:mb-7">
            <div class="flex items-center gap-3 md:gap-4">
                <div class="text-[15px] font-extrabold text-secondary md:text-[22px]">⚡ پیشنهاد شگفت‌انگیز</div>
                <div class="js-countdown flex items-center gap-1.5" data-remaining="<?= (int) $remaining ?>">
                    <span class="hidden text-[11px] text-[#9a8a98] md:inline">پایان تا:</span>
                    <span class="js-cd-h min-w-[20px] rounded-md bg-secondary px-1.5 py-1 text-center text-[12px] font-bold text-white nums md:text-[15px] md:px-2.5"><?= fa($h) ?></span>
                    <span class="font-bold text-secondary">:</span>
                    <span class="js-cd-m min-w-[20px] rounded-md bg-secondary px-1.5 py-1 text-center text-[12px] font-bold text-white nums md:text-[15px] md:px-2.5"><?= fa($m) ?></span>
                    <span class="font-bold text-secondary">:</span>
                    <span class="js-cd-s min-w-[20px] rounded-md bg-secondary px-1.5 py-1 text-center text-[12px] font-bold text-white nums md:text-[15px] md:px-2.5"><?= fa($s) ?></span>
                </div>
            </div>
            <a href="<?= e(url('/category?in_stock=1')) ?>" class="hidden text-[13px] font-semibold text-secondary md:inline">مشاهده همه ›</a>
        </div>
        <div class="grid grid-cols-2 gap-3.5 md:grid-cols-5 md:gap-4">
            <?php foreach (array_slice($flashSale, 0, 5) as $product): ?>
                <?php $this->partial('product-card', ['product' => $product]); ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ── Ad posters (پوسترهای تبلیغاتی، admin-managed) ───────── -->
<?php $posterBanners = $posterBanners ?? []; ?>
<?php if ($posterBanners !== []): $pCount = count($posterBanners); ?>
<section class="container-page pt-8 md:pt-12">
    <div class="grid gap-4 md:gap-5 <?= $pCount === 3 ? 'md:grid-cols-3' : ($pCount > 1 ? 'md:grid-cols-2' : '') ?>">
        <?php foreach ($posterBanners as $po): ?>
            <a href="<?= e(url((string) ($po['link_url'] ?? '/category') ?: '/category')) ?>"
               class="card-rise group relative block overflow-hidden rounded-2xl md:rounded-3xl">
                <?php if (!empty($po['image'])): ?>
                    <img src="<?= e(asset((string) $po['image'])) ?>" alt="<?= e($po['title']) ?>" loading="lazy" decoding="async"
                         class="<?= $pCount === 1 ? 'h-44 md:h-72' : 'h-40 md:h-56' ?> w-full object-cover transition duration-500 group-hover:scale-[1.03]">
                <?php else: ?>
                    <div class="flex <?= $pCount === 1 ? 'h-44 md:h-72' : 'h-40 md:h-56' ?> w-full items-center justify-center"
                         style="background:<?= (string) ($po['bg_color'] ?? '') ?: 'linear-gradient(135deg,#5C2D46,#8a5470)' ?>">
                        <div class="px-6 text-center">
                            <?php if (!empty($po['kicker'])): ?><div class="mb-1.5 text-[10px] font-bold tracking-wide text-white/80 md:text-[11px]"><?= e($po['kicker']) ?></div><?php endif; ?>
                            <div class="text-[17px] font-extrabold text-white md:text-[24px]"><?= e($po['title']) ?></div>
                            <?php if (!empty($po['subtitle'])): ?><div class="mt-1.5 text-[11.5px] text-white/85 md:text-[13px]"><?= e($po['subtitle']) ?></div><?php endif; ?>
                            <?php if (!empty($po['cta_label'])): ?><span class="mt-3 inline-block rounded-full bg-white px-5 py-2 text-[11.5px] font-bold text-secondary"><?= e($po['cta_label']) ?> ›</span><?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- ── Per-category rails (بنرهای تصویری میان صفحه بین ردیف‌ها) ── -->
<?php $inlineBanners = $inlineBanners ?? []; ?>
<?php foreach ($sections as $si => $section): ?>
<section class="pt-9 md:pt-12">
    <div class="container-page mb-4 flex items-baseline justify-between md:mb-6">
        <h2 class="section-title"><?= e($section['category']['name']) ?></h2>
        <a href="<?= e(url('/category/' . $section['category']['slug'])) ?>" class="text-[11.5px] text-mauve md:text-[13px]">مشاهده همه ›</a>
    </div>
    <div class="hscroll flex gap-3.5 overflow-x-auto px-4 pb-2 md:px-8 lg:px-16">
        <?php foreach ($section['items'] as $product): ?>
            <div class="w-[152px] flex-none md:w-[210px]">
                <?php $this->partial('product-card', ['product' => $product]); ?>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php if (isset($inlineBanners[$si])): $ib = $inlineBanners[$si]; ?>
<section class="container-page pt-9 md:pt-12">
    <a href="<?= e(url((string) ($ib['link_url'] ?? '/category') ?: '/category')) ?>" class="card-rise relative block overflow-hidden rounded-2xl md:rounded-3xl">
        <?php if (!empty($ib['image'])): ?>
            <img src="<?= e(asset((string) $ib['image'])) ?>" alt="<?= e($ib['title']) ?>" loading="lazy" decoding="async" class="h-28 w-full object-cover md:h-44">
        <?php else: ?>
            <div class="flex h-28 w-full items-center justify-center md:h-44" style="background:<?= (string) ($ib['bg_color'] ?? '') ?: 'linear-gradient(120deg,#5C2D46,#7c4862)' ?>">
                <div class="px-6 text-center">
                    <?php if (!empty($ib['kicker'])): ?><div class="mb-1 text-[10px] font-bold text-white/80"><?= e($ib['kicker']) ?></div><?php endif; ?>
                    <div class="text-[15px] font-extrabold text-white md:text-[20px]"><?= e($ib['title']) ?></div>
                    <?php if (!empty($ib['subtitle'])): ?><div class="mt-1 text-[11px] text-white/85 md:text-[12.5px]"><?= e($ib['subtitle']) ?></div><?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </a>
</section>
<?php endif; ?>
<?php endforeach; ?>

<!-- ── Brands (animated marquee of international wordmarks) ── -->
<?php
// Text "logos" with per-brand typography; an infinite CSS marquee.
$brandMarks = [
    ['Dove',           'font-serif italic text-[22px] md:text-[26px]'],
    ['CLEAR',          'text-[16px] font-extrabold tracking-[0.18em] md:text-[19px]'],
    ['Bioxcin',        'text-[17px] font-bold md:text-[20px]'],
    ['Dafi',           'text-[18px] font-extrabold italic md:text-[21px]'],
    ["L'ORÉAL",        'font-serif text-[16px] font-bold tracking-[0.22em] md:text-[19px]'],
    ['NIVEA',          'text-[17px] font-extrabold tracking-[0.14em] md:text-[20px]'],
    ['PANTENE',        'text-[15px] font-bold tracking-[0.2em] md:text-[18px]'],
    ['CLINIQUE',       'font-serif text-[15px] font-light tracking-[0.3em] md:text-[18px]'],
    ['Lancôme',        'font-serif text-[19px] md:text-[23px]'],
    ['LA ROCHE-POSAY', 'text-[13px] font-bold tracking-[0.12em] md:text-[15px]'],
    ['The Ordinary.',  'text-[15px] font-medium md:text-[17px]'],
    ['ESTĒE LAUDER',   'font-serif text-[14px] font-semibold tracking-[0.24em] md:text-[17px]'],
    ['OGX',            'text-[18px] font-extrabold tracking-[0.1em] md:text-[21px]'],
];
$brandRow = '';
foreach ($brandMarks as [$mark, $cls]) {
    $brandRow .= '<span class="mx-7 flex-none whitespace-nowrap text-secondary/80 transition hover:text-secondary md:mx-11 ' . $cls . '">' . e($mark) . '</span>';
}
?>
<section class="mt-10 bg-surface py-7 text-center md:py-10">
    <div class="mb-5 text-[10px] tracking-[0.3em] text-mauve md:text-[12px]">برندهای معتبر</div>
    <div class="marquee mx-auto max-w-page" dir="ltr">
        <div class="marquee-inner">
            <?= $brandRow ?>
            <?= $brandRow /* second copy makes the -50% loop seamless */ ?>
        </div>
    </div>
</section>

<!-- ── Reviews ────────────────────────────────────────────── -->
<?php if ($reviews !== []): ?>
<section class="pt-9 md:pt-12">
    <div class="container-page mb-4 md:mb-6"><h2 class="section-title">نظر مشتریان</h2></div>
    <div class="hscroll flex gap-3.5 overflow-x-auto px-4 pb-2 md:px-8 lg:px-16">
        <?php foreach ($reviews as $r): ?>
            <div class="w-[260px] flex-none rounded-2xl border border-line2 bg-white p-4 md:w-[320px]">
                <div class="mb-3 flex items-center gap-2.5">
                    <div class="h-9 w-9 rounded-full bg-[#F3EBE2]"></div>
                    <div>
                        <div class="text-[12.5px] font-bold text-[#333]"><?= e($r['author_name']) ?></div>
                        <div class="text-[11px] text-star"><?= str_repeat('★', (int) $r['rating']) . str_repeat('☆', 5 - (int) $r['rating']) ?></div>
                    </div>
                    <?php if (!empty($r['is_verified'])): ?>
                        <span class="badge-verified me-auto">خرید تاییدشده</span>
                    <?php endif; ?>
                </div>
                <p class="clamp-2 text-[12px] leading-7 text-[#666]"><?= e($r['body']) ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- ── Blog (latest published posts) ──────────────────────── -->
<?php if ($posts !== []): ?>
<section class="pt-9 md:pt-12">
    <div class="container-page mb-4 flex items-baseline justify-between md:mb-6">
        <h2 class="section-title">مجله‌ی زیبایی</h2>
        <a href="<?= e(url('/blog')) ?>" class="text-[11.5px] text-mauve transition hover:text-secondary md:text-[13px]">مشاهده همه ›</a>
    </div>
    <div class="hscroll flex gap-3.5 overflow-x-auto px-4 pb-2 md:px-8 lg:px-16">
        <?php foreach ($posts as $post): ?>
            <article class="w-[230px] flex-none overflow-hidden rounded-2xl border border-line2 bg-white md:w-[300px]">
                <a href="<?= e(url('/blog/' . $post['slug'])) ?>" class="block">
                    <div class="h-[120px] overflow-hidden bg-surface md:h-[160px]">
                        <?php if (!empty($post['cover_image'])): ?>
                            <img src="<?= e(asset((string) $post['cover_image'])) ?>" alt="<?= e($post['title']) ?>" loading="lazy" class="h-full w-full object-cover transition duration-500 hover:scale-105">
                        <?php endif; ?>
                    </div>
                    <div class="p-3.5">
                        <?php if (!empty($post['category_name'])): ?>
                            <div class="mb-1 text-[10px] font-semibold text-mauve"><?= e($post['category_name']) ?></div>
                        <?php endif; ?>
                        <h3 class="clamp-2 text-[13px] font-bold leading-6 text-[#333]"><?= e($post['title']) ?></h3>
                        <div class="mt-2 text-[10px] text-[#aaa] nums"><?= e(jdate((string) $post['published_at'], 'Y/m/d')) ?></div>
                    </div>
                </a>
            </article>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- ── Newsletter ─────────────────────────────────────────── 
<section class="container-page pt-10 md:pt-12">
    <div class="rounded-4xl bg-gradient-to-br from-secondary to-secondary-light p-6 text-center text-white md:p-12">
        <h2 class="text-[16px] font-bold md:text-[24px]">عضویت در خبرنامه</h2>
        <p class="mx-auto mt-2 max-w-md text-[11.5px] leading-7 opacity-85 md:text-[14px]">از تخفیف‌ها و محصولات جدید باخبر شوید</p>
        <form class="js-newsletter mx-auto mt-4 flex max-w-md gap-2 rounded-2xl bg-white/15 p-1.5">
            <input type="text" required class="flex-1 bg-transparent px-3 py-2 text-[12px] text-white outline-none placeholder:text-white/70 md:text-[14px]" placeholder="ایمیل یا شماره موبایل">
            <button type="submit" class="rounded-xl bg-primary px-5 py-2.5 text-[12px] font-bold text-secondary md:text-[14px]">عضویت</button>
        </form>
    </div>
</section> -->
