<?php
/**
 * Router for PHP's built-in server (development only). Laragon/Apache use
 * public/.htaccess instead.
 *
 *   php -S 127.0.0.1:8000 -t public tools/dev-router.php
 */
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$file = __DIR__ . '/../public' . $path;

if ($path !== '/' && is_file($file)) {
    return false; // let the built-in server serve the static asset
}

require __DIR__ . '/../public/index.php';
