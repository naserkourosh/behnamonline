<?php

declare(strict_types=1);

namespace App\Services\Shipping;

/**
 * Local postal-fee estimator used until the National Post web service is
 * connected. Computes پست پیشتاز / پست سفارشی prices from the parcel's
 * billable weight (max of actual & volumetric) and the destination's zone
 * relative to the shop origin (گلستان). Deliberately simple and clearly an
 * approximation — the real tariff comes from NationalPostDriver.
 */
final class MockPostDriver implements PostDriver
{
    /** @param array<string,mixed> $cfg the config('shipping.post.mock') array */
    public function __construct(private array $cfg)
    {
    }

    public function quote(array $dest, array $parcel): array
    {
        $minPer = (int) ($this->cfg['min_item_grams'] ?? 500);
        $grams  = max((int) $parcel['billable_g'], (int) $parcel['items'] * $minPer, $minPer);
        $kg     = max(1, (int) ceil($grams / 1000));

        $zone      = $this->zoneFor((string) $dest['province']);
        $base      = (int) ($this->cfg['base'] ?? 60000);
        $perKg     = (int) ($this->cfg['per_kg'] ?? 18000);
        $surcharge = (int) (($this->cfg['zone_surcharge'] ?? [])[$zone] ?? 0);

        $pishtaz = $base + $surcharge + ($kg - 1) * $perKg;

        // Postal shipping is a single method (پست پیشتاز). Delivery-time text
        // is applied by ShippingService from the admin ETA settings.
        return [
            [
                'key'      => 'post_pishtaz',
                'label'    => 'پست پیشتاز',
                'desc'     => 'وزن محاسبه‌شده حدود ' . $kg . ' کیلوگرم',
                'cost'     => $pishtaz,
                'delivery' => '',
            ],
        ];
    }

    public function name(): string
    {
        return 'mock';
    }

    /** Map a destination province to a postal zone (1 nearest … 3 farthest). */
    private function zoneFor(string $province): int
    {
        foreach ((array) ($this->cfg['zones'] ?? []) as $zone => $provinces) {
            if (in_array($province, (array) $provinces, true)) {
                return (int) $zone;
            }
        }
        return 3; // default: farthest zone
    }
}
