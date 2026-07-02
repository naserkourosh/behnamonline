<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Allowlist HTML sanitizer for stored rich text (product descriptions).
 *
 * Permits a small set of formatting tags plus a single, tightly-validated
 * Aparat <iframe> embed. Everything else (scripts, event handlers, foreign
 * iframes, javascript: URLs) is stripped — XSS protection for admin content.
 */
final class Html
{
    private const ALLOWED_TAGS = [
        'p', 'br', 'hr', 'strong', 'b', 'em', 'i', 'u', 's', 'ul', 'ol', 'li',
        'h1', 'h2', 'h3', 'h4', 'blockquote', 'span', 'div', 'a', 'table', 'thead',
        'tbody', 'tr', 'td', 'th', 'img', 'figure', 'figcaption',
    ];

    public static function sanitize(string $html): string
    {
        if (trim($html) === '') {
            return '';
        }

        // Extract and re-insert validated Aparat embeds via placeholders so
        // strip_tags() does not remove them.
        $embeds = [];
        $html = preg_replace_callback(
            '#<iframe[^>]*src=["\']([^"\']*aparat\.com[^"\']*)["\'][^>]*>\s*</iframe>#i',
            static function (array $m) use (&$embeds): string {
                $src = filter_var($m[1], FILTER_SANITIZE_URL);
                if ($src === false || !preg_match('#^https?://(www\.)?aparat\.com/#i', $src)) {
                    return '';
                }
                $token = '%%APARAT_' . count($embeds) . '%%';
                $embeds[$token] = '<div class="aspect-video w-full overflow-hidden rounded-2xl">'
                    . '<iframe src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '" '
                    . 'class="h-full w-full" allowfullscreen loading="lazy" '
                    . 'referrerpolicy="strict-origin"></iframe></div>';
                return $token;
            },
            $html
        ) ?? $html;

        $allowed = '<' . implode('><', self::ALLOWED_TAGS) . '>';
        $clean   = strip_tags($html, $allowed);

        // Remove inline event handlers and javascript: URLs from any survivors.
        $clean = preg_replace('/\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $clean) ?? $clean;
        $clean = preg_replace('/(href|src)\s*=\s*("|\')\s*javascript:[^"\']*("|\')/i', '$1="#"', $clean) ?? $clean;

        return strtr($clean, $embeds);
    }
}
