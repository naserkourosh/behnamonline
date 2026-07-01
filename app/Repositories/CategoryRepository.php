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
                    (SELECT COUNT(*) FROM products p
                      WHERE p.category_id = c.id AND p.is_active = 1) AS product_count
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
}
