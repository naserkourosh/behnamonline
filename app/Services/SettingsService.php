<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\SettingsRepository;
use Throwable;

/**
 * Loads the `settings` table once per request and exposes typed values.
 * Falls back gracefully if the table is missing (e.g. before migration).
 */
final class SettingsService
{
    /** @var array<string,mixed>|null */
    private static ?array $cache = null;

    public static function get(string $key, mixed $default = null): mixed
    {
        self::boot();
        return self::$cache[$key] ?? $default;
    }

    private static function boot(): void
    {
        if (self::$cache !== null) {
            return;
        }
        self::$cache = [];

        try {
            foreach ((new SettingsRepository())->all() as $row) {
                self::$cache[$row['setting_key']] = self::cast($row['setting_value'], $row['setting_type']);
            }
        } catch (Throwable) {
            // Settings unavailable yet; callers use their defaults.
        }
    }

    private static function cast(string $value, string $type): mixed
    {
        return match ($type) {
            'int'  => (int) $value,
            'bool' => in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true),
            'json' => json_decode($value, true),
            default => $value,
        };
    }
}
