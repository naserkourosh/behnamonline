<?php

declare(strict_types=1);

namespace App\Controllers\Storefront;

use App\Controllers\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\ProductRepository;
use App\Services\CompareService;

/**
 * Side-by-side product comparison page. Products come from the session compare
 * list; rows are the core facts plus the union of every product's attributes.
 */
final class CompareController extends Controller
{
    public function index(Request $request): Response
    {
        $repo     = new ProductRepository();
        $ids      = (new CompareService())->ids();
        $products = $repo->cardsByIds($ids);

        $attrs    = [];
        $attrKeys = [];
        foreach ($products as $p) {
            $map = [];
            foreach ($repo->attributes((int) $p['id']) as $row) {
                $key = (string) $row['attr_key'];
                $map[$key] = (string) $row['attr_value'];
                if (!in_array($key, $attrKeys, true)) {
                    $attrKeys[] = $key;
                }
            }
            $attrs[(int) $p['id']] = $map;
        }

        return $this->view('storefront/compare', [
            'products' => $products,
            'attrs'    => $attrs,
            'attrKeys' => $attrKeys,
        ]);
    }
}
