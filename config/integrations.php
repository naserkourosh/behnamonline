<?php

declare(strict_types=1);

use App\Core\Env;

/**
 * Third-party integrations.
 *
 *  • Torob (ترب): a public product feed Torob's crawler reads, plus
 *    crawler-friendly markup on product pages. Enable/disable and the API
 *    key for the accounting bridge are admin settings (DB), not here.
 *
 *  • Accounting/inventory (هلو / محک): an inbound token-protected API these
 *    systems call to read products/orders and push stock+price, plus an
 *    outbound driver scaffold to PULL from their web service once credentials
 *    are provided (mirrors the Zarinpal / National-Post driver pattern).
 */
return [
    'torob' => [
        // Torob shows Toman; some feeds expect Rial. Default Toman.
        'price_in_rial' => (bool) Env::get('TOROB_PRICE_RIAL', false),
    ],

    'accounting' => [
        'driver'   => (string) Env::get('ACCOUNTING_DRIVER', 'none'), // none | holoo | mahak
        'base_url' => (string) Env::get('ACCOUNTING_URL', ''),
        'api_key'  => (string) Env::get('ACCOUNTING_KEY', ''),
        'username' => (string) Env::get('ACCOUNTING_USER', ''),
        'password' => (string) Env::get('ACCOUNTING_PASS', ''),
        'timeout'  => (int) Env::get('ACCOUNTING_TIMEOUT', 15),
    ],
];
