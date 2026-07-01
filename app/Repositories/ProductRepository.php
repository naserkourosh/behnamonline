<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\BaseRepository;

final class ProductRepository extends BaseRepository
{
    /** Columns selected for product "cards" (lists, rails, grids). */
    private const CARD_COLUMNS = "
        p.id, p.name, p.slug, p.price, p.old_price, p.stock, p.reserved,
        p.low_stock_threshold, p.rating_avg, p.rating_count, p.is_new, p.is_featured,
        b.name AS brand_name, b.slug AS brand_slug,
        (SELECT i.path FROM product_images i WHERE i.product_id = p.id AND i.is_primary = 1 ORDER BY i.sort LIMIT 1) AS image,
        (SELECT i.alt  FROM product_images i WHERE i.product_id = p.id AND i.is_primary = 1 ORDER BY i.sort LIMIT 1) AS image_alt,
        (SELECT i.path FROM product_images i WHERE i.product_id = p.id AND i.is_hover = 1 ORDER BY i.sort LIMIT 1) AS hover_image
    ";

    private const SORTS = [
        'newest'     => 'p.is_new DESC, p.id DESC',
        'price_asc'  => 'p.price ASC',
        'price_desc' => 'p.price DESC',
        'rating'     => 'p.rating_avg DESC, p.rating_count DESC',
        'bestselling'=> 'p.view_count DESC',
        'default'    => 'p.is_featured DESC, p.id DESC',
    ];

    /**
     * @param array<string,mixed> $filters
     * @return list<array<string,mixed>>
     */
    public function cards(array $filters = [], string $sort = 'default', int $limit = 12, int $offset = 0): array
    {
        [$where, $params] = $this->buildWhere($filters);
        $order  = self::SORTS[$sort] ?? self::SORTS['default'];
        $limit  = max(1, min(60, $limit));
        $offset = max(0, $offset);

        $sql = "SELECT " . self::CARD_COLUMNS . "
                  FROM products p
             LEFT JOIN brands b ON b.id = p.brand_id
                 WHERE {$where}
              ORDER BY {$order}
                 LIMIT {$limit} OFFSET {$offset}";

        return $this->selectAll($sql, $params);
    }

    /** @param array<string,mixed> $filters */
    public function countCards(array $filters = []): int
    {
        [$where, $params] = $this->buildWhere($filters);
        return (int) $this->scalar(
            "SELECT COUNT(*) FROM products p LEFT JOIN brands b ON b.id = p.brand_id WHERE {$where}",
            $params
        );
    }

    /** @return list<array<string,mixed>> */
    public function flashSale(int $limit = 5): array
    {
        return $this->cards(['flag_flash' => true], 'default', $limit);
    }

    /** @return list<array<string,mixed>> */
    public function featured(int $limit = 8): array
    {
        return $this->cards(['flag_featured' => true], 'default', $limit);
    }

    /** @return list<array<string,mixed>> */
    public function byCategory(int $categoryId, int $limit = 8): array
    {
        return $this->cards(['category_id' => $categoryId], 'default', $limit);
    }

    /** @return list<array<string,mixed>> */
    public function related(int $categoryId, int $excludeId, int $limit = 6): array
    {
        return $this->cards(['category_id' => $categoryId, 'exclude_id' => $excludeId], 'rating', $limit);
    }

