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

$slides = [
    ['kicker' => 'مجموعه‌ی جدید بهار', 't1' => 'درخششی که', 't2' => 'شایسته‌ی شماست', 'sub' => 'محصولات اصل آرایشی و مراقبتی، با ضمانت اصالت', 'grad' => 'linear-gradient(155deg,#F4E4E6 0%,#EAD0D4 55%,#E8C5C8 100%)'],
    ['kicker' => 'عطرهای لوکس', 't1' => 'رایحه‌ای', 't2' => 'به‌یادماندنی', 'sub' => 'مجموعه‌ی عطرهای زنانه‌ی اصل و ماندگار', 'grad' => 'linear-gradient(155deg,#EFE6DC 0%,#E4D3C4 55%,#D8BFA8 100%)'],
    ['kicker' => 'مراقبت پوست', 't1' => 'پوستی سالم،', 't2' => 'احساسی تازه', 'sub' => 'با برندهای معتبر جهانی و قیمت ویژه', 'grad' => 'linear-gradient(155deg,#E9E0E6 0%,#D9C7D2 55%,#C9AEC0 100%)'],
];

$flashEnds = (string) setting('flash_sale_ends_at', date('Y-m-d H:i:s', time() + 7200));
$remaining = max(0, strtotime($flashEnds) - time());
if ($remaining <= 0) {
    // Campaign expired/misaligned — roll to end of today so the countdown stays live.
    $remaining = strtotime('tomorrow') - time();
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
                <a href="<?= e(url('/category')) ?>"
                   class="hero-slide absolute inset-0 flex flex-col justify-end p-6 transition-opacity duration-700 md:justify-center md:px-14 <?= $i === 0 ? 'opacity-100' : 'opacity-0' ?>"
                   style="background:<?= $sl['grad'] ?>" <?= $i === 0 ? '' : 'aria-hidden="true"' ?>>
                    <div class="absolute inset-0 bg-gradient-to-t from-secondary/50 to-transparent md:bg-gradient-to-l md:from-transparent md:to-secondary/10"></div>
                    <div class="relative max-w-[460px]">
                        <div class="mb-2.5 pr-[0.32em] text-[10px] tracking-[0.32em] text-white/90 md:mb-4 md:text-[12px]"><?= e($sl['kicker']) ?></div>
                        <h2 class="mb-2 text-[30px] font-light leading-snug text-white md:text-[46px]"><?= e($sl['t1']) ?> <span class="font-bold"><?= e($sl['t2']) ?></span></h2>
                        <p class="mb-4 text-[12.5px] leading-7 text-white/90 md:mb-7 md:text-[15px]"><?= e($sl['sub']) ?></p>
                        <span class="inline-block rounded-full bg-white px-7 py-3 text-[13px] font-bold text-secondary shadow-card md:text-[14px]">خرید اکنون</span>
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

<!-- ── Per-category rails ─────────────────────────────────── -->
<?php foreach ($sections as $section): ?>
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
<?php endforeach; ?>

<!-- ── Brands ─────────────────────────────────────────────── -->
<?php if ($brands !== []): ?>
<section class="mt-10 bg-surface py-7 text-center md:py-10">
    <div class="mb-5 text-[10px] tracking-[0.3em] text-mauve md:text-[12px]">برندهای معتبر</div>
    <div class="hscroll mx-auto flex max-w-page items-center gap-7 overflow-x-auto px-6 md:justify-center md:gap-12">
        <?php foreach ($brands as $b): ?>
            <a href="<?= e(url('/category?brand[]=' . (int) $b['id'])) ?>" class="flex-none whitespace-nowrap text-[15px] font-light tracking-wide text-secondary transition hover:opacity-70 md:text-[18px]"><?= e($b['name']) ?></a>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

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

<!-- ── Blog ───────────────────────────────────────────────── -->
<section class="pt-9 md:pt-12">
    <div class="container-page mb-4 flex items-baseline justify-between md:mb-6">
        <h2 class="section-title">مجله‌ی زیبایی</h2>
        <span class="text-[11.5px] text-mauve md:text-[13px]">مشاهده همه ›</span>
    </div>
    <div class="hscroll flex gap-3.5 overflow-x-auto px-4 pb-2 md:px-8 lg:px-16">
        <?php foreach ($posts as $post): ?>
            <article class="w-[230px] flex-none overflow-hidden rounded-2xl border border-line2 bg-white md:w-[300px]">
                <div class="h-[120px] bg-[#F3EBE2] md:h-[160px]"></div>
                <div class="p-3.5">
                    <h3 class="clamp-2 text-[13px] font-bold leading-6 text-[#333]"><?= e($post['title']) ?></h3>
                    <div class="mt-2 text-[10px] text-[#aaa]"><?= e($post['date']) ?> · <?= e($post['read']) ?></div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<!-- ── Newsletter ─────────────────────────────────────────── -->
<section class="container-page pt-10 md:pt-12">
    <div class="rounded-4xl bg-gradient-to-br from-secondary to-secondary-light p-6 text-center text-white md:p-12">
        <h2 class="text-[16px] font-bold md:text-[24px]">عضویت در خبرنامه</h2>
        <p class="mx-auto mt-2 max-w-md text-[11.5px] leading-7 opacity-85 md:text-[14px]">از تخفیف‌ها و محصولات جدید باخبر شوید</p>
        <form class="js-newsletter mx-auto mt-4 flex max-w-md gap-2 rounded-2xl bg-white/15 p-1.5">
            <input type="text" required class="flex-1 bg-transparent px-3 py-2 text-[12px] text-white outline-none placeholder:text-white/70 md:text-[14px]" placeholder="ایمیل یا شماره موبایل">
            <button type="submit" class="rounded-xl bg-primary px-5 py-2.5 text-[12px] font-bold text-secondary md:text-[14px]">عضویت</button>
        </form>
    </div>
</section>
