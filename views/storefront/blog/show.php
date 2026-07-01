<?php
/** @var array<string,mixed> $post @var list<array<string,mixed>> $comments @var list<array<string,mixed>> $related */
$brand = (string) setting('brand_name', 'بهنام');
$cover = asset((string) ($post['cover_image'] ?: 'assets/images/placeholder-product.svg'));
$this->meta([
    'title'       => ($post['seo_title'] ?: $post['title'] . ' | مجله ' . $brand),
    'description' => ($post['seo_description'] ?: $post['excerpt'] ?: $post['title']),
    'og_image'    => $cover,
]);
$this->push('json_ld', '<script type="application/ld+json">' . json_encode([
    '@context'      => 'https://schema.org',
    '@type'         => 'Article',
    'headline'      => $post['title'],
    'image'         => str_starts_with($cover, 'http') ? $cover : base_url() . $cover,
    'datePublished' => $post['published_at'] ? date('c', strtotime((string) $post['published_at'])) : null,
    'dateModified'  => $post['updated_at'] ? date('c', strtotime((string) $post['updated_at'])) : null,
    'author'        => ['@type' => 'Organization', 'name' => $post['author_name'] ?: $brand],
    'publisher'     => ['@type' => 'Organization', 'name' => $brand],
    'mainEntityOfPage' => abs_url('blog/' . $post['slug']),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>');

$loggedIn = admin() === null && \App\Services\AuthService::check();
$currentUser = \App\Services\AuthService::user();
?>
<article class="container-page max-w-3xl py-6 md:py-10">
    <nav class="mb-4 flex flex-wrap items-center gap-2 text-[11.5px] text-[#999]">
        <a href="<?= e(url('/')) ?>" class="hover:text-secondary">خانه</a><span>/</span>
        <a href="<?= e(url('/blog')) ?>" class="hover:text-secondary">مجله</a>
        <?php if (!empty($post['category_name'])): ?><span>/</span><a href="<?= e(url('/blog/category/' . $post['category_slug'])) ?>" class="hover:text-secondary"><?= e($post['category_name']) ?></a><?php endif; ?>
    </nav>

    <h1 class="text-[22px] font-extrabold leading-9 text-secondary md:text-[30px]"><?= e($post['title']) ?></h1>
    <div class="mt-3 flex items-center gap-4 text-[11.5px] text-[#999]">
        <span>✍ <?= e($post['author_name'] ?: $brand) ?></span>
        <span class="nums"><?= $post['published_at'] ? jdate((string) $post['published_at']) : '' ?></span>
        <span class="nums"><?= fa((int) $post['view_count']) ?> بازدید</span>
    </div>

    <div class="mt-5 overflow-hidden rounded-2xl bg-surface">
        <img src="<?= e($cover) ?>" alt="<?= e($post['title']) ?>" class="w-full object-cover">
    </div>

    <div class="prose-fa mt-6 space-y-4 text-[14px] leading-8 text-[#333]">
        <?= html_clean((string) $post['body']) ?>
    </div>

    <!-- Comments -->
    <section id="comments" class="mt-10 border-t border-line2 pt-8">
        <h2 class="mb-5 text-[16px] font-bold text-secondary">دیدگاه‌ها <span class="text-[12px] font-normal text-[#999] nums">(<?= fa(count($comments)) ?>)</span></h2>

        <?php if ($comments === []): ?>
            <p class="mb-6 rounded-xl2 bg-surface px-4 py-4 text-[12.5px] text-[#888]">هنوز دیدگاهی ثبت نشده است. اولین نفر باشید!</p>
        <?php else: ?>
            <div class="mb-8 space-y-4">
                <?php foreach ($comments as $c): ?>
                    <div class="rounded-2xl border border-line2 bg-white p-4">
                        <div class="mb-2 flex items-center justify-between">
                            <span class="text-[12.5px] font-bold text-[#444]"><?= e($c['author_name']) ?></span>
                            <span class="text-[10.5px] text-[#aaa] nums"><?= jdate((string) $c['created_at']) ?></span>
                        </div>
                        <p class="text-[12.5px] leading-7 text-[#666]"><?= nl2br(e($c['body'])) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= e(url('/blog/' . $post['slug'] . '/comment')) ?>" class="rounded-2xl border border-line2 bg-surface p-4">
            <?= csrf_field() ?>
            <h3 class="mb-3 text-[13px] font-bold text-[#333]">ثبت دیدگاه</h3>
            <?php if (!$loggedIn): ?>
                <input name="author_name" placeholder="نام شما" class="mb-2 w-full rounded-xl2 border border-line bg-white px-3.5 py-2.5 text-[13px] outline-none focus:border-secondary">
            <?php else: ?>
                <p class="mb-2 text-[11.5px] text-[#888]">به‌عنوان <span class="font-bold text-secondary"><?= e(trim(($currentUser['first_name'] ?? '') . ' ' . ($currentUser['last_name'] ?? ''))) ?></span></p>
            <?php endif; ?>
            <textarea name="body" rows="3" placeholder="دیدگاه خود را بنویسید…" class="w-full rounded-xl2 border border-line bg-white px-3.5 py-2.5 text-[13px] outline-none focus:border-secondary"></textarea>
            <div class="mt-3 flex items-center justify-between">
                <span class="text-[10.5px] text-[#aaa]">دیدگاه پس از تأیید مدیر نمایش داده می‌شود.</span>
                <button class="btn-primary px-6 py-2.5 text-[13px]">ارسال دیدگاه</button>
            </div>
        </form>
    </section>

    <?php if ($related !== []): ?>
        <section class="mt-10">
            <h2 class="mb-4 text-[16px] font-bold text-secondary">مطالب مرتبط</h2>
            <div class="grid gap-4 sm:grid-cols-3">
                <?php foreach ($related as $r): if ((int) $r['id'] === (int) $post['id']) { continue; } ?>
                    <a href="<?= e(url('/blog/' . $r['slug'])) ?>" class="group overflow-hidden rounded-2xl border border-line2 bg-white">
                        <div class="aspect-[16/10] overflow-hidden bg-surface">
                            <img src="<?= e(asset((string) ($r['cover_image'] ?: 'assets/images/placeholder-product.svg'))) ?>" alt="<?= e($r['title']) ?>" loading="lazy" class="h-full w-full object-cover transition group-hover:scale-105">
                        </div>
                        <h3 class="p-3 text-[12.5px] font-bold leading-6 text-[#333] group-hover:text-secondary"><?= e($r['title']) ?></h3>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</article>
