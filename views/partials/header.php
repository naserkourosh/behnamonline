<?php
$categories = (new \App\Repositories\CategoryRepository())->allActiveWithCounts();
// Admin-managed primary menu drives the nav; fall back to categories.
$menuItems = (new \App\Repositories\MenuRepository())->primaryItems();
if ($menuItems === []) {
    $menuItems = array_map(static fn ($c) => ['label' => $c['name'], 'url' => '/category/' . $c['slug']], $categories);
}
$menuHref = static fn (string $u): string => str_starts_with($u, 'http') ? $u : url($u);
$cartCount  = (new \App\Services\CartService())->count();
$brand      = (string) setting('brand_name', 'بهنام');
$wordmark   = (string) config('app.wordmark', 'BEHNAM');
$authUser   = auth();
$accountUrl = $authUser !== null ? url('/account') : url('/login');
$accountLbl = $authUser !== null ? \App\Services\AuthService::displayName() : 'ورود / ثبت‌نام';

$logo = '<a href="' . e(url('/')) . '" class="text-center leading-none">'
    . '<div class="text-[22px] font-extrabold text-secondary md:text-[30px]">' . e($brand) . '<span class="text-gold">.</span></div>'
    . '<div class="mt-1 pr-[0.4em] text-[8px] tracking-[0.4em] text-gold md:text-[9px]">' . e($wordmark) . '</div></a>';
?>
<header class="sticky top-0 z-40 bg-white/95 backdrop-blur">
    <!-- ── Mobile bar ────────────────────────────────────────── -->
    <div class="md:hidden">
        <div class="flex items-center justify-between px-[18px] pb-2.5 pt-3.5">
            <button type="button" class="js-menu-open text-secondary" aria-label="منو">
                <svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M3 6h18M3 12h18M3 18h18" stroke-linecap="round"/></svg>
            </button>
            <?= $logo ?>
            <div class="flex gap-4 text-secondary">
                <a href="<?= e(url('/category')) ?>" aria-label="علاقه‌مندی" class="text-secondary">
                    <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 21s-7-4.4-9.4-8.6C1 9.3 2.6 5.6 6 5.6c2 0 3 1.1 4 2.6 1-1.5 2-2.6 4-2.6 3.4 0 5 3.7 3.4 6.8C19 16.6 12 21 12 21z"/></svg>
                </a>
                <a href="<?= e(url('/cart')) ?>" class="relative text-secondary" aria-label="سبد خرید">
                    <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M6 8h12l-1 12H7L6 8z"/><path d="M9 8a3 3 0 0 1 6 0"/></svg>
                    <span class="js-cart-count absolute -left-1.5 -top-1.5 flex h-[15px] min-w-[15px] items-center justify-center rounded-full bg-gold px-1 text-[8px] font-bold text-white <?= $cartCount > 0 ? '' : 'hidden' ?>"><?= fa($cartCount) ?></span>
                </a>
            </div>
        </div>
        <div class="px-[18px] pb-3.5 pt-1">
            <form action="<?= e(url('/category')) ?>" method="get" class="relative">
                <div class="flex items-center gap-2.5 rounded-xl2 border border-line bg-surface px-3.5 py-2.5 text-mauve">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4-4" stroke-linecap="round"/></svg>
                    <input name="q" autocomplete="off" aria-label="جستجو" class="js-search-input w-full bg-transparent text-[13px] text-ink outline-none placeholder:text-mauve" placeholder="جستجوی محصولات، برندها…">
                </div>
                <div class="js-search-results absolute inset-x-0 top-full z-50 mt-2 hidden overflow-hidden rounded-2xl border border-line bg-white shadow-card"></div>
            </form>
        </div>
    </div>

    <!-- ── Desktop bar ───────────────────────────────────────── -->
    <div class="hidden border-b border-line2 md:block">
        <div class="container-page grid grid-cols-[auto_1fr_auto] items-center gap-10 py-5">
            <?= $logo ?>
            <form action="<?= e(url('/category')) ?>" method="get" class="relative mx-auto w-full max-w-[620px]">
                <div class="flex items-center gap-3 rounded-2xl border border-line bg-surface px-5 py-3.5 text-mauve">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4-4" stroke-linecap="round"/></svg>
                    <input name="q" autocomplete="off" aria-label="جستجو" class="js-search-input w-full bg-transparent text-[14px] text-ink outline-none placeholder:text-mauve" placeholder="جستجوی محصولات، برندها و دسته‌بندی‌ها…">
                </div>
                <div class="js-search-results absolute inset-x-0 top-full z-50 mt-2 hidden overflow-hidden rounded-2xl border border-line bg-white shadow-card"></div>
            </form>
            <div class="flex items-center gap-6 text-secondary">
                <a href="<?= e(url('/category')) ?>" class="flex flex-col items-center gap-1">
                    <svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 21s-7-4.4-9.4-8.6C1 9.3 2.6 5.6 6 5.6c2 0 3 1.1 4 2.6 1-1.5 2-2.6 4-2.6 3.4 0 5 3.7 3.4 6.8C19 16.6 12 21 12 21z"/></svg>
                    <span class="text-[10px] text-[#8a7080]">علاقه‌مندی</span>
                </a>
                <a href="<?= e(url('/cart')) ?>" class="relative flex flex-col items-center gap-1">
                    <svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 8h12l-1 12H7L6 8z"/><path d="M9 8a3 3 0 0 1 6 0"/></svg>
                    <span class="text-[10px] text-[#8a7080]">سبد خرید</span>
                    <span class="js-cart-count absolute -top-1.5 left-1.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-gold px-1 text-[9px] font-bold text-white <?= $cartCount > 0 ? '' : 'hidden' ?>"><?= fa($cartCount) ?></span>
                </a>
                <a href="<?= e($accountUrl) ?>" class="flex items-center gap-2 rounded-xl2 bg-secondary px-4 py-2.5 text-white">
                    <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/></svg>
                    <span class="max-w-[120px] truncate text-[12.5px] font-semibold"><?= e($accountLbl) ?></span>
                </a>
            </div>
        </div>
        <!-- mega-nav -->
        <div class="container-page flex items-center gap-8 border-t border-line2 py-3.5">
            <a href="<?= e(url('/category')) ?>" class="flex items-center gap-2 border-l border-line pl-3.5 text-[13.5px] font-bold text-secondary">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 6h18M3 12h18M3 18h18" stroke-linecap="round"/></svg>
                همه دسته‌بندی‌ها
            </a>
            <?php foreach (array_slice($menuItems, 0, 6) as $mi): ?>
                <a href="<?= e($menuHref($mi['url'])) ?>" class="text-[13.5px] font-medium text-[#5a5a5a] transition hover:text-secondary"><?= e($mi['label']) ?></a>
            <?php endforeach; ?>
            <a href="<?= e(url('/blog')) ?>" class="text-[13.5px] font-medium text-[#5a5a5a] transition hover:text-secondary">مجله</a>
            <a href="<?= e(url('/faq')) ?>" class="text-[13.5px] font-medium text-[#5a5a5a] transition hover:text-secondary">سوالات متداول</a>
            <a href="<?= e(url('/category?in_stock=1')) ?>" class="me-auto text-[13px] font-bold text-danger">🔥 حراج ویژه</a>
        </div>
    </div>
