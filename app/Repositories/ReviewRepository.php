<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\BaseRepository;

final class ReviewRepository extends BaseRepository
{
    /** @return list<array<string,mixed>> Approved reviews for a product. */
    public function approvedForProduct(int $productId, int $limit = 10): array
    {
        $limit = max(1, min(50, $limit));
        return $this->selectAll(
            "SELECT id, author_name, rating, body, is_verified, created_at
               FROM reviews
              WHERE product_id = ? AND status = 'approved'
              ORDER BY created_at DESC
              LIMIT {$limit}",
            [$productId]
        );
    }

    /** @return list<array<string,mixed>> Latest approved reviews across the store (home page). */
    public function latestApproved(int $limit = 6): array
    {
        $limit = max(1, min(20, $limit));
        return $this->selectAll(
            "SELECT r.author_name, r.rating, r.body, r.is_verified, p.name AS product_name
               FROM reviews r
               JOIN products p ON p.id = r.product_id
              WHERE r.status = 'approved'
              ORDER BY r.created_at DESC
              LIMIT {$limit}"
        );
    }
}
