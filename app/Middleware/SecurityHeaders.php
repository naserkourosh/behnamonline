<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Config;
use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;
use Closure;

/**
 * Adds a Content-Security-Policy and supporting headers. The CSP allows
 * self-hosted assets, inline styles (the design uses some), and the Aparat
 * video domain for product embeds.
 */
final class SecurityHeaders implements Middleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $csp = implode('; ', [
            "default-src 'self'",
            "img-src 'self' data: https:",
            "style-src 'self' 'unsafe-inline'",
            "script-src 'self' 'unsafe-inline'",
            "font-src 'self' data:",
            "frame-src https://www.aparat.com https://aparat.com",
            "connect-src 'self'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
        ]);

        $response->header('Content-Security-Policy', $csp);
        $response->header('X-Content-Type-Options', 'nosniff');
        $response->header('X-Frame-Options', 'SAMEORIGIN');
        $response->header('Referrer-Policy', 'strict-origin-when-cross-origin');

        if (!Config::get('app.debug', false)) {
            $response->header('X-Powered-By', 'Behnam');
        }

        return $response;
    }
}
