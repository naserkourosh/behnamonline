<?php

declare(strict_types=1);

use App\Core\Config;
use App\Core\Csrf;
use App\Core\Response;
use App\Core\View;
use App\Services\SettingsService;
use App\Support\Html;
use App\Support\Jalali;

/*
|--------------------------------------------------------------------------
| Global helper functions (Persian-aware view & formatting utilities)
|--------------------------------------------------------------------------
*/

if (!function_exists('e')) {
    /** HTML-escape a value for safe output (XSS protection). */
    function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('fa')) {
    /** Convert ASCII/Arabic digits to Persian digits. */
    function fa(int|string $value): string
    {
        $latin   = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $arabic  = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        return str_replace($arabic, $persian, str_replace($latin, $persian, (string) $value));
    }
}

if (!function_exists('money')) {
    /** Format an integer Toman amount with thousands separators + Persian digits. */
    function money(int|float|string $amount): string
    {
        $n = (int) round((float) $amount);
        return fa(number_format($n, 0, '.', '٬'));
    }
}

if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        return Config::get($key, $default);
    }
}

if (!function_exists('setting')) {
    /** Read an admin-configurable setting (cached). */
    function setting(string $key, mixed $default = null): mixed
    {
        return SettingsService::get($key, $default);
    }
}

if (!function_exists('url')) {
    /** Root-relative URL so links work on any host (behnam.test, localhost…). */
    function url(string $path = ''): string
    {
        return '/' . ltrim($path, '/');
    }
}

if (!function_exists('base_url')) {
    /** Absolute site base (scheme://host), detected from the request at runtime. */
    function base_url(): string
    {
        $envUrl = rtrim((string) Config::get('app.url', ''), '/');
        if (PHP_SAPI === 'cli' || empty($_SERVER['HTTP_HOST'])) {
            return $envUrl !== '' ? $envUrl : 'http://localhost';
        }
        $https  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443;
        $scheme = $https ? 'https' : 'http';
        return $scheme . '://' . $_SERVER['HTTP_HOST'];
    }
}

if (!function_exists('abs_url')) {
    /** Absolute URL for canonical/OG/JSON-LD. */
    function abs_url(string $path = ''): string
    {
        return base_url() . '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {
    /** Versioned, root-relative asset URL (cache-busted by file mtime). */
    function asset(string $path): string
    {
        $path = ltrim($path, '/');
        $full = BASE_PATH . '/public/' . $path;
        $version = is_file($full) ? '?v=' . filemtime($full) : '';
        return '/' . $path . $version;
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return Csrf::token();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="_token" value="' . e(Csrf::token()) . '">';
    }
}

if (!function_exists('jdate')) {
    /** Format a Y-m-d (or timestamp) as a Jalali date string with Persian digits. */
    function jdate(string|int $date, string $format = 'Y/m/d'): string
    {
        return Jalali::format($date, $format);
    }
}

if (!function_exists('html_clean')) {
    /** Sanitize stored HTML (product descriptions) to a safe allowlist. */
    function html_clean(string $html): string
    {
        return Html::sanitize($html);
    }
}

if (!function_exists('view')) {
    /**
     * Render a view and return an HTML Response.
     * @param array<string,mixed> $data
     */
    function view(string $template, array $data = [], ?string $layout = 'storefront', int $status = 200): Response
    {
        return Response::html(View::render($template, $data, $layout), $status);
    }
}

if (!function_exists('old')) {
    function old(string $key, mixed $default = ''): mixed
    {
        $old = \App\Core\Session::flash('__old');
        if (is_array($old) && array_key_exists($key, $old)) {
            return $old[$key];
        }
        return $default;
    }
}

if (!function_exists('discount_percent')) {
    /** Compute the discount % from old/new price (0 when none). */
    function discount_percent(int|float|null $old, int|float $price): int
    {
        $old = (float) $old;
        if ($old <= 0 || $price >= $old) {
            return 0;
        }
        return (int) round((($old - $price) / $old) * 100);
    }
}
