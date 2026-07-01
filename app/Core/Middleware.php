<?php

declare(strict_types=1);

namespace App\Core;

use Closure;

/**
 * Contract for middleware. Each receives the request and a $next callable
 * that continues the pipeline, returning a Response.
 */
interface Middleware
{
    public function handle(Request $request, Closure $next): Response;
}
