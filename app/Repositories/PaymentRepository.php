<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\BaseRepository;

final class PaymentRepository extends BaseRepository
{
    public function create(int $orderId, string $gateway, int $amount): int
    {
        $now = date('Y-m-d H:i:s');
        $this->execute(
            'INSERT INTO payments (order_id, gateway, amount, status, created_at, updated_at) VALUES (?,?,?,?,?,?)',
            [$orderId, $gateway, $amount, 'initiated', $now, $now]
        );
        return $this->lastInsertId();
    }

    public function setAuthority(int $id, string $authority): void
    {
        $this->execute(
            'UPDATE payments SET authority = ?, updated_at = ? WHERE id = ?',
            [$authority, date('Y-m-d H:i:s'), $id]
        );
    }

    public function markPaid(int $id, string $refId): void
    {
        $this->execute(
            'UPDATE payments SET status = ?, ref_id = ?, updated_at = ? WHERE id = ?',
            ['paid', $refId, date('Y-m-d H:i:s'), $id]
        );
    }

    public function markFailed(int $id): void
    {
        $this->execute(
            'UPDATE payments SET status = ?, updated_at = ? WHERE id = ?',
            ['failed', date('Y-m-d H:i:s'), $id]
        );
    }

    /** Record a manual (card-to-card) transfer reference awaiting confirmation. */
    public function recordManual(int $orderId, int $amount, string $ref): int
    {
        $now = date('Y-m-d H:i:s');
        $this->execute(
            'INSERT INTO payments (order_id, gateway, amount, ref_id, status, created_at, updated_at) VALUES (?,?,?,?,?,?,?)',
            [$orderId, 'card', $amount, $ref, 'initiated', $now, $now]
        );
        return $this->lastInsertId();
    }

    /** @return array<string,mixed>|null */
    public function latestForOrder(int $orderId): ?array
    {
        return $this->selectOne(
            'SELECT * FROM payments WHERE order_id = ? ORDER BY id DESC LIMIT 1',
            [$orderId]
        );
    }
}
