<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Session;

/**
 * Tracks recently viewed product ids in the session (most-recent first).
 */
final class RecentlyViewed
{
    private const KEY = 'recently_viewed';
    private const MAX = 10;

    public static function add(int $productId): void
    {
        $ids = self::ids();
        $ids = array_values(array_filter($ids, static fn ($id) => $id !== $productId));
        array_unshift($ids, $productId);
        Session::set(self::KEY, array_slice($ids, 0, self::MAX));
    }

    /** @return list<int> */
    public static function ids(int $excludeId = 0): array
    {
        $ids = Session::get(self::KEY, []);
        $ids = is_array($ids) ? array_map('intval', $ids) : [];
        if ($excludeId > 0) {
            $ids = array_values(array_filter($ids, static fn ($id) => $id !== $excludeId));
        }
        return $ids;
    }
}
