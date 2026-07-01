<?php

declare(strict_types=1);

use App\Core\Env;

/**
 * SMS / OTP configuration. Used from Phase 2 onward.
 * The "mock" driver writes the OTP to storage/logs instead of sending.
 */
return [
    'driver' => (string) Env::get('SMS_DRIVER', 'mock'),

    'melipayamak' => [
        'username' => (string) Env::get('MELIPAYAMAK_USERNAME', ''),
        'password' => (string) Env::get('MELIPAYAMAK_PASSWORD', ''),
        'from'     => (string) Env::get('MELIPAYAMAK_FROM', ''),
    ],

    'otp' => [
        'length'      => 5,
        'ttl'         => 120,  // seconds
        'resend_wait' => 90,   // seconds
    ],
];
