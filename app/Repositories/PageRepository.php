<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\BaseRepository;

final class PageRepository extends BaseRepository
{
    /** @return list<array<string,mixed>> */
    public function all(): array
    {
        return $this->selectAll('SELECT * FROM pages ORDER BY sort, id');
    }

    /** @return array<string,mixed>|null */
    public function find(int $id): ?array
    {
        return $this->selectOne('SELECT * FROM pages WHERE id = ? LIMIT 1', [$id]);
    }

    /** Active page for the storefront. @return array<string,mixed>|null */
    public function findActiveBySlug(string $slug): ?array
    {
        return $this->selectOne('SELECT * FROM pages WHERE slug = ? AND is_active = 1 LIMIT 1', [$slug]);
    }

    public function slugExists(string $slug, int $exceptId = 0): bool
    {
        return (int) $this->scalar('SELECT COUNT(*) FROM pages WHERE slug = ? AND id != ?', [$slug, $exceptId]) > 0;
    }

    /** Footer quick links: active pages flagged for the footer. @return list<array<string,mixed>> */
    public function footerPages(): array
    {
        return $this->selectAll(
            'SELECT title, slug FROM pages WHERE is_active = 1 AND show_in_footer = 1 ORDER BY sort, id LIMIT 6'
        );
    }

    /** For the XML sitemap. @return list<array<string,mixed>> */
    public function sitemapList(): array
    {
        return $this->selectAll('SELECT slug, created_at, updated_at FROM pages WHERE is_active = 1');
    }

    /** @param array<string,mixed> $d */
    public function insert(array $d): int
    {
        $this->execute(
            'INSERT INTO pages (title, slug, body, seo_title, seo_description, show_in_footer, sort, is_active, created_at)
             VALUES (?,?,?,?,?,?,?,?,?)',
            [
                $d['title'], $d['slug'], $d['body'], $d['seo_title'], $d['seo_description'],
                $d['show_in_footer'], $d['sort'], $d['is_active'], date('Y-m-d H:i:s'),
            ]
        );
        return $this->lastInsertId();
    }

    /** @param array<string,mixed> $d */
    public function update(int $id, array $d): void
    {
        $this->execute(
            'UPDATE pages SET title=?, slug=?, body=?, seo_title=?, seo_description=?,
                    show_in_footer=?, sort=?, is_active=?, updated_at=? WHERE id=?',
            [
                $d['title'], $d['slug'], $d['body'], $d['seo_title'], $d['seo_description'],
                $d['show_in_footer'], $d['sort'], $d['is_active'], date('Y-m-d H:i:s'), $id,
            ]
        );
    }

    public function delete(int $id): void
    {
        $this->execute('DELETE FROM pages WHERE id = ?', [$id]);
    }
}
