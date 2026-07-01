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
            'SELECT t.*, (SELECT COUNT(*) FROM product_tags pt WHERE pt.tag_id = t.id) AS product_count
               FROM tags t ORDER BY t.name'
        );
    }

    /** @return list<array<string,mixed>> */
    public function all(): array
    {
        return $this->selectAll('SELECT id, name, slug FROM tags ORDER BY name');
    }

    /** @return array<string,mixed>|null */
    public function find(int $id): ?array
    {
        return $this->selectOne('SELECT * FROM tags WHERE id = ? LIMIT 1', [$id]);
    }

    public function insert(string $name, string $slug): int
    {
        $this->execute('INSERT INTO tags (name, slug, created_at) VALUES (?,?,?)', [$name, $slug, date('Y-m-d H:i:s')]);
        return $this->lastInsertId();
    }

    public function update(int $id, string $name, string $slug): void
    {
        $this->execute('UPDATE tags SET name = ?, slug = ? WHERE id = ?', [$name, $slug, $id]);
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
