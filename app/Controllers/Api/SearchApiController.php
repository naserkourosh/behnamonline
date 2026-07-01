<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\ProductRepository;

final class SearchApiController extends Controller
{
    public function suggest(Request $request): Response
    {
        $q = trim((string) $request->query('q', ''));
        if (mb_strlen($q) < 2) {
            return $this->json(['ok' => true, 'results' => []]);
        }

        $rows = (new ProductRepository())->cards(['search' => $q], 'default', 6);

        $results = array_map(static fn (array $p) => [
            'name'  => $p['name'],
            'slug'  => $p['slug'],
            'brand' => $p['brand_name'],
            'price' => (int) $p['price'],
            'image' => $p['image'] ?: 'assets/images/placeholder-product.svg',
        ], $rows);

        return $this->json(['ok' => true, 'results' => $results]);
    }
}
