<?php

declare(strict_types=1);

/**
 * Seed demo data that mirrors the design mockups (categories, brands,
 * products with images/specs/variants, verified reviews). Idempotent:
 * it truncates the slice tables and re-inserts.
 *
 *   php database/seed.php
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
$PLACEHOLDER = 'assets/images/placeholder-product.svg';

echo "→ Clearing slice tables…\n";
$pdo->exec('SET FOREIGN_KEY_CHECKS=0');
$truncate = ['cart_items','carts','review_images','reviews','product_variants','product_attributes','product_images','products','brands','categories','settings'];
foreach (['product_categories', 'product_tags'] as $extra) {
    if ((int) $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = '{$extra}'")->fetchColumn() > 0) {
        $truncate[] = $extra;
    }
}
foreach ($truncate as $t) {
    $pdo->exec("TRUNCATE TABLE `{$t}`");
}
$pdo->exec('SET FOREIGN_KEY_CHECKS=1');

/* ── Settings ─────────────────────────────────────────────── */
$settings = [
    ['brand_name', 'بهنام', 'string'],
    ['announcement_text', 'ارسال رایگان سفارش‌های بالای ۵۰۰ هزار تومان · ۷ روز ضمانت بازگشت کالا · پشتیبانی ۲۴ ساعته', 'string'],
    ['show_announcement', '1', 'bool'],
    ['free_shipping_threshold', '500000', 'int'],
    ['show_stock_qty', '1', 'bool'],          // hide stock numbers when 0
    ['low_stock_threshold', '5', 'int'],      // show "تنها N عدد" at/below this
    ['flash_sale_ends_at', date('Y-m-d H:i:s', time() + 8 * 3600), 'string'],
];
$s = $pdo->prepare('INSERT INTO settings (setting_key, setting_value, setting_type, updated_at) VALUES (?,?,?,?)');
foreach ($settings as [$k, $v, $type]) {
    $s->execute([$k, $v, $type, $now]);
}
echo "✓ settings\n";

/* ── Categories ───────────────────────────────────────────── */
$categories = [
    ['skincare', 'مراقبت پوست'],
    ['makeup',   'آرایش'],
    ['perfume',  'عطر و ادکلن'],
    ['haircare', 'مراقبت مو'],
    ['body',     'مراقبت بدن'],
    ['hygiene',  'بهداشتی و شوینده'],
];
$catId = [];
$c = $pdo->prepare('INSERT INTO categories (name, slug, image, sort, is_active, seo_title, seo_description, created_at) VALUES (?,?,?,?,1,?,?,?)');
foreach ($categories as $i => [$slug, $name]) {
    $c->execute([$name, $slug, 'assets/images/placeholder-category.svg', $i, $name . ' | بهنام', 'خرید اینترنتی ' . $name . ' اصل با ضمانت اصالت', $now]);
    $catId[$slug] = (int) $pdo->lastInsertId();
}
echo "✓ categories\n";

/* ── Brands ───────────────────────────────────────────────── */
$brands = [
    'glossylab' => 'گلوسی‌لب', 'belrose' => 'بل‌رز', 'hydramax' => 'هیدرامکس',
    'auria' => 'اوریا', 'dermashield' => 'درماشیلد', 'lumina' => 'لومینا',
    'blackrose' => 'بلک‌رز', 'artistry' => 'آرتیستری', 'maisonnoor' => 'مزون نور',
    'flora' => 'فلورا', 'herbal' => 'هربال', 'keratinplus' => 'کراتین‌پلاس',
    'pura' => 'پیورا', 'skinlab' => 'سکین‌لب', 'stylist' => 'استایلیست',
];
$brandId = [];
$b = $pdo->prepare('INSERT INTO brands (name, slug, sort, is_active) VALUES (?,?,?,1)');
$i = 0;
foreach ($brands as $slug => $name) {
    $b->execute([$name, $slug, $i++]);
    $brandId[$slug] = (int) $pdo->lastInsertId();
}
echo "✓ brands\n";

