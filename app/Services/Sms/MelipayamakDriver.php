<?php

declare(strict_types=1);

namespace App\Services\Sms;

/**
 * Melipayamak REST driver, following the official SDK
 * (github.com/Melipayamak/melipayamak-php): every call POSTs form fields to
 * https://rest.payamak-panel.com/api/SendSMS/{Method}, authenticates with the
 * panel username/password, and returns {"Value","RetStatus","StrRetStatus"}
 * where RetStatus=1 means OK and Value is the RecId (or an error code).
 *
 * Connection settings (username/password/sender line/OTP bodyId) come from
 * SmsConfig — editable in the admin panel (پیامک‌ها › اتصال به پنل) with
 * .env as fallback. Pattern sends (BaseServiceNumber) go via the shared
 * service line, which also reaches numbers that blocked promotional SMS.
 */
final class MelipayamakDriver implements SmsDriver
{
    private const PATH = 'https://rest.payamak-panel.com/api/SendSMS/%s';

    /**
     * Error codes documented by Melipayamak (a RecId is much longer).
     * Positive codes: SendSMS. Negative codes: BaseServiceNumber (pattern).
     */
    private const ERRORS = [
        '0'  => 'نام کاربری یا رمز عبور اشتباه است',
        '2'  => 'اعتبار پنل کافی نیست',
        '3'  => 'محدودیت ارسال روزانه',
        '4'  => 'محدودیت در حجم ارسال',
        '5'  => 'شماره فرستنده معتبر نیست',
        '6'  => 'سامانه در حال بروزرسانی است (یا حساب/خط هنوز مجاز به ارسال وب‌سرویسی نیست)',
        '7'  => 'متن حاوی کلمهٔ فیلترشده است',
        '9'  => 'ارسال از خطوط عمومی از طریق وب‌سرویس ممکن نیست',
        '10' => 'کاربر مورد نظر فعال نیست',
        '11' => 'ارسال نشد',
        '12' => 'مدارک کاربر کامل نیست',
        '-1' => 'دسترسی وب‌سرویس فعال نیست',
        '-5' => 'متغیرهای ارسالی با الگوی (پترن) تاییدشده مطابقت ندارد — تعداد/ترتیب متغیرها را چک کنید',
        '-6' => 'کد الگو (bodyId) نامعتبر است یا هنوز تایید نشده',
    ];

    public function send(string $mobile, string $message): bool
    {
        return $this->sendMany([$mobile], $message);
    }

    /** @param list<string> $mobiles */
    public function sendMany(array $mobiles, string $message): bool
    {
        if ($mobiles === []) {
            return false;
        }
        $result = $this->call('SendSMS', [
            'to'      => implode(',', $mobiles),
            'from'    => SmsConfig::from(),
            'text'    => $message,
            'isflash' => 'false',
        ]);
        return $this->isSendOk($result, 'SendSMS → ' . implode(',', array_slice($mobiles, 0, 3)) . (count($mobiles) > 3 ? '…(' . count($mobiles) . ')' : ''));
    }

    public function sendPattern(string $mobile, string $args, string $bodyId): bool
    {
        $result = $this->call('BaseServiceNumber', [
            'text'   => $args,
            'to'     => $mobile,
            'bodyId' => $bodyId,
        ]);
        return $this->isSendOk($result, 'BaseServiceNumber → ' . $mobile);
    }

    public function credit(): ?float
    {
        $result = $this->call('GetCredit', []);
        if ($result === null || (int) ($result['RetStatus'] ?? 0) !== 1) {
            return null;
        }
        return (float) $result['Value'];
    }

    /**
     * POST username/password + fields to the REST endpoint and decode the
     * JSON envelope. Returns null on transport failure.
     * @param array<string,string> $fields
     * @return array<string,mixed>|null
     */
    private function call(string $method, array $fields): ?array
    {
        $payload = array_merge([
            'username' => SmsConfig::username(),
            'password' => SmsConfig::password(),
        ], $fields);

        $ch = curl_init(sprintf(self::PATH, $method));
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($payload),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_TIMEOUT        => 20,
        ]);
        $response = curl_exec($ch);
        $status   = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($response === false || $status !== 200) {
            $this->log(sprintf('%s HTTP %d: %s', $method, $status, $error !== '' ? $error : (string) $response));
            return null;
        }

        $decoded = json_decode((string) $response, true);
        if (!is_array($decoded)) {
            $this->log(sprintf('%s bad response: %s', $method, (string) $response));
            return null;
        }
        return $decoded;
    }

    /**
     * A send succeeded when RetStatus=1 and Value is a RecId — a long number,
     * unlike the short (or negative) error codes.
     * @param array<string,mixed>|null $result
     */
    private function isSendOk(?array $result, string $context): bool
    {
        if ($result === null) {
            return false;
        }
        $value = trim((string) ($result['Value'] ?? ''));
        $ok    = (int) ($result['RetStatus'] ?? 0) === 1 && is_numeric($value) && strlen($value) > 10;

        if (!$ok) {
            $reason = self::ERRORS[$value] ?? ((string) ($result['StrRetStatus'] ?? '') ?: 'خطای نامشخص');
            $this->log(sprintf('%s FAILED (Value=%s): %s', $context, $value, $reason));
        }
        return $ok;
    }

    private function log(string $line): void
    {
        @file_put_contents(
            BASE_PATH . '/storage/logs/sms.log',
            sprintf("[%s] MELIPAYAMAK %s\n", date('Y-m-d H:i:s'), $line),
            FILE_APPEND | LOCK_EX
        );
    }
}
