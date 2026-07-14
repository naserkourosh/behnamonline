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

    /** Create a customer review (pending until an admin approves it). */
    public function create(int $productId, ?int $userId, string $author, int $rating, string $body, bool $verified): int
    {
        $this->execute(
            'INSERT INTO reviews (product_id, user_id, author_name, rating, body, is_verified, status, created_at)
             VALUES (?,?,?,?,?,?,?,?)',
            [$productId, $userId, $author, $rating, $body, $verified ? 1 : 0, 'pending', date('Y-m-d H:i:s')]
        );
        return $this->lastInsertId();
    }

    /** One review per customer per product. */
    public function existsForUser(int $productId, int $userId): bool
    {
        return (int) $this->scalar(
            'SELECT COUNT(*) FROM reviews WHERE product_id = ? AND user_id = ?',
            [$productId, $userId]
        ) > 0;
    }

    /** @return array<string,mixed>|null */
    public function find(int $id): ?array
    {
        return $this->selectOne('SELECT * FROM reviews WHERE id = ? LIMIT 1', [$id]);
    }

    /** @return list<array<string,mixed>> */
    public function adminList(string $status, int $limit, int $offset): array
    {
        $limit  = max(1, min(100, $limit));
        $offset = max(0, $offset);
        return $this->selectAll(
            "SELECT r.*, p.name AS product_name, p.slug AS product_slug
               FROM reviews r
               JOIN products p ON p.id = r.product_id
              WHERE r.status = ?
              ORDER BY r.id DESC
              LIMIT {$limit} OFFSET {$offset}",
            [$status]
        );
    }

    public function countByStatus(string $status): int
    {
        return (int) $this->scalar('SELECT COUNT(*) FROM reviews WHERE status = ?', [$status]);
    }

    public function setStatus(int $id, string $status): void
    {
        $this->execute('UPDATE reviews SET status = ? WHERE id = ?', [$status, $id]);
    }

    public function delete(int $id): void
    {
        $this->execute('DELETE FROM reviews WHERE id = ?', [$id]);
    }

    /** Refresh a product's cached rating stats from its APPROVED reviews. */
    public function recalcProduct(int $productId): void
    {
        $this->execute(
            "UPDATE products p SET
                p.rating_count = (SELECT COUNT(*) FROM reviews r WHERE r.product_id = p.id AND r.status = 'approved'),
                p.rating_avg   = COALESCE((SELECT ROUND(AVG(r.rating), 2) FROM reviews r WHERE r.product_id = p.id AND r.status = 'approved'), 0)
              WHERE p.id = ?",
            [$productId]
        );
    }
}
