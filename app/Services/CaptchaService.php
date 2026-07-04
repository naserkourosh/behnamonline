<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Session;

/**
 * Self-hosted image CAPTCHA for the admin login (GD, no external service).
 * The expected code lives in the session; the PNG is rendered on demand.
 */
final class CaptchaService
{
    private const KEY = 'admin_captcha';
    // Unambiguous alphabet (no 0/O/1/I).
    private const ALPHABET = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';

    /** Generate a new code, store it in the session, and return it. */
    public static function generate(int $length = 5): string
    {
        $code = '';
        $max  = strlen(self::ALPHABET) - 1;
        for ($i = 0; $i < $length; $i++) {
            $code .= self::ALPHABET[random_int(0, $max)];
        }
        Session::set(self::KEY, $code);
        return $code;
    }

    /** Verify (case-insensitive) and consume the current code (one-shot). */
    public static function verify(string $input): bool
    {
        $expected = Session::get(self::KEY);
        Session::forget(self::KEY);
        if (!is_string($expected) || $expected === '') {
            return false;
        }
        return hash_equals(strtoupper($expected), strtoupper(trim($input)));
    }

    /** Render the current (freshly generated) code as PNG bytes. */
    public static function png(): string
    {
        $code = self::generate();
        $w = 160;
        $h = 54;

        $img = imagecreatetruecolor($w, $h);
        $bg  = imagecolorallocate($img, 250, 246, 240); // cream
        imagefilledrectangle($img, 0, 0, $w, $h, $bg);

        // Speckle noise.
        for ($i = 0; $i < 550; $i++) {
            $c = imagecolorallocate($img, random_int(200, 240), random_int(190, 225), random_int(200, 225));
            imagesetpixel($img, random_int(0, $w), random_int(0, $h), $c);
        }
        // Distraction lines.
        for ($i = 0; $i < 6; $i++) {
            $c = imagecolorallocate($img, random_int(150, 200), random_int(110, 170), random_int(150, 190));
            imageline($img, random_int(0, $w), random_int(0, $h), random_int(0, $w), random_int(0, $h), $c);
        }
        // Characters (built-in largest font) with per-glyph jitter.
        $len = strlen($code);
        for ($i = 0; $i < $len; $i++) {
            $c = imagecolorallocate($img, random_int(60, 110), random_int(20, 60), random_int(50, 90)); // brand-ish
            $x = 16 + $i * 28 + random_int(-3, 3);
            $y = random_int(10, 22);
            imagestring($img, 5, $x, $y, $code[$i], $c);
        }
        // Scale up slightly for a softer, harder-to-OCR result.
        $scaled = imagescale($img, (int) ($w * 1.15), (int) ($h * 1.15));
        if ($scaled !== false) {
            imagedestroy($img);
            $img = $scaled;
        }

        ob_start();
        imagepng($img);
        $png = (string) ob_get_clean();
        imagedestroy($img);
        return $png;
    }
}
