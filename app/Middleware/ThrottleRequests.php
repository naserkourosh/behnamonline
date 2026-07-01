<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Middleware;
use App\Core\RateLimiter;
use App\Core\Request;
use App\Core\Response;
use Closure;

/**
 * Per-IP throttle for API/cart/search endpoints: 60 requests / minute.
 */
final class ThrottleRequests implements Middleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $limiter = new RateLimiter();
        $bucket  = 'api:' . $request->path();

        if (!$limiter->attempt($bucket, $request->ip(), 60, 60)) {
            return Response::json(
                ['ok' => false, 'error' => 'تعداد درخواست‌ها بیش از حد مجاز است. کمی صبر کنید.'],
                429
            );
        }

        return $next($request);
    }
}
