<?php
/**
 * Zero-dependency PSR-4 autoloader for the "App\" namespace.
 *
 * Maps  App\Foo\Bar  ->  app/Foo/Bar.php
 *
 * Composer is optional: if a Composer autoloader exists it is loaded too,
 * but the application runs fully without it.
 */

declare(strict_types=1);

// Web root on disk. public/index.php defines it as its own directory (which
// on a real host may be public_html, OUTSIDE the app folder); this fallback
// covers CLI scripts that only define BASE_PATH.
if (!defined('PUBLIC_PATH')) {
    define('PUBLIC_PATH', BASE_PATH . '/public');
}

// Load Composer's autoloader if the user later runs `composer install`.
$composer = dirname(__DIR__, 2) . '/vendor/autoload.php';
if (is_file($composer)) {
    require $composer;
}

spl_autoload_register(static function (string $class): void {
    $prefix  = 'App\\';
    $baseDir = dirname(__DIR__) . '/'; // .../app/

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $file     = $baseDir . str_replace('\\', '/', $relative) . '.php';

    if (is_file($file)) {
        require $file;
    }
});
