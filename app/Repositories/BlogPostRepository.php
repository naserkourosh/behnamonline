<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\BaseRepository;

final class BlogPostRepository extends BaseRepository
{
    private const LIST_COLUMNS = 'p.id, p.title, p.slug, p.excerpt, p.cover_image, p.author_name,
        p.is_featured, p.view_count, p.published_at, c.name AS category_name, c.slug AS category_slug';

    /** @return list<array<string,mixed>> */
    public function published(?int $categoryId, int $limit, int $offset): array
    {
        $limit  = max(1, min(48, $limit));
        $offset = max(0, $offset);
        $where  = "p.status = 'published' AND p.published_at <= NOW()";
        $params = [];
        if ($categoryId !== null) {
            $where .= ' AND p.category_id = ?';
            $params[] = $categoryId;
        }
        return $this->selectAll(
            "SELECT " . self::LIST_COLUMNS . "
               FROM blog_posts p
          LEFT JOIN blog_categories c ON c.id = p.category_id
              WHERE {$where}
              ORDER BY p.is_featured DESC, p.published_at DESC
              LIMIT {$limit} OFFSET {$offset}",
            $params
        );
    }

    public function publishedCount(?int $categoryId): int
    {
        $where  = "status = 'published' AND published_at <= NOW()";
        $params = [];
        if ($categoryId !== null) {
            $where .= ' AND category_id = ?';
            $params[] = $categoryId;
        }
        return (int) $this->scalar("SELECT COUNT(*) FROM blog_posts WHERE {$where}", $params);
    }

    /** @return list<array<string,mixed>> */
    public function featured(int $limit = 3): array
    {
        return $this->selectAll(
            "SELECT " . self::LIST_COLUMNS . "
               FROM blog_posts p
          LEFT JOIN blog_categories c ON c.id = p.category_id
              WHERE p.status = 'published' AND p.published_at <= NOW()
              ORDER BY p.is_featured DESC, p.published_at DESC
              LIMIT " . max(1, min(12, $limit))
        );
    }

    /** @return array<string,mixed>|null */
    public function findPublishedBySlug(string $slug): ?array
    {
        return $this->selectOne(
            "SELECT p.*, c.name AS category_name, c.slug AS category_slug
               FROM blog_posts p
          LEFT JOIN blog_categories c ON c.id = p.category_id
              WHERE p.slug = ? AND p.status = 'published' AND p.published_at <= NOW()
              LIMIT 1",
            [$slug]
        );
    }

    public function incrementViews(int $id): void
    {
        $this->execute('UPDATE blog_posts SET view_count = view_count + 1 WHERE id = ?', [$id]);
    }

    /** @return list<array<string,mixed>> slug + lastmod for published posts (sitemap). */
    public function sitemapList(): array
    {
        return $this->selectAll(
            "SELECT slug, updated_at, published_at FROM blog_posts
              WHERE status = 'published' AND published_at <= NOW()
              ORDER BY published_at DESC"
        );
    }

    /* ───────────────────────── Admin ───────────────────────── */

    /** @return list<array<string,mixed>> */
    public function adminList(string $search, int $limit, int $offset): array
    {
        $limit  = max(1, min(100, $limit));
        $offset = max(0, $offset);
        $where  = '1=1';
        $params = [];
        if ($search !== '') {
            $where = 'p.title LIKE ?';
            $params = ['%' . $search . '%'];
        }
        return $this->selectAll(
            "SELECT p.id, p.title, p.slug, p.status, p.is_featured, p.view_count, p.published_at,
                    c.name AS category_name,
                    (SELECT COUNT(*) FROM blog_comments bc WHERE bc.post_id = p.id AND bc.status = 'pending') AS pending_comments
               FROM blog_posts p
          LEFT JOIN blog_categories c ON c.id = p.category_id
              WHERE {$where}
              ORDER BY p.id DESC
              LIMIT {$limit} OFFSET {$offset}",
            $params
        );
    }

    public function adminCount(string $search): int
    {
        if ($search === '') {
            return (int) $this->scalar('SELECT COUNT(*) FROM blog_posts');
        }
        return (int) $this->scalar('SELECT COUNT(*) FROM blog_posts WHERE title LIKE ?', ['%' . $search . '%']);
    }

    /** @return array<string,mixed>|null */
    public function find(int $id): ?array
    {
        return $this->selectOne('SELECT * FROM blog_posts WHERE id = ? LIMIT 1', [$id]);
    }

    /** @param array<string,mixed> $d */
    public function insert(array $d): int
    {
        $now = date('Y-m-d H:i:s');
        $this->execute(
            'INSERT INTO blog_posts
                (category_id, title, slug, excerpt, body, cover_image, author_name, status, is_featured,
                 seo_title, seo_description, published_at, created_at, updated_at)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
            [
                $d['category_id'], $d['title'], $d['slug'], $d['excerpt'], $d['body'], $d['cover_image'],
                $d['author_name'], $d['status'], $d['is_featured'], $d['seo_title'], $d['seo_description'],
                $d['published_at'], $now, $now,
            ]
        );
        return $this->lastInsertId();
    }

    /** @param array<string,mixed> $d */
    public function update(int $id, array $d): void
    {
        $this->execute(
            'UPDATE blog_posts SET category_id=?, title=?, slug=?, excerpt=?, body=?, cover_image=?,
                author_name=?, status=?, is_featured=?, seo_title=?, seo_description=?, published_at=?, updated_at=?
              WHERE id=?',
            [
                $d['category_id'], $d['title'], $d['slug'], $d['excerpt'], $d['body'], $d['cover_image'],
                $d['author_name'], $d['status'], $d['is_featured'], $d['seo_title'], $d['seo_description'],
                $d['published_at'], date('Y-m-d H:i:s'), $id,
            ]
        );
    }

    public function delete(int $id): void
    {
        $this->execute('DELETE FROM blog_posts WHERE id = ?', [$id]);
    }

    public function slugExists(string $slug, int $exceptId = 0): bool
    {
        return (int) $this->scalar('SELECT COUNT(*) FROM blog_posts WHERE slug = ? AND id <> ?', [$slug, $exceptId]) > 0;
    }
}
