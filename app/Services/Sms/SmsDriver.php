<?php

declare(strict_types=1);

namespace App\Services\Sms;

/**
 * Contract for SMS providers. Implementations send plain-text messages to
 * Iranian mobile numbers and report success.
 */
interface SmsDriver
{
    public function send(string $mobile, string $message): bool;

    /**
     * Send one message to many recipients in a single provider call.
     * @param list<string> $mobiles
     */
    public function sendMany(array $mobiles, string $message): bool;

    /**
     * Send through an approved pattern on the provider's shared service line
     * (خط خدماتی) — reaches numbers that blocked promotional SMS. $args holds
     * the pattern variables (multiple values joined by ';').
     */
    public function sendPattern(string $mobile, string $args, string $bodyId): bool;

    /** Remaining panel credit, or null when the driver has no such concept. */
    public function credit(): ?float;
}
