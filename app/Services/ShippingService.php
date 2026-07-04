<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use App\Repositories\ShippingZoneRepository;
use App\Services\Shipping\PostService;
use Throwable;

/**
 * Resolves shipping options for a destination.
 *
 *  1. A city-specific courier rule (e.g. گرگان → پیک موتوری, from the
 *     shipping_zones table) overrides everything — no post needed.
 *  2. Otherwise the postal fee is quoted from the National Post web service
 *     (PostService) using the cart parcel's billable weight + destination.
 *  3. The free-shipping threshold and the global پس‌کرایه (freight-collect)
 *     toggle are applied last: when پس‌کرایه is on, the post cost is zeroed
 *     and the customer pays the carrier on delivery.
 *
 * @phpstan-type Parcel array{weight_g:int,volumetric_g:int,billable_g:int,items:int}
 */
final class ShippingService
{
    /**
     * @param array{weight_g:int,volumetric_g:int,billable_g:int,items:int} $parcel
     * @return list<array{key:string,label:string,desc:string,cost:int,free:bool,collect?:bool}>
     */
    public function options(string $province, string $city, int $cartNet, array $parcel): array
    {
        $city = trim($city);

        $etaOn      = (bool) SettingsService::get('shipping_eta_enabled', true);
        $etaGorgan  = (string) SettingsService::get('shipping_eta_gorgan', 'کمتر از یک روز کاری');
        $etaDefault = (string) SettingsService::get('shipping_eta_default', '۲ تا ۴ روز کاری');

        // 1. City courier rule (e.g. گرگان) overrides post entirely.
        try {
            $cityZones = $city !== '' ? (new ShippingZoneRepository())->activeForCity($city) : [];
        } catch (Throwable) {
            $cityZones = [];
        }
        if ($cityZones !== []) {
            return array_map(function (array $z) use ($cartNet, $etaOn, $etaGorgan): array {
                $o = $this->mapZone($z, $cartNet);
                $o['eta'] = $etaOn ? $etaGorgan : '';
                return $o;
            }, $cityZones);
        }

        // 2. Postal methods + any admin-defined nationwide methods.
        //    Prepaid post and پس‌کرایه are independent toggles, so disabling
        //    prepaid post still lets پس‌کرایه (or a custom method) carry checkout.
        $eta       = $etaOn ? $etaDefault : '';
        $threshold = (int) SettingsService::get('free_shipping_threshold', (int) Config::get('shipping.free_shipping_threshold', 500000));
        $free      = $threshold > 0 && $cartNet >= $threshold;
        $opts      = [];

        // 2a. Prepaid post (پست پیشتاز) — quoted by weight/destination.
        if ((bool) SettingsService::get('shipping_post_enabled', true)) {
            try {
                $quoted = (new PostService())->quote(['province' => $province, 'city' => $city], $parcel);
            } catch (Throwable) {
                $quoted = $this->configOptions($city, $cartNet);
            }
            if ($quoted === []) {
                $quoted = $this->configOptions($city, $cartNet);
            }
            foreach ($quoted as $q) {
                $q['free']    = false;
                $q['collect'] = false;
                $q['eta']     = $eta;
                if ($free) {
                    $q['free'] = true;
                    $q['cost'] = 0;
                }
                $opts[] = $q;
            }
        }

        // 2b. پس‌کرایه (post, paid on delivery) — its own method, cost 0.
        if ((bool) SettingsService::get('shipping_collect_enabled', false)) {
            $opts[] = [
                'key'     => 'post_collect',
                'label'   => 'پس‌کرایه',
                'desc'    => 'هزینه ارسال هنگام تحویل توسط پست دریافت می‌شود',
                'cost'    => 0,
                'free'    => false,
                'collect' => true,
                'eta'     => $eta,
            ];
        }

        // 2c. Admin-defined nationwide methods (shipping_zones with city = '*').
        try {
            foreach ((new ShippingZoneRepository())->activeDefaults() as $z) {
                $o = $this->mapZone($z, $cartNet);
                $o['collect'] = false;
                $o['eta']     = $eta;
                $opts[] = $o;
            }
        } catch (Throwable) {
            // ignore — nationwide zones are optional
        }

        return $opts;
    }

    /**
     * Resolve a chosen method into the authoritative cost snapshot.
     *
     * @param array{weight_g:int,volumetric_g:int,billable_g:int,items:int} $parcel
     * @return array{key:string,label:string,cost:int,collect:bool}|null
     */
    public function resolve(string $province, string $city, string $methodKey, int $cartNet, array $parcel): ?array
    {
        foreach ($this->options($province, $city, $cartNet, $parcel) as $opt) {
            if ($opt['key'] === $methodKey) {
                return [
                    'key'     => $opt['key'],
                    'label'   => $opt['label'],
                    'cost'    => (int) $opt['cost'],
                    'collect' => !empty($opt['collect']),
                ];
            }
        }
        return null;
    }

    /**
     * Map a DB courier zone row to an option, applying its free-over threshold.
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
     * Legacy config-driven fallback used only if the post service and the
     * DB zones are both unavailable.
     * @return list<array{key:string,label:string,desc:string,cost:int,free:bool}>
     */
    private function configOptions(string $city, int $cartNet): array
    {
        $threshold = (int) SettingsService::get('free_shipping_threshold', (int) Config::get('shipping.free_shipping_threshold', 500000));
        $postFree  = $cartNet >= $threshold;

        return [[
            'key'   => 'post_pishtaz',
            'label' => 'پست پیشتاز',
            'desc'  => '۲ تا ۳ روز کاری',
            'cost'  => $postFree ? 0 : (int) Config::get('shipping.default_cost', 45000),
            'free'  => $postFree,
        ]];
    }
}
