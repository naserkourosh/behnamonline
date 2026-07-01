<?php
/** @var list<array<string,mixed>> $posts @var list<array<string,mixed>> $categories */
/** @var array<string,mixed>|null $category @var int $page @var int $pages @var int $total */
$brand = (string) setting('brand_name', 'بهنام');
$pageTitle = $category !== null ? ($category['name'] . ' | مجله ' . $brand) : ('مجله ' . $brand);
$this->meta([
    'title'       => $pageTitle,
    'description' => 'مقالات آموزشی مراقبت پوست، آرایش و راهنمای خرید در مجله ' . $brand . '.',
]);
$img = static fn (?string $p): string => asset($p ?: 'assets/images/placeholder-product.svg');
?>
<div class="container-page py-6 md:py-8">
    <nav class="mb-4 flex items-center gap-2 text-[11.5px] text-[#999]">
        <a href="<?= e(url('/')) ?>" class="hover:text-secondary">خانه</a>
        <span>/</span>
        <a href="<?= e(url('/blog')) ?>" class="hover:text-secondary">مجله</a>
        <?php if ($category !== null): ?><span>/</span><span class="text-secondary"><?= e($category['name']) ?></span><?php endif; ?>
    </nav>

    <div class="mb-6">
        <h1 class="text-[20px] font-extrabold text-secondary md:text-[26px]"><?= $category !== null ? e($category['name']) : 'مجلهٔ زیبایی ' . e($brand) ?></h1>
        <p class="mt-1.5 text-[12.5px] text-[#888]"><?= fa($total) ?> مقاله</p>
    </div>

    <div class="md:flex md:items-start md:gap-8">
        <div class="md:flex-1">
            <?php if ($posts === []): ?>
                <div class="rounded-2xl border border-line2 bg-surface py-16 text-center text-[14px] text-[#666]">هنوز مقاله‌ای منتشر نشده است.</div>
            <?php else: ?>
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    <?php foreach ($posts as $p): ?>
                        <a href="<?= e(url('/blog/' . $p['slug'])) ?>" class="group flex flex-col overflow-hidden rounded-2xl border border-line2 bg-white transition hover:shadow-soft">
                            <div class="aspect-[16/10] overflow-hidden bg-surface">
                                <img src="<?= e($img($p['cover_image'])) ?>" alt="<?= e($p['title']) ?>" loading="lazy" class="h-full w-full object-cover transition group-hover:scale-105">
                            </div>
                            <div class="flex flex-1 flex-col p-4">
                                <?php if (!empty($p['category_name'])): ?>
                                    <span class="mb-2 inline-block w-fit rounded-lg bg-pink px-2 py-0.5 text-[10.5px] font-bold text-secondary"><?= e($p['category_name']) ?></span>
                                <?php endif; ?>
                                <h2 class="text-[14px] font-bold leading-7 text-[#333] group-hover:text-secondary"><?= e($p['title']) ?></h2>
                                <p class="mt-1.5 line-clamp-2 flex-1 text-[12px] leading-6 text-[#888]"><?= e($p['excerpt']) ?></p>
                                <div class="mt-3 flex items-center justify-between text-[10.5px] text-[#aaa]">
                                    <span><?= e($p['author_name'] ?: $brand) ?></span>
                                    <span class="nums"><?= $p['published_at'] ? jdate((string) $p['published_at']) : '' ?></span>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>

                <?php if ($pages > 1): ?>
                    <div class="mt-8 flex items-center justify-center gap-2">
                        <?php $base = url('/blog' . ($category !== null ? '/category/' . $category['slug'] : '')); ?>
                        <?php for ($i = 1; $i <= $pages; $i++): ?>
                            <a href="<?= e($base . '?page=' . $i) ?>" class="flex h-9 min-w-9 items-center justify-center rounded-xl2 border px-3 text-[12.5px] nums <?= $i === $page ? 'border-secondary bg-secondary text-white' : 'border-line text-[#666] hover:border-secondary' ?>"><?= fa($i) ?></a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <aside class="mt-8 md:mt-0 md:w-64 md:flex-none">
            <div class="rounded-2xl border border-line2 bg-white p-4">
                <h3 class="mb-3 text-[13px] font-bold text-[#333]">دسته‌بندی‌ها</h3>
                <a href="<?= e(url('/blog')) ?>" class="mb-1.5 flex items-center justify-between rounded-xl2 px-3 py-2 text-[12.5px] <?= $category === null ? 'bg-pink font-bold text-secondary' : 'text-[#555] hover:bg-surface' ?>">
                    همهٔ مقالات
                </a>
                <?php foreach ($categories as $c): ?>
                    <a href="<?= e(url('/blog/category/' . $c['slug'])) ?>" class="mb-1.5 flex items-center justify-between rounded-xl2 px-3 py-2 text-[12.5px] <?= ($category['id'] ?? 0) === $c['id'] ? 'bg-pink font-bold text-secondary' : 'text-[#555] hover:bg-surface' ?>">
                        <span><?= e($c['name']) ?></span>
                        <span class="nums text-[10.5px] text-[#aaa]"><?= fa((int) $c['post_count']) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </aside>
    </div>
</div>
