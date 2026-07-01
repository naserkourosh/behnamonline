<?php

declare(strict_types=1);

use App\Controllers\Storefront\AccountController;
use App\Controllers\Storefront\AuthController;
use App\Controllers\Storefront\CartController;
use App\Controllers\Storefront\CategoryController;
use App\Controllers\Storefront\CheckoutController;
use App\Controllers\Storefront\HomeController;
use App\Controllers\Storefront\PaymentController;
use App\Controllers\Storefront\ProductController;
use App\Core\Router;
use App\Middleware\RequireAuth;
use App\Middleware\SecurityHeaders;
use App\Middleware\VerifyCsrf;

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

        // ── Checkout (OTP) ──
        $r->get('/checkout', [CheckoutController::class, 'index']);
        $r->post('/checkout/send-otp', [CheckoutController::class, 'sendOtp'], [VerifyCsrf::class]);
        $r->post('/checkout/verify', [CheckoutController::class, 'verify'], [VerifyCsrf::class]);

        // ── Auth (OTP login) ──
        $r->get('/login', [AuthController::class, 'show']);
        $r->post('/login/send-otp', [AuthController::class, 'sendOtp'], [VerifyCsrf::class]);
        $r->post('/login/verify', [AuthController::class, 'verify'], [VerifyCsrf::class]);
        $r->post('/logout', [AuthController::class, 'logout'], [VerifyCsrf::class]);

        // ── Payment pipeline (auth required) ──
        $r->group([RequireAuth::class], static function (Router $p): void {
            // Literal routes must precede the /pay/{order} catch-all.
            $p->get('/pay/mock', [PaymentController::class, 'mock']);
            $p->get('/pay/callback', [PaymentController::class, 'callback']);
            $p->post('/pay/card/{order}', [PaymentController::class, 'cardConfirm'], [VerifyCsrf::class]);
            $p->get('/pay/{order}', [PaymentController::class, 'start']);
            $p->get('/checkout/success/{order}', [PaymentController::class, 'success']);
            $p->get('/checkout/failed/{order}', [PaymentController::class, 'failed']);
        });

        // ── Customer dashboard (auth required) ──
        $r->group([RequireAuth::class], static function (Router $a): void {
            $a->get('/account', [AccountController::class, 'dashboard']);
            $a->get('/account/orders', [AccountController::class, 'orders']);
            $a->get('/account/orders/{id}', [AccountController::class, 'orderShow']);
            $a->get('/account/orders/{id}/invoice', [AccountController::class, 'invoice']);
            $a->get('/account/addresses', [AccountController::class, 'addresses']);
            $a->post('/account/addresses', [AccountController::class, 'addressStore'], [VerifyCsrf::class]);
            $a->post('/account/addresses/{id}/delete', [AccountController::class, 'addressDelete'], [VerifyCsrf::class]);
            $a->get('/account/profile', [AccountController::class, 'profile']);
            $a->post('/account/profile', [AccountController::class, 'profileUpdate'], [VerifyCsrf::class]);
            $a->get('/account/wishlist', [AccountController::class, 'wishlist']);
        });
    });
};
