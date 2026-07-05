<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\Core\Config;
use RuntimeException;

/**
 * Mahak (محک) web-service driver — SCAFFOLD.
 *
 * Mahak provides a REST API for products/inventory. Set in .env:
 *   ACCOUNTING_DRIVER=mahak
 *   ACCOUNTING_URL=https://<mahak-api-endpoint>
 *   ACCOUNTING_KEY=<token>
 *
 * Adjust the request path and response field names to the real Mahak contract.
 * Throws on any failure; AccountingSyncService reports it.
 */
final class MahakDriver implements AccountingDriver
{
    public function pullProducts(): array
    {
        $base = rtrim((string) Config::get('integrations.accounting.base_url', ''), '/');
        if ($base === '') {
            throw new RuntimeException('آدرس API محک تنظیم نشده است (ACCOUNTING_URL).');
        }

        $res = AccountingHttp::getJson($base . '/api/products', [], [
            'Authorization: Bearer ' . (string) Config::get('integrations.accounting.api_key', ''),
        ]);

        $rows = $res['items'] ?? $res['products'] ?? $res['data'] ?? null;
        if (!is_array($rows)) {
            throw new RuntimeException('پاسخ API محک نامعتبر بود.');
        }

        $out = [];
        foreach ($rows as $r) {
            $code = (string) ($r['barcode'] ?? $r['code'] ?? $r['sku'] ?? '');
            if ($code === '') {
                continue;
            }
            $out[] = [
                'code'  => $code,
                'stock' => isset($r['quantity']) ? (int) $r['quantity'] : (isset($r['stock']) ? (int) $r['stock'] : null),
                'price' => isset($r['price']) ? (int) $r['price'] : (isset($r['sellPrice']) ? (int) $r['sellPrice'] : null),
            ];
        }
        return $out;
    }

    public function name(): string
    {
        return 'mahak';
    }
}
