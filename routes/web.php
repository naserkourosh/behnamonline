<?php

declare(strict_types=1);

use App\Controllers\Storefront\CartController;
use App\Controllers\Storefront\CategoryController;
use App\Controllers\Storefront\HomeController;
use App\Controllers\Storefront\ProductController;
use App\Core\Router;
use App\Middleware\SecurityHeaders;

/**
 * Storefront (HTML) routes. All wrapped in security headers.
 */
return static function (Router $router): void {
    $router->group([SecurityHeaders::class], static function (Router $r): void {
        $r->get('/', [HomeController::class, 'index']);

        $r->get('/category', [CategoryController::class, 'index']);
        $r->get('/category/{slug}', [CategoryController::class, 'show']);

        $r->get('/product/{slug}', [ProductController::class, 'show']);

        $r->get('/cart', [CartController::class, 'index']);
    });
};
