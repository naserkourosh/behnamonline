<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\BaseRepository;

final class OrderRepository extends BaseRepository
{
    /** @param array<string,mixed> $data */
    public function create(array $data): int
    {
        $now = date('Y-m-d H:i:s');
        $this->execute(
            'INSERT INTO orders
                (order_number, user_id, status, subtotal, discount, shipping_cost, total,
                 shipping_method, payment_method, payment_status,
                 receiver_name, mobile, province, city, address, postal_code, created_at, updated_at)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
            [
                $data['order_number'], $data['user_id'], $data['status'] ?? 'processing',
                $data['subtotal'], $data['discount'] ?? 0, $data['shipping_cost'] ?? 0, $data['total'],
                $data['shipping_method'] ?? null, $data['payment_method'] ?? null, $data['payment_status'] ?? 'unpaid',
                $data['receiver_name'] ?? null, $data['mobile'] ?? null, $data['province'] ?? null,
                $data['city'] ?? null, $data['address'] ?? null, $data['postal_code'] ?? null, $now, $now,
            ]
        );
        return $this->lastInsertId();
    }

    public function setNumber(int $id, string $number): void
    {
        $this->execute('UPDATE orders SET order_number = ? WHERE id = ?', [$number, $id]);
    }

    public function markPaid(int $id): void
    {
        $this->execute(
            'UPDATE orders SET payment_status = ?, updated_at = ? WHERE id = ?',
            ['paid', date('Y-m-d H:i:s'), $id]
        );
    }

    /** Settle a paid order: mark paid, advance to processing, attach tracking. */
    public function finalizePaid(int $id, string $trackingCode): void
    {
        $this->execute(
            'UPDATE orders SET payment_status = ?, status = ?, tracking_code = ?, updated_at = ?
              WHERE id = ? AND payment_status <> ?',
            ['paid', 'processing', $trackingCode, date('Y-m-d H:i:s'), $id, 'paid']
        );
    }

    public function setStatus(int $id, string $status): void
    {
        $this->execute(
            'UPDATE orders SET status = ?, updated_at = ? WHERE id = ?',
            [$status, date('Y-m-d H:i:s'), $id]
        );
    }

    /** @param array<string,mixed> $item */
    public function addItem(int $orderId, array $item): void
    {
        $this->execute(
            'INSERT INTO order_items (order_id, product_id, variant_id, name, variant_label, qty, unit_price, line_total)
             VALUES (?,?,?,?,?,?,?,?)',
            [
                $orderId, $item['product_id'], $item['variant_id'] ?? null, $item['name'],
                $item['variant_label'] ?? null, $item['qty'], $item['unit_price'], $item['line_total'],
            ]
        );
    }

    /** @return list<array<string,mixed>> */
    public function forUser(int $userId, int $limit = 20): array
    {
        $limit = max(1, min(100, $limit));
        return $this->selectAll(
            "SELECT *, (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = orders.id) AS item_count
               FROM orders WHERE user_id = ? ORDER BY id DESC LIMIT {$limit}",
            [$userId]
        );
    }

    /** @return array<string,mixed>|null */
    public function find(int $id, int $userId): ?array
    {
        return $this->selectOne('SELECT * FROM orders WHERE id = ? AND user_id = ? LIMIT 1', [$id, $userId]);
    }

    /** @return list<array<string,mixed>> */
    public function items(int $orderId): array
    {
        return $this->selectAll(
            "SELECT oi.*, p.slug,
                    (SELECT i.path FROM product_images i WHERE i.product_id = p.id AND i.is_primary = 1 ORDER BY i.sort LIMIT 1) AS image
               FROM order_items oi
          LEFT JOIN products p ON p.id = oi.product_id
              WHERE oi.order_id = ? ORDER BY oi.id",
            [$orderId]
        );
    }

    public function countForUser(int $userId): int
    {
        return (int) $this->scalar('SELECT COUNT(*) FROM orders WHERE user_id = ?', [$userId]);
    }
}
