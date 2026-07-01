<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;
use App\Services\AuthService;
use Closure;

/**
 * Guards customer-only routes. Unauthenticated visitors are redirected to
 * the login page (JSON callers get a 401).
 */
final class RequireAuth implements Middleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (AuthService::check()) {
            return $next($request);
        }

        if ($request->wantsJson()) {
            return Response::json(['ok' => false, 'error' => 'برای این عملیات باید وارد شوید.', 'auth' => false], 401);
        }

        return Response::redirect(url('/login?redirect=' . urlencode($request->path())));
    }
}
