<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Dependency-free Gregorian → Jalali (Shamsi) date conversion.
 * Algorithm adapted from the well-known jdf/roozbeh conversion.
 */
final class Jalali
{
    /** @return array{0:int,1:int,2:int} [jy, jm, jd] */
    public static function toJalali(int $gy, int $gm, int $gd): array
    {
        // Cumulative Gregorian days elapsed before the start of each month.
        $gMonthOffset = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];

        $gy2 = ($gm > 2) ? ($gy + 1) : $gy;
        $days = 355666 + (365 * $gy) + intdiv($gy2 + 3, 4) - intdiv($gy2 + 99, 100)
            + intdiv($gy2 + 399, 400) + $gd + $gMonthOffset[$gm - 1];

        $jy = -1595 + (33 * intdiv($days, 12053));
        $days %= 12053;
        $jy += 4 * intdiv($days, 1461);
        $days %= 1461;
        if ($days > 365) {
            $jy += intdiv($days - 1, 365);
            $days = ($days - 1) % 365;
        }
        if ($days < 186) {
            $jm = 1 + intdiv($days, 31);
            $jd = 1 + ($days % 31);
        } else {
            $jm = 7 + intdiv($days - 186, 30);
            $jd = 1 + (($days - 186) % 30);
        }

        return [$jy, $jm, $jd];
    }

    public static function format(string|int $date, string $format = 'Y/m/d'): string
    {
        if (is_int($date) || ctype_digit((string) $date)) {
            $ts = (int) $date;
            $gy = (int) date('Y', $ts);
            $gm = (int) date('n', $ts);
            $gd = (int) date('j', $ts);
            $hh = date('H', $ts);
            $ii = date('i', $ts);
        } else {
            $parts = preg_split('/[-\/ :T]/', $date) ?: [];
            $gy = (int) ($parts[0] ?? date('Y'));
            $gm = (int) ($parts[1] ?? date('n'));
            $gd = (int) ($parts[2] ?? date('j'));
            $hh = str_pad((string) (int) ($parts[3] ?? 0), 2, '0', STR_PAD_LEFT);
            $ii = str_pad((string) (int) ($parts[4] ?? 0), 2, '0', STR_PAD_LEFT);
        }

        [$jy, $jm, $jd] = self::toJalali($gy, $gm, $gd);

        $replacements = [
            'Y' => (string) $jy,
            'm' => str_pad((string) $jm, 2, '0', STR_PAD_LEFT),
            'n' => (string) $jm,
            'd' => str_pad((string) $jd, 2, '0', STR_PAD_LEFT),
            'j' => (string) $jd,
            'F' => self::MONTHS[$jm - 1] ?? '',
            'H' => $hh,
            'i' => $ii,
        ];

        return \fa(strtr($format, $replacements));
    }

    private const MONTHS = [
        'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور',
        'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند',
    ];
}
