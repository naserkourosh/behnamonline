<?php

declare(strict_types=1);

namespace App\Services\Sms;

use App\Repositories\SmsMessageRepository;
use App\Repositories\SmsTemplateRepository;

/**
 * Resolves the configured SMS driver, sends messages through it, and records
 * each attempt in sms_messages so the admin panel has a delivery history.
 */
final class SmsManager
{
    /** Recipients per provider call for bulk sends (Melipayamak accepts ~100). */
    private const BULK_CHUNK = 90;

    private SmsDriver $driver;
    private string $driverName;

    public function __construct()
    {
        $this->driverName = SmsConfig::driver();
        $this->driver = match ($this->driverName) {
            'melipayamak' => new MelipayamakDriver(),
            default       => new MockDriver(),
        };
    }

    public function driverName(): string
    {
        return $this->driverName;
    }

    /**
     * @param string $kind Logical category for the history log (otp, order, manual…).
     */
    public function send(string $mobile, string $message, string $kind = 'system'): bool
    {
        $ok = $this->driver->send($mobile, $message);
        $this->log($mobile, $message, $kind, $ok);
        return $ok;
    }

    /**
     * Send one message to many recipients (promotional campaigns), chunked
     * into group provider calls and logged per recipient.
     * @param list<string> $mobiles
     * @return array{sent:int,failed:int}
     */
    public function sendBulk(array $mobiles, string $message, string $kind = 'campaign', ?int $campaignId = null): array
    {
        $sent = $failed = 0;
        foreach (array_chunk($mobiles, self::BULK_CHUNK) as $chunk) {
            $ok = $this->driver->sendMany($chunk, $message);
            foreach ($chunk as $mobile) {
                $this->log($mobile, $message, $kind, $ok, $campaignId);
                $ok ? $sent++ : $failed++;
            }
        }
        return ['sent' => $sent, 'failed' => $failed];
    }

    /**
     * Send an OTP code. When a Melipayamak pattern (bodyId) is configured the
     * code goes through the shared SERVICE line — delivered even to numbers
     * that blocked promotional SMS. Falls back to a plain send otherwise.
     */
    public function sendOtp(string $mobile, string $code, string $fullMessage): bool
    {
        $bodyId = SmsConfig::otpBodyId();
        if ($this->driverName === 'melipayamak' && $bodyId !== '') {
            $ok = $this->driver->sendPattern($mobile, $code, $bodyId);
            $this->log($mobile, $fullMessage, 'otp', $ok);
            return $ok;
        }
        return $this->send($mobile, $fullMessage, 'otp');
    }

    /**
     * Render an sms_templates row and send it. When the template has a
     * Melipayamak pattern code (pattern_body_id), the VARIABLE VALUES are sent
     * via the shared service line (BaseServiceNumber, values joined by ';' in
     * the order given) — the pattern text itself lives in the Melipayamak
     * panel and must list its variables in the same order.
     * @param array<string,string> $vars
     */
    public function sendTemplate(string $mobile, string $key, array $vars, string $fallback, string $kind = 'order'): bool
    {
        $templates = new SmsTemplateRepository();
        $row       = $templates->find($key);
        $rendered  = $templates->render($key, $vars, $fallback);

        $bodyId = $row !== null ? trim((string) ($row['pattern_body_id'] ?? '')) : '';
        if ($bodyId !== '' && $this->driverName === 'melipayamak' && $row !== null && (int) $row['is_active'] === 1) {
            $ok = $this->driver->sendPattern($mobile, implode(';', array_values($vars)), $bodyId);
            $this->log($mobile, $rendered, $kind, $ok);
            return $ok;
        }
        return $this->send($mobile, $rendered, $kind);
    }

    /** Remaining panel credit (null for the mock driver or on API failure). */
    public function credit(): ?float
    {
        try {
            return $this->driver->credit();
        } catch (\Throwable) {
            return null;
        }
    }

    private function log(string $mobile, string $message, string $kind, bool $ok, ?int $campaignId = null): void
    {
        try {
            (new SmsMessageRepository())->log($mobile, $message, $kind, $ok, $this->driverName, $campaignId);
        } catch (\Throwable) {
            // Logging must never break the send path (e.g. before migration).
        }
    }
}
