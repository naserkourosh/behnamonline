<?php

declare(strict_types=1);

namespace App\Services\Shipping;

use App\Core\Config;
use RuntimeException;

/**
 * National Post (شرکت ملی پست) fee web service.
 *
 * SCAFFOLD: the exact endpoint and field names depend on the postal
 * contract you receive (the fee/tariff web service). Set the endpoint and
 * credentials in .env:
 *
 *   POST_DRIVER=national
 *   POST_API_URL=https://<post-fee-web-service-endpoint>
 *   POST_API_KEY=...            (or POST_USERNAME / POST_PASSWORD)
 *   POST_ORIGIN_POSTAL=<shop 10-digit postal code>
 *
 * The request payload below (origin/destination postal codes + weight +
 * dimensions) and the response mapping are the fields such services expect;
 * adjust the key names to match the real contract. On any error — missing
 * config, network failure, unexpected shape — this throws so PostService
 * transparently falls back to the local estimate (MockPostDriver).
 */
final class NationalPostDriver implements PostDriver
{
    public function quote(array $dest, array $parcel): array
    {
        $apiUrl = (string) Config::get('shipping.post.api_url', '');
        if ($apiUrl === '') {
            throw new RuntimeException('National Post web service is not configured (POST_API_URL missing).');
        }

        $payload = [
            'origin_postal_code' => (string) Config::get('shipping.post.origin_postal', ''),
            'origin_city'        => (string) Config::get('shipping.post.origin_city', ''),
            'dest_province'      => $dest['province'],
            'dest_city'          => $dest['city'],
            'weight_grams'       => (int) $parcel['billable_g'],
            'length_cm'          => 0,
            'width_cm'           => 0,
            'height_cm'          => 0,
        ];

        $res = $this->request($apiUrl, $payload);

        // Expected: a list of services each with a label + fee (Rial or Toman).
        // Adapt the keys to the real response. We normalise fee to Toman.
        $services = $res['services'] ?? $res['data'] ?? null;
        if (!is_array($services) || $services === []) {
            throw new RuntimeException('National Post web service returned no services.');
        }

        $out = [];
        foreach ($services as $s) {
            $fee = (int) ($s['fee'] ?? $s['price'] ?? $s['amount'] ?? 0);
            // Many Iranian services return Rial; convert to Toman when it looks like Rial.
            if (($s['currency'] ?? 'IRR') === 'IRR' && $fee >= 10000) {
                $fee = (int) round($fee / 10);
            }
            $out[] = [
                'key'      => (string) ($s['key'] ?? $s['code'] ?? 'post'),
                'label'    => (string) ($s['label'] ?? $s['title'] ?? 'پست'),
                'desc'     => (string) ($s['desc'] ?? $s['delivery'] ?? ''),
                'cost'     => max(0, $fee),
                'delivery' => (string) ($s['delivery'] ?? ''),
            ];
        }
        return $out;
    }

    public function name(): string
    {
        return 'national';
    }

    /**
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    private function request(string $url, array $payload): array
    {
        $headers = ['Content-Type: application/json', 'Accept: application/json'];
        if (($key = (string) Config::get('shipping.post.api_key', '')) !== '') {
            $headers[] = 'Authorization: Bearer ' . $key;
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => (int) Config::get('shipping.post.timeout', 10),
        ]);
        $body = curl_exec($ch);
        $err  = curl_error($ch);
        curl_close($ch);

        if (!is_string($body) || $body === '') {
            throw new RuntimeException('National Post web service unreachable: ' . $err);
        }
        $decoded = json_decode($body, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('National Post web service returned an invalid response.');
        }
        return $decoded;
    }
}