/* ── Products ─────────────────────────────────────────────── */
// [slug, name, cat, brand, price, old, stock, isNew, isFeatured, flash, rating, ratingCount, short]
$products = [
    ['serum-vitamin-c', 'سرم روشن‌کننده ویتامین C ۲۰٪', 'skincare', 'glossylab', 285000, 320000, 2, 0, 1, 1, 4.8, 246, 'سرم روشن‌کننده با ویتامین C و هیالورونیک اسید'],
    ['daily-moisturizer', 'کرم آبرسان روزانه', 'skincare', 'hydramax', 198000, null, 24, 1, 1, 0, 4.6, 88, 'آبرسانی سبک و بدون چربی برای استفاده روزانه'],
    ['gentle-toner', 'تونر پاک‌کننده ملایم', 'skincare', 'pura', 135000, 150000, 40, 0, 0, 0, 4.5, 51, 'پاک‌سازی ملایم و تنظیم pH پوست'],
    ['hydra-sheet-mask', 'ماسک ورقه‌ای آبرسان', 'skincare', 'skinlab', 45000, null, 120, 1, 0, 0, 4.7, 33, 'ماسک ورقه‌ای آبرسان فوری'],
    ['sunscreen-spf50', 'کرم ضد آفتاب SPF50', 'skincare', 'dermashield', 175000, 198000, 30, 0, 1, 1, 4.7, 140, 'محافظت بالا، بدون رنگ و چرب نشدن'],
    ['gold-face-mask', 'ماسک صورت طلایی', 'skincare', 'auria', 220000, 260000, 12, 0, 0, 1, 4.5, 64, 'ماسک آبرسان و روشن‌کننده با عصاره طلا'],

    ['matte-lipstick-12', 'رژ لب مات مخملی شماره ۱۲', 'makeup', 'belrose', 145000, 170000, 18, 0, 1, 1, 4.9, 312, 'رژ لب مات با ماندگاری بالا'],
    ['foundation-hd', 'کرم پودر پوشش بالا SPF20', 'makeup', 'lumina', 310000, null, 22, 0, 1, 0, 4.6, 121, 'پوشش بالا با حالت طبیعی'],
    ['volume-mascara', 'ریمل حجم‌دهنده ضدآب', 'makeup', 'blackrose', 189000, null, 35, 1, 0, 0, 4.8, 96, 'حجم‌دهی و ضدآب'],
    ['nude-eyeshadow', 'پالت سایه چشم نود', 'makeup', 'artistry', 265000, 290000, 3, 0, 1, 0, 4.7, 77, 'پالت ۱۲ رنگ نود مات و براق'],
    ['blush-duo', 'رژگونه دو کاره', 'makeup', 'lumina', 125000, null, 50, 0, 0, 0, 4.5, 42, 'رژگونه و هایلایتر دو کاره'],
    ['concealer', 'کانسیلر روشن‌کننده', 'makeup', 'belrose', 168000, 195000, 28, 0, 0, 0, 4.6, 58, 'پوشش تیرگی دور چشم'],

    ['floral-nova', 'عطر زنانه فلورال نوّا', 'perfume', 'maisonnoor', 890000, null, 9, 0, 1, 0, 4.7, 73, 'رایحه گل‌های سفید و مشک'],
    ['edp-rosegold', 'ادوپرفیوم رز گلد', 'perfume', 'maisonnoor', 1250000, 1400000, 6, 0, 1, 1, 4.9, 110, 'ادوپرفیوم لوکس و ماندگار'],
    ['body-mist-strawberry', 'بادی میست توت‌فرنگی', 'perfume', 'flora', 95000, null, 60, 1, 0, 0, 4.4, 39, 'بادی میست خنک و میوه‌ای'],
    ['pocket-perfume', 'عطر جیبی مسافرتی', 'perfume', 'flora', 65000, null, 80, 0, 0, 0, 4.3, 25, 'عطر جیبی قابل حمل'],

    ['sulfate-free-shampoo', 'شامپو تقویتی بدون سولفات', 'haircare', 'herbal', 125000, null, 44, 0, 1, 0, 4.5, 67, 'تقویت مو بدون سولفات'],
    ['hair-repair-mask', 'ماسک ترمیم‌کننده مو', 'haircare', 'herbal', 168000, 190000, 26, 0, 0, 1, 4.6, 54, 'ترمیم موهای آسیب‌دیده'],
    ['split-end-serum', 'سرم احیای موخوره', 'haircare', 'keratinplus', 210000, null, 19, 1, 1, 0, 4.8, 81, 'احیای موخوره و درخشندگی'],
    ['styling-spray', 'اسپری حالت‌دهنده', 'haircare', 'stylist', 89000, null, 70, 0, 0, 0, 4.2, 30, 'حالت‌دهی و تثبیت'],
];

