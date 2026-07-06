<?php

declare(strict_types=1);

namespace App\Services\Sms;

use App\Core\Config;

/**
 * SMS connection settings, editable from the admin panel (settings table,
 * keys sms_*) with .env as the fallback for anything not saved yet. All SMS
 * code must read connection values through here — never Config directly.
 */
final class SmsConfig
{
    public static function driver(): string
    {
        $db = (string) setting('sms_driver', '');
        return in_array($db, ['mock', 'melipayamak'], true)
            ? $db
            : (string) Config::get('sms.driver', 'mock');
    }

    public static function username(): string
    {
        return (string) (setting('sms_username', '') ?: Config::get('sms.melipayamak.username', ''));
    }

    public static function password(): string
    {
        return (string) (setting('sms_password', '') ?: Config::get('sms.melipayamak.password', ''));
    }

    /** Sender line number (شماره خط اختصاصی). */
    public static function from(): string
    {
        return (string) (setting('sms_from', '') ?: Config::get('sms.melipayamak.from', ''));
    }

    /** Approved pattern code (bodyId) for OTP via the shared service line. */
    public static function otpBodyId(): string
    {
        return (string) (setting('sms_otp_body_id', '') ?: Config::get('sms.melipayamak.otp_body_id', ''));
    }
}
