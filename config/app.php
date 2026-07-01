<?php

declare(strict_types=1);

use App\Core\Env;

return [
    'name'     => Env::get('APP_NAME', 'بهنام'),
    'env'      => Env::get('APP_ENV', 'production'),
    'debug'    => (bool) Env::get('APP_DEBUG', false),
    'url'      => rtrim((string) Env::get('APP_URL', 'http://localhost'), '/'),
    'key'      => (string) Env::get('APP_KEY', ''),
    'timezone' => 'Asia/Tehran',
    'locale'   => 'fa',

    // Latin wordmark shown beneath the Persian logo (luxury style).
    'wordmark' => 'BEHNAM',

    'session' => [
        'lifetime' => (int) Env::get('SESSION_LIFETIME', 120),
        'secure'   => (bool) Env::get('SECURE_COOKIES', false),
        'name'     => 'behnam_session',
    ],
];
