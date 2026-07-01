<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\BaseRepository;

final class BlogCategoryRepository extends BaseRepository
{
    /** @return list<array<string,mixed>> Active categories with published-post counts. */
    public function activeWithCounts(): array
    {
        return $this->selectAll(
            "SELECT c.*, (SELECT COUNT(*) FROM blog_posts p
                            WHERE p.category_id = c.id AND p.status = 'published' AND p.published_at <= NOW()) AS post_count
               FROM blog_categories c
              WHERE c.is_active = 1
              ORDER BY c.sort, c.name"
        );
    }

    /** @return array<string,mixed>|null */
    public function findBySlug(string $slug): ?array
    {
        return $this->selectOne('SELECT * FROM blog_categories WHERE slug = ? AND is_active = 1 LIMIT 1', [$slug]);
    }

    /* ── Admin ── */

    /** @return list<array<string,mixed>> */
    public function all(): array
    {
        return $this->selectAll(
            'SELECT c.*, (SELECT COUNT(*) FROM blog_posts p WHERE p.category_id = c.id) AS post_count
               FROM blog_categories c ORDER BY c.sort, c.name'
        );
    }

    /** @return array<string,mixed>|null */
    public function find(int $id): ?array
    {
        return $this->selectOne('SELECT * FROM blog_categories WHERE id = ? LIMIT 1', [$id]);
    }

    /** @param array<string,mixed> $d */
    public function insert(array $d): int
    {
        $this->execute(
            'INSERT INTO blog_categories (name, slug, sort, is_active, created_at) VALUES (?,?,?,?,?)',
            [$d['name'], $d['slug'], $d['sort'], $d['is_active'], date('Y-m-d H:i:s')]
        );
        return $this->lastInsertId();
    }

    /** @param array<string,mixed> $d */
    public function update(int $id, array $d): void
    {
        $this->execute(
            'UPDATE blog_categories SET name=?, slug=?, sort=?, is_active=? WHERE id=?',
            [$d['name'], $d['slug'], $d['sort'], $d['is_active'], $id]
        );
    }

    public function delete(int $id): void
    {
        $this->execute('DELETE FROM blog_categories WHERE id = ?', [$id]);
    }

    public function slugExists(string $slug, int $exceptId = 0): bool
    {
        return (int) $this->scalar('SELECT COUNT(*) FROM blog_categories WHERE slug = ? AND id <> ?', [$slug, $exceptId]) > 0;
    }
}
