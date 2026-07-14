<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\BaseRepository;

final class OrderRepository extends BaseRepository
{
    /** Has this customer a PAID order containing the product? (buyer badge) */
    public function userPurchasedProduct(int $userId, int $productId): bool
    {
        return (int) $this->scalar(
            "SELECT COUNT(*) FROM orders o
               JOIN order_items oi ON oi.order_id = o.id
              WHERE o.user_id = ? AND o.payment_status = 'paid' AND oi.product_id = ?",
            [$userId, $productId]
        ) > 0;
    }

    /** @param array<string,mixed> $data */
    public function create(array $data): int
    {
        $now = date('Y-m-d H:i:s');
        $this->execute(
            'INSERT INTO orders
                (order_number, user_id, status, subtotal, discount, coupon_code, coupon_discount, shipping_cost, total,
                 shipping_method, payment_method, payment_status,
                 receiver_name, mobile, province, city, address, postal_code, note, created_at, updated_at)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
            [
                $data['order_number'], $data['user_id'], $data['status'] ?? 'processing',
                $data['subtotal'], $data['discount'] ?? 0, $data['coupon_code'] ?? null, $data['coupon_discount'] ?? 0,
                $data['shipping_cost'] ?? 0, $data['total'],
                $data['shipping_method'] ?? null, $data['payment_method'] ?? null, $data['payment_status'] ?? 'unpaid',
                $data['receiver_name'] ?? null, $data['mobile'] ?? null, $data['province'] ?? null,
                $data['city'] ?? null, $data['address'] ?? null, $data['postal_code'] ?? null, $data['note'] ?? null, $now, $now,
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

    /**
     * Settle a paid order WITHOUT a tracking code. The postal tracking code
     * is issued by the admin only after the parcel actually ships.
     */
    /**
     * Atomically claim the unpaid→paid transition. Returns the affected row
     * count: 0 means the order was ALREADY paid (double gateway callback or a
     * concurrent admin confirm) and the caller must skip stock/promotions.
     */
    public function markPaidProcessing(int $id): int
    {
        return $this->execute(
            'UPDATE orders SET payment_status = ?, status = ?, updated_at = ?
              WHERE id = ? AND payment_status <> ?',
            ['paid', 'processing', date('Y-m-d H:i:s'), $id, 'paid']
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

    /* ───────────────────────── Admin ───────────────────────── */

    /**
     * @param array<string,mixed> $filters  status, payment_status, search
     * @return list<array<string,mixed>>
     */
    public function adminList(array $filters, int $limit, int $offset): array
    {
        [$where, $params] = $this->adminWhere($filters);
        $limit  = max(1, min(100, $limit));
        $offset = max(0, $offset);
        return $this->selectAll(
            "SELECT o.*, (SELECT COUNT(*) FROM order_items i WHERE i.order_id = o.id) AS item_count
               FROM orders o WHERE {$where} ORDER BY o.id DESC LIMIT {$limit} OFFSET {$offset}",
            $params
        );
    }

    /** @param array<string,mixed> $filters */
    public function adminCount(array $filters): int
    {
        [$where, $params] = $this->adminWhere($filters);
        return (int) $this->scalar("SELECT COUNT(*) FROM orders o WHERE {$where}", $params);
    }

    /** @return array<string,mixed>|null */
    public function findAny(int $id): ?array
    {
        return $this->selectOne('SELECT * FROM orders WHERE id = ? LIMIT 1', [$id]);
    }

    /**
     * Orders for the accounting/inventory API (newest first, optional
     * created-since filter and paid-only restriction).
     * @return list<array<string,mixed>>
     */
    public function apiOrders(int $limit, int $offset, ?string $since, bool $paidOnly): array
    {
        $limit  = max(1, min(500, $limit));
        $offset = max(0, $offset);
        $clauses = [];
        $params  = [];
        if ($paidOnly) {
            $clauses[] = "o.payment_status = 'paid'";
        }
        if ($since !== null && preg_match('/^\d{4}-\d{2}-\d{2}/', $since)) {
            $clauses[] = 'o.created_at >= ?';
            $params[]  = $since;
        }
        $where = $clauses === [] ? '' : 'WHERE ' . implode(' AND ', $clauses);
        return $this->selectAll(
            "SELECT o.id, o.order_number, o.created_at, o.subtotal, o.discount, o.coupon_discount,
                    o.shipping_cost, o.total, o.payment_method, o.payment_status, o.status,
                    o.mobile, o.receiver_name, o.province, o.city, o.tracking_code,
                    (SELECT COUNT(*) FROM order_items i WHERE i.order_id = o.id) AS item_count
               FROM orders o {$where} ORDER BY o.id DESC LIMIT {$limit} OFFSET {$offset}",
            $params
        );
    }

    public function adminUpdate(int $id, string $status, string $paymentStatus, ?string $tracking): void
    {
        $this->execute(
            'UPDATE orders SET status = ?, payment_status = ?, tracking_code = ?, updated_at = ? WHERE id = ?',
            [$status, $paymentStatus, $tracking, date('Y-m-d H:i:s'), $id]
        );
    }

    /**
     * @param array<string,mixed> $filters
     * @return array{0:string,1:list<mixed>}
     */
    private function adminWhere(array $filters): array
    {
        $clauses = ['1=1'];
        $params  = [];
        if (!empty($filters['status'])) {
            $clauses[] = 'o.status = ?';
            $params[]  = (string) $filters['status'];
        }
        if (!empty($filters['payment_status'])) {
            $clauses[] = 'o.payment_status = ?';
            $params[]  = (string) $filters['payment_status'];
        }
        if (!empty($filters['search'])) {
            $clauses[] = '(o.order_number LIKE ? OR o.mobile LIKE ? OR o.receiver_name LIKE ?)';
            $term = '%' . $filters['search'] . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }
        return [implode(' AND ', $clauses), $params];
    }
}
