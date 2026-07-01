<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\BaseRepository;

final class WishlistRepository extends BaseRepository
{
    /** @return bool the new state (true = now in wishlist) */
    public function toggle(int $userId, int $productId): bool
    {
        if ($this->has($userId, $productId)) {
            $this->execute('DELETE FROM wishlists WHERE user_id = ? AND product_id = ?', [$userId, $productId]);
            return false;
        }
        $this->execute(
            'INSERT INTO wishlists (user_id, product_id, created_at) VALUES (?,?,?)',
            [$userId, $productId, date('Y-m-d H:i:s')]
        );
        return true;
    }

    public function has(int $userId, int $productId): bool
    {
        return (int) $this->scalar(
            'SELECT COUNT(*) FROM wishlists WHERE user_id = ? AND product_id = ?',
            [$userId, $productId]
        ) > 0;
    }

    /** @return list<int> */
    public function productIds(int $userId): array
    {
        $rows = $this->selectAll('SELECT product_id FROM wishlists WHERE user_id = ? ORDER BY id DESC', [$userId]);
        return array_map(static fn ($r) => (int) $r['product_id'], $rows);
    }

    public function count(int $userId): int
    {
        return (int) $this->scalar('SELECT COUNT(*) FROM wishlists WHERE user_id = ?', [$userId]);
    }
}
