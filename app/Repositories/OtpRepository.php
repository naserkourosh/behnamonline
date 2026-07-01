<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\BaseRepository;

final class OtpRepository extends BaseRepository
{
    public function create(string $mobile, string $codeHash, string $purpose, int $ttlSeconds): int
    {
        // Invalidate previous unconsumed codes for this mobile+purpose.
        $this->execute(
            'UPDATE otp_codes SET consumed = 1 WHERE mobile = ? AND purpose = ? AND consumed = 0',
            [$mobile, $purpose]
        );

        $this->execute(
            'INSERT INTO otp_codes (mobile, code_hash, purpose, expires_at, created_at) VALUES (?,?,?,?,?)',
            [$mobile, $codeHash, $purpose, date('Y-m-d H:i:s', time() + $ttlSeconds), date('Y-m-d H:i:s')]
        );
        return $this->lastInsertId();
    }

    /** @return array<string,mixed>|null Latest valid (unconsumed, unexpired) code. */
    public function latestValid(string $mobile, string $purpose): ?array
    {
        return $this->selectOne(
            'SELECT * FROM otp_codes
              WHERE mobile = ? AND purpose = ? AND consumed = 0 AND expires_at > ?
              ORDER BY id DESC LIMIT 1',
            [$mobile, $purpose, date('Y-m-d H:i:s')]
        );
    }

    public function incrementAttempts(int $id): void
    {
        $this->execute('UPDATE otp_codes SET attempts = attempts + 1 WHERE id = ?', [$id]);
    }

    public function consume(int $id): void
    {
        $this->execute('UPDATE otp_codes SET consumed = 1 WHERE id = ?', [$id]);
    }

    /** Count codes created for a mobile within the last N seconds (send throttle). */
    public function recentCount(string $mobile, int $withinSeconds): int
    {
        return (int) $this->scalar(
            'SELECT COUNT(*) FROM otp_codes WHERE mobile = ? AND created_at > ?',
            [$mobile, date('Y-m-d H:i:s', time() - $withinSeconds)]
        );
    }
}
