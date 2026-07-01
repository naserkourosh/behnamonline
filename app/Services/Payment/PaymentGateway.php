<?php

declare(strict_types=1);

namespace App\Services\Payment;

/**
 * Contract for payment gateways. `initiate` starts a payment and returns a
 * URL to send the customer to; `verify` confirms the gateway callback.
 * Amounts are passed in Toman; adapters convert if their API needs Rial.
 */
interface PaymentGateway
{
    public function key(): string;

    public function label(): string;

    /**
     * @return array{ok:bool,redirect_url?:string,authority?:string,error?:string}
     */
    public function initiate(int $orderId, int $amount, string $description, string $callbackUrl): array;

    /**
     * @param array<string,mixed> $params  callback query/body params
     * @return array{ok:bool,ref_id?:string,error?:string}
     */
    public function verify(array $params, int $amount): array;
}
