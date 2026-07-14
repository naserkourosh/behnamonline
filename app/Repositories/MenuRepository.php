<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\BaseRepository;

final class MenuRepository extends BaseRepository
{
    /** @return list<array<string,mixed>> */
    public function allMenus(): array
    {
        return $this->selectAll(
            'SELECT m.*, (SELECT COUNT(*) FROM menu_items i WHERE i.menu_id = m.id) AS item_count
               FROM menus m ORDER BY m.id'
        );
    }

    /** @return array<string,mixed>|null */
    public function find(int $id): ?array
    {
        return $this->selectOne('SELECT * FROM menus WHERE id = ? LIMIT 1', [$id]);
    }

    /** @return array<string,mixed>|null */
    public function findBySlug(string $slug): ?array
    {
        return $this->selectOne('SELECT * FROM menus WHERE slug = ? LIMIT 1', [$slug]);
    }

    public function insert(string $name, string $slug): int
    {
        $this->execute('INSERT INTO menus (name, slug, created_at) VALUES (?,?,?)', [$name, $slug, date('Y-m-d H:i:s')]);
        return $this->lastInsertId();
    }

    public function slugExists(string $slug): bool
    {
        return (int) $this->scalar('SELECT COUNT(*) FROM menus WHERE slug = ?', [$slug]) > 0;
    }

    /** @return list<array<string,mixed>> Top-level items with their children. */
    public function items(int $menuId): array
    {
        return $this->selectAll(
            'SELECT * FROM menu_items WHERE menu_id = ? ORDER BY sort, id',
            [$menuId]
        );
    }

    public function addItem(int $menuId, ?int $parentId, string $label, string $url, int $sort, bool $isMega = false): int
    {
        $this->execute(
            'INSERT INTO menu_items (menu_id, parent_id, label, url, is_mega, sort, created_at) VALUES (?,?,?,?,?,?,?)',
            [$menuId, $parentId, $label, $url, $isMega ? 1 : 0, $sort, date('Y-m-d H:i:s')]
        );
        return $this->lastInsertId();
    }

    /** Depth of an item (0 = top level). Cycle-safe via a hop limit. */
    public function itemDepth(int $id): int
    {
        $depth = 0;
        for ($i = 0; $i < 5; $i++) {
            $row = $this->findItem($id);
            $parent = $row !== null ? (int) ($row['parent_id'] ?? 0) : 0;
            if ($parent <= 0) {
                break;
            }
            $depth++;
            $id = $parent;
        }
        return $depth;
    }

    /** @return array<string,mixed>|null */
    public function findItem(int $id): ?array
    {
        return $this->selectOne('SELECT * FROM menu_items WHERE id = ? LIMIT 1', [$id]);
    }

    public function deleteItem(int $id): void
    {
        $this->execute('DELETE FROM menu_items WHERE id = ?', [$id]);
    }

    public function maxSort(int $menuId): int
    {
        return (int) $this->scalar('SELECT COALESCE(MAX(sort),0) FROM menu_items WHERE menu_id = ?', [$menuId]);
    }

    /** Primary menu items for the storefront header (flat, ordered). @return list<array<string,mixed>> */
    public function primaryItems(): array
    {
        return $this->selectAll(
            "SELECT i.label, i.url FROM menu_items i
               JOIN menus m ON m.id = i.menu_id
              WHERE m.slug = 'primary' AND i.parent_id IS NULL
              ORDER BY i.sort, i.id"
        );
    }

    /**
     * Primary menu as a nested tree (max 3 levels) for the storefront header:
     * top items each carry 'children'; children carry their own 'children'.
     * @return list<array<string,mixed>>
     */
    public function primaryTree(): array
    {
        $rows = $this->selectAll(
            "SELECT i.id, i.parent_id, i.label, i.url, i.is_mega
               FROM menu_items i
               JOIN menus m ON m.id = i.menu_id
              WHERE m.slug = 'primary'
              ORDER BY i.sort, i.id"
        );
        return $this->buildTree($rows, null);
    }

    /**
     * @param list<array<string,mixed>> $rows
     * @return list<array<string,mixed>>
     */
    private function buildTree(array $rows, ?int $parent): array
    {
        $out = [];
        foreach ($rows as $r) {
            $pid = $r['parent_id'] !== null ? (int) $r['parent_id'] : null;
            if ($pid === $parent) {
                $r['children'] = $this->buildTree($rows, (int) $r['id']);
                $out[] = $r;
            }
        }
        return $out;
    }
}
