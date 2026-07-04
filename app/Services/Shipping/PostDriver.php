<?php

declare(strict_types=1);

namespace App\Services\Shipping;

/**
 * A postal-fee provider. Given a destination and a parcel (billable weight),
 * returns the available postal service options with their prices.
 *
 * Mirrors the payment PaymentGateway abstraction: a MockPostDriver computes
 * an estimate locally, while NationalPostDriver calls the real شرکت ملی پست
 * web service once credentials are configured.
 */
interface PostDriver
{
    /**
     * @param array{province:string,city:string} $dest
     * @param array{weight_g:int,volumetric_g:int,billable_g:int,items:int} $parcel
     * @return list<array{key:string,label:string,desc:string,cost:int,delivery:string}>
     */
    public function quote(array $dest, array $parcel): array;

    /** Short driver identifier (for logs/debug). */
    public function name(): string;
}
