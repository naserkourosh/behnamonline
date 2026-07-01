<?php

declare(strict_types=1);

namespace App\Controllers\Storefront;

use App\Controllers\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Services\CatalogService;

final class CategoryController extends Controller
{
    public function index(Request $request): Response
    {
        return $this->respond(null, $request);
    }

    public function show(Request $request): Response
    {
        $slug = (string) $request->param('slug');
        $data = (new CatalogService())->category($slug, $request->queryAll());

        if ($data['category'] === null) {
            return $this->notFound();
        }

        return $this->finish($data, $request);
    }

    private function respond(?string $slug, Request $request): Response
    {
        $data = (new CatalogService())->category($slug, $request->queryAll());
        return $this->finish($data, $request);
    }

    /** @param array<string,mixed> $data */
    private function finish(array $data, Request $request): Response
    {
        // AJAX "load more": return rendered cards + pagination flag only.
        if ($request->query('partial') === '1') {
            $html = '';
            foreach ($data['products'] as $product) {
                $html .= View::render('partials/product-card', ['product' => $product], null);
            }
            return $this->json([
                'ok'      => true,
                'html'    => $html,
                'hasMore' => $data['hasMore'],
                'page'    => $data['page'],
            ]);
        }

        return $this->view('storefront/category', $data);
    }
}