$pStmt = $pdo->prepare(
    'INSERT INTO products
        (category_id, brand_id, name, slug, sku, barcode, short_desc, description, aparat_embed,
         price, old_price, stock, reserved, low_stock_threshold, is_active, is_new, is_featured,
         on_flash_sale, expiration_date, rating_avg, rating_count, view_count,
         seo_title, seo_description, og_image, created_at, updated_at)
     VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,1,?,?,?,?,?,?,?,?,?,?,?,?)'
);
$imgStmt  = $pdo->prepare('INSERT INTO product_images (product_id, path, alt, title, sort, is_primary, is_hover) VALUES (?,?,?,?,?,?,?)');
$attrStmt = $pdo->prepare('INSERT INTO product_attributes (product_id, attr_key, attr_value, sort) VALUES (?,?,?,?)');
$varStmt  = $pdo->prepare('INSERT INTO product_variants (product_id, label, sku, price_override, stock, sort) VALUES (?,?,?,?,?,?)');
$hasPivot = (int) $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'product_categories'")->fetchColumn() > 0;
$pcStmt   = $hasPivot ? $pdo->prepare('INSERT IGNORE INTO product_categories (product_id, category_id) VALUES (?,?)') : null;

$productIds = [];
foreach ($products as $idx => $p) {
    [$slug, $name, $cat, $brand, $price, $old, $stock, $isNew, $isFeatured, $flash, $rating, $ratingCount, $short] = $p;

    $sku     = strtoupper(substr($brand, 0, 2)) . '-' . str_pad((string) ($idx + 1), 4, '0', STR_PAD_LEFT);
    $barcode = '626' . str_pad((string) (1000000 + $idx * 137), 10, '0', STR_PAD_LEFT);
    $desc    = '<p>' . $short . '. این محصول اصل و دارای کد رهگیری اصالت است. مناسب انواع پوست و فاقد مواد مضر.</p>';
    $aparat  = $idx === 0 ? 'https://www.aparat.com/embed/abcd123' : null;
    $expire  = '1406-' . str_pad((string) (($idx % 12) + 1), 2, '0', STR_PAD_LEFT);

    $pStmt->execute([
        $catId[$cat], $brandId[$brand], $name, $slug, $sku, $barcode, $short, $desc, $aparat,
        $price, $old, $stock, 0, 5, $isNew, $isFeatured, $flash,
        $expire . '-01', $rating, $ratingCount, random_int(80, 2400),
        $name . ' | خرید اینترنتی با بهترین قیمت | بهنام',
        $short . ' — اصل، با ضمانت اصالت و ارسال سریع از فروشگاه بهنام.',
        $PLACEHOLDER, $now, $now,
    ]);
    $pid = (int) $pdo->lastInsertId();
    $productIds[$slug] = $pid;

    // primary category membership (many-to-many pivot)
    if ($pcStmt) {
        $pcStmt->execute([$pid, $catId[$cat]]);
    }

    // images: primary + hover + two gallery
    $imgStmt->execute([$pid, $PLACEHOLDER, $name, $name . ' - نمای اصلی', 0, 1, 0]);
    $imgStmt->execute([$pid, $PLACEHOLDER, $name . ' - نمای دوم', $name . ' - نمای دوم', 1, 0, 1]);
    $imgStmt->execute([$pid, $PLACEHOLDER, $name . ' - جزئیات', $name . ' - جزئیات', 2, 0, 0]);
    $imgStmt->execute([$pid, $PLACEHOLDER, $name . ' - بسته‌بندی', $name . ' - بسته‌بندی', 3, 0, 0]);

    // attributes (specs table)
    $attrs = [
        ['برند', $brands[$brand]],
        ['کد کالا (SKU)', $sku],
        ['بارکد', $barcode],
        ['تاریخ انقضا', str_replace('-', '/', $expire)],
        ['مناسب', 'انواع پوست'],
        ['کشور سازنده', 'فرانسه'],
    ];
    foreach ($attrs as $j => [$k, $v]) {
        $attrStmt->execute([$pid, $k, $v, $j]);
    }
}
echo "✓ products (" . count($products) . ")\n";

