<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Services\CartService;
use App\Services\ShippingService;

/**
 * Returns shipping options (courier rule or National Post quote) for a
 * destination, priced from the current cart's parcel weight. Powers the
 * checkout shipping selector — the cost is computed server-side so the
 * customer sees the authoritative quote.
 */
final class ShippingApiController extends Controller
{
    public function quote(Request $request): Response
    {
        $province = trim((string) $request->query('province', ''));
        $city     = trim((string) $request->query('city', ''));

        $cart    = new CartService();
        $summary = $cart->summary();
        if ((int) $summary['count'] === 0) {
            return $this->json(['ok' => false, 'error' => 'سبد خرید خالی است.', 'options' => []], 422);
        }
        if ($city === '') {
            return $this->json(['ok' => true, 'options' => []]);
        }

        $options = (new ShippingService())->options(
            $province,
            $city,
            (int) $summary['subtotal'],
            $cart->parcel()
        );

        // Trim to the fields the checkout UI needs.
        $out = array_map(static fn (array $o): array => [
            'key'     => $o['key'],
            'label'   => $o['label'],
            'desc'    => $o['desc'] ?? '',
            'eta'     => $o['eta'] ?? '',
            'cost'    => (int) $o['cost'],
            'free'    => !empty($o['free']),
            'collect' => !empty($o['collect']),
        ], $options);

        return $this->json(['ok' => true, 'options' => $out]);
    }
}
