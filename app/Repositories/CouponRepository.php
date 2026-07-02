<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\BaseRepository;

final class CouponRepository extends BaseRepository
{
    /** @return array<string,mixed>|null */
    public function findByCode(string $code): ?array
    {
        return $this->selectOne('SELECT * FROM coupons WHERE code = ? LIMIT 1', [strtoupper(trim($code))]);
    }

    public function usageCountForUser(int $couponId, int $userId): int
    {
        return (int) $this->scalar(
            'SELECT COUNT(*) FROM coupon_usages WHERE coupon_id = ? AND user_id = ?',
            [$couponId, $userId]
        );
    }

    public function hasUsageForOrder(int $orderId): bool
    {
        return (int) $this->scalar('SELECT COUNT(*) FROM coupon_usages WHERE order_id = ?', [$orderId]) > 0;
    }

    public function recordUsage(int $couponId, ?int $userId, ?int $orderId, int $discount): void
    {
        $this->execute(
            'INSERT INTO coupon_usages (coupon_id, user_id, order_id, discount, created_at) VALUES (?,?,?,?,?)',
            [$couponId, $userId, $orderId, $discount, date('Y-m-d H:i:s')]
        );
        $this->execute('UPDATE coupons SET used_count = used_count + 1 WHERE id = ?', [$couponId]);
    }

    /* ───────────────────────── Admin ───────────────────────── */

    /** @return list<array<string,mixed>> */
    public function all(): array
    {
        return $this->selectAll('SELECT * FROM coupons ORDER BY id DESC');
    }

    /** @return array<string,mixed>|null */
    public function find(int $id): ?array
    {
        return $this->selectOne('SELECT * FROM coupons WHERE id = ? LIMIT 1', [$id]);
    }

    /** @param array<string,mixed> $d */
    public function insert(array $d): int
    {
        $this->execute(
            'INSERT INTO coupons
                (code, description, type, value, min_cart, max_discount, usage_limit, per_user_limit,
                 starts_at, ends_at, is_active, created_at)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?)',
            [
                $d['code'], $d['description'], $d['type'], $d['value'], $d['min_cart'], $d['max_discount'],
                $d['usage_limit'], $d['per_user_limit'], $d['starts_at'], $d['ends_at'], $d['is_active'],
                date('Y-m-d H:i:s'),
            ]
        );
        return $this->lastInsertId();
    }

    /** @param array<string,mixed> $d */
    public function update(int $id, array $d): void
    {
        $this->execute(
            'UPDATE coupons SET code=?, description=?, type=?, value=?, min_cart=?, max_discount=?,
                usage_limit=?, per_user_limit=?, starts_at=?, ends_at=?, is_active=? WHERE id=?',
            [
                $d['code'], $d['description'], $d['type'], $d['value'], $d['min_cart'], $d['max_discount'],
                $d['usage_limit'], $d['per_user_limit'], $d['starts_at'], $d['ends_at'], $d['is_active'], $id,
            ]
        );
    }

    public function delete(int $id): void
    {
        $this->execute('DELETE FROM coupons WHERE id = ?', [$id]);
    }

    public function codeExists(string $code, int $exceptId = 0): bool
    {
        return (int) $this->scalar('SELECT COUNT(*) FROM coupons WHERE code = ? AND id <> ?', [strtoupper(trim($code)), $exceptId]) > 0;
    }
}
