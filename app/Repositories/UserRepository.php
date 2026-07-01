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
}
