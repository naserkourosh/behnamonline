<?php

declare(strict_types=1);

namespace App\Controllers\Storefront;

use App\Controllers\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\ProductRepository;
use App\Services\CartService;
use App\Services\RecentlyViewed;

final class CartController extends Controller
{
    public function index(Request $request): Response
    {
        $summary  = (new CartService())->summary();
        $recently = (new ProductRepository())->cardsByIds(RecentlyViewed::ids());

        return $this->view('storefront/cart', [
            'summary'  => $summary,
            'recently' => $recently,
        ]);
    }
}
