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
        $dir = BASE_PATH . '/storage/logs';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $line = sprintf("[%s] SMS → %s : %s\n", date('Y-m-d H:i:s'), $mobile, $message);
        @file_put_contents($dir . '/sms.log', $line, FILE_APPEND | LOCK_EX);
        return true;
    }
}
