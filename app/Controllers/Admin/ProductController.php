<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\BrandRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\ProductRepository;
use App\Repositories\TagRepository;
use App\Services\MediaService;

final class ProductController extends AdminController
{
    private ProductRepository $products;

    public function __construct()
    {
        $this->products = new ProductRepository();
    }

    public function index(Request $request): Response
    {
        if ($r = $this->guard('products')) {
            return $r;
        }
        $filters = [
            'q'        => trim((string) $request->query('q', '')),
            'category' => (int) $request->query('category', 0),
            'brand'    => (int) $request->query('brand', 0),
            'tag'      => (int) $request->query('tag', 0),
            'stock'    => (string) $request->query('stock', ''),
        ];
        $perPage = 20;
        $page    = max(1, (int) $request->query('page', 1));
        $total   = $this->products->adminCount($filters);

        return $this->adminView('admin/products/index', [
            'items'      => $this->products->adminList($filters, $perPage, ($page - 1) * $perPage),
            'total'      => $total,
            'page'       => $page,
            'perPage'    => $perPage,
            'pages'      => (int) ceil($total / $perPage),
            'filters'    => $filters,
            'categories' => (new CategoryRepository())->allAdmin(),
            'brands'     => (new BrandRepository())->allAdmin(),
            'tags'       => (new TagRepository())->all(),
        ], 'محصولات');
    }

    /** AJAX: update a product's display order (sort) from the list. */
    public function sort(Request $request): Response
    {
        if ($r = $this->guard('products')) {
            return $r;
        }
        $id = (int) $request->param('id');
        if ($this->products->findById($id) === null) {
            return $this->json(['ok' => false], 404);
        }
        $sort = (int) en_num((string) $request->input('sort', 0));
        $this->products->updateSort($id, $sort);
        $this->audit($request, 'update', 'product', $id, 'sort=' . $sort);
        return $this->json(['ok' => true, 'sort' => $sort]);
    }

    public function create(Request $request): Response
    {
        if ($r = $this->guard('products')) {
            return $r;
        }
        return $this->form(null);
    }

    public function edit(Request $request): Response
    {
        if ($r = $this->guard('products')) {
            return $r;
        }
        $product = $this->products->findById((int) $request->param('id'));
        if ($product === null) {
            return $this->notFound();
        }
        return $this->form($product);
    }

    public function store(Request $request): Response
    {
        if ($r = $this->guard('products')) {
            return $r;
        }
        $data = $this->collect($request);
        if ($data['name'] === '' || $data['price'] < 0) {
            Session::flash('error', 'نام و قیمت معتبر الزامی است.');
            return $this->redirect(url('/admin/products/create'));
        }
        $data['slug'] = $this->uniqueSlug($data['slug'] !== '' ? $data['slug'] : $data['name'], 0);

        $categoryIds = $this->collectCategories($request, $data);
        $id = $this->products->insert($data);
        $this->products->syncCategories($id, $categoryIds);
        $this->saveRelations($request, $id);
        $this->handleUploads($id);
        $this->products->ensurePrimary($id);

        $this->audit($request, 'create', 'product', $id, $data['name']);
        Session::flash('success', 'محصول ایجاد شد.');
        return $this->redirect(url('/admin/products/' . $id . '/edit'));
    }

    public function update(Request $request): Response
    {
        if ($r = $this->guard('products')) {
            return $r;
        }
        $id = (int) $request->param('id');
        if ($this->products->findById($id) === null) {
            return $this->notFound();
        }
        $data = $this->collect($request);
        if ($data['name'] === '') {
            Session::flash('error', 'نام محصول الزامی است.');
            return $this->redirect(url('/admin/products/' . $id . '/edit'));
        }
        $data['slug'] = $this->uniqueSlug($data['slug'] !== '' ? $data['slug'] : $data['name'], $id);

        $categoryIds = $this->collectCategories($request, $data);
        $this->products->updateProduct($id, $data);
        $this->products->syncCategories($id, $categoryIds);
        $this->saveRelations($request, $id);
        $this->updateImageMeta($request, $id);
        $this->handleUploads($id);
        $this->products->ensurePrimary($id);

        $this->audit($request, 'update', 'product', $id, $data['name']);
        Session::flash('success', 'تغییرات ذخیره شد.');
        return $this->redirect(url('/admin/products/' . $id . '/edit'));
    }

