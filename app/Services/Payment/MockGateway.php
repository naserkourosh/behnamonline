<?php

declare(strict_types=1);

namespace App\Services\Payment;

/**
 * Development gateway: instead of an external provider it redirects to an
 * internal test page (/pay/mock) where the payment can be approved or
 * canceled — exercising the full initiate → redirect → callback → verify
 * pipeline without real credentials. Used when a real gateway is not
 * configured (e.g. SnapPay/Digipay without keys, or Zarinpal sandbox off).
 */
final class MockGateway implements PaymentGateway
{
    public function __construct(private string $label = 'درگاه آزمایشی', private string $key = 'mock')
    {
    }

    public function key(): string
    {
        return $this->key;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function initiate(int $orderId, int $amount, string $description, string $callbackUrl): array
    {
        $authority = 'MOCK-' . bin2hex(random_bytes(6));
        $redirect  = url('/pay/mock?order=' . $orderId . '&authority=' . $authority . '&gateway=' . $this->key);

        return ['ok' => true, 'authority' => $authority, 'redirect_url' => $redirect];
    }

    public function verify(array $params, int $amount): array
    {
        if (($params['status'] ?? '') === 'OK') {
            return ['ok' => true, 'ref_id' => 'MREF-' . random_int(100000, 999999)];
        }
        return ['ok' => false, 'error' => 'پرداخت توسط کاربر لغو شد.'];
    }
}
