<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\BaseRepository;

/**
 * Group-SMS campaigns: persistence plus audience → recipient resolution.
 * Audiences: all (active customers), buyers (has a paid order),
 * noorder (registered, never bought), custom (numbers pasted by the admin).
 */
final class SmsCampaignRepository extends BaseRepository
{
    public const AUDIENCES = [
        'all'     => 'همهٔ مشتریان',
        'buyers'  => 'خریداران (سفارش پرداخت‌شده)',
        'noorder' => 'بدون خرید',
        'custom'  => 'شماره‌های دلخواه',
    ];

    public function create(string $title, string $body, string $audience, int $total, ?int $createdBy): int
    {
        $this->execute(
            'INSERT INTO sms_campaigns (title, body, audience, total, status, created_by, created_at) VALUES (?,?,?,?,?,?,?)',
            [$title, $body, $audience, $total, 'sending', $createdBy, date('Y-m-d H:i:s')]
        );
        return $this->lastInsertId();
    }

    public function finish(int $id, int $sent, int $failed): void
    {
        $this->execute(
            'UPDATE sms_campaigns SET sent = ?, failed = ?, status = ?, finished_at = ? WHERE id = ?',
            [$sent, $failed, $failed === 0 ? 'done' : ($sent === 0 ? 'failed' : 'partial'), date('Y-m-d H:i:s'), $id]
        );
    }

    /** @return list<array<string,mixed>> */
    public function recent(int $limit = 10): array
    {
        $limit = max(1, min(50, $limit));
        return $this->selectAll("SELECT * FROM sms_campaigns ORDER BY id DESC LIMIT {$limit}");
    }

    /** @return list<string> */
    public function mobilesFor(string $audience): array
    {
        $rows = $this->selectAll($this->audienceSql($audience, 'u.mobile'));
        return array_values(array_unique(array_map(static fn (array $r): string => (string) $r['mobile'], $rows)));
    }

    public function countFor(string $audience): int
    {
        return (int) $this->scalar($this->audienceSql($audience, 'COUNT(DISTINCT u.mobile)'));
    }

    private function audienceSql(string $audience, string $select): string
    {
        return match ($audience) {
            'buyers'  => "SELECT {$select} FROM users u JOIN orders o ON o.user_id = u.id AND o.payment_status = 'paid' WHERE u.is_active = 1",
            'noorder' => "SELECT {$select} FROM users u LEFT JOIN orders o ON o.user_id = u.id AND o.payment_status = 'paid' WHERE u.is_active = 1 AND o.id IS NULL",
            default   => "SELECT {$select} FROM users u WHERE u.is_active = 1",
        };
    }
}
