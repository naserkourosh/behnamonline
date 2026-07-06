<?php
/** @var string $content */
/** @var array<string,mixed> $meta */
$brand = (string) setting('brand_name', 'بهنام');
$title = $meta['title'] ?? 'پنل مدیریت';
$path  = '/' . trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/', '/');
$adminUser = admin();

$nav = [
    ['dashboard',  '/admin',            'داشبورد',       '<rect x="3" y="3" width="8" height="8" rx="1"/><rect x="13" y="3" width="8" height="5" rx="1"/><rect x="13" y="10" width="8" height="11" rx="1"/><rect x="3" y="13" width="8" height="8" rx="1"/>'],
    ['reports',    '/admin/reports',    'گزارش‌ها و آمار','<path d="M4 20V10M10 20V4M16 20v-7M22 20H2" stroke-linecap="round"/>'],
    ['products',   '/admin/products',   'محصولات',       '<path d="M20 7l-8-4-8 4 8 4 8-4zM4 7v10l8 4 8-4V7M12 11v10"/>'],
    ['categories', '/admin/categories', 'دسته‌بندی‌ها',  '<path d="M3 7h7l2 2h9v9a2 2 0 0 1-2 2H3z"/>'],
    ['brands',     '/admin/brands',     'برندها',        '<circle cx="12" cy="12" r="9"/><path d="M12 3v18M3 12h18"/>'],
    ['tags',       '/admin/tags',       'برچسب‌ها',      '<path d="M20 12l-8 8-9-9V3h8z"/><circle cx="7.5" cy="7.5" r="1.5"/>'],
    ['orders',     '/admin/orders',     'سفارش‌ها',      '<path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2"/>'],
    ['coupons',    '/admin/coupons',    'کدهای تخفیف',   '<path d="M3 8a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v2a2 2 0 0 0 0 4v2a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-2a2 2 0 0 0 0-4z"/><path d="M9 9l6 6M9 15h.01M15 9h.01"/>'],
    ['popups',     '/admin/popups',     'پاپ‌آپ‌ها',     '<rect x="3" y="4" width="18" height="14" rx="2"/><path d="M8 21h8M12 18v3"/>'],
    ['customers',  '/admin/customers',  'مشتریان',       '<circle cx="9" cy="8" r="4"/><path d="M15 8a4 4 0 0 1 0 8M2 21c0-4 3.5-6 7-6s7 2 7 6M16 15c3 0 6 2 6 6"/>'],
    ['menus',      '/admin/menus',      'منوها',         '<path d="M3 6h18M3 12h18M3 18h18" stroke-linecap="round"/>'],
    ['banners',    '/admin/banners',    'بنرها',         '<rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="8" cy="10" r="1.6"/><path d="M21 16l-5-4-4 3-3-2-6 4"/>'],
    ['media',      '/admin/media',      'کتابخانه رسانه','<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/>'],
    ['blog',       '/admin/blog',       'مجله',          '<path d="M4 4h11l5 5v11a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1z"/><path d="M14 4v5h5M8 13h8M8 17h5"/>'],
    ['support',    '/admin/chat',       'گفتگوی آنلاین', '<path d="M8 10h8M8 14h5M21 12a8 8 0 0 1-11.5 7.2L4 20l.8-5.3A8 8 0 1 1 21 12z" stroke-linecap="round"/>'],
    ['support',    '/admin/tickets',    'تیکت‌ها',       '<path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8z"/>'],
    ['support',    '/admin/faq',        'سوالات متداول', '<circle cx="12" cy="12" r="9"/><path d="M9.1 9a3 3 0 0 1 5.8 1c0 2-3 3-3 3M12 17h.01"/>'],
    ['accounting', '/admin/accounting', 'حسابداری',      '<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 7h8M8 11h8M8 15h5"/>'],
    ['shipping',   '/admin/shipping',   'ارسال و مناطق', '<path d="M3 7h11v8H3zM14 10h4l3 3v2h-7z"/><circle cx="7" cy="17" r="1.6"/><circle cx="17" cy="17" r="1.6"/>'],
    ['sms',        '/admin/sms',        'پیامک‌ها',      '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>'],
    ['staff',      '/admin/staff',      'کاربران مدیریت','<circle cx="9" cy="8" r="3.2"/><path d="M3.5 20a5.5 5.5 0 0 1 11 0M16 5.5a3 3 0 0 1 0 6M17.5 20a5.5 5.5 0 0 0-3-4.9"/>'],
    ['settings',   '/admin/settings',   'تنظیمات',       '<circle cx="12" cy="12" r="3"/><path d="M19 12a7 7 0 0 0-.1-1l2-1.6-2-3.4-2.3 1a7 7 0 0 0-1.7-1l-.4-2.5h-4l-.4 2.5a7 7 0 0 0-1.7 1l-2.3-1-2 3.4 2 1.6a7 7 0 0 0 0 2l-2 1.6 2 3.4 2.3-1a7 7 0 0 0 1.7 1l.4 2.5h4l.4-2.5a7 7 0 0 0 1.7-1l2.3 1 2-3.4-2-1.6a7 7 0 0 0 .1-1z"/>'],
];
$isActive = static function (string $href) use ($path): bool {
    return $href === '/admin' ? $path === '/admin' : str_starts_with($path, $href);
};

