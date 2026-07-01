<?php

declare(strict_types=1);

/**
 * Seeds the default admin account and a primary navigation menu.
 * Idempotent — safe to run repeatedly.
 *
 *   php database/seed_admin.php
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

/* Default admin (username: admin / password: admin1234) */
$exists = (int) $pdo->query("SELECT COUNT(*) FROM admin_users WHERE username = 'admin'")->fetchColumn();
if ($exists === 0) {
    $stmt = $pdo->prepare(
        'INSERT INTO admin_users (username, password_hash, name, role, is_active, created_at) VALUES (?,?,?,?,1,?)'
    );
    $stmt->execute(['admin', password_hash('admin1234', PASSWORD_BCRYPT), 'مدیر کل', 'super', $now]);
    echo "✓ admin user created (admin / admin1234)\n";
} else {
    echo "• admin user already exists\n";
}

/* Primary menu from existing categories */
$menuId = (int) ($pdo->query("SELECT id FROM menus WHERE slug = 'primary'")->fetchColumn() ?: 0);
if ($menuId === 0) {
    $pdo->prepare('INSERT INTO menus (name, slug, created_at) VALUES (?,?,?)')->execute(['منوی اصلی', 'primary', $now]);
    $menuId = (int) $pdo->lastInsertId();

    $cats = $pdo->query('SELECT name, slug FROM categories WHERE is_active = 1 ORDER BY sort, id')->fetchAll();
    $ins  = $pdo->prepare('INSERT INTO menu_items (menu_id, label, url, sort, created_at) VALUES (?,?,?,?,?)');
    $i = 0;
    foreach ($cats as $c) {
        $ins->execute([$menuId, $c['name'], '/category/' . $c['slug'], $i++, $now]);
    }
    echo "✓ primary menu created with " . count($cats) . " items\n";
} else {
    echo "• primary menu already exists\n";
}

echo "Done. ✓\n";
