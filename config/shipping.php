<?php

declare(strict_types=1);

/**
 * Baseline shipping config. Full zone/rule engine arrives in Phase 3;
 * the storefront slice only needs the free-shipping threshold (also
 * mirrored in the `settings` table so admins can change it at runtime).
 */
return [
    'free_shipping_threshold' => 500_000, // Toman
    'default_cost'            => 45_000,

    // City-specific example from the design: Gorgan → motorbike courier.
    'city_rules' => [
        'گرگان' => [
            'method' => 'پیک موتوری',
            'cost'   => 35_000,
            'note'   => 'تحویل امروز',
        ],
    ],
];