// Unread live-chat badge (customer messages no admin has opened yet).
$chatUnread = 0;
if (admin_can('support')) {
    try {
        $chatUnread = (new \App\Repositories\ChatRepository())->adminUnreadTotal();
    } catch (\Throwable) {
        $chatUnread = 0;
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?> | <?= e($brand) ?></title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="<?= e(asset('assets/css/app.css')) ?>">
</head>
<body class="bg-[#F4F2F4] font-sans text-ink">
<?php $this->partial('loader'); ?>
<div class="flex min-h-screen">
    <!-- Sidebar -->
    <aside class="js-admin-sidebar fixed inset-y-0 right-0 z-50 w-64 -translate-x-0 overflow-y-auto border-l border-line bg-white transition-transform md:static md:translate-x-0 max-md:translate-x-full max-md:shadow-2xl">
        <div class="flex items-center gap-2.5 border-b border-line px-5 py-4">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl2 bg-gradient-to-br from-secondary to-secondary-light text-[15px] font-extrabold text-white"><?= e(mb_substr($brand, 0, 1)) ?></div>
            <div>
                <div class="text-[13px] font-bold text-[#333]">پنل مدیریت</div>
                <div class="text-[10px] text-[#999]"><?= e($brand) ?></div>
            </div>
        </div>
        <nav class="p-3">
            <?php foreach ($nav as [$cap, $href, $label, $icon]):
                if (!admin_can($cap)) { continue; }
                $active = $isActive($href); ?>
                <a href="<?= e(url($href)) ?>" class="mb-1 flex items-center gap-3 rounded-xl2 px-3.5 py-2.5 text-[13px] transition <?= $active ? 'bg-pink font-bold text-secondary' : 'text-[#555] hover:bg-surface' ?>">
                    <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><?= $icon ?></svg>
                    <span class="flex-1"><?= e($label) ?></span>
                    <?php if ($href === '/admin/chat' && $chatUnread > 0): ?>
                        <span class="flex h-5 min-w-5 items-center justify-center rounded-full bg-danger px-1.5 text-[10px] font-bold text-white nums"><?= fa($chatUnread) ?></span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </nav>
    </aside>
    <div class="js-admin-overlay fixed inset-0 z-40 hidden bg-black/40 md:hidden"></div>

    <!-- Main -->
    <div class="flex min-w-0 flex-1 flex-col">
        <header class="sticky top-0 z-30 flex items-center justify-between border-b border-line bg-white px-4 py-3 md:px-6">
            <div class="flex items-center gap-3">
                <button type="button" class="js-admin-menu text-secondary md:hidden" aria-label="منو">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M3 6h18M3 12h18M3 18h18" stroke-linecap="round"/></svg>
                </button>
                <h1 class="text-[15px] font-bold text-[#333] md:text-[17px]"><?= e($title) ?></h1>
            </div>
            <div class="flex items-center gap-3">
                <a href="<?= e(url('/')) ?>" target="_blank" class="hidden rounded-xl2 border border-line px-3 py-1.5 text-[12px] text-[#666] hover:text-secondary sm:block">مشاهده سایت ↗</a>
                <span class="text-[12.5px] font-semibold text-[#444]"><?= e($adminUser['name'] ?? '') ?></span>
                <a href="<?= e(url('/admin/logout')) ?>" class="js-logout rounded-xl2 bg-pink px-3 py-1.5 text-[12px] font-semibold text-secondary">خروج</a>
            </div>
        </header>

        <main class="p-4 md:p-6">
            <?php foreach (['success' => 'bg-[#E7F7F0] text-success', 'error' => 'bg-[#FDECEC] text-danger'] as $type => $cls):
                $msg = \App\Core\Session::flash($type);
                if ($msg): ?>
                    <div class="mb-4 rounded-xl2 px-4 py-3 text-[13px] font-semibold <?= $cls ?>"><?= e($msg) ?></div>
                <?php endif; endforeach; ?>
            <?= $content ?>
        </main>
    </div>
</div>
<form id="js-logout-form" method="post" action="<?= e(url('/admin/logout')) ?>" class="hidden"><?= csrf_field() ?></form>
<script src="<?= e(asset('assets/js/jquery.min.js')) ?>"></script>
<script src="<?= e(asset('assets/js/admin.js')) ?>" defer></script>
</body>
</html>
