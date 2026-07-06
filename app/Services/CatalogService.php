<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\BannerRepository;
use App\Repositories\BrandRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\ProductRepository;
use App\Repositories\ReviewRepository;

/**
 * Aggregates catalog data into clean view-models for the storefront pages.
 */
final class CatalogService
{
    private ProductRepository $products;
    private CategoryRepository $categories;
    private BrandRepository $brands;
    private ReviewRepository $reviews;

    public function __construct()
    {
        $this->products   = new ProductRepository();
        $this->categories = new CategoryRepository();
        $this->brands     = new BrandRepository();
        $this->reviews    = new ReviewRepository();
    }

    /** @return array<string,mixed> */
    public function home(): array
    {
        $categories = $this->categories->allActiveWithCounts();

        $sections = [];
        foreach ($categories as $cat) {
            if ((int) $cat['product_count'] === 0) {
                continue;
            }
            $items = $this->products->byCategory((int) $cat['id'], 10);
            if ($items !== []) {
                $sections[] = ['category' => $cat, 'items' => $items];
            }
            if (count($sections) >= 4) {
                break;
            }
        }

        $banners = new BannerRepository();

        return [
            'categories'   => $categories,
            'flashSale'    => $this->products->flashSale(8),
            'flashEndsAt'  => $this->products->flashSoonestEnd(),
            'sections'     => $sections,
            'brands'       => $this->brands->allActive(),
            'reviews'      => $this->reviews->latestApproved(6),
            'posts'        => $this->demoPosts(),
            'heroBanners'  => $banners->activeByPlacement('hero', 6),
            'promoBanners' => $banners->activeByPlacement('promo', 2),
            'stripBanners' => $banners->activeByPlacement('strip', 2),
            'inlineBanners' => $banners->activeByPlacement('inline', 4),
        ];
    }

    /**
     * @param array<string,mixed> $query  raw request query (filters/sort/page)
     * @return array<string,mixed>
     */
    public function category(?string $slug, array $query): array
    {
        $category = $slug !== null ? $this->categories->findBySlug($slug) : null;

        $perPage = 8;
        $page    = max(1, (int) ($query['page'] ?? 1));
        $sort    = (string) ($query['sort'] ?? 'default');

        $brandIds = [];
        if (!empty($query['brand']) && is_array($query['brand'])) {
            $brandIds = array_map('intval', $query['brand']);
        }

        $filters = [
            'category_id' => $category['id'] ?? null,
            'brand_ids'   => $brandIds,
            'min_price'   => $query['min_price'] ?? '',
            'max_price'   => $query['max_price'] ?? '',
            'in_stock'    => !empty($query['in_stock']),
            'on_sale'     => !empty($query['on_sale']),
            'min_rating'  => $query['min_rating'] ?? '',
            'search'      => $query['q'] ?? '',
        ];

        $total    = $this->products->countCards($filters);
        $products = $this->products->cards($filters, $sort, $perPage, ($page - 1) * $perPage);

        return [
            'category'    => $category,
            'allCategories' => $this->categories->allActiveWithCounts(),
            'brands'      => $category ? $this->brands->inCategory((int) $category['id']) : $this->brands->allActive(),
            'products'    => $products,
            'total'       => $total,
            'page'        => $page,
            'perPage'     => $perPage,
            'hasMore'     => ($page * $perPage) < $total,
            'sort'        => $sort,
            'filters'     => $filters,
        ];
    }

    /** @return array<string,mixed>|null */
    public function product(string $slug): ?array
    {
        $product = $this->products->findBySlug($slug);
        if ($product === null) {
            return null;
        }

        $pid = (int) $product['id'];
        $this->products->incrementViews($pid);
        RecentlyViewed::add($pid);

        $categoryId = (int) ($product['category_id'] ?? 0);
        $related    = $categoryId > 0 ? $this->products->related($categoryId, $pid, 8) : [];

        return [
            'product'    => $product,
            'images'     => $this->products->images($pid),
            'attributes' => $this->products->attributes($pid),
            'variants'   => $this->products->variants($pid),
            'reviews'    => $this->reviews->approvedForProduct($pid, 10),
            'related'    => $related,
            'fbt'        => array_slice($related, 0, 3),
            'recently'   => $this->products->cardsByIds(RecentlyViewed::ids($pid)),
        ];
    }

    /** @return list<array<string,mixed>> Demo blog teasers (real blog arrives in Phase 6). */
    private function demoPosts(): array
    {
        return [
            ['title' => '۷ گام طلایی برای روتین مراقبت پوست شبانه', 'date' => '۳ روز پیش', 'read' => '۵ دقیقه مطالعه'],
            ['title' => 'چطور رنگ رژ لب مناسب پوستمان را انتخاب کنیم؟', 'date' => '۱ هفته پیش', 'read' => '۴ دقیقه مطالعه'],
            ['title' => 'راهنمای انتخاب عطر بر اساس فصل', 'date' => '۲ هفته پیش', 'read' => '۶ دقیقه مطالعه'],
        ];
    }
}
