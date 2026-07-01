<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\BaseRepository;

final class SettingsRepository extends BaseRepository
{
    /** @return list<array{setting_key:string,setting_value:string,setting_type:string}> */
    public function all(): array
    {
        /** @var list<array{setting_key:string,setting_value:string,setting_type:string}> $rows */
        $rows = $this->selectAll('SELECT setting_key, setting_value, setting_type FROM settings');
        return $rows;
    }

    public function set(string $key, string $value, string $type): void
    {
        $this->execute(
            'INSERT INTO settings (setting_key, setting_value, setting_type, updated_at) VALUES (?,?,?,?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), setting_type = VALUES(setting_type), updated_at = VALUES(updated_at)',
            [$key, $value, $type, date('Y-m-d H:i:s')]
        );
    }
}
