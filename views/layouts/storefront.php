<?php
/** @var string $content */
/** @var array<string,mixed> $meta */

$brand       = (string) setting('brand_name', 'بهنام');
$wordmark    = (string) config('app.wordmark', 'BEHNAM');
$title       = $meta['title']       ?? ($brand . ' | فروشگاه لوکس آرایشی، بهداشتی و شوینده');
$description  = $meta['description'] ?? 'خرید اینترنتی محصولات آرایشی، مراقبت پوست، عطر و مواد بهداشتی و شوینده اصل، با ضمانت اصالت و ارسال سریع — فروشگاه ' . $brand . '.';
$robots      = $meta['robots']      ?? 'index, follow';
$ogImageRaw  = $meta['og_image']    ?? asset('assets/images/placeholder-product.svg');
$ogImage     = str_starts_with($ogImageRaw, 'http') ? $ogImageRaw : base_url() . $ogImageRaw;
$currentUrl  = abs_url(ltrim($_SERVER['REQUEST_URI'] ?? '/', '/'));
$canonical   = $meta['canonical']   ?? strtok($currentUrl, '?');
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#5C2D46">
    <title><?= e($title) ?></title>
    <meta name="description" content="<?= e($description) ?>">
    <meta name="robots" content="<?= e($robots) ?>">
    <link rel="canonical" href="<?= e($canonical) ?>">

    <!-- Open Graph / Twitter -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?= e($brand) ?>">
    <meta property="og:title" content="<?= e($title) ?>">
    <meta property="og:description" content="<?= e($description) ?>">
    <meta property="og:image" content="<?= e($ogImage) ?>">
    <meta property="og:url" content="<?= e($canonical) ?>">
    <meta name="twitter:card" content="summary_large_image">

    <link rel="preload" href="<?= e(asset('assets/fonts/vazirmatn-400.woff2')) ?>" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="<?= e(asset('assets/css/app.css')) ?>">
    <?= $this->stack('head') ?>

    <script type="application/ld+json"><?= json_encode([
        '@context' => 'https://schema.org',
        '@type'    => 'Organization',
        'name'     => $brand,
        'url'      => base_url(),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
    <?= $this->stack('json_ld') ?>
</head>
<body class="min-h-screen bg-white pb-[78px] md:pb-0">

    <a href="#main" class="skip-link">رفتن به محتوای اصلی</a>

    <?php $this->partial('loader'); ?>

    <?php if (setting('show_announcement', true)): ?>
        <div class="bg-secondary px-3 py-2 text-center text-[11.5px] tracking-wide text-[#F6E9EC] md:text-[13px]">
            <?= e(setting('announcement_text', 'ارسال رایگان سفارش‌های بالای ۵۰۰ هزار تومان')) ?>
        </div>
    <?php endif; ?>

    <?php $this->partial('header'); ?>

    <?php $this->partial('flash'); ?>

    <main id="main">
        <?= $content ?>
    </main>

    <?php $this->partial('footer'); ?>
    <?php $this->partial('bottom-nav'); ?>
    <?php $this->partial('floating-support'); ?>
    <?php $this->partial('compare-bar'); ?>
    <?php $this->partial('popup'); ?>

    <!-- Toast container -->
    <div id="toast-root" role="status" aria-live="polite" class="pointer-events-none fixed inset-x-0 bottom-24 z-[80] flex flex-col items-center gap-2 px-4 md:bottom-8"></div>

    <script>
        window.Behnam = {
            csrf: <?= json_encode(csrf_token(), JSON_UNESCAPED_SLASHES) ?>,
            baseUrl: "",
            placeholder: <?= json_encode(asset('assets/images/placeholder-product.svg'), JSON_UNESCAPED_SLASHES) ?>
        };
    </script>
    <script src="<?= e(asset('assets/js/jquery.min.js')) ?>" defer></script>
    <script src="<?= e(asset('assets/js/app.js')) ?>" defer></script>
    <?= $this->stack('scripts') ?>
</body>
</html>
