<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\Core\Config;
use RuntimeException;

/** Minimal JSON HTTP GET for the accounting drivers (cURL). */
final class AccountingHttp
{
    /**
     * @param array<string,mixed> $query
     * @param list<string> $headers
     * @return array<string,mixed>
     */
    public static function getJson(string $url, array $query = [], array $headers = []): array
    {
        if ($query !== []) {
            $url .= (str_contains($url, '?') ? '&' : '?') . http_build_query($query);
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => array_merge(['Accept: application/json'], $headers),
            CURLOPT_TIMEOUT        => (int) Config::get('integrations.accounting.timeout', 15),
        ]);
        $body = curl_exec($ch);
        $err  = curl_error($ch);
        curl_close($ch);

        if (!is_string($body) || $body === '') {
            throw new RuntimeException('اتصال به وب‌سرویس حسابداری ناموفق بود: ' . $err);
        }
        $decoded = json_decode($body, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('پاسخ وب‌سرویس حسابداری نامعتبر بود.');
        }
        return $decoded;
    }
}
