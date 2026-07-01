<?php

declare(strict_types=1);

namespace App\Services\Sms;

use App\Core\Config;

/**
 * Resolves the configured SMS driver and sends messages through it.
 */
final class SmsManager
{
    private SmsDriver $driver;

    public function __construct()
    {
        $this->driver = match ((string) Config::get('sms.driver', 'mock')) {
            'melipayamak' => new MelipayamakDriver(),
            default       => new MockDriver(),
        };
    }

    public function send(string $mobile, string $message): bool
    {
        return $this->driver->send($mobile, $message);
    }
}
