<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Session;
use App\Repositories\UserRepository;

/**
 * Session-based authentication for storefront customers (OTP-only).
 */
final class AuthService
{
    private const KEY = 'auth_user_id';

    /** @var array<string,mixed>|null */
    private static ?array $cachedUser = null;
    private static bool $loaded = false;

    public static function login(int $userId): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
        Session::set(self::KEY, $userId);
        self::$loaded = false;
        self::$cachedUser = null;
        (new UserRepository())->touchLogin($userId);
    }

    public static function logout(): void
    {
        Session::forget(self::KEY);
        self::$loaded = false;
        self::$cachedUser = null;
    }

    public static function id(): ?int
    {
        $id = Session::get(self::KEY);
        return $id !== null ? (int) $id : null;
    }

    public static function check(): bool
    {
        return self::id() !== null;
    }

    /** @return array<string,mixed>|null */
    public static function user(): ?array
    {
        if (self::$loaded) {
            return self::$cachedUser;
        }
        self::$loaded = true;

        $id = self::id();
        if ($id === null) {
            return self::$cachedUser = null;
        }

        $user = (new UserRepository())->find($id);
        if ($user === null) {
            self::logout();
            return self::$cachedUser = null;
        }

        return self::$cachedUser = $user;
    }

    public static function displayName(): string
    {
        $user = self::user();
        if ($user === null) {
            return '';
        }
        $name = trim(((string) $user['first_name']) . ' ' . ((string) $user['last_name']));
        return $name !== '' ? $name : (string) $user['mobile'];
    }
}
