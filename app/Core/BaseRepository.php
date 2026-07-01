<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

/**
 * Base for all repositories. Every query runs through PDO prepared
 * statements — there is no raw string interpolation of user input.
 */
abstract class BaseRepository
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    /**
     * @param array<string,mixed> $params
     * @return array<string,mixed>|null
     */
    protected function selectOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    /**
     * @param array<string,mixed> $params
     * @return list<array<string,mixed>>
     */
    protected function selectAll(string $sql, array $params = []): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        /** @var list<array<string,mixed>> $rows */
        $rows = $stmt->fetchAll();
        return $rows;
    }

    /** @param array<string,mixed> $params */
    protected function execute(string $sql, array $params = []): int
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /** @param array<string,mixed> $params */
    protected function scalar(string $sql, array $params = []): mixed
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    protected function lastInsertId(): int
    {
        return (int) $this->db->lastInsertId();
    }

    /**
     * Build a safe `IN (...)` placeholder list and matching params.
     * @param list<int|string> $values
     * @return array{0:string,1:array<int,int|string>}
     */
    protected function inClause(array $values): array
    {
        if ($values === []) {
            return ['(NULL)', []];
        }
        $placeholders = implode(',', array_fill(0, count($values), '?'));
        return ["($placeholders)", array_values($values)];
    }
}
