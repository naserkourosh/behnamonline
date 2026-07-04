<?php

declare(strict_types=1);

use App\Core\Env;

/**
 * Shipping configuration.
 *
 * Postal fees are quoted through the National Post (شرکت ملی پست) web
 * service. Like the payment gateways, this uses a driver abstraction:
 *   - driver = 'mock'     → local estimate from weight/volume + province zone
 *                           (works offline, before the web-service contract).
 *   - driver = 'national' → real Post web service (App\Services\Shipping\
 *                           NationalPostDriver); needs a URL + credentials.
 * If the national driver is misconfigured or unreachable, PostService falls
 * back to the mock estimate so checkout never dead-ends.
 *
 * City-specific rules (e.g. گرگان → پیک موتوری) live in the shipping_zones
 * table and are managed at /admin/shipping; they take precedence over post.
 */
return [
    'free_shipping_threshold' => 500_000, // Toman — order subtotal ≥ this ⇒ free post
    'default_cost'            => 45_000,   // last-ditch flat fallback

    // City-specific example from the design: Gorgan → motorbike courier
    // (kept for the config fallback; the live rule is a shipping_zones row).
    'city_rules' => [
        'گرگان' => [
            'method' => 'پیک موتوری',
            'cost'   => 35_000,
            'note'   => 'تحویل امروز',
        ],
    ],

    // ── National Post web service ──────────────────────────────
    'post' => [
        'driver'          => (string) Env::get('POST_DRIVER', 'mock'), // 'mock' | 'national'
        'origin_province' => (string) Env::get('POST_ORIGIN_PROVINCE', 'گلستان'),
        'origin_city'     => (string) Env::get('POST_ORIGIN_CITY', 'گرگان'),
        'origin_postal'   => (string) Env::get('POST_ORIGIN_POSTAL', ''),

        // Real web-service endpoint + credentials (filled once contracted).
        'api_url'   => (string) Env::get('POST_API_URL', ''),
        'api_key'   => (string) Env::get('POST_API_KEY', ''),
        'username'  => (string) Env::get('POST_USERNAME', ''),
        'password'  => (string) Env::get('POST_PASSWORD', ''),
        'timeout'   => (int) Env::get('POST_TIMEOUT', 10),

        // Mock estimator tariff (Toman). Approximation until the web
        // service is connected — the real driver ignores these.
        'mock' => [
            'base'            => 60_000,  // پست پیشتاز, first 1kg, same zone
            'per_kg'          => 18_000,  // per extra kg
            'min_item_grams'  => 500,     // assumed weight when a product has none set
            // Province → zone relative to the گلستان origin.
            'zone_surcharge'  => [1 => 0, 2 => 20_000, 3 => 45_000],
            'zones'           => [
                1 => ['گلستان'],
                2 => ['مازندران', 'سمنان', 'خراسان شمالی', 'خراسان رضوی', 'تهران', 'البرز'],
                // everything else defaults to zone 3
            ],
        ],
    ],
];
