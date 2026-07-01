<?php

declare(strict_types=1);

namespace App\Services\Payment;

use App\Core\Config;

/**
 * Zarinpal REST (v4) gateway. Set PAYMENT_DRIVER=zarinpal and
 * ZARINPAL_MERCHANT_ID in .env. Amounts are converted Toman → Rial.
 */
final class ZarinpalGateway implements PaymentGateway
{
    public function key(): string
    {
        return 'zarinpal';
    }

    public function label(): string
    {
        return 'زرین‌پال';
    }

    public function initiate(int $orderId, int $amount, string $description, string $callbackUrl): array
    {
        $res = $this->request('payment/request.json', [
            'merchant_id'  => (string) Config::get('payment.gateways.zarinpal.merchant_id', ''),
            'amount'       => $amount * 10, // Toman → Rial
            'description'  => $description,
            'callback_url' => $callbackUrl,
        ]);

        $code      = (int) ($res['data']['code'] ?? 0);
        $authority = (string) ($res['data']['authority'] ?? '');

        if ($code === 100 && $authority !== '') {
            return ['ok' => true, 'authority' => $authority, 'redirect_url' => $this->startPayUrl($authority)];
        }

        return ['ok' => false, 'error' => 'خطا در اتصال به درگاه زرین‌پال.'];
    }

    public function verify(array $params, int $amount): array
    {
        if (($params['Status'] ?? $params['status'] ?? '') !== 'OK') {
            return ['ok' => false, 'error' => 'پرداخت لغو شد.'];
        }

        $authority = (string) ($params['Authority'] ?? $params['authority'] ?? '');
        $res = $this->request('payment/verify.json', [
            'merchant_id' => (string) Config::get('payment.gateways.zarinpal.merchant_id', ''),
            'amount'      => $amount * 10,
            'authority'   => $authority,
        ]);

        $code = (int) ($res['data']['code'] ?? 0);
        if ($code === 100 || $code === 101) {
            return ['ok' => true, 'ref_id' => (string) ($res['data']['ref_id'] ?? '')];
        }

        return ['ok' => false, 'error' => 'تایید پرداخت ناموفق بود.'];
    }

    private function baseUrl(): string
    {
        return (bool) Config::get('payment.gateways.zarinpal.sandbox', true)
            ? 'https://sandbox.zarinpal.com/pg/v4/'
            : 'https://api.zarinpal.com/pg/v4/';
    }

    private function startPayUrl(string $authority): string
    {
        $host = (bool) Config::get('payment.gateways.zarinpal.sandbox', true) ? 'sandbox' : 'www';
        return "https://{$host}.zarinpal.com/pg/StartPay/{$authority}";
    }

    /**
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    private function request(string $path, array $payload): array
    {
        $ch = curl_init($this->baseUrl() . $path);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Accept: application/json'],
            CURLOPT_TIMEOUT        => 20,
        ]);
        $body = curl_exec($ch);
        curl_close($ch);

        $decoded = is_string($body) ? json_decode($body, true) : null;
        return is_array($decoded) ? $decoded : [];
    }
}
