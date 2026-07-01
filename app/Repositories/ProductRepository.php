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
        'default'    => 'p.sort DESC, p.is_featured DESC, p.id DESC',
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

    /* ───────────────────────── Admin ───────────────────────── */

    /**
     * @param array{q?:string,category?:int,brand?:int,tag?:int,stock?:string} $filters
     * @return list<array<string,mixed>> All products (active + inactive) for the admin list.
     */
    public function adminList(array $filters, int $limit, int $offset): array
    {
        $limit  = max(1, min(100, $limit));
        $offset = max(0, $offset);
        [$where, $params] = $this->adminWhere($filters);
        return $this->selectAll(
            "SELECT p.id, p.name, p.slug, p.price, p.old_price, p.stock, p.reserved, p.sort,
                    p.is_active, p.is_new, p.is_featured,
                    b.name AS brand_name, c.name AS category_name,
                    (SELECT i.path FROM product_images i WHERE i.product_id = p.id AND i.is_primary = 1 ORDER BY i.sort LIMIT 1) AS image
               FROM products p
          LEFT JOIN brands b ON b.id = p.brand_id
          LEFT JOIN categories c ON c.id = p.category_id
              WHERE {$where}
              ORDER BY p.sort DESC, p.id DESC
              LIMIT {$limit} OFFSET {$offset}",
            $params
        );
    }

    /** @param array{q?:string,category?:int,brand?:int,tag?:int,stock?:string} $filters */
    public function adminCount(array $filters): int
    {
        [$where, $params] = $this->adminWhere($filters);
        return (int) $this->scalar(
            "SELECT COUNT(*) FROM products p LEFT JOIN brands b ON b.id = p.brand_id WHERE {$where}",
            $params
        );
    }

    /**
     * Build the WHERE clause for the admin product list from its filters.
     * @param array{q?:string,category?:int,brand?:int,tag?:int,stock?:string} $filters
     * @return array{0:string,1:list<mixed>}
     */
    private function adminWhere(array $filters): array
    {
        $clauses = ['1=1'];
        $params  = [];

        $q = trim((string) ($filters['q'] ?? ''));
        if ($q !== '') {
            $clauses[] = '(p.name LIKE ? OR p.sku LIKE ?)';
            $params[]  = '%' . $q . '%';
            $params[]  = '%' . $q . '%';
        }
        if (!empty($filters['category'])) {
            $clauses[] = 'EXISTS (SELECT 1 FROM product_categories pc WHERE pc.product_id = p.id AND pc.category_id = ?)';
            $params[]  = (int) $filters['category'];
        }
        if (!empty($filters['brand'])) {
            $clauses[] = 'p.brand_id = ?';
            $params[]  = (int) $filters['brand'];
        }
        if (!empty($filters['tag'])) {
            $clauses[] = 'EXISTS (SELECT 1 FROM product_tags pt WHERE pt.product_id = p.id AND pt.tag_id = ?)';
            $params[]  = (int) $filters['tag'];
        }
        if (($filters['stock'] ?? '') === 'in') {
            $clauses[] = '(p.stock - p.reserved) > 0';
        } elseif (($filters['stock'] ?? '') === 'out') {
            $clauses[] = '(p.stock - p.reserved) <= 0';
        }

        return [implode(' AND ', $clauses), $params];
    }

    public function updateSort(int $id, int $sort): void
    {
        $this->execute('UPDATE products SET sort = ?, updated_at = ? WHERE id = ?', [$sort, date('Y-m-d H:i:s'), $id]);
    }

    /** @return array<string,mixed>|null */
    public function findById(int $id): ?array
    {
        return $this->selectOne('SELECT * FROM products WHERE id = ? LIMIT 1', [$id]);
    }

    /** @param array<string,mixed> $d */
    public function insert(array $d): int
    {
        $now = date('Y-m-d H:i:s');
        $this->execute(
            'INSERT INTO products
                (category_id, brand_id, name, slug, sku, barcode, short_desc, description, aparat_embed,
                 price, old_price, stock, low_stock_threshold, is_active, is_new, is_featured, on_flash_sale,
                 expiration_date, sort, seo_title, seo_description, created_at, updated_at)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
            [
                $d['category_id'], $d['brand_id'], $d['name'], $d['slug'], $d['sku'], $d['barcode'],
                $d['short_desc'], $d['description'], $d['aparat_embed'], $d['price'], $d['old_price'],
                $d['stock'], $d['low_stock_threshold'], $d['is_active'], $d['is_new'], $d['is_featured'],
                $d['on_flash_sale'], $d['expiration_date'], $d['sort'], $d['seo_title'], $d['seo_description'], $now, $now,
            ]
        );
        return $this->lastInsertId();
    }

    /** @param array<string,mixed> $d */
    public function updateProduct(int $id, array $d): void
    {
        $this->execute(
            'UPDATE products SET category_id=?, brand_id=?, name=?, slug=?, sku=?, barcode=?, short_desc=?,
                description=?, aparat_embed=?, price=?, old_price=?, stock=?, low_stock_threshold=?,
                is_active=?, is_new=?, is_featured=?, on_flash_sale=?, expiration_date=?, sort=?,
                seo_title=?, seo_description=?, updated_at=?
              WHERE id=?',
            [
                $d['category_id'], $d['brand_id'], $d['name'], $d['slug'], $d['sku'], $d['barcode'],
                $d['short_desc'], $d['description'], $d['aparat_embed'], $d['price'], $d['old_price'],
                $d['stock'], $d['low_stock_threshold'], $d['is_active'], $d['is_new'], $d['is_featured'],
                $d['on_flash_sale'], $d['expiration_date'], $d['sort'], $d['seo_title'], $d['seo_description'],
                date('Y-m-d H:i:s'), $id,
            ]
        );
    }

    public function deleteProduct(int $id): void
    {
        $this->execute('DELETE FROM products WHERE id = ?', [$id]);
    }

    public function slugExists(string $slug, int $exceptId = 0): bool
    {
        return (int) $this->scalar('SELECT COUNT(*) FROM products WHERE slug = ? AND id <> ?', [$slug, $exceptId]) > 0;
    }

    public function addImage(int $productId, string $path, string $alt, string $title, bool $isPrimary, bool $isHover, int $sort): void
    {
        $this->execute(
            'INSERT INTO product_images (product_id, path, alt, title, sort, is_primary, is_hover) VALUES (?,?,?,?,?,?,?)',
            [$productId, $path, $alt, $title, $sort, $isPrimary ? 1 : 0, $isHover ? 1 : 0]
        );
    }

    /** @return array<string,mixed>|null */
    public function imageById(int $imgId): ?array
    {
        return $this->selectOne('SELECT * FROM product_images WHERE id = ? LIMIT 1', [$imgId]);
    }

    public function deleteImageById(int $imgId): void
    {
        $this->execute('DELETE FROM product_images WHERE id = ?', [$imgId]);
    }

    public function updateImageMeta(int $imgId, string $alt, string $title): void
    {
        $this->execute('UPDATE product_images SET alt = ?, title = ? WHERE id = ?', [$alt, $title, $imgId]);
    }

    public function setPrimaryImage(int $productId, int $imgId): void
    {
        $this->execute('UPDATE product_images SET is_primary = 0 WHERE product_id = ?', [$productId]);
        $this->execute('UPDATE product_images SET is_primary = 1 WHERE id = ? AND product_id = ?', [$imgId, $productId]);
    }

    /** @return list<array<string,mixed>> All images for admin editing. */
    public function allImages(int $productId): array
    {
        return $this->selectAll(
            'SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, sort, id',
            [$productId]
        );
    }

    /** Make sure exactly one image is primary (first by sort) if any exist. */
    public function ensurePrimary(int $productId): void
    {
        $hasPrimary = (int) $this->scalar('SELECT COUNT(*) FROM product_images WHERE product_id = ? AND is_primary = 1', [$productId]);
        if ($hasPrimary === 0) {
            $first = $this->scalar('SELECT id FROM product_images WHERE product_id = ? ORDER BY sort, id LIMIT 1', [$productId]);
            if ($first) {
                $this->execute('UPDATE product_images SET is_primary = 1 WHERE id = ?', [(int) $first]);
            }
        }
    }

    /** @param list<array{key:string,value:string}> $pairs */
    public function replaceAttributes(int $productId, array $pairs): void
    {
        $this->execute('DELETE FROM product_attributes WHERE product_id = ?', [$productId]);
        $i = 0;
        foreach ($pairs as $p) {
            if (trim($p['key']) === '' || trim($p['value']) === '') {
                continue;
            }
            $this->execute(
                'INSERT INTO product_attributes (product_id, attr_key, attr_value, sort) VALUES (?,?,?,?)',
                [$productId, $p['key'], $p['value'], $i++]
            );
        }
    }

    /** @param list<array{label:string,sku:string,price_override:?int,stock:int}> $variants */
    public function replaceVariants(int $productId, array $variants): void
    {
        $this->execute('DELETE FROM product_variants WHERE product_id = ?', [$productId]);
        $i = 0;
        foreach ($variants as $v) {
            if (trim($v['label']) === '') {
                continue;
            }
            $this->execute(
                'INSERT INTO product_variants (product_id, label, sku, price_override, stock, sort) VALUES (?,?,?,?,?,?)',
                [$productId, $v['label'], $v['sku'] ?: null, $v['price_override'], $v['stock'], $i++]
            );
        }
    }

    /** @return list<int> */
    public function tagIds(int $productId): array
    {
        $rows = $this->selectAll('SELECT tag_id FROM product_tags WHERE product_id = ?', [$productId]);
        return array_map(static fn ($r) => (int) $r['tag_id'], $rows);
    }

    /** @return list<int> Category ids this product belongs to (many-to-many). */
    public function categoryIds(int $productId): array
    {
        $rows = $this->selectAll('SELECT category_id FROM product_categories WHERE product_id = ?', [$productId]);
        return array_map(static fn ($r) => (int) $r['category_id'], $rows);
    }

    /** @param list<int> $categoryIds */
    public function syncCategories(int $productId, array $categoryIds): void
    {
        $this->execute('DELETE FROM product_categories WHERE product_id = ?', [$productId]);
        foreach (array_unique(array_filter($categoryIds)) as $cid) {
            $this->execute('INSERT IGNORE INTO product_categories (product_id, category_id) VALUES (?,?)', [$productId, (int) $cid]);
        }
    }

    /** @param list<int> $tagIds */
    public function syncTags(int $productId, array $tagIds): void
    {
        $this->execute('DELETE FROM product_tags WHERE product_id = ?', [$productId]);
        foreach (array_unique($tagIds) as $tid) {
            $this->execute('INSERT IGNORE INTO product_tags (product_id, tag_id) VALUES (?,?)', [$productId, (int) $tid]);
        }
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
            // Membership is many-to-many: match any product linked to the category.
            $clauses[] = 'EXISTS (SELECT 1 FROM product_categories pc WHERE pc.product_id = p.id AND pc.category_id = ?)';
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