    /**
     * @param list<int> $ids
     * @return list<array<string,mixed>>
     */
    public function cardsByIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }
        [$in, $params] = $this->inClause($ids);
        $rows = $this->selectAll(
            "SELECT " . self::CARD_COLUMNS . "
               FROM products p
          LEFT JOIN brands b ON b.id = p.brand_id
              WHERE p.is_active = 1 AND p.id IN {$in}",
            $params
        );
        // Preserve the requested order.
        $byId = [];
        foreach ($rows as $r) {
            $byId[(int) $r['id']] = $r;
        }
        $ordered = [];
        foreach ($ids as $id) {
            if (isset($byId[$id])) {
                $ordered[] = $byId[$id];
            }
        }
        return $ordered;
    }

    /** @return array<string,mixed>|null */
    public function findBySlug(string $slug): ?array
    {
        return $this->selectOne(
            "SELECT p.*, b.name AS brand_name, b.slug AS brand_slug,
                    c.name AS category_name, c.slug AS category_slug
               FROM products p
          LEFT JOIN brands b ON b.id = p.brand_id
          LEFT JOIN categories c ON c.id = p.category_id
              WHERE p.slug = ? AND p.is_active = 1
              LIMIT 1",
            [$slug]
        );
    }

    /** @return array<string,mixed>|null */
    public function findActive(int $id): ?array
    {
        return $this->selectOne(
            'SELECT * FROM products WHERE id = ? AND is_active = 1 LIMIT 1',
            [$id]
        );
    }

    /** @return list<array<string,mixed>> */
    public function images(int $productId): array
    {
        return $this->selectAll(
            'SELECT path, alt, title, is_primary, is_hover FROM product_images
              WHERE product_id = ? ORDER BY is_primary DESC, sort, id',
            [$productId]
        );
    }

    /** @return list<array<string,mixed>> */
    public function attributes(int $productId): array
    {
        return $this->selectAll(
            'SELECT attr_key, attr_value FROM product_attributes WHERE product_id = ? ORDER BY sort, id',
            [$productId]
        );
    }

    /** @return list<array<string,mixed>> */
    public function variants(int $productId): array
    {
        return $this->selectAll(
            'SELECT id, label, sku, price_override, stock FROM product_variants
              WHERE product_id = ? ORDER BY sort, id',
            [$productId]
        );
    }

    /** @return array<string,mixed>|null */
    public function findVariant(int $variantId, int $productId): ?array
    {
        return $this->selectOne(
            'SELECT id, label, sku, price_override, stock FROM product_variants
              WHERE id = ? AND product_id = ? LIMIT 1',
            [$variantId, $productId]
        );
    }

    public function incrementViews(int $id): void
    {
        $this->execute('UPDATE products SET view_count = view_count + 1 WHERE id = ?', [$id]);
    }

    public function decrementStock(int $productId, int $qty): void
    {
        $this->execute(
            'UPDATE products SET stock = GREATEST(0, stock - ?) WHERE id = ?',
            [$qty, $productId]
        );
    }

    public function decrementVariantStock(int $variantId, int $qty): void
    {
        $this->execute(
            'UPDATE product_variants SET stock = GREATEST(0, stock - ?) WHERE id = ?',
            [$qty, $variantId]
        );
    }

    /**
     * Build the WHERE clause and positional params from a filter array.
     * @param array<string,mixed> $filters
     * @return array{0:string,1:list<mixed>}
     */
    private function buildWhere(array $filters): array
    {
        $clauses = ['p.is_active = 1'];
        $params  = [];

        if (!empty($filters['category_id'])) {
            $clauses[] = 'p.category_id = ?';
            $params[]  = (int) $filters['category_id'];
        }
        if (!empty($filters['exclude_id'])) {
            $clauses[] = 'p.id <> ?';
            $params[]  = (int) $filters['exclude_id'];
        }
        if (!empty($filters['brand_ids']) && is_array($filters['brand_ids'])) {
            $ids = array_map('intval', $filters['brand_ids']);
            [$in, $inParams] = $this->inClause($ids);
            $clauses[] = "p.brand_id IN {$in}";
            $params    = array_merge($params, $inParams);
        }
        if (isset($filters['min_price']) && $filters['min_price'] !== '') {
            $clauses[] = 'p.price >= ?';
            $params[]  = (int) $filters['min_price'];
        }
        if (isset($filters['max_price']) && $filters['max_price'] !== '') {
            $clauses[] = 'p.price <= ?';
            $params[]  = (int) $filters['max_price'];
        }
        if (!empty($filters['in_stock'])) {
            $clauses[] = '(p.stock - p.reserved) > 0';
        }
        if (!empty($filters['min_rating'])) {
            $clauses[] = 'p.rating_avg >= ?';
            $params[]  = (float) $filters['min_rating'];
        }
        if (!empty($filters['flag_flash'])) {
            $clauses[] = 'p.on_flash_sale = 1';
        }
        if (!empty($filters['flag_featured'])) {
            $clauses[] = 'p.is_featured = 1';
        }
        if (!empty($filters['flag_new'])) {
            $clauses[] = 'p.is_new = 1';
        }
        if (!empty($filters['search'])) {
            $term      = '%' . trim((string) $filters['search']) . '%';
            $clauses[] = '(p.name LIKE ? OR p.sku LIKE ? OR b.name LIKE ?)';
            $params[]  = $term;
            $params[]  = $term;
            $params[]  = $term;
        }

        return [implode(' AND ', $clauses), $params];
    }
}
