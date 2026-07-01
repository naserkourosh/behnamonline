<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\BaseRepository;

final class CartRepository extends BaseRepository
{
    /** @return array<string,mixed>|null */
    public function findByToken(string $token): ?array
    {
        return $this->selectOne('SELECT * FROM carts WHERE token = ? LIMIT 1', [$token]);
    }

    public function create(string $token): int
    {
        $now = date('Y-m-d H:i:s');
        $this->execute(
            'INSERT INTO carts (token, created_at, updated_at) VALUES (?,?,?)',
            [$token, $now, $now]
        );
        return $this->lastInsertId();
    }

    /** Cart line items joined with product display data. @return list<array<string,mixed>> */
    public function items(int $cartId): array
    {
        return $this->selectAll(
            "SELECT ci.id, ci.product_id, ci.variant_id, ci.qty, ci.unit_price,
                    p.name, p.slug, p.stock, p.reserved, p.old_price,
                    b.name AS brand_name,
                    v.label AS variant_label,
                    (SELECT i.path FROM product_images i WHERE i.product_id = p.id AND i.is_primary = 1 ORDER BY i.sort LIMIT 1) AS image,
                    (SELECT i.alt  FROM product_images i WHERE i.product_id = p.id AND i.is_primary = 1 ORDER BY i.sort LIMIT 1) AS image_alt
               FROM cart_items ci
               JOIN products p ON p.id = ci.product_id
          LEFT JOIN brands b ON b.id = p.brand_id
          LEFT JOIN product_variants v ON v.id = ci.variant_id
              WHERE ci.cart_id = ?
              ORDER BY ci.id",
            [$cartId]
        );
    }

    /** @return array<string,mixed>|null */
    public function findLine(int $cartId, int $productId, ?int $variantId): ?array
    {
        return $this->selectOne(
            'SELECT * FROM cart_items
              WHERE cart_id = ? AND product_id = ? AND variant_id <=> ?
              LIMIT 1',
            [$cartId, $productId, $variantId]
        );
    }

    public function addLine(int $cartId, int $productId, ?int $variantId, int $qty, int $unitPrice): void
    {
        $this->execute(
            'INSERT INTO cart_items (cart_id, product_id, variant_id, qty, unit_price, created_at)
             VALUES (?,?,?,?,?,?)',
            [$cartId, $productId, $variantId, $qty, $unitPrice, date('Y-m-d H:i:s')]
        );
    }

    public function setQty(int $lineId, int $cartId, int $qty): void
    {
        $this->execute(
            'UPDATE cart_items SET qty = ? WHERE id = ? AND cart_id = ?',
            [$qty, $lineId, $cartId]
        );
    }

    public function removeLine(int $lineId, int $cartId): void
    {
        $this->execute('DELETE FROM cart_items WHERE id = ? AND cart_id = ?', [$lineId, $cartId]);
    }

    public function touch(int $cartId): void
    {
        $this->execute('UPDATE carts SET updated_at = ? WHERE id = ?', [date('Y-m-d H:i:s'), $cartId]);
    }

    public function clear(int $cartId): void
    {
        $this->execute('DELETE FROM cart_items WHERE cart_id = ?', [$cartId]);
    }
}
