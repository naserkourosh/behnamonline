<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;
use Closure;

/**
 * Guards the machine-to-machine integration API (accounting/inventory
 * software). Authenticates via a shared key in the `X-Api-Key` header (or an
 * `api_key` query param). The key is generated/rotated in the admin panel and
 * stored in settings; when it is empty the API is considered disabled.
 */
final class RequireApiKey implements Middleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $configured = trim((string) setting('integration_api_key', ''));
        if ($configured === '') {
            return Response::json(['ok' => false, 'error' => 'API یکپارچه‌سازی فعال نیست.'], 503);
        }

        $provided = (string) ($request->header('X-Api-Key') ?? $request->query('api_key', ''));
        if ($provided === '' || !hash_equals($configured, $provided)) {
            return Response::json(['ok' => false, 'error' => 'دسترسی غیرمجاز.'], 401);
        }

        return $next($request);
    }
}
