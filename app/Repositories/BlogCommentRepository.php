<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\BaseRepository;

final class BlogCommentRepository extends BaseRepository
{
    /** @return list<array<string,mixed>> Approved comments for a post. */
    public function approvedForPost(int $postId): array
    {
        return $this->selectAll(
            "SELECT author_name, body, created_at FROM blog_comments
              WHERE post_id = ? AND status = 'approved' ORDER BY created_at DESC",
            [$postId]
        );
    }

    /** @param array<string,mixed> $d */
    public function create(array $d): int
    {
        $this->execute(
            'INSERT INTO blog_comments (post_id, user_id, author_name, body, status, created_at) VALUES (?,?,?,?,?,?)',
            [$d['post_id'], $d['user_id'], $d['author_name'], $d['body'], $d['status'] ?? 'pending', date('Y-m-d H:i:s')]
        );
        return $this->lastInsertId();
    }

    /* ── Admin moderation ── */

    /**
     * @return list<array<string,mixed>>
     */
    public function adminList(string $status, int $limit, int $offset): array
    {
        $limit  = max(1, min(100, $limit));
        $offset = max(0, $offset);
        $where  = '1=1';
        $params = [];
        if (in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $where = 'bc.status = ?';
            $params = [$status];
        }
        return $this->selectAll(
            "SELECT bc.*, p.title AS post_title, p.slug AS post_slug
               FROM blog_comments bc
               JOIN blog_posts p ON p.id = bc.post_id
              WHERE {$where}
              ORDER BY bc.id DESC
              LIMIT {$limit} OFFSET {$offset}",
            $params
        );
    }

    public function adminCount(string $status): int
    {
        if (!in_array($status, ['pending', 'approved', 'rejected'], true)) {
            return (int) $this->scalar('SELECT COUNT(*) FROM blog_comments');
        }
        return (int) $this->scalar('SELECT COUNT(*) FROM blog_comments WHERE status = ?', [$status]);
    }

    public function pendingCount(): int
    {
        return (int) $this->scalar("SELECT COUNT(*) FROM blog_comments WHERE status = 'pending'");
    }

    public function setStatus(int $id, string $status): void
    {
        if (!in_array($status, ['pending', 'approved', 'rejected'], true)) {
            return;
        }
        $this->execute('UPDATE blog_comments SET status = ? WHERE id = ?', [$status, $id]);
    }

    public function delete(int $id): void
    {
        $this->execute('DELETE FROM blog_comments WHERE id = ?', [$id]);
    }
}
