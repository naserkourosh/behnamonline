<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Minimal .env parser (replaces vlucas/phpdotenv so the app stays
 * dependency-free). Values are exposed via Env::get().
 */
final class Env
{
    /** @var array<string,string> */
    private static array $vars = [];
    private static bool $loaded = false;

    public static function load(string $path): void
    {
        if (self::$loaded) {
            return;
        }
        self::$loaded = true;

        if (!is_file($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (!str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key   = trim($key);
            $value = self::clean($value);

            self::$vars[$key] = $value;
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $value = self::$vars[$key] ?? $_ENV[$key] ?? getenv($key);

        if ($value === false || $value === null) {
            return $default;
        }

        return match (strtolower((string) $value)) {
            'true'  => true,
            'false' => false,
            'null'  => null,
            'empty' => '',
            default => $value,
        };
    }

    private static function clean(string $value): string
    {
        $value = trim($value);

        // Strip an inline comment that is not inside quotes.
        if ($value !== '' && $value[0] !== '"' && $value[0] !== "'") {
            $value = preg_replace('/\s+#.*$/', '', $value) ?? $value;
            $value = trim($value);
        }

        // Strip surrounding quotes.
        if (strlen($value) >= 2) {
            $first = $value[0];
            $last  = $value[-1];
            if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                $value = substr($value, 1, -1);
            }
        }

        return $value;
    }
}
