<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use App\Repositories\ShippingZoneRepository;
use Throwable;

/**
 * Resolves shipping options for a destination city. City-specific rules and
 * nationwide methods are managed in the admin (shipping_zones table); if that
 * table is empty/unavailable it falls back to config/shipping.php so the
 * storefront keeps working before the admin has configured anything.
 */
final class ShippingService
{
    /**
     * @return list<array{key:string,label:string,desc:string,cost:int,free:bool}>
     */
    public function options(string $city, int $cartNet): array
    {
        $city = trim($city);

        // Try DB-backed zones first.
        try {
            $repo = new ShippingZoneRepository();

            // A city-specific rule overrides the nationwide defaults entirely.
            $cityZones = $city !== '' ? $repo->activeForCity($city) : [];
            if ($cityZones !== []) {
                return array_map(fn (array $z): array => $this->mapZone($z, $cartNet), $cityZones);
            }

            $defaults = $repo->activeDefaults();
            if ($defaults !== []) {
                return array_map(fn (array $z): array => $this->mapZone($z, $cartNet), $defaults);
            }
        } catch (Throwable) {
            // Fall through to config-based defaults below.
        }

        return $this->configOptions($city, $cartNet);
    }

    /** @return array{key:string,label:string,cost:int}|null */
    public function resolve(string $city, string $methodKey, int $cartNet): ?array
    {
        foreach ($this->options($city, $cartNet) as $opt) {
            if ($opt['key'] === $methodKey) {
                return ['key' => $opt['key'], 'label' => $opt['label'], 'cost' => $opt['cost']];
            }
        }
        return null;
    }

    /**
     * Map a DB zone row to an option, applying its free-over threshold.
     * @param array<string,mixed> $z
     * @return array{key:string,label:string,desc:string,cost:int,free:bool}
     */
    private function mapZone(array $z, int $cartNet): array
    {
        $freeOver = $z['free_over'] !== null ? (int) $z['free_over'] : null;
        $free     = $freeOver !== null && $cartNet >= $freeOver;
        $note     = (string) ($z['note'] ?? '');
        if ((string) $z['city'] !== '*') {
            $note = 'ویژه ' . (string) $z['city'] . ($note !== '' ? ' · ' . $note : '');
        }

        return [
            'key'   => (string) $z['method_key'],
            'label' => (string) $z['method_label'],
            'desc'  => $note,
            'cost'  => $free ? 0 : (int) $z['cost'],
            'free'  => $free,
        ];
    }

    /**
     * Legacy config-driven fallback (matches the original storefront behavior).
     * @return list<array{key:string,label:string,desc:string,cost:int,free:bool}>
     */
    private function configOptions(string $city, int $cartNet): array
    {
        $rules = (array) Config::get('shipping.city_rules', []);
        if (isset($rules[$city])) {
            $rule = $rules[$city];
            return [[
                'key'   => 'courier',
                'label' => (string) $rule['method'],
                'desc'  => 'ویژه ' . $city . ' · ' . ((string) ($rule['note'] ?? '')),
                'cost'  => (int) $rule['cost'],
                'free'  => false,
            ]];
        }

        $threshold = (int) SettingsService::get('free_shipping_threshold', (int) Config::get('shipping.free_shipping_threshold', 500000));
        $postFree  = $cartNet >= $threshold;

        return [
            [
                'key'   => 'post',
                'label' => 'پست پیشتاز',
                'desc'  => '۲ تا ۳ روز کاری',
                'cost'  => $postFree ? 0 : (int) Config::get('shipping.default_cost', 45000),
                'free'  => $postFree,
            ],
            [
                'key'   => 'tipax',
                'label' => 'تیپاکس (سریع)',
                'desc'  => '۱ روز کاری',
                'cost'  => 45000,
                'free'  => false,
            ],
        ];
    }
}
