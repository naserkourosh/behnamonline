<?php

declare(strict_types=1);

namespace App\Core;

/**
 * CSRF token issuing and verification. Tokens live in the session and are
 * compared in constant time. AJAX sends the token via the X-CSRF-Token
 * header; forms send it via the _token field.
 */
final class Csrf
{
    private const SESSION_KEY = '__csrf_token';

    public static function token(): string
    {
        if (!Session::has(self::SESSION_KEY)) {
            Session::set(self::SESSION_KEY, bin2hex(random_bytes(32)));
        }
        return (string) Session::get(self::SESSION_KEY);
    }

    public static function verify(?string $token): bool
    {
        if ($token === null || $token === '') {
            return false;
        }
        $stored = Session::get(self::SESSION_KEY);
        return is_string($stored) && hash_equals($stored, $token);
    }

    public static function check(Request $request): bool
    {
        $token = $request->header('X-CSRF-Token')
            ?? (is_string($request->input('_token')) ? $request->input('_token') : null);

        return self::verify($token);
    }
}
