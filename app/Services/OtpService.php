<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use App\Repositories\OtpRepository;
use App\Services\Sms\SmsManager;

/**
 * Issues and verifies one-time passwords, with per-mobile send throttling
 * and per-code attempt limits.
 */
final class OtpService
{
    private const MAX_ATTEMPTS = 5;

    private OtpRepository $repo;

    public function __construct()
    {
        $this->repo = new OtpRepository();
    }

    /**
     * @return array{ok:bool,message:string,resend_wait:int,dev_code?:string}
     */
    public function send(string $mobile, string $purpose = 'login'): array
    {
        $resendWait = (int) Config::get('sms.otp.resend_wait', 90);

        // One code per resend window.
        if ($this->repo->recentCount($mobile, $resendWait) > 0) {
            return ['ok' => false, 'message' => 'کد قبلی هنوز معتبر است. کمی صبر کنید.', 'resend_wait' => $resendWait];
        }

        $length = (int) Config::get('sms.otp.length', 5);
        $ttl    = (int) Config::get('sms.otp.ttl', 120);
        $code   = $this->generateCode($length);

        $this->repo->create($mobile, hash('sha256', $code), $purpose, $ttl);

        $brand   = (string) Config::get('app.name', 'بهنام');
        $message = "کد تایید {$brand}: {$code}\nاین کد تا " . ((int) ($ttl / 60)) . " دقیقه معتبر است.";
        // Goes via the service-line pattern when configured (see SmsManager).
        (new SmsManager())->sendOtp($mobile, $code, $message);

        $result = ['ok' => true, 'message' => 'کد تایید ارسال شد.', 'resend_wait' => $resendWait];

        // In debug/mock mode, expose the code so local testing is frictionless.
        if ((bool) Config::get('app.debug', false) && \App\Services\Sms\SmsConfig::driver() === 'mock') {
            $result['dev_code'] = $code;
        }

        return $result;
    }

    /** @return array{ok:bool,message:string} */
    public function verify(string $mobile, string $code, string $purpose = 'login'): array
    {
        $code = $this->normalizeDigits($code);
        $row  = $this->repo->latestValid($mobile, $purpose);

        if ($row === null) {
            return ['ok' => false, 'message' => 'کد منقضی شده یا نامعتبر است. دوباره تلاش کنید.'];
        }
        if ((int) $row['attempts'] >= self::MAX_ATTEMPTS) {
            $this->repo->consume((int) $row['id']);
            return ['ok' => false, 'message' => 'تعداد تلاش‌ها بیش از حد مجاز بود. کد جدید بگیرید.'];
        }

        $this->repo->incrementAttempts((int) $row['id']);

        if (!hash_equals((string) $row['code_hash'], hash('sha256', $code))) {
            return ['ok' => false, 'message' => 'کد وارد شده صحیح نیست.'];
        }

        $this->repo->consume((int) $row['id']);
        return ['ok' => true, 'message' => 'شماره موبایل تایید شد.'];
    }

    private function generateCode(int $length): string
    {
        $min = (int) str_pad('1', $length, '0');
        $max = (int) str_pad('', $length, '9');
        return (string) random_int($min, $max);
    }

    private function normalizeDigits(string $value): string
    {
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $arabic  = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $latin   = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        return str_replace($arabic, $latin, str_replace($persian, $latin, trim($value)));
    }
}
