<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\BaseRepository;

/**
 * Shipping zones: city-specific rules plus nationwide default methods
 * (stored with city = '*'). Consumed by ShippingService.
 */
final class ShippingZoneRepository extends BaseRepository
{
    /** @return list<array<string,mixed>> */
    public function all(): array
    {
        return $this->selectAll("SELECT * FROM shipping_zones ORDER BY (city = '*') DESC, city, sort, id");
    }

    /** @return array<string,mixed>|null */
    public function find(int $id): ?array
    {
        return $this->selectOne('SELECT * FROM shipping_zones WHERE id = ? LIMIT 1', [$id]);
    }

    /**
     * Active methods for a specific city (excludes the '*' defaults).
     * @return list<array<string,mixed>>
     */
    public function activeForCity(string $city): array
    {
        return $this->selectAll(
            'SELECT * FROM shipping_zones WHERE city = ? AND is_active = 1 ORDER BY sort, id',
            [$city]
        );
    }

    /**
     * Active nationwide default methods (city = '*').
     * @return list<array<string,mixed>>
     */
    public function activeDefaults(): array
    {
        return $this->selectAll("SELECT * FROM shipping_zones WHERE city = '*' AND is_active = 1 ORDER BY sort, id");
    }

    /** @param array<string,mixed> $d */
    public function insert(array $d): int
    {
        $this->execute(
            'INSERT INTO shipping_zones (city, method_key, method_label, note, cost, free_over, sort, is_active, created_at)
             VALUES (?,?,?,?,?,?,?,?,?)',
            [$d['city'], $d['method_key'], $d['method_label'], $d['note'], $d['cost'], $d['free_over'], $d['sort'], $d['is_active'], date('Y-m-d H:i:s')]
        );
        return $this->lastInsertId();
    }

    /** @param array<string,mixed> $d */
    public function update(int $id, array $d): void
    {
        $this->execute(
            'UPDATE shipping_zones SET city=?, method_key=?, method_label=?, note=?, cost=?, free_over=?, sort=?, is_active=? WHERE id=?',
            [$d['city'], $d['method_key'], $d['method_label'], $d['note'], $d['cost'], $d['free_over'], $d['sort'], $d['is_active'], $id]
        );
    }

    public function delete(int $id): void
    {
        $this->execute('DELETE FROM shipping_zones WHERE id = ?', [$id]);
    }
}
