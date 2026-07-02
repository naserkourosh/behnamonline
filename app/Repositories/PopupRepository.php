<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\BaseRepository;

final class PopupRepository extends BaseRepository
{
    /**
     * The first active popup (within its date window) whose target matches
     * the given request path. Target is 'all', 'home', or a path prefix.
     * @return array<string,mixed>|null
     */
    public function activeForPath(string $path): ?array
    {
        $rows = $this->selectAll(
            "SELECT * FROM popups
              WHERE is_active = 1
                AND (starts_at IS NULL OR starts_at <= NOW())
                AND (ends_at   IS NULL OR ends_at   >= NOW())
              ORDER BY sort, id"
        );
        foreach ($rows as $p) {
            if ($this->matches((string) $p['target'], $path)) {
                return $p;
            }
        }
        return null;
    }

    private function matches(string $target, string $path): bool
    {
        $target = trim($target);
        if ($target === '' || $target === 'all') {
            return true;
        }
        if ($target === 'home') {
            return $path === '/' || $path === '';
        }
        return str_starts_with($path, $target);
    }

    /* ── Admin ── */

    /** @return list<array<string,mixed>> */
    public function all(): array
    {
        return $this->selectAll('SELECT * FROM popups ORDER BY sort, id');
    }

    /** @return array<string,mixed>|null */
    public function find(int $id): ?array
    {
        return $this->selectOne('SELECT * FROM popups WHERE id = ? LIMIT 1', [$id]);
    }

    /** @param array<string,mixed> $d */
    public function insert(array $d): int
    {
        $this->execute(
            'INSERT INTO popups
                (title, body, image, cta_label, cta_url, position, delay_seconds, frequency, target,
                 starts_at, ends_at, is_active, sort, created_at)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
            [
                $d['title'], $d['body'], $d['image'], $d['cta_label'], $d['cta_url'], $d['position'],
                $d['delay_seconds'], $d['frequency'], $d['target'], $d['starts_at'], $d['ends_at'],
                $d['is_active'], $d['sort'], date('Y-m-d H:i:s'),
            ]
        );
        return $this->lastInsertId();
    }

    /** @param array<string,mixed> $d */
    public function update(int $id, array $d): void
    {
        $this->execute(
            'UPDATE popups SET title=?, body=?, image=?, cta_label=?, cta_url=?, position=?, delay_seconds=?,
                frequency=?, target=?, starts_at=?, ends_at=?, is_active=?, sort=? WHERE id=?',
            [
                $d['title'], $d['body'], $d['image'], $d['cta_label'], $d['cta_url'], $d['position'],
                $d['delay_seconds'], $d['frequency'], $d['target'], $d['starts_at'], $d['ends_at'],
                $d['is_active'], $d['sort'], $id,
            ]
        );
    }

    public function delete(int $id): void
    {
        $this->execute('DELETE FROM popups WHERE id = ?', [$id]);
    }
}
