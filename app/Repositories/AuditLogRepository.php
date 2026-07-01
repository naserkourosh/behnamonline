<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\BaseRepository;

final class AuditLogRepository extends BaseRepository
{
    public function log(?int $adminId, string $action, string $entity, ?int $entityId, string $meta, string $ip): void
    {
        $this->execute(
            'INSERT INTO audit_logs (admin_id, action, entity, entity_id, meta, ip, created_at) VALUES (?,?,?,?,?,?,?)',
            [$adminId, $action, $entity, $entityId, mb_substr($meta, 0, 500), $ip, date('Y-m-d H:i:s')]
        );
    }

    /** @return list<array<string,mixed>> */
    public function recent(int $limit = 30): array
    {
        $limit = max(1, min(100, $limit));
        return $this->selectAll(
            "SELECT a.*, u.name AS admin_name FROM audit_logs a
          LEFT JOIN admin_users u ON u.id = a.admin_id
             ORDER BY a.id DESC LIMIT {$limit}"
        );
    }
}
