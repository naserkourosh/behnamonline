<?php
$path = '/' . trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/', '/');
$items = [
    ['/account', 'داشبورد', '📊'],
    ['/account/orders', 'سفارش‌های من', '📋'],
    ['/account/wishlist', 'لیست علاقه‌مندی‌ها', '♡'],
    ['/account/addresses', 'آدرس‌های من', '📍'],
    ['/account/tickets', 'تیکت‌های پشتیبانی', '💬'],
    ['/account/profile', 'ویرایش پروفایل', '👤'],
];
$soon = [
    ['کیف پول و تراکنش‌ها', '💳'],
    ['امتیازها و باشگاه مشتریان', '★'],
    ['دیدگاه‌های من', '✎'],
    ['فاکتورها', '🧾'],
];
?>
<div class="overflow-hidden rounded-2xl border border-line2 bg-white">
    <?php foreach ($items as [$href, $label, $icon]):
        $active = $path === $href; ?>
        <a href="<?= e(url($href)) ?>" class="flex items-center gap-3 border-b border-line2 px-4 py-3.5 text-[13px] <?= $active ? 'bg-pink font-bold text-secondary' : 'text-[#444]' ?>">
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-surface"><?= $icon ?></span>
            <span class="flex-1"><?= e($label) ?></span>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="1.7"><path d="M15 6l-6 6 6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </a>
    <?php endforeach; ?>
    <?php foreach ($soon as [$label, $icon]): ?>
        <div class="flex items-center gap-3 border-b border-line2 px-4 py-3.5 text-[13px] text-[#bbb]">
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-surface opacity-60"><?= $icon ?></span>
            <span class="flex-1"><?= e($label) ?></span>
            <span class="rounded-lg bg-surface px-2 py-0.5 text-[9px]">به‌زودی</span>
        </div>
    <?php endforeach; ?>
    <form method="post" action="<?= e(url('/logout')) ?>">
        <?= csrf_field() ?>
        <button type="submit" class="flex w-full items-center gap-3 px-4 py-3.5 text-[13px] font-semibold text-danger">
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#FDECEC]">⎋</span>
            خروج از حساب کاربری
        </button>
    </form>
</div>
