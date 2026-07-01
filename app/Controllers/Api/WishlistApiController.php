<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\WishlistRepository;
use App\Services\AuthService;

final class WishlistApiController extends Controller
{
    public function toggle(Request $request): Response
    {
        if (!AuthService::check()) {
            return $this->json([
                'ok'      => false,
                'auth'    => false,
                'message' => 'برای افزودن به علاقه‌مندی‌ها ابتدا وارد شوید.',
            ], 200);
        }

        $productId = (int) $request->input('product_id', 0);
        if ($productId <= 0) {
            return $this->json(['ok' => false, 'error' => 'محصول نامعتبر است.'], 422);
        }

        $repo   = new WishlistRepository();
        $userId = (int) AuthService::id();
        $active = $repo->toggle($userId, $productId);

        return $this->json([
            'ok'      => true,
            'active'  => $active,
            'count'   => $repo->count($userId),
            'message' => $active ? 'به علاقه‌مندی‌ها اضافه شد.' : 'از علاقه‌مندی‌ها حذف شد.',
        ]);
    }
}