</header>

<!-- ── Mobile off-canvas menu ───────────────────────────────── -->
<div class="js-menu-overlay fixed inset-0 z-[70] hidden bg-black/40 md:hidden"></div>
<aside class="js-menu-panel fixed inset-y-0 right-0 z-[71] hidden w-[80%] max-w-[320px] overflow-y-auto bg-white md:hidden">
    <div class="flex items-center justify-between border-b border-line px-5 py-4">
        <span class="text-[15px] font-bold text-secondary"><?= e($brand) ?><span class="text-gold">.</span></span>
        <button type="button" class="js-menu-close text-2xl leading-none text-mauve" aria-label="بستن">&times;</button>
    </div>
    <nav class="flex flex-col py-2">
        <a href="<?= e(url('/')) ?>" class="border-b border-line2 px-5 py-3.5 text-[14px] text-ink">خانه</a>
        <?php foreach ($menuItems as $mi): ?>
            <a href="<?= e($menuHref($mi['url'])) ?>" class="flex items-center justify-between border-b border-line2 px-5 py-3.5 text-[14px] text-ink">
                <span><?= e($mi['label']) ?></span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="1.7"><path d="M15 6l-6 6 6 6" stroke-linecap="round"/></svg>
            </a>
        <?php endforeach; ?>
        <a href="<?= e(url('/blog')) ?>" class="border-b border-line2 px-5 py-3.5 text-[14px] text-ink">مجله</a>
        <a href="<?= e(url('/faq')) ?>" class="border-b border-line2 px-5 py-3.5 text-[14px] text-ink">سوالات متداول</a>
        <a href="<?= e(url('/cart')) ?>" class="border-b border-line2 px-5 py-3.5 text-[14px] text-ink">سبد خرید</a>
        <a href="<?= e($accountUrl) ?>" class="px-5 py-3.5 text-[14px] font-semibold text-secondary"><?= $authUser !== null ? 'حساب کاربری من' : 'ورود / ثبت‌نام' ?></a>
    </nav>
</aside>
