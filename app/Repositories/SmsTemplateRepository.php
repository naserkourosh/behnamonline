<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\BaseRepository;

final class SmsTemplateRepository extends BaseRepository
{
    /** @return list<array<string,mixed>> */
    public function all(): array
    {
        return $this->selectAll('SELECT * FROM sms_templates ORDER BY id');
    }

    /** @return array<string,mixed>|null */
    public function find(string $key): ?array
    {
        return $this->selectOne('SELECT * FROM sms_templates WHERE tkey = ? LIMIT 1', [$key]);
    }

    public function update(string $key, string $body, bool $active): void
    {
        $this->execute(
            'UPDATE sms_templates SET body = ?, is_active = ?, updated_at = ? WHERE tkey = ?',
            [$body, $active ? 1 : 0, date('Y-m-d H:i:s'), $key]
        );
    }

    /**
     * Render a template with {placeholder} substitution. Falls back to the
     * supplied default when the template is missing or disabled.
     * @param array<string,string> $vars
     */
    public function render(string $key, array $vars, string $fallback): string
    {
        $row = $this->find($key);
        $body = ($row !== null && (int) $row['is_active'] === 1) ? (string) $row['body'] : $fallback;
        foreach ($vars as $k => $v) {
            $body = str_replace('{' . $k . '}', $v, $body);
        }
        return $body;
    }
}
