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
}
