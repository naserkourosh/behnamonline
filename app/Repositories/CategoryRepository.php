<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\BaseRepository;

final class CategoryRepository extends BaseRepository
{
    /** @return list<array<string,mixed>> Active categories with live product counts. */
    public function allActiveWithCounts(): array
    {
        return $this->selectAll(
            'SELECT c.*,
                    (SELECT COUNT(*) FROM product_categories pc
                       JOIN products p ON p.id = pc.product_id
                      WHERE pc.category_id = c.id AND p.is_active = 1) AS product_count
               FROM categories c
              WHERE c.is_active = 1
              ORDER BY c.sort, c.id'
        );
    }

    /** @return array<string,mixed>|null */
    public function findBySlug(string $slug): ?array
    {
        return $this->selectOne(
            'SELECT * FROM categories WHERE slug = ? AND is_active = 1 LIMIT 1',
            [$slug]
        );
    }

    /* ───────────────────────── Admin ───────────────────────── */

    /** @return list<array<string,mixed>> */
    public function allAdmin(): array
    {
        return $this->selectAll(
            'SELECT c.*, p.name AS parent_name,
                    (SELECT COUNT(*) FROM product_categories pc WHERE pc.category_id = c.id) AS product_count
               FROM categories c
          LEFT JOIN categories p ON p.id = c.parent_id
              ORDER BY c.sort, c.id'
        );
    }

    /** @return array<string,mixed>|null */
    public function find(int $id): ?array
    {
        return $this->selectOne('SELECT * FROM categories WHERE id = ? LIMIT 1', [$id]);
    }

    /** @param array<string,mixed> $d */
    public function insert(array $d): int
    {
        $this->execute(
            'INSERT INTO categories (parent_id, name, slug, image, sort, is_active, seo_title, seo_description, created_at)
             VALUES (?,?,?,?,?,?,?,?,?)',
            [$d['parent_id'], $d['name'], $d['slug'], $d['image'], $d['sort'], $d['is_active'], $d['seo_title'], $d['seo_description'], date('Y-m-d H:i:s')]
        );
        return $this->lastInsertId();
    }

    /** @param array<string,mixed> $d */
    public function update(int $id, array $d): void
    {
        $this->execute(
            'UPDATE categories SET parent_id=?, name=?, slug=?, image=?, sort=?, is_active=?, seo_title=?, seo_description=? WHERE id=?',
            [$d['parent_id'], $d['name'], $d['slug'], $d['image'], $d['sort'], $d['is_active'], $d['seo_title'], $d['seo_description'], $id]
        );
    }

    public function delete(int $id): void
    {
        $this->execute('DELETE FROM categories WHERE id = ?', [$id]);
    }

    public function slugExists(string $slug, int $exceptId = 0): bool
    {
        return (int) $this->scalar('SELECT COUNT(*) FROM categories WHERE slug = ? AND id <> ?', [$slug, $exceptId]) > 0;
    }
}
