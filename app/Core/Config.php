<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Loads config/*.php files and exposes them with dot-notation:
 *   Config::get('database.host')
 */
final class Config
{
    /** @var array<string,mixed> */
    private static array $items = [];
    private static bool $loaded = false;

    public static function load(string $configDir): void
    {
        if (self::$loaded) {
            return;
        }
        self::$loaded = true;

        foreach (glob($configDir . '/*.php') ?: [] as $file) {
            $name = basename($file, '.php');
            self::$items[$name] = require $file;
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $segments = explode('.', $key);
        $value    = self::$items;

        foreach ($segments as $segment) {
            if (is_array($value) && array_key_exists($segment, $value)) {
                $value = $value[$segment];
            } else {
                return $default;
            }
        }

        return $value;
    }
}
