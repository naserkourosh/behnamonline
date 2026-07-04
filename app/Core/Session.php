<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Secure session wrapper: hardened cookie params, flash messages, and
 * helpers used by CSRF and (later) authentication.
 */
final class Session
{
    public static function start(string $sessionPath): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        if (is_dir($sessionPath)) {
            session_save_path($sessionPath);
        }

        $secure = (bool) Config::get('app.session.secure', false);
        // Keep customers signed in for a year (persistent cookie + server GC).
        $lifetime = (int) Config::get('app.session.lifetime', 60 * 60 * 24 * 365);

        session_name((string) Config::get('app.session.name', 'behnam_session'));
        session_set_cookie_params([
            'lifetime' => $lifetime,
            'path'     => '/',
            'domain'   => '',
            'secure'   => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.gc_maxlifetime', (string) $lifetime);

        session_start();

        // Periodic id regeneration to limit fixation windows.
        if (!isset($_SESSION['__created'])) {
            $_SESSION['__created'] = time();
        } elseif (time() - (int) $_SESSION['__created'] > 1800) {
            session_regenerate_id(true);
            $_SESSION['__created'] = time();
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function flash(string $key, mixed $value = null): mixed
    {
        if ($value !== null) {
            $_SESSION['__flash'][$key] = $value;
            return null;
        }

        $val = $_SESSION['__flash'][$key] ?? null;
        unset($_SESSION['__flash'][$key]);
        return $val;
    }
}
