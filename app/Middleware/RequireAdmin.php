<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\AdminAuthService;
use Closure;

/**
 * Guards the /admin area. Unauthenticated requests are sent to the admin
 * login; the intended path is remembered for post-login redirect.
 */
final class RequireAdmin implements Middleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!AdminAuthService::check()) {
            if ($request->wantsJson()) {
                return Response::json(['ok' => false, 'error' => 'دسترسی غیرمجاز.'], 401);
            }
            Session::set('admin_intended', $request->path());
            return Response::redirect(url('/admin/login'));
        }

        return $next($request);
    }
}
