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
        'manager' => ['dashboard', 'products', 'categories', 'brands', 'tags', 'orders', 'customers', 'menus', 'inventory', 'settings'],
        'editor'  => ['dashboard', 'products', 'categories', 'brands', 'tags', 'inventory'],
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
        $caps = self::CAPS[(string) $user['role']] ?? [];
        return in_array('*', $caps, true) || in_array($capability, $caps, true);
    }
}
