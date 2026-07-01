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
}
