<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\BaseRepository;

final class AdminRememberTokenRepository extends BaseRepository
{
    public function insert(int $adminUserId, string $selector, string $tokenHash, string $expiresAt): void
    {
        $this->execute(
            'INSERT INTO admin_remember_tokens (admin_user_id, selector, token_hash, expires_at, created_at)
             VALUES (?,?,?,?,?)',
            [$adminUserId, $selector, $tokenHash, $expiresAt, date('Y-m-d H:i:s')]
        );
    }

    /** @return array<string,mixed>|null */
    public function findBySelector(string $selector): ?array
    {
        return $this->selectOne(
            'SELECT * FROM admin_remember_tokens WHERE selector = ? LIMIT 1',
            [$selector]
        );
    }

    public function deleteBySelector(string $selector): void
    {
        $this->execute('DELETE FROM admin_remember_tokens WHERE selector = ?', [$selector]);
    }

    /** Wipe every token for one admin (logout-everywhere / theft response). */
    public function deleteForUser(int $adminUserId): void
    {
        $this->execute('DELETE FROM admin_remember_tokens WHERE admin_user_id = ?', [$adminUserId]);
    }

    public function deleteExpired(): void
    {
        $this->execute('DELETE FROM admin_remember_tokens WHERE expires_at < ?', [date('Y-m-d H:i:s')]);
    }
}