    public function destroy(Request $request): Response
    {
        if ($r = $this->guard('products')) {
            return $r;
        }
        $id = (int) $request->param('id');
        $media = new MediaService();
        foreach ($this->products->allImages($id) as $img) {
            $media->delete((string) $img['path']);
        }
        $this->products->deleteProduct($id);
        $this->audit($request, 'delete', 'product', $id);
        Session::flash('success', 'محصول حذف شد.');
        return $this->redirect(url('/admin/products'));
    }

    public function uploadImages(Request $request): Response
    {
        if ($r = $this->guard('products')) {
            return $r;
        }
        $id = (int) $request->param('id');
        if ($this->products->findById($id) === null) {
            return $this->notFound();
        }
        $this->handleUploads($id);
        $this->products->ensurePrimary($id);
        Session::flash('success', 'تصاویر افزوده شد.');
        return $this->redirect(url('/admin/products/' . $id . '/edit'));
    }

    public function deleteImage(Request $request): Response
    {
        if ($r = $this->guard('products')) {
            return $r;
        }
        $imgId = (int) $request->param('img');
        $img   = $this->products->imageById($imgId);
        if ($img !== null) {
            (new MediaService())->delete((string) $img['path']);
            $this->products->deleteImageById($imgId);
            $this->products->ensurePrimary((int) $img['product_id']);
        }
        Session::flash('success', 'تصویر حذف شد.');
        return $this->redirect(url('/admin/products/' . ($img['product_id'] ?? '') . '/edit'));
    }

    /* ─────────────────────── helpers ─────────────────────── */

    private function form(?array $product): Response
    {
        $id = $product ? (int) $product['id'] : 0;
        return $this->adminView('admin/products/form', [
            'product'    => $product,
            'categories' => (new CategoryRepository())->allAdmin(),
            'brands'     => (new BrandRepository())->allAdmin(),
            'tags'       => (new TagRepository())->all(),
            'images'      => $id ? $this->products->allImages($id) : [],
            'attributes'  => $id ? $this->products->attributes($id) : [],
            'variants'    => $id ? $this->products->variants($id) : [],
            'tagIds'      => $id ? $this->products->tagIds($id) : [],
            'categoryIds' => $id ? $this->products->categoryIds($id) : [],
        ], $product ? 'ویرایش محصول' : 'محصول جدید');
    }

    /**
     * Merge the primary category (select) with the additional categories
     * (checkboxes) into the full membership list. Resolves the primary from
     * the checkboxes when the select is empty. Mutates $data['category_id'].
     * @param array<string,mixed> $data
     * @return list<int>
     */
    private function collectCategories(Request $request, array &$data): array
    {
        $extra   = array_map('intval', (array) $request->input('categories', []));
        $primary = (int) ($data['category_id'] ?? 0);
        $all     = array_values(array_unique(array_filter(array_merge([$primary], $extra))));
        if ($primary === 0 && $all !== []) {
            $data['category_id'] = $all[0];
        }
        return $all;
    }

