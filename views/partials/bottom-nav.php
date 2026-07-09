<?php
$path = '/' . trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/', '/');

// These pages have their own fixed bottom action bar (checkout button,
// add-to-cart, etc.). Hide the bottom nav there so they don't overlap.
foreach (['/cart', '/checkout', '/product', '/pay'] as $p) {
    if ($path === $p || str_starts_with($path, $p . '/')) {
        return;
    }
}

$is = static fn (string $p): bool => $p === '/' ? $path === '/' : str_starts_with($path, $p);

$items = [
    ['/', 'خانه', '<path d="M3 11l9-7 9 7"/><path d="M5 10v10h14V10"/>'],
    ['/category', 'دسته‌ها', '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>'],
    ['/cart', 'سبد', '<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z" stroke-linecap="round" stroke-linejoin="round"/><path d="M3 6h18" stroke-linecap="round"/><path d="M16 10a4 4 0 0 1-8 0" stroke-linecap="round"/>'],
    ['/account', 'حساب', '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/>'],
];
?>
<nav class="fixed bottom-0 left-1/2 z-50 flex w-full max-w-mobile -translate-x-1/2 items-center justify-around border-t border-line bg-white px-0 pb-3 pt-2.5 shadow-nav md:hidden">
    <?php foreach ($items as $i => $item): [$href, $label, $icon] = $item;
        $active = $is($href);
        $color  = $active ? 'text-secondary' : 'text-[#c2a9b6]';
        $weight = $active ? 'font-bold' : ''; ?>
        <a href="<?= e(url($href)) ?>" class="flex flex-col items-center gap-1 <?= $color ?>">
            <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><?= $icon ?></svg>
            <span class="text-[8.5px] <?= $weight ?>"><?= e($label) ?></span>
        </a>
    <?php endforeach; ?>
</nav>
