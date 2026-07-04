<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\BaseRepository;

final class TagRepository extends BaseRepository
{
    /** @return list<array<string,mixed>> */
    public function allWithCounts(): array
    {
        return $this->selectAll(
            "SELECT t.*, (SELECT COUNT(*) FROM product_tags pt WHERE pt.tag_id = t.id) AS product_count
               FROM tags t
              ORDER BY (t.tag_group IS NULL OR t.tag_group = ''), t.tag_group, t.name"
        );
    }

    /** @return list<array<string,mixed>> */
    public function all(): array
    {
        return $this->selectAll(
            "SELECT id, name, slug, tag_group FROM tags
              ORDER BY (tag_group IS NULL OR tag_group = ''), tag_group, name"
        );
    }

    /** Distinct existing group names (for the datalist autocomplete). @return list<string> */
    public function groups(): array
    {
        $rows = $this->selectAll("SELECT DISTINCT tag_group FROM tags WHERE tag_group IS NOT NULL AND tag_group <> '' ORDER BY tag_group");
        return array_map(static fn ($r) => (string) $r['tag_group'], $rows);
    }

    /** @return array<string,mixed>|null */
    public function find(int $id): ?array
    {
        return $this->selectOne('SELECT * FROM tags WHERE id = ? LIMIT 1', [$id]);
    }

    public function insert(string $name, string $slug, ?string $group = null): int
    {
        $this->execute(
            'INSERT INTO tags (name, tag_group, slug, created_at) VALUES (?,?,?,?)',
            [$name, $group, $slug, date('Y-m-d H:i:s')]
        );
        return $this->lastInsertId();
    }

    public function update(int $id, string $name, string $slug, ?string $group = null): void
    {
        $this->execute('UPDATE tags SET name = ?, tag_group = ?, slug = ? WHERE id = ?', [$name, $group, $slug, $id]);
    }

    public function delete(int $id): void
    {
        $this->execute('DELETE FROM tags WHERE id = ?', [$id]);
    }

    public function slugExists(string $slug, int $exceptId = 0): bool
    {
        return (int) $this->scalar('SELECT COUNT(*) FROM tags WHERE slug = ? AND id <> ?', [$slug, $exceptId]) > 0;
    }
}
