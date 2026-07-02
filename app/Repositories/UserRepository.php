<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\BaseRepository;

final class UserRepository extends BaseRepository
{
    /** @return array<string,mixed>|null */
    public function findByMobile(string $mobile): ?array
    {
        return $this->selectOne('SELECT * FROM users WHERE mobile = ? LIMIT 1', [$mobile]);
    }

    /** @return array<string,mixed>|null */
    public function find(int $id): ?array
    {
        return $this->selectOne('SELECT * FROM users WHERE id = ? LIMIT 1', [$id]);
    }

    public function create(string $mobile, ?string $firstName = null, ?string $lastName = null): int
    {
        $now = date('Y-m-d H:i:s');
        $this->execute(
            'INSERT INTO users (mobile, first_name, last_name, created_at, updated_at) VALUES (?,?,?,?,?)',
            [$mobile, $firstName, $lastName, $now, $now]
        );
        return $this->lastInsertId();
    }

    public function updateProfile(int $id, string $firstName, string $lastName): void
    {
        $this->execute(
            'UPDATE users SET first_name = ?, last_name = ?, updated_at = ? WHERE id = ?',
            [$firstName, $lastName, date('Y-m-d H:i:s'), $id]
        );
    }

    public function touchLogin(int $id): void
    {
        $this->execute('UPDATE users SET last_login_at = ? WHERE id = ?', [date('Y-m-d H:i:s'), $id]);
    }

    /** Add (or subtract, when negative) reward points, clamped at zero. */
    public function addPoints(int $id, int $points): void
    {
        $this->execute(
            'UPDATE users SET reward_points = GREATEST(0, reward_points + ?), updated_at = ? WHERE id = ?',
            [$points, date('Y-m-d H:i:s'), $id]
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
            $where  = '(mobile LIKE ? OR first_name LIKE ? OR last_name LIKE ?)';
            $params = ['%' . $search . '%', '%' . $search . '%', '%' . $search . '%'];
        }
        return $this->selectAll(
            "SELECT u.*,
                    (SELECT COUNT(*) FROM orders o WHERE o.user_id = u.id) AS order_count,
                    (SELECT COALESCE(SUM(o.total),0) FROM orders o WHERE o.user_id = u.id AND o.payment_status='paid') AS total_spent
               FROM users u WHERE {$where} ORDER BY u.id DESC LIMIT {$limit} OFFSET {$offset}",
            $params
        );
    }

    public function adminCount(string $search): int
    {
        if ($search === '') {
            return (int) $this->scalar('SELECT COUNT(*) FROM users');
        }
        return (int) $this->scalar(
            'SELECT COUNT(*) FROM users WHERE mobile LIKE ? OR first_name LIKE ? OR last_name LIKE ?',
            ['%' . $search . '%', '%' . $search . '%', '%' . $search . '%']
        );
    }
}
