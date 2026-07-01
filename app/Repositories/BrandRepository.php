<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\BaseRepository;

final class BrandRepository extends BaseRepository
{
    /** @return list<array<string,mixed>> */
    public function allActive(): array
    {
        return $this->selectAll(
            'SELECT * FROM brands WHERE is_active = 1 ORDER BY sort, name'
        );
    }

    /**
     * Brands that have active products in a given category (for filters).
     * @return list<array<string,mixed>>
     */
    public function inCategory(int $categoryId): array
    {
        return $this->selectAll(
            'SELECT DISTINCT b.id, b.name, b.slug
               FROM brands b
               JOIN products p ON p.brand_id = b.id
              WHERE p.category_id = ? AND p.is_active = 1 AND b.is_active = 1
              ORDER BY b.name',
            [$categoryId]
        );
    }

    /* ───────────────────────── Admin ───────────────────────── */

    /** @return list<array<string,mixed>> */
    public function allAdmin(): array
    {
        return $this->selectAll(
            'SELECT b.*, (SELECT COUNT(*) FROM products p WHERE p.brand_id = b.id) AS product_count
               FROM brands b ORDER BY b.sort, b.name'
        );
    }

    /** @return array<string,mixed>|null */
    public function find(int $id): ?array
    {
        return $this->selectOne('SELECT * FROM brands WHERE id = ? LIMIT 1', [$id]);
    }

    /** @param array<string,mixed> $d */
    public function insert(array $d): int
    {
        $this->execute(
            'INSERT INTO brands (name, slug, logo, sort, is_active) VALUES (?,?,?,?,?)',
            [$d['name'], $d['slug'], $d['logo'], $d['sort'], $d['is_active']]
        );
        return $this->lastInsertId();
    }

    /** @param array<string,mixed> $d */
    public function update(int $id, array $d): void
    {
        $this->execute(
            'UPDATE brands SET name=?, slug=?, logo=?, sort=?, is_active=? WHERE id=?',
            [$d['name'], $d['slug'], $d['logo'], $d['sort'], $d['is_active'], $id]
        );
    }

    public function delete(int $id): void
    {
        $this->execute('DELETE FROM brands WHERE id = ?', [$id]);
    }

    public function slugExists(string $slug, int $exceptId = 0): bool
    {
        return (int) $this->scalar('SELECT COUNT(*) FROM brands WHERE slug = ? AND id <> ?', [$slug, $exceptId]) > 0;
    }
}
