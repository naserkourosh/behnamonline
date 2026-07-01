<?php

declare(strict_types=1);

namespace App\Services\Sms;

/**
 * Contract for SMS providers. Implementations send a plain-text message
 * to an Iranian mobile number and report success.
 */
interface SmsDriver
{
    public function send(string $mobile, string $message): bool;
}
