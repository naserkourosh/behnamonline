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
        // card_to_card, snappay, digipay … configured in Phase 3
    ],
];
