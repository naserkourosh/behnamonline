<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\BaseRepository;

final class SmsMessageRepository extends BaseRepository
{
    public function log(string $mobile, string $body, string $kind, bool $ok, string $driver): void
    {
        $this->execute(
            'INSERT INTO sms_messages (mobile, body, kind, status, driver, created_at) VALUES (?,?,?,?,?,?)',
            [$mobile, $body, $kind, $ok ? 'sent' : 'failed', $driver, date('Y-m-d H:i:s')]
        );
    }

    /**
     * @param array{kind?:string,search?:string} $filters
     * @return list<array<string,mixed>>
     */
    public function recent(array $filters, int $limit, int $offset): array
    {
        [$where, $params] = $this->buildWhere($filters);
        $limit  = max(1, min(100, $limit));
        $offset = max(0, $offset);
        return $this->selectAll(
            "SELECT * FROM sms_messages WHERE {$where} ORDER BY id DESC LIMIT {$limit} OFFSET {$offset}",
            $params
        );
    }

    /** @param array{kind?:string,search?:string} $filters */
    public function count(array $filters): int
    {
        [$where, $params] = $this->buildWhere($filters);
        return (int) $this->scalar("SELECT COUNT(*) FROM sms_messages WHERE {$where}", $params);
    }

    /**
     * @param array{kind?:string,search?:string} $filters
     * @return array{0:string,1:list<mixed>}
     */
    private function buildWhere(array $filters): array
    {
        $clauses = ['1=1'];
        $params  = [];
        if (!empty($filters['kind'])) {
            $clauses[] = 'kind = ?';
            $params[]  = (string) $filters['kind'];
        }
        if (!empty($filters['search'])) {
            $clauses[] = '(mobile LIKE ? OR body LIKE ?)';
            $params[]  = '%' . $filters['search'] . '%';
            $params[]  = '%' . $filters['search'] . '%';
        }
        return [implode(' AND ', $clauses), $params];
    }
}
