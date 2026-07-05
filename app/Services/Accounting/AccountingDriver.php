<?php

declare(strict_types=1);

namespace App\Services\Accounting;

/**
 * An accounting/inventory system we can PULL data from (هلو / محک …).
 * Mirrors the payment/post driver abstraction: a concrete driver talks to the
 * vendor's web service and returns a normalized product list (code/stock/price)
 * that AccountingSyncService writes back into the catalog.
 */
interface AccountingDriver
{
    /**
     * Fetch products from the accounting system.
     * @return list<array{code:string,stock:int|null,price:int|null}>
     */
    public function pullProducts(): array;

    public function name(): string;
}
