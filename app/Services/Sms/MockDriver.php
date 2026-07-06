<?php

declare(strict_types=1);

namespace App\Services\Sms;

/**
 * Development SMS driver: instead of sending, it appends the message to
 * storage/logs/sms.log so OTP codes can be read during local testing.
 */
final class MockDriver implements SmsDriver
{
    public function send(string $mobile, string $message): bool
    {
        $this->log(sprintf('SMS → %s : %s', $mobile, $message));
        return true;
    }

    /** @param list<string> $mobiles */
    public function sendMany(array $mobiles, string $message): bool
    {
        $this->log(sprintf('SMS ×%d → %s : %s', count($mobiles), implode(',', $mobiles), $message));
        return $mobiles !== [];
    }

    public function sendPattern(string $mobile, string $args, string $bodyId): bool
    {
        $this->log(sprintf('SMS PATTERN(bodyId=%s) → %s : %s', $bodyId, $mobile, $args));
        return true;
    }

    public function credit(): ?float
    {
        return null;
    }

    private function log(string $line): void
    {
        $dir = BASE_PATH . '/storage/logs';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        @file_put_contents($dir . '/sms.log', sprintf("[%s] %s\n", date('Y-m-d H:i:s'), $line), FILE_APPEND | LOCK_EX);
    }
}
