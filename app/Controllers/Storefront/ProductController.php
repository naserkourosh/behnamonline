<?php

declare(strict_types=1);

namespace App\Controllers\Storefront;

use App\Controllers\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Services\CatalogService;

final class ProductController extends Controller
{
    public function show(Request $request): Response
    {
        $slug = (string) $request->param('slug');
        $data = (new CatalogService())->product($slug);

        if ($data === null) {
            return $this->notFound();
        }

        return $this->view('storefront/product', $data);
    }
}
