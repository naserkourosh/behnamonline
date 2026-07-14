<?php
/** @var array<string,mixed> $page */
$brand = (string) setting('brand_name', 'بهنام');
$this->meta([
    'title'       => ((string) ($page['seo_title'] ?: $page['title'])) . ' | ' . $brand,
    'description' => (string) ($page['seo_description'] ?: mb_substr(trim(strip_tags((string) $page['body'])), 0, 160)),
]);
$this->push('json_ld', '<script type="application/ld+json">' . json_encode([
    '@context' => 'https://schema.org',
    '@type'    => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'خانه', 'item' => base_url() . '/'],
        ['@type' => 'ListItem', 'position' => 2, 'name' => (string) $page['title'], 'item' => abs_url('page/' . $page['slug'])],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>');
?>
<div class="container-page py-8 md:py-12">
    <nav class="mb-5 text-[11px] text-mauve"><a href="<?= e(url('/')) ?>" class="hover:text-secondary">خانه</a> <span class="mx-1">/</span> <span class="text-[#777]"><?= e($page['title']) ?></span></nav>

    <div class="rounded-4xl bg-gradient-to-br from-secondary to-secondary-light p-7 text-center text-white md:p-10">
        <h1 class="text-[20px] font-extrabold md:text-[26px]"><?= e($page['title']) ?></h1>
    </div>

    <div class="rich mx-auto mt-8 max-w-3xl rounded-2xl border border-line2 bg-white p-6 text-[13.5px] leading-9 text-[#555] md:p-8">
        <?= html_clean((string) $page['body']) ?>
    </div>
</div>
