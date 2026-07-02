<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Services\CartService;

final class CartApiController extends Controller
{
    public function show(Request $request): Response
    {
        return $this->json(['ok' => true, 'summary' => (new CartService())->summary()]);
    }

    public function add(Request $request): Response
    {
        $productId = (int) $request->input('product_id', 0);
        $qty       = (int) $request->input('qty', 1);
        $variantId = $request->input('variant_id');
        $variantId = ($variantId === null || $variantId === '' || (int) $variantId === 0)
            ? null
            : (int) $variantId;

        if ($productId <= 0) {
            return $this->json(['ok' => false, 'error' => 'شناسه محصول نامعتبر است.'], 422);
        }

        $result = (new CartService())->add($productId, $variantId, max(1, $qty));

        return $this->json($result, $result['ok'] ? 200 : 422);
    }

    public function update(Request $request): Response
    {
        $lineId = (int) $request->input('item_id', 0);
        $qty    = (int) $request->input('qty', 1);

        if ($lineId <= 0) {
            return $this->json(['ok' => false, 'error' => 'آیتم نامعتبر است.'], 422);
        }

        return $this->json((new CartService())->updateQty($lineId, $qty));
    }

    public function remove(Request $request): Response
    {
        $lineId = (int) $request->input('item_id', 0);

        if ($lineId <= 0) {
            return $this->json(['ok' => false, 'error' => 'آیتم نامعتبر است.'], 422);
        }

        return $this->json((new CartService())->remove($lineId));
    }

    public function applyCoupon(Request $request): Response
    {
        $code = trim((string) $request->input('code', ''));
        if ($code === '') {
            return $this->json(['ok' => false, 'error' => 'کد تخفیف را وارد کنید.'], 422);
        }
        $result = (new CartService())->applyCoupon($code);
        // Always 200: the JS reads the `ok` flag so it can update the summary
        // whether the code was accepted or rejected.
        return $this->json([
            'ok'      => $result['ok'],
            'message' => $result['message'],
            'error'   => $result['ok'] ? null : $result['message'],
            'summary' => $result['summary'],
        ]);
    }

    public function removeCoupon(Request $request): Response
    {
        return $this->json((new CartService())->removeCoupon());
    }
}
