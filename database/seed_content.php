<?php

declare(strict_types=1);

/**
 * Seeds demo blog content and FAQ entries (Phase 6). Idempotent.
 *
 *   php database/seed_content.php
 */

use App\Core\Config;
use App\Core\Database;
use App\Core\Env;

define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/app/Core/autoload.php';

Env::load(BASE_PATH . '/.env');
Config::load(BASE_PATH . '/config');
date_default_timezone_set((string) Config::get('app.timezone', 'Asia/Tehran'));

$pdo = Database::connection();
$now = date('Y-m-d H:i:s');

/* ── Blog categories ── */
$cats = [
    ['مراقبت از پوست', 'skincare-tips'],
    ['آرایش و زیبایی', 'makeup'],
    ['راهنمای خرید', 'buying-guide'],
];
$catId = [];
$findCat = $pdo->prepare('SELECT id FROM blog_categories WHERE slug = ? LIMIT 1');
$insCat  = $pdo->prepare('INSERT INTO blog_categories (name, slug, sort, is_active, created_at) VALUES (?,?,?,1,?)');
foreach ($cats as $i => [$name, $slug]) {
    $findCat->execute([$slug]);
    $id = (int) ($findCat->fetchColumn() ?: 0);
    if ($id === 0) {
        $insCat->execute([$name, $slug, $i, $now]);
        $id = (int) $pdo->lastInsertId();
    }
    $catId[$slug] = $id;
}
echo "✓ blog categories ready (" . count($catId) . ")\n";

/* ── Blog posts ── */
$posts = [
    [
        'skincare-tips',
        'راهنمای کامل مراقبت روزانه از پوست',
        'daily-skincare-routine',
        'یک روتین اصولی مراقبت از پوست می‌تواند ظاهر و سلامت پوست شما را متحول کند. در این مقاله گام‌به‌گام یاد می‌گیرید.',
        '<p>مراقبت از پوست با سه گام ساده آغاز می‌شود: <strong>پاک‌سازی، آبرسانی و محافظت در برابر آفتاب</strong>. هر صبح پوست خود را با یک شوینده ملایم بشویید.</p><p>استفاده روزانه از ضدآفتاب با <em>SPF ۵۰</em> مهم‌ترین گام ضدپیری است.</p>',
    ],
    [
        'makeup',
        'ترفندهای آرایش ماندگار برای مهمانی',
        'long-lasting-makeup',
        'با چند نکته ساده آرایش خود را برای ساعت‌ها ماندگار کنید و در طول مهمانی درخشان بمانید.',
        '<p>برای ماندگاری بیشتر، ابتدا از <strong>پرایمر</strong> استفاده کنید و در پایان با پودر فیکس، آرایش را تثبیت کنید.</p>',
    ],
    [
        'buying-guide',
        'چطور محصول اصل را از تقلبی تشخیص دهیم؟',
        'spot-authentic-products',
        'خرید محصولات آرایشی اصل اهمیت زیادی برای سلامت پوست دارد. این نکات به شما کمک می‌کند.',
        '<p>همیشه کد اصالت کالا را بررسی کنید و از فروشگاه‌های معتبر خرید کنید. بهنام ضمانت اصالت تمام محصولات را ارائه می‌دهد.</p>',
    ],
];
$findPost = $pdo->prepare('SELECT id FROM blog_posts WHERE slug = ? LIMIT 1');
$insPost  = $pdo->prepare(
    'INSERT INTO blog_posts (category_id, title, slug, excerpt, body, author_name, status, is_featured, published_at, created_at, updated_at)
     VALUES (?,?,?,?,?,?,\'published\',?,?,?,?)'
);
$made = 0;
foreach ($posts as $i => [$catSlug, $title, $slug, $excerpt, $body]) {
    $findPost->execute([$slug]);
    if ((int) ($findPost->fetchColumn() ?: 0) === 0) {
        $insPost->execute([$catId[$catSlug], $title, $slug, $excerpt, $body, 'تیم بهنام', $i === 0 ? 1 : 0, $now, $now, $now]);
        $made++;
    }
}
echo "✓ blog posts ready ({$made} new)\n";

/* ── FAQ ── */
$faqs = [
    ['سفارش و ارسال', 'هزینه و زمان ارسال چقدر است؟', 'سفارش‌های بالای ۵۰۰ هزار تومان ارسال رایگان دارند. ارسال معمولاً ۲ تا ۴ روز کاری زمان می‌برد.'],
    ['سفارش و ارسال', 'چطور سفارشم را پیگیری کنم؟', 'پس از ارسال، کد رهگیری از طریق پیامک برای شما ارسال می‌شود و در بخش «سفارش‌های من» نیز قابل مشاهده است.'],
    ['پرداخت', 'چه روش‌های پرداختی پشتیبانی می‌شود؟', 'پرداخت آنلاین از طریق درگاه زرین‌پال و همچنین کارت‌به‌کارت امکان‌پذیر است.'],
    ['بازگشت کالا', 'شرایط مرجوعی کالا چیست؟', 'کالای سالم و استفاده‌نشده تا ۷ روز پس از دریافت قابل مرجوع است.'],
    ['اصالت کالا', 'آیا محصولات اصل هستند؟', 'بله، تمام محصولات بهنام دارای ضمانت اصالت و کد رهگیری سلامت فیزیکی هستند.'],
];
$countFaq = (int) $pdo->query('SELECT COUNT(*) FROM faqs')->fetchColumn();
if ($countFaq === 0) {
    $insFaq = $pdo->prepare('INSERT INTO faqs (category, question, answer, sort, is_active, created_at) VALUES (?,?,?,?,1,?)');
    foreach ($faqs as $i => [$cat, $q, $a]) {
        $insFaq->execute([$cat, $q, $a, $i, $now]);
    }
    echo "✓ FAQ seeded (" . count($faqs) . ")\n";
} else {
    echo "• FAQ already present ({$countFaq})\n";
}

echo "Done. ✓\n";
