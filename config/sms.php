<?php

declare(strict_types=1);

use App\Core\Env;

/**
 * SMS / OTP configuration. Used from Phase 2 onward.
 * The "mock" driver writes the OTP to storage/logs instead of sending.
 */
return [
    'driver' => (string) Env::get('SMS_DRIVER', 'mock'),

    // REST API per github.com/Melipayamak/melipayamak-php — authenticates
    // with the PANEL username/password (no API key). 'from' is the sender
    // line number; 'otp_body_id' is an approved pattern code that routes OTP
    // through the shared SERVICE line (reaches blacklisted numbers too).
    'melipayamak' => [
        'username'    => (string) Env::get('MELIPAYAMAK_USERNAME', ''),
        'password'    => (string) Env::get('MELIPAYAMAK_PASSWORD', ''),
        'from'        => (string) Env::get('MELIPAYAMAK_FROM', ''),
        'otp_body_id' => (string) Env::get('MELIPAYAMAK_OTP_BODY_ID', ''),
    ],

    'otp' => [
        'length'      => 5,
        'ttl'         => 120,  // seconds
        'resend_wait' => 90,   // seconds
    ],
];
