<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\BaseRepository;

final class PointTransactionRepository extends BaseRepository
{
    public function add(int $userId, ?int $orderId, int $points, string $type, string $note): void
    {
        $this->execute(
            'INSERT INTO point_transactions (user_id, order_id, points, type, note, created_at) VALUES (?,?,?,?,?,?)',
            [$userId, $orderId, $points, $type, $note, date('Y-m-d H:i:s')]
        );
    }

    public function existsForOrder(int $orderId): bool
    {
        return (int) $this->scalar('SELECT COUNT(*) FROM point_transactions WHERE order_id = ?', [$orderId]) > 0;
    }

    /** @return list<array<string,mixed>> */
    public function forUser(int $userId, int $limit = 30): array
    {
        $limit = max(1, min(100, $limit));
        return $this->selectAll(
            "SELECT * FROM point_transactions WHERE user_id = ? ORDER BY id DESC LIMIT {$limit}",
            [$userId]
        );
    }
}
