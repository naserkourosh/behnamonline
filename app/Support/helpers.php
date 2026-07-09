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

if (!function_exists('en_num')) {
    /** Convert Persian/Arabic digits in a string to ASCII (for mobile, OTP, postal codes). */
    function en_num(string $value): string
    {
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $arabic  = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $latin   = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        return str_replace($arabic, $latin, str_replace($persian, $latin, trim($value)));
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
        $full = PUBLIC_PATH . '/' . $path;
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

if (!function_exists('auth')) {
    /** Current authenticated customer (or null). @return array<string,mixed>|null */
    function auth(): ?array
    {
        return \App\Services\AuthService::user();
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

if (!function_exists('order_status')) {
    /**
     * Persian label + Tailwind color classes for an order status.
     * @return array{label:string,text:string,bg:string}
     */
    function order_status(string $status): array
    {
        return match ($status) {
            'processing' => ['label' => 'در حال پردازش', 'text' => 'text-secondary', 'bg' => 'bg-pink'],
            'shipped'    => ['label' => 'در حال ارسال', 'text' => 'text-warning', 'bg' => 'bg-[#FFF6E6]'],
            'delivered'  => ['label' => 'تحویل شده', 'text' => 'text-success', 'bg' => 'bg-[#E7F7F0]'],
            'canceled'   => ['label' => 'لغو شده', 'text' => 'text-danger', 'bg' => 'bg-[#FDECEC]'],
            default      => ['label' => 'در انتظار', 'text' => 'text-mauve', 'bg' => 'bg-surface'],
        };
    }
}

if (!function_exists('en_num')) {
    /** Convert Persian/Arabic digits to Latin (for numeric input parsing). */
    function en_num(string $value): string
    {
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $arabic  = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $latin   = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        return str_replace($arabic, $latin, str_replace($persian, $latin, trim($value)));
    }
}

if (!function_exists('slugify')) {
    /** Build a URL slug, preserving Persian letters. */
    function slugify(string $text, string $fallback = ''): string
    {
        $text = trim($text);
        $text = preg_replace('/[\s\/\\\\_]+/u', '-', $text) ?? $text;
        $text = preg_replace('/[^\p{L}\p{N}\-]+/u', '', $text) ?? $text;
        $text = preg_replace('/-+/', '-', $text) ?? $text;
        $text = trim($text, '-');
        return $text !== '' ? $text : ($fallback !== '' ? $fallback : 'item-' . substr(md5((string) microtime(true)), 0, 6));
    }
}

if (!function_exists('admin')) {
    /** @return array<string,mixed>|null The current admin user, if any. */
    function admin(): ?array
    {
        return \App\Services\AdminAuthService::user();
    }
}

if (!function_exists('admin_can')) {
    function admin_can(string $capability): bool
    {
        return \App\Services\AdminAuthService::can($capability);
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

if (!function_exists('flash_active')) {
    /**
     * True when a product row's flash-sale window is currently open.
     * @param array<string,mixed> $p
     */
    function flash_active(array $p): bool
    {
        if (empty($p['on_flash_sale']) || empty($p['flash_sale_ends_at'])) {
            return false;
        }
        return strtotime((string) $p['flash_sale_ends_at']) > time();
    }
}
