<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Csrf;
use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;
use Closure;

/**
 * Rejects state-changing requests that lack a valid CSRF token.
 * Safe methods (GET/HEAD/OPTIONS) pass through untouched.
 */
final class VerifyCsrf implements Middleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true)) {
            return $next($request);
        }

        if (!Csrf::check($request)) {
            if ($request->wantsJson()) {
                return Response::json(['ok' => false, 'error' => 'توکن امنیتی نامعتبر است.'], 419);
            }
            return Response::html(\App\Core\View::renderError(419), 419);
        }

        return $next($request);
    }
}
