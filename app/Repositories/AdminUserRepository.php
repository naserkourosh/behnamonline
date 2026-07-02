<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\BaseRepository;

final class AdminUserRepository extends BaseRepository
{
    /** @return array<string,mixed>|null */
    public function findByUsername(string $username): ?array
    {
        return $this->selectOne('SELECT * FROM admin_users WHERE username = ? AND is_active = 1 LIMIT 1', [$username]);
    }

    /** @return array<string,mixed>|null */
    public function find(int $id): ?array
    {
        return $this->selectOne('SELECT * FROM admin_users WHERE id = ? LIMIT 1', [$id]);
    }

    public function touchLogin(int $id): void
    {
        $this->execute('UPDATE admin_users SET last_login_at = ? WHERE id = ?', [date('Y-m-d H:i:s'), $id]);
    }

    /* ───────────────────────── Admin (staff management) ───────────────────────── */

    /** @return list<array<string,mixed>> */
    public function all(): array
    {
        return $this->selectAll('SELECT * FROM admin_users ORDER BY id');
    }

    public function usernameExists(string $username, int $exceptId = 0): bool
    {
        return (int) $this->scalar(
            'SELECT COUNT(*) FROM admin_users WHERE username = ? AND id <> ?',
            [$username, $exceptId]
        ) > 0;
    }

    /** Number of active super-admins (guards against locking everyone out). */
    public function activeSuperCount(): int
    {
        return (int) $this->scalar("SELECT COUNT(*) FROM admin_users WHERE role = 'super' AND is_active = 1");
    }

    /** @param array<string,mixed> $d */
    public function insert(array $d): int
    {
        $this->execute(
            'INSERT INTO admin_users (username, password_hash, name, email, role, capabilities, is_active, created_at)
             VALUES (?,?,?,?,?,?,?,?)',
            [$d['username'], $d['password_hash'], $d['name'], $d['email'], $d['role'], $d['capabilities'], $d['is_active'], date('Y-m-d H:i:s')]
        );
        return $this->lastInsertId();
    }

    /**
     * Update profile fields (not the password).
     * @param array<string,mixed> $d
     */
    public function update(int $id, array $d): void
    {
        $this->execute(
            'UPDATE admin_users SET username=?, name=?, email=?, role=?, capabilities=?, is_active=? WHERE id=?',
            [$d['username'], $d['name'], $d['email'], $d['role'], $d['capabilities'], $d['is_active'], $id]
        );
    }

    public function updatePassword(int $id, string $hash): void
    {
        $this->execute('UPDATE admin_users SET password_hash = ? WHERE id = ?', [$hash, $id]);
    }

    public function delete(int $id): void
    {
        $this->execute('DELETE FROM admin_users WHERE id = ?', [$id]);
    }
}
