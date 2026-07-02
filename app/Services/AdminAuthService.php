<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Session;
use App\Repositories\AdminUserRepository;

/**
 * Admin authentication (username + password) and role-based capabilities.
 * Kept separate from the customer OTP session.
 */
final class AdminAuthService
{
    private const KEY = 'admin_user_id';

    /** Capabilities per role. 'super' has the '*' wildcard. */
    private const CAPS = [
        'super'   => ['*'],
        'manager' => ['dashboard', 'reports', 'products', 'categories', 'brands', 'tags', 'orders', 'customers', 'menus', 'banners', 'inventory', 'blog', 'support', 'accounting', 'coupons', 'popups', 'media', 'shipping', 'sms', 'settings'],
        'editor'  => ['dashboard', 'products', 'categories', 'brands', 'tags', 'inventory', 'blog', 'media', 'banners'],
    ];

    /**
     * Every capability the panel recognises, with a Persian label.
     * Drives the RBAC editor's checkbox list. 'staff' is super-only in practice.
     * @var array<string,string>
     */
    public const ALL_CAPS = [
        'dashboard'  => 'داشبورد',
        'reports'    => 'گزارش‌ها و آمار',
        'products'   => 'محصولات',
        'categories' => 'دسته‌بندی‌ها',
        'brands'     => 'برندها',
        'tags'       => 'برچسب‌ها',
        'inventory'  => 'موجودی',
        'orders'     => 'سفارش‌ها',
        'customers'  => 'مشتریان',
        'coupons'    => 'کدهای تخفیف',
        'popups'     => 'پاپ‌آپ‌ها',
        'banners'    => 'بنرها',
        'menus'      => 'منوها',
        'media'      => 'کتابخانه رسانه',
        'blog'       => 'مجله',
        'support'    => 'پشتیبانی و سوالات',
        'accounting' => 'حسابداری',
        'shipping'   => 'ارسال و مناطق',
        'sms'        => 'پیامک‌ها',
        'settings'   => 'تنظیمات',
        'staff'      => 'کاربران مدیریت',
    ];

    /** @var array<string,mixed>|null */
    private static ?array $cached = null;
    private static bool $loaded = false;

    /** @return array{ok:bool,message?:string} */
    public static function attempt(string $username, string $password): array
    {
        $user = (new AdminUserRepository())->findByUsername($username);
        if ($user === null || !password_verify($password, (string) $user['password_hash'])) {
            return ['ok' => false, 'message' => 'نام کاربری یا رمز عبور نادرست است.'];
        }
        self::loginUser((int) $user['id']);
        return ['ok' => true];
    }

    public static function loginUser(int $id): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
        Session::set(self::KEY, $id);
        self::$loaded = false;
        self::$cached = null;
        (new AdminUserRepository())->touchLogin($id);
    }

    public static function logout(): void
    {
        Session::forget(self::KEY);
        self::$loaded = false;
        self::$cached = null;
    }

    public static function id(): ?int
    {
        $id = Session::get(self::KEY);
        return $id !== null ? (int) $id : null;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    /** @return array<string,mixed>|null */
    public static function user(): ?array
    {
        if (self::$loaded) {
            return self::$cached;
        }
        self::$loaded = true;

        $id = self::id();
        if ($id === null) {
            return self::$cached = null;
        }
        $user = (new AdminUserRepository())->find($id);
        if ($user === null || (int) $user['is_active'] !== 1) {
            self::logout();
            return self::$cached = null;
        }
        return self::$cached = $user;
    }

    public static function can(string $capability): bool
    {
        $user = self::user();
        if ($user === null) {
            return false;
        }
        // Super admins always have full access.
        if ((string) $user['role'] === 'super') {
            return true;
        }
        // A per-user override (non-empty capabilities column) replaces role defaults.
        $custom = self::parseCaps($user['capabilities'] ?? null);
        if ($custom !== null) {
            return in_array($capability, $custom, true);
        }
        $caps = self::CAPS[(string) $user['role']] ?? [];
        return in_array('*', $caps, true) || in_array($capability, $caps, true);
    }

    /**
     * Effective capability slugs for an admin row — used to pre-check the
     * RBAC editor and to display a user's access.
     *
     * @param array<string,mixed> $user
     * @return list<string>
     */
    public static function effectiveCaps(array $user): array
    {
        if ((string) $user['role'] === 'super') {
            return array_keys(self::ALL_CAPS);
        }
        $custom = self::parseCaps($user['capabilities'] ?? null);
        if ($custom !== null) {
            return $custom;
        }
        $caps = self::CAPS[(string) $user['role']] ?? [];
        return in_array('*', $caps, true) ? array_keys(self::ALL_CAPS) : $caps;
    }

    /**
     * Parse a comma-separated capability override into a clean list, keeping
     * only recognised caps. Returns null when nothing is overridden.
     *
     * @return list<string>|null
     */
    private static function parseCaps(mixed $raw): ?array
    {
        $raw = trim((string) ($raw ?? ''));
        if ($raw === '') {
            return null;
        }
        $caps = array_values(array_filter(
            array_map('trim', explode(',', $raw)),
            static fn (string $c): bool => $c !== '' && array_key_exists($c, self::ALL_CAPS)
        ));
        return $caps === [] ? null : $caps;
    }
}
