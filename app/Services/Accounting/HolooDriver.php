<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\Core\Config;
use RuntimeException;

/**
 * Holoo (هلو) web-service driver — SCAFFOLD.
 *
 * Holoo exposes an online-shop web service (وب‌سرویس فروشگاه هلو). Set in .env:
 *   ACCOUNTING_DRIVER=holoo
 *   ACCOUNTING_URL=https://<holoo-web-service-endpoint>
 *   ACCOUNTING_KEY=...   (or ACCOUNTING_USER / ACCOUNTING_PASS)
 *
 * Adjust the request path and the response field names below to the exact
 * Holoo contract you receive. On any error (missing config / network / bad
 * shape) it throws, and AccountingSyncService reports the failure.
 */
final class HolooDriver implements AccountingDriver
{
    public function pullProducts(): array
    {
        $base = rtrim((string) Config::get('integrations.accounting.base_url', ''), '/');
        if ($base === '') {
            throw new RuntimeException('آدرس وب‌سرویس هلو تنظیم نشده است (ACCOUNTING_URL).');
        }

        $res = AccountingHttp::getJson($base . '/GetProducts', [
            'ApiKey'   => (string) Config::get('integrations.accounting.api_key', ''),
            'Username' => (string) Config::get('integrations.accounting.username', ''),
            'Password' => (string) Config::get('integrations.accounting.password', ''),
        ]);

        $rows = $res['Products'] ?? $res['products'] ?? $res['data'] ?? null;
        if (!is_array($rows)) {
            throw new RuntimeException('پاسخ وب‌سرویس هلو نامعتبر بود.');
        }

        $out = [];
        foreach ($rows as $r) {
            $code = (string) ($r['Barcode'] ?? $r['Code'] ?? $r['code'] ?? '');
            if ($code === '') {
                continue;
            }
            $out[] = [
                'code'  => $code,
                'stock' => isset($r['Count']) ? (int) $r['Count'] : (isset($r['stock']) ? (int) $r['stock'] : null),
                'price' => isset($r['SellPrice']) ? (int) $r['SellPrice'] : (isset($r['price']) ? (int) $r['price'] : null),
            ];
        }
        return $out;
    }

    public function name(): string
    {
        return 'holoo';
    }
}
