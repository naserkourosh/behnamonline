<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\BaseRepository;

final class BannerRepository extends BaseRepository
{
    /** @return list<array<string,mixed>> */
    public function all(): array
    {
        return $this->selectAll('SELECT * FROM banners ORDER BY placement, sort, id DESC');
    }

    /**
     * Active banners for a placement whose schedule window (if any) is current.
     * @return list<array<string,mixed>>
     */
    public function activeByPlacement(string $placement, int $limit = 10): array
    {
        $now = date('Y-m-d H:i:s');
        return $this->selectAll(
            'SELECT * FROM banners
              WHERE placement = ? AND is_active = 1
                AND (starts_at IS NULL OR starts_at <= ?)
                AND (ends_at   IS NULL OR ends_at   >= ?)
              ORDER BY sort, id DESC
              LIMIT ' . max(1, $limit),
            [$placement, $now, $now]
        );
    }

    /** @return array<string,mixed>|null */
    public function find(int $id): ?array
    {
        return $this->selectOne('SELECT * FROM banners WHERE id = ? LIMIT 1', [$id]);
    }

    /** @param array<string,mixed> $d */
    public function insert(array $d): int
    {
        $this->execute(
            'INSERT INTO banners
                (title, subtitle, kicker, image, link_url, cta_label, placement, bg_color, sort, starts_at, ends_at, is_active, created_at)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)',
            [
                $d['title'], $d['subtitle'], $d['kicker'], $d['image'], $d['link_url'], $d['cta_label'],
                $d['placement'], $d['bg_color'], $d['sort'], $d['starts_at'], $d['ends_at'], $d['is_active'],
                date('Y-m-d H:i:s'),
            ]
        );
        return $this->lastInsertId();
    }

    /** @param array<string,mixed> $d */
    public function update(int $id, array $d): void
    {
        $this->execute(
            'UPDATE banners SET title=?, subtitle=?, kicker=?, image=?, link_url=?, cta_label=?,
                placement=?, bg_color=?, sort=?, starts_at=?, ends_at=?, is_active=? WHERE id=?',
            [
                $d['title'], $d['subtitle'], $d['kicker'], $d['image'], $d['link_url'], $d['cta_label'],
                $d['placement'], $d['bg_color'], $d['sort'], $d['starts_at'], $d['ends_at'], $d['is_active'], $id,
            ]
        );
    }

    public function delete(int $id): void
    {
        $this->execute('DELETE FROM banners WHERE id = ?', [$id]);
    }
}
