<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\BaseRepository;

final class FaqRepository extends BaseRepository
{
    /**
     * Active FAQs grouped by category for the storefront page.
     * @return array<string,list<array<string,mixed>>>
     */
    public function activeGrouped(): array
    {
        $rows = $this->selectAll('SELECT * FROM faqs WHERE is_active = 1 ORDER BY category, sort, id');
        $grouped = [];
        foreach ($rows as $r) {
            $grouped[(string) $r['category']][] = $r;
        }
        return $grouped;
    }

    /* ── Admin ── */

    /** @return list<array<string,mixed>> */
    public function all(): array
    {
        return $this->selectAll('SELECT * FROM faqs ORDER BY category, sort, id');
    }

    /** @return array<string,mixed>|null */
    public function find(int $id): ?array
    {
        return $this->selectOne('SELECT * FROM faqs WHERE id = ? LIMIT 1', [$id]);
    }

    /** @param array<string,mixed> $d */
    public function insert(array $d): int
    {
        $this->execute(
            'INSERT INTO faqs (category, question, answer, sort, is_active, created_at) VALUES (?,?,?,?,?,?)',
            [$d['category'], $d['question'], $d['answer'], $d['sort'], $d['is_active'], date('Y-m-d H:i:s')]
        );
        return $this->lastInsertId();
    }

    /** @param array<string,mixed> $d */
    public function update(int $id, array $d): void
    {
        $this->execute(
            'UPDATE faqs SET category=?, question=?, answer=?, sort=?, is_active=? WHERE id=?',
            [$d['category'], $d['question'], $d['answer'], $d['sort'], $d['is_active'], $id]
        );
    }

    public function delete(int $id): void
    {
        $this->execute('DELETE FROM faqs WHERE id = ?', [$id]);
    }
}
