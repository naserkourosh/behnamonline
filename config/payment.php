<?php

declare(strict_types=1);

use App\Core\Env;

/**
 * Payment gateways. Used from Phase 3 onward.
 * The "mock" driver simulates a successful payment for local testing.
 */
return [
    'driver'   => (string) Env::get('PAYMENT_DRIVER', 'mock'),
    'currency' => 'IRT', // Toman

    'gateways' => [
        'zarinpal' => [
            'merchant_id' => (string) Env::get('ZARINPAL_MERCHANT_ID', ''),
            'sandbox'     => true,
        ],
        'card_to_card' => [
            'number' => (string) Env::get('CARD_NUMBER', '6037-9911-2233-4455'),
            'holder' => (string) Env::get('CARD_HOLDER', 'فروشگاه بهنام'),
            'bank'   => (string) Env::get('CARD_BANK', 'بانک ملی ایران'),
        ],
    ],
];
