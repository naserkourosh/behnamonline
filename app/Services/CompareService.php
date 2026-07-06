<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Session;

/**
 * Session-backed product comparison list (works for guests too). Holds up to
 * MAX product ids; the /compare page renders them side-by-side.
 */
final class CompareService
{
    private const KEY = 'compare_ids';
    public const MAX  = 4;

    /** @return list<int> */
    public function ids(): array
    {
        $ids = Session::get(self::KEY, []);
        return is_array($ids) ? array_values(array_unique(array_map('intval', $ids))) : [];
    }

    public function count(): int
    {
        return count($this->ids());
    }

    public function has(int $id): bool
    {
        return in_array($id, $this->ids(), true);
    }

    /**
     * Toggle a product in the compare list.
     * @return array{in:bool,count:int,ids:list<int>,error:?string}
     */
    public function toggle(int $id): array
    {
        $ids = $this->ids();
        $pos = array_search($id, $ids, true);

        if ($pos !== false) {
            array_splice($ids, (int) $pos, 1);
            Session::set(self::KEY, $ids);
            return ['in' => false, 'count' => count($ids), 'ids' => $ids, 'error' => null];
        }

        if (count($ids) >= self::MAX) {
            return ['in' => false, 'count' => count($ids), 'ids' => $ids,
                    'error' => 'حداکثر ' . self::MAX . ' محصول را می‌توانید مقایسه کنید.'];
        }

        $ids[] = $id;
        Session::set(self::KEY, $ids);
        return ['in' => true, 'count' => count($ids), 'ids' => $ids, 'error' => null];
    }

    public function remove(int $id): void
    {
        $ids = $this->ids();
        $pos = array_search($id, $ids, true);
        if ($pos !== false) {
            array_splice($ids, (int) $pos, 1);
            Session::set(self::KEY, $ids);
        }
    }

    public function clear(): void
    {
        Session::forget(self::KEY);
    }
}
