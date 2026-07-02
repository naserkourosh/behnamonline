<?php

declare(strict_types=1);

/**
 * Seeds a demo coupon, a welcome popup, and enables the loyalty club.
 * Idempotent — safe to run repeatedly.
 *
 *   php database/seed_promotions.php
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

/* Demo coupon: 10% off, min cart 200,000 T, capped at 100,000 T, once per user */
$exists = (int) $pdo->query("SELECT COUNT(*) FROM coupons WHERE code = 'WELCOME10'")->fetchColumn();
if ($exists === 0) {
    $pdo->prepare(
        'INSERT INTO coupons (code, description, type, value, min_cart, max_discount, per_user_limit, is_active, created_at)
         VALUES (?,?,?,?,?,?,?,1,?)'
    )->execute(['WELCOME10', 'تخفیف خوش‌آمدگویی', 'percent', 10, 200000, 100000, 1, $now]);
    echo "✓ demo coupon WELCOME10 created\n";
} else {
    echo "• coupon WELCOME10 already exists\n";
}

/* Welcome popup on the home page */
$popups = (int) $pdo->query('SELECT COUNT(*) FROM popups')->fetchColumn();
if ($popups === 0) {
    $pdo->prepare(
        'INSERT INTO popups (title, body, cta_label, cta_url, position, delay_seconds, frequency, target, is_active, sort, created_at)
         VALUES (?,?,?,?,?,?,?,?,1,0,?)'
    )->execute([
        'به بهنام خوش آمدید 🌸',
        '<p>با کد <strong>WELCOME10</strong> در اولین خرید ۱۰٪ تخفیف بگیرید.</p>',
        'شروع خرید', '/category', 'center', 3, 'once_session', 'home', $now,
    ]);
    echo "✓ welcome popup created\n";
} else {
    echo "• popups already present ({$popups})\n";
}

/* Enable the loyalty club (2% of each order → points) if not configured */
$has = (int) $pdo->query("SELECT COUNT(*) FROM settings WHERE setting_key = 'points_enabled'")->fetchColumn();
if ($has === 0) {
    $repo = new App\Repositories\SettingsRepository();
    $repo->set('points_enabled', '1', 'bool');
    $repo->set('points_earn_percent', '2', 'int');
    echo "✓ loyalty club enabled (2% earn rate)\n";
} else {
    echo "• points settings already configured\n";
}

echo "Done. ✓\n";