    /** @return array<string,mixed> */
    private function collect(Request $request): array
    {
        $int = static fn ($v): int => (int) en_num((string) $v);
        $old = $request->input('old_price');
        $exp = trim((string) $request->input('expiration_date', ''));

        return [
            'category_id'         => ($c = $int($request->input('category_id', 0))) > 0 ? $c : null,
            'brand_id'            => ($b = $int($request->input('brand_id', 0))) > 0 ? $b : null,
            'name'                => trim((string) $request->input('name', '')),
            'slug'                => slugify((string) $request->input('slug', '')),
            'sku'                 => trim((string) $request->input('sku', '')) ?: null,
            'barcode'             => trim((string) $request->input('barcode', '')) ?: null,
            'short_desc'          => html_clean((string) $request->input('short_desc', '')) ?: null,
            'description'         => html_clean((string) $request->input('description', '')),
            'aparat_embed'        => trim((string) $request->input('aparat_embed', '')) ?: null,
            'price'               => $int($request->input('price', 0)),
            'old_price'           => ($old !== null && trim((string) $old) !== '') ? $int($old) : null,
            'stock'               => $int($request->input('stock', 0)),
            'low_stock_threshold' => max(0, $int($request->input('low_stock_threshold', 5))),
            'sort'                => $int($request->input('sort', 0)),
            'is_active'           => $request->input('is_active') ? 1 : 0,
            'is_new'              => $request->input('is_new') ? 1 : 0,
            'is_featured'         => $request->input('is_featured') ? 1 : 0,
            'on_flash_sale'       => $request->input('on_flash_sale') ? 1 : 0,
            'expiration_date'     => preg_match('/^\d{4}-\d{2}-\d{2}$/', $exp) ? $exp : null,
            'seo_title'           => trim((string) $request->input('seo_title', '')) ?: null,
            'seo_description'     => trim((string) $request->input('seo_description', '')) ?: null,
        ];
    }

    private function saveRelations(Request $request, int $id): void
    {
        // Attributes (specs)
        $keys   = (array) $request->input('attr_key', []);
        $values = (array) $request->input('attr_value', []);
        $pairs  = [];
        foreach ($keys as $i => $k) {
            $pairs[] = ['key' => (string) $k, 'value' => (string) ($values[$i] ?? '')];
        }
        $this->products->replaceAttributes($id, $pairs);

        // Variants
        $labels = (array) $request->input('var_label', []);
        $skus   = (array) $request->input('var_sku', []);
        $prices = (array) $request->input('var_price', []);
        $stocks = (array) $request->input('var_stock', []);
        $vars   = [];
        foreach ($labels as $i => $label) {
            $p = trim((string) ($prices[$i] ?? ''));
            $vars[] = [
                'label'          => (string) $label,
                'sku'            => (string) ($skus[$i] ?? ''),
                'price_override' => $p !== '' ? (int) en_num($p) : null,
                'stock'          => (int) en_num((string) ($stocks[$i] ?? '0')),
            ];
        }
        $this->products->replaceVariants($id, $vars);

        // Tags
        $tagIds = array_map('intval', (array) $request->input('tags', []));
        $this->products->syncTags($id, $tagIds);
    }

    private function updateImageMeta(Request $request, int $id): void
    {
        $alts    = (array) $request->input('img_alt', []);
        $titles  = (array) $request->input('img_title', []);
        $primary = (int) en_num((string) $request->input('primary_image', 0));

        foreach ($alts as $imgId => $alt) {
            $this->products->updateImageMeta((int) $imgId, (string) $alt, (string) ($titles[$imgId] ?? ''));
        }
        if ($primary > 0) {
            $this->products->setPrimaryImage($id, $primary);
        }
    }

    private function handleUploads(int $productId): void
    {
        if (empty($_FILES['images']) || !is_array($_FILES['images']['name'])) {
            return;
        }
        $media = new MediaService();
        $count = count($_FILES['images']['name']);
        for ($i = 0; $i < $count; $i++) {
            $file = [
                'name'     => $_FILES['images']['name'][$i],
                'type'     => $_FILES['images']['type'][$i],
                'tmp_name' => $_FILES['images']['tmp_name'][$i],
                'error'    => $_FILES['images']['error'][$i],
                'size'     => $_FILES['images']['size'][$i],
            ];
            $path = $media->store($file, 'products');
            if ($path !== null) {
                $this->products->addImage($productId, $path, '', '', false, false, $i + 10);
            }
        }
    }

    private function uniqueSlug(string $base, int $exceptId): string
    {
        $slug = slugify($base);
        $candidate = $slug;
        $n = 2;
        while ($this->products->slugExists($candidate, $exceptId)) {
            $candidate = $slug . '-' . $n++;
        }
        return $candidate;
    }
}
