<?php

declare(strict_types=1);

use App\Controllers\Api\CartApiController;
use App\Controllers\Api\IntegrationApiController;
use App\Controllers\Api\SearchApiController;
use App\Controllers\Api\ShippingApiController;
use App\Controllers\Api\WishlistApiController;
use App\Core\Router;
use App\Middleware\RequireApiKey;
use App\Middleware\SecurityHeaders;
use App\Middleware\ThrottleRequests;
use App\Middleware\VerifyCsrf;

/**
 * JSON API routes (power the AJAX cart & search). Throttled per IP;
 * mutations additionally require a valid CSRF token.
 */
return static function (Router $router): void {
    $router->group([SecurityHeaders::class, ThrottleRequests::class], static function (Router $r): void {
        $r->get('/api/cart', [CartApiController::class, 'show']);
        $r->post('/api/cart', [CartApiController::class, 'add'], [VerifyCsrf::class]);
        $r->post('/api/cart/update', [CartApiController::class, 'update'], [VerifyCsrf::class]);
        $r->post('/api/cart/remove', [CartApiController::class, 'remove'], [VerifyCsrf::class]);
        $r->post('/api/cart/coupon', [CartApiController::class, 'applyCoupon'], [VerifyCsrf::class]);
        $r->post('/api/cart/coupon/remove', [CartApiController::class, 'removeCoupon'], [VerifyCsrf::class]);

        $r->get('/api/search', [SearchApiController::class, 'suggest']);

        $r->get('/api/shipping/quote', [ShippingApiController::class, 'quote']);

        $r->post('/api/wishlist', [WishlistApiController::class, 'toggle'], [VerifyCsrf::class]);
    });

    // Accounting / inventory integration API (هلو / محک). Machine-to-machine:
    // authenticated by an API key (RequireApiKey), not a browser CSRF token.
    $router->group([SecurityHeaders::class, RequireApiKey::class], static function (Router $r): void {
        $r->get('/api/integration/products', [IntegrationApiController::class, 'products']);
        $r->get('/api/integration/orders', [IntegrationApiController::class, 'orders']);
        $r->post('/api/integration/stock', [IntegrationApiController::class, 'stock']);
    });
};