/* ── Demo multi-category memberships (a product in several categories) ── */
if ($pcStmt) {
    $extraMemberships = [
        ['sunscreen-spf50', 'body'], ['gold-face-mask', 'body'],
        ['body-mist-strawberry', 'body'], ['pocket-perfume', 'body'],
        ['sulfate-free-shampoo', 'hygiene'], ['gentle-toner', 'hygiene'],
    ];
    foreach ($extraMemberships as [$mSlug, $mCat]) {
        if (isset($productIds[$mSlug], $catId[$mCat])) {
            $pcStmt->execute([$productIds[$mSlug], $catId[$mCat]]);
        }
    }
    echo "✓ product_categories (multi)\n";
}

/* ── Variants for the hero product (size selector) ────────── */
$serum = $productIds['serum-vitamin-c'];
$varStmt->execute([$serum, '۱۵ میلی‌لیتر', 'GL-VC20-15', 185000, 8, 0]);
$varStmt->execute([$serum, '۳۰ میلی‌لیتر', 'GL-VC20-30', null, 2, 1]);
$varStmt->execute([$serum, '۵۰ میلی‌لیتر', 'GL-VC20-50', 420000, 5, 2]);
echo "✓ variants\n";

/* ── Reviews (verified) ───────────────────────────────────── */
$reviews = [
    ['serum-vitamin-c', 'مریم احمدی', 5, 'بعد از دو هفته استفاده پوستم واقعاً شفاف‌تر شده. بسته‌بندی هم خیلی شیک بود.'],
    ['serum-vitamin-c', 'سارا کریمی', 5, 'اصل بودنش با کد رهگیری تایید شد. بوی ملایمی داره و جذب پوست سریع.'],
    ['serum-vitamin-c', 'نگار رضایی', 4, 'کیفیت خوبه ولی کاش حجمش بیشتر بود. در کل راضی‌ام.'],
    ['matte-lipstick-12', 'الهام موسوی', 5, 'ماندگاری فوق‌العاده، رنگش دقیقاً مثل عکسه.'],
    ['edp-rosegold', 'پریسا حسینی', 5, 'رایحه‌ی لوکس و ماندگار، حتماً دوباره می‌خرم.'],
    ['daily-moisturizer', 'زهرا نوری', 5, 'برای پوست مختلط عالیه، اصلاً چرب نمی‌کنه.'],
];
$rStmt = $pdo->prepare('INSERT INTO reviews (product_id, author_name, rating, body, is_verified, status, created_at) VALUES (?,?,?,?,1,?,?)');
foreach ($reviews as $k => [$slug, $author, $rating, $body]) {
    $rStmt->execute([$productIds[$slug], $author, $rating, $body, 'approved', date('Y-m-d H:i:s', time() - $k * 86400)]);
}
echo "✓ reviews\n";

echo "Done seeding. ✓\n";
