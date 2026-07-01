<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;

/**
 * Resolves shipping options for a destination city. Implements the design's
 * city-specific rule (گرگان → پیک موتوری) plus normal nationwide methods,
 * honoring the free-shipping threshold.
 */
final class ShippingService
{
    /**
     * @return list<array{key:string,label:string,desc:string,cost:int,free:bool}>
     */
    public function options(string $city, int $cartNet): array
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
}
