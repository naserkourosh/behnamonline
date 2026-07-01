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
}
