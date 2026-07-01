<?php

declare(strict_types=1);

namespace App\Services\Sms;

use App\Core\Config;

/**
 * Melipayamak REST SMS driver. Configure credentials in .env
 * (MELIPAYAMAK_USERNAME / PASSWORD / FROM) and set SMS_DRIVER=melipayamak.
 */
final class MelipayamakDriver implements SmsDriver
{
    private const ENDPOINT = 'https://rest.melipayamak.com/api/SendSMS/SendSMS';

    public function send(string $mobile, string $message): bool
    {
        $payload = [
            'username' => (string) Config::get('sms.melipayamak.username', ''),
            'password' => (string) Config::get('sms.melipayamak.password', ''),
            'to'       => $mobile,
            'from'     => (string) Config::get('sms.melipayamak.from', ''),
            'text'     => $message,
        ];

        $ch = curl_init(self::ENDPOINT);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT        => 15,
        ]);
        $response = curl_exec($ch);
        $status   = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Melipayamak returns a numeric RecId (> 0) on success.
        $ok = $status === 200 && $response !== false && (float) trim((string) $response, "\"' ") > 0;

        if (!$ok) {
            @file_put_contents(
                BASE_PATH . '/storage/logs/sms.log',
                sprintf("[%s] SMS FAIL → %s (http %d): %s\n", date('Y-m-d H:i:s'), $mobile, $status, (string) $response),
                FILE_APPEND | LOCK_EX
            );
        }

        return $ok;
    }
}
