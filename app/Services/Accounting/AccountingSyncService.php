<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\Core\Config;
use App\Repositories\ProductRepository;
use Throwable;

/**
 * Facade over the accounting drivers. Resolves the configured driver
 * (none | holoo | mahak) and pulls stock/price updates into the catalog,
 * matching products by SKU/barcode.
 */
final class AccountingSyncService
{
    public function driverName(): string
    {
        return (string) Config::get('integrations.accounting.driver', 'none');
    }

    public function isConfigured(): bool
    {
        return $this->driverName() !== 'none'
            && (string) Config::get('integrations.accounting.base_url', '') !== '';
    }

    public function driver(): ?AccountingDriver
    {
        return match ($this->driverName()) {
            'holoo' => new HolooDriver(),
            'mahak' => new MahakDriver(),
            default => null,
        };
    }

    /**
     * Pull products from the accounting system and update stock/price by code.
     * @return array{ok:bool,updated?:int,matched?:int,error?:string}
     */
    public function syncProducts(): array
    {
        $driver = $this->driver();
        if ($driver === null) {
            return ['ok' => false, 'error' => 'راه‌انداز حسابداری انتخاب نشده است (ACCOUNTING_DRIVER).'];
        }

        try {
            $items = $driver->pullProducts();
        } catch (Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }

        $repo    = new ProductRepository();
        $updated = 0;
        foreach ($items as $it) {
            $product = $repo->findByCode((string) $it['code']);
            if ($product === null) {
                continue;
            }
            $repo->setStockPrice((int) $product['id'], $it['stock'] ?? null, $it['price'] ?? null);
            $updated++;
        }

        return ['ok' => true, 'matched' => count($items), 'updated' => $updated];
    }
}
