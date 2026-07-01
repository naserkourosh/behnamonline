<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\BaseRepository;

final class AddressRepository extends BaseRepository
{
    /** @return list<array<string,mixed>> */
    public function forUser(int $userId): array
    {
        return $this->selectAll(
            'SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC',
            [$userId]
        );
    }

    /** @return array<string,mixed>|null */
    public function find(int $id, int $userId): ?array
    {
        return $this->selectOne('SELECT * FROM addresses WHERE id = ? AND user_id = ? LIMIT 1', [$id, $userId]);
    }

    /** @return array<string,mixed>|null */
    public function defaultFor(int $userId): ?array
    {
        return $this->selectOne(
            'SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC LIMIT 1',
            [$userId]
        );
    }

    /** @param array<string,mixed> $data */
    public function create(int $userId, array $data): int
    {
        if (!empty($data['is_default'])) {
            $this->execute('UPDATE addresses SET is_default = 0 WHERE user_id = ?', [$userId]);
        }
        $this->execute(
            'INSERT INTO addresses (user_id, receiver_name, mobile, province, city, address, postal_code, is_default, created_at)
             VALUES (?,?,?,?,?,?,?,?,?)',
            [
                $userId, $data['receiver_name'], $data['mobile'], $data['province'], $data['city'],
                $data['address'], $data['postal_code'] ?? null, !empty($data['is_default']) ? 1 : 0,
                date('Y-m-d H:i:s'),
            ]
        );
        return $this->lastInsertId();
    }

    /** @param array<string,mixed> $data */
    public function update(int $id, int $userId, array $data): void
    {
        if (!empty($data['is_default'])) {
            $this->execute('UPDATE addresses SET is_default = 0 WHERE user_id = ?', [$userId]);
        }
        $this->execute(
            'UPDATE addresses SET receiver_name = ?, mobile = ?, province = ?, city = ?, address = ?, postal_code = ?, is_default = ?
              WHERE id = ? AND user_id = ?',
            [
                $data['receiver_name'], $data['mobile'], $data['province'], $data['city'], $data['address'],
                $data['postal_code'] ?? null, !empty($data['is_default']) ? 1 : 0, $id, $userId,
            ]
        );
    }

    public function delete(int $id, int $userId): void
    {
        $this->execute('DELETE FROM addresses WHERE id = ? AND user_id = ?', [$id, $userId]);
    }

    public function count(int $userId): int
    {
        return (int) $this->scalar('SELECT COUNT(*) FROM addresses WHERE user_id = ?', [$userId]);
    }
}
