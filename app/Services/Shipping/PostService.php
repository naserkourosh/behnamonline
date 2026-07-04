<?php

declare(strict_types=1);

namespace App\Services\Shipping;

use App\Core\Config;
use Throwable;

/**
 * Facade over the postal-fee drivers. Resolves the configured driver
 * (mock | national) and, if the national web service fails, transparently
 * falls back to the local estimate so checkout always has a quote.
 */
final class PostService
{
    /**
     * @param array{province:string,city:string} $dest
     * @param array{weight_g:int,volumetric_g:int,billable_g:int,items:int} $parcel
     * @return list<array{key:string,label:string,desc:string,cost:int,delivery:string}>
     */
    public function quote(array $dest, array $parcel): array
    {
        $driver = (string) Config::get('shipping.post.driver', 'mock');

        if ($driver === 'national') {
            try {
                return (new NationalPostDriver())->quote($dest, $parcel);
            } catch (Throwable) {
                // Web service unavailable/misconfigured → local estimate.
            }
        }

        return $this->mock()->quote($dest, $parcel);
    }

    private function mock(): MockPostDriver
    {
        return new MockPostDriver((array) Config::get('shipping.post.mock', []));
    }
}
