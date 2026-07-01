<?php

declare(strict_types=1);

namespace App\Services\Sms;

use App\Core\Config;
use App\Repositories\SmsMessageRepository;

/**
 * Resolves the configured SMS driver, sends messages through it, and records
 * each attempt in sms_messages so the admin panel has a delivery history.
 */
final class SmsManager
{
    private SmsDriver $driver;
    private string $driverName;

    public function __construct()
    {
        $this->driverName = (string) Config::get('sms.driver', 'mock');
        $this->driver = match ($this->driverName) {
            'melipayamak' => new MelipayamakDriver(),
            default       => new MockDriver(),
        };
    }

    /**
     * @param string $kind Logical category for the history log (otp, order, manual…).
     */
    public function send(string $mobile, string $message, string $kind = 'system'): bool
    {
        $ok = $this->driver->send($mobile, $message);
        try {
            (new SmsMessageRepository())->log($mobile, $message, $kind, $ok, $this->driverName);
        } catch (\Throwable) {
            // Logging must never break the send path (e.g. before migration).
        }
        return $ok;
    }
}
