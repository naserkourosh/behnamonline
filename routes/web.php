<?php

declare(strict_types=1);

use App\Controllers\Storefront\AccountController;
use App\Controllers\Storefront\AuthController;
use App\Controllers\Storefront\BlogController;
use App\Controllers\Storefront\CartController;
use App\Controllers\Storefront\CategoryController;
use App\Controllers\Storefront\CheckoutController;
use App\Controllers\Storefront\FaqController;
use App\Controllers\Storefront\HomeController;
use App\Controllers\Storefront\PaymentController;
use App\Controllers\Storefront\ProductController;
use App\Controllers\Admin\AccountingController as AdminAccountingController;
use App\Controllers\Admin\AuthController as AdminAuthController;
use App\Controllers\Admin\BannerController as AdminBannerController;
use App\Controllers\Admin\BlogController as AdminBlogController;
use App\Controllers\Admin\BrandController as AdminBrandController;
use App\Controllers\Admin\CouponController as AdminCouponController;
use App\Controllers\Admin\ReportController as AdminReportController;
use App\Controllers\Admin\ShippingController as AdminShippingController;
use App\Controllers\Admin\StaffController as AdminStaffController;
use App\Controllers\Admin\PopupController as AdminPopupController;
use App\Controllers\Admin\FaqController as AdminFaqController;
use App\Controllers\Admin\MediaController as AdminMediaController;
use App\Controllers\Admin\TicketController as AdminTicketController;
use App\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Controllers\Admin\MenuController as AdminMenuController;
use App\Controllers\Admin\OrderController as AdminOrderController;
use App\Controllers\Admin\ProductController as AdminProductController;
use App\Controllers\Admin\SettingController as AdminSettingController;
use App\Controllers\Admin\SmsController as AdminSmsController;
use App\Controllers\Admin\TagController as AdminTagController;
use App\Core\Router;
use App\Middleware\RequireAdmin;
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

        // ── Blog / magazine ──
        $r->get('/blog', [BlogController::class, 'index']);
        $r->get('/blog/category/{slug}', [BlogController::class, 'index']);
        $r->post('/blog/{slug}/comment', [BlogController::class, 'comment'], [VerifyCsrf::class]);
        $r->get('/blog/{slug}', [BlogController::class, 'show']);

        // ── FAQ ──
        $r->get('/faq', [FaqController::class, 'index']);

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
            $a->get('/account/points', [AccountController::class, 'points']);
            $a->get('/account/tickets', [AccountController::class, 'tickets']);
            $a->post('/account/tickets', [AccountController::class, 'ticketStore'], [VerifyCsrf::class]);
            $a->get('/account/tickets/{id}', [AccountController::class, 'ticketShow']);
            $a->post('/account/tickets/{id}/reply', [AccountController::class, 'ticketReply'], [VerifyCsrf::class]);
        });

        // ── Admin authentication ──
        $r->get('/admin/login', [AdminAuthController::class, 'showLogin']);
        $r->post('/admin/login', [AdminAuthController::class, 'login'], [VerifyCsrf::class]);
        $r->post('/admin/logout', [AdminAuthController::class, 'logout'], [VerifyCsrf::class]);

        // ── Admin panel (auth required) ──
        $r->group([RequireAdmin::class], static function (Router $x): void {
            $x->get('/admin', [AdminDashboardController::class, 'index']);

            // Reports & analytics
            $x->get('/admin/reports', [AdminReportController::class, 'index']);

            // Products
            $x->get('/admin/products', [AdminProductController::class, 'index']);
            $x->get('/admin/products/create', [AdminProductController::class, 'create']);
            $x->post('/admin/products', [AdminProductController::class, 'store'], [VerifyCsrf::class]);
            $x->get('/admin/products/{id}/edit', [AdminProductController::class, 'edit']);
            $x->post('/admin/products/{id}', [AdminProductController::class, 'update'], [VerifyCsrf::class]);
            $x->post('/admin/products/{id}/delete', [AdminProductController::class, 'destroy'], [VerifyCsrf::class]);
            $x->post('/admin/products/{id}/sort', [AdminProductController::class, 'sort'], [VerifyCsrf::class]);
            $x->post('/admin/products/{id}/images', [AdminProductController::class, 'uploadImages'], [VerifyCsrf::class]);
            $x->post('/admin/products/images/{img}/delete', [AdminProductController::class, 'deleteImage'], [VerifyCsrf::class]);

            // Categories
            $x->get('/admin/categories', [AdminCategoryController::class, 'index']);
            $x->get('/admin/categories/create', [AdminCategoryController::class, 'create']);
            $x->post('/admin/categories', [AdminCategoryController::class, 'store'], [VerifyCsrf::class]);
            $x->get('/admin/categories/{id}/edit', [AdminCategoryController::class, 'edit']);
            $x->post('/admin/categories/{id}', [AdminCategoryController::class, 'update'], [VerifyCsrf::class]);
            $x->post('/admin/categories/{id}/delete', [AdminCategoryController::class, 'destroy'], [VerifyCsrf::class]);

            // Brands
            $x->get('/admin/brands', [AdminBrandController::class, 'index']);
            $x->get('/admin/brands/create', [AdminBrandController::class, 'create']);
            $x->post('/admin/brands', [AdminBrandController::class, 'store'], [VerifyCsrf::class]);
            $x->get('/admin/brands/{id}/edit', [AdminBrandController::class, 'edit']);
            $x->post('/admin/brands/{id}', [AdminBrandController::class, 'update'], [VerifyCsrf::class]);
            $x->post('/admin/brands/{id}/delete', [AdminBrandController::class, 'destroy'], [VerifyCsrf::class]);

            // Tags
            $x->get('/admin/tags', [AdminTagController::class, 'index']);
            $x->post('/admin/tags', [AdminTagController::class, 'store'], [VerifyCsrf::class]);
            $x->post('/admin/tags/{id}', [AdminTagController::class, 'update'], [VerifyCsrf::class]);
            $x->post('/admin/tags/{id}/delete', [AdminTagController::class, 'destroy'], [VerifyCsrf::class]);

            // Orders
            $x->get('/admin/orders', [AdminOrderController::class, 'index']);
            $x->get('/admin/orders/{id}', [AdminOrderController::class, 'show']);
            $x->post('/admin/orders/{id}/update', [AdminOrderController::class, 'update'], [VerifyCsrf::class]);

            // Customers
            $x->get('/admin/customers', [AdminCustomerController::class, 'index']);
            $x->get('/admin/customers/{id}', [AdminCustomerController::class, 'show']);

            // Menus
            $x->get('/admin/menus', [AdminMenuController::class, 'index']);
            $x->get('/admin/menus/{id}', [AdminMenuController::class, 'edit']);
            $x->post('/admin/menus', [AdminMenuController::class, 'store'], [VerifyCsrf::class]);
            $x->post('/admin/menus/{id}/items', [AdminMenuController::class, 'addItem'], [VerifyCsrf::class]);
            $x->post('/admin/menus/items/{id}/delete', [AdminMenuController::class, 'deleteItem'], [VerifyCsrf::class]);

            // Media library
            $x->get('/admin/media', [AdminMediaController::class, 'index']);
            $x->get('/admin/media/list', [AdminMediaController::class, 'listJson']);
            $x->post('/admin/media/upload', [AdminMediaController::class, 'upload'], [VerifyCsrf::class]);
            $x->post('/admin/media/delete', [AdminMediaController::class, 'delete'], [VerifyCsrf::class]);

            // Coupons
            $x->get('/admin/coupons', [AdminCouponController::class, 'index']);
            $x->get('/admin/coupons/create', [AdminCouponController::class, 'create']);
            $x->post('/admin/coupons', [AdminCouponController::class, 'store'], [VerifyCsrf::class]);
            $x->get('/admin/coupons/{id}/edit', [AdminCouponController::class, 'edit']);
            $x->post('/admin/coupons/{id}', [AdminCouponController::class, 'update'], [VerifyCsrf::class]);
            $x->post('/admin/coupons/{id}/delete', [AdminCouponController::class, 'destroy'], [VerifyCsrf::class]);

            // Popups
            $x->get('/admin/popups', [AdminPopupController::class, 'index']);
            $x->get('/admin/popups/create', [AdminPopupController::class, 'create']);
            $x->post('/admin/popups', [AdminPopupController::class, 'store'], [VerifyCsrf::class]);
            $x->get('/admin/popups/{id}/edit', [AdminPopupController::class, 'edit']);
            $x->post('/admin/popups/{id}', [AdminPopupController::class, 'update'], [VerifyCsrf::class]);
            $x->post('/admin/popups/{id}/delete', [AdminPopupController::class, 'destroy'], [VerifyCsrf::class]);

            // Banners
            $x->get('/admin/banners', [AdminBannerController::class, 'index']);
            $x->get('/admin/banners/create', [AdminBannerController::class, 'create']);
            $x->post('/admin/banners', [AdminBannerController::class, 'store'], [VerifyCsrf::class]);
            $x->get('/admin/banners/{id}/edit', [AdminBannerController::class, 'edit']);
            $x->post('/admin/banners/{id}', [AdminBannerController::class, 'update'], [VerifyCsrf::class]);
            $x->post('/admin/banners/{id}/delete', [AdminBannerController::class, 'destroy'], [VerifyCsrf::class]);

            // Shipping zones / methods
            $x->get('/admin/shipping', [AdminShippingController::class, 'index']);
            $x->post('/admin/shipping/settings', [AdminShippingController::class, 'saveSettings'], [VerifyCsrf::class]);
            $x->get('/admin/shipping/create', [AdminShippingController::class, 'create']);
            $x->post('/admin/shipping', [AdminShippingController::class, 'store'], [VerifyCsrf::class]);
            $x->get('/admin/shipping/{id}/edit', [AdminShippingController::class, 'edit']);
            $x->post('/admin/shipping/{id}', [AdminShippingController::class, 'update'], [VerifyCsrf::class]);
            $x->post('/admin/shipping/{id}/delete', [AdminShippingController::class, 'destroy'], [VerifyCsrf::class]);

            // Staff & roles (RBAC)
            $x->get('/admin/staff', [AdminStaffController::class, 'index']);
            $x->get('/admin/staff/create', [AdminStaffController::class, 'create']);
            $x->post('/admin/staff', [AdminStaffController::class, 'store'], [VerifyCsrf::class]);
            $x->get('/admin/staff/{id}/edit', [AdminStaffController::class, 'edit']);
            $x->post('/admin/staff/{id}', [AdminStaffController::class, 'update'], [VerifyCsrf::class]);
            $x->post('/admin/staff/{id}/delete', [AdminStaffController::class, 'destroy'], [VerifyCsrf::class]);

            // Blog / magazine
            $x->get('/admin/blog', [AdminBlogController::class, 'index']);
            $x->get('/admin/blog/create', [AdminBlogController::class, 'create']);
            $x->post('/admin/blog', [AdminBlogController::class, 'store'], [VerifyCsrf::class]);
            $x->get('/admin/blog/categories', [AdminBlogController::class, 'categories']);
            $x->post('/admin/blog/categories', [AdminBlogController::class, 'categoryStore'], [VerifyCsrf::class]);
            $x->post('/admin/blog/categories/{id}', [AdminBlogController::class, 'categoryStore'], [VerifyCsrf::class]);
            $x->post('/admin/blog/categories/{id}/delete', [AdminBlogController::class, 'categoryDelete'], [VerifyCsrf::class]);
            $x->get('/admin/blog/comments', [AdminBlogController::class, 'comments']);
            $x->post('/admin/blog/comments/{id}/moderate', [AdminBlogController::class, 'commentModerate'], [VerifyCsrf::class]);
            $x->get('/admin/blog/{id}/edit', [AdminBlogController::class, 'edit']);
            $x->post('/admin/blog/{id}', [AdminBlogController::class, 'update'], [VerifyCsrf::class]);
            $x->post('/admin/blog/{id}/delete', [AdminBlogController::class, 'destroy'], [VerifyCsrf::class]);

            // FAQ
            $x->get('/admin/faq', [AdminFaqController::class, 'index']);
            $x->post('/admin/faq', [AdminFaqController::class, 'store'], [VerifyCsrf::class]);
            $x->post('/admin/faq/{id}', [AdminFaqController::class, 'store'], [VerifyCsrf::class]);
            $x->post('/admin/faq/{id}/delete', [AdminFaqController::class, 'destroy'], [VerifyCsrf::class]);

            // Support tickets
            $x->get('/admin/tickets', [AdminTicketController::class, 'index']);
            $x->get('/admin/tickets/{id}', [AdminTicketController::class, 'show']);
            $x->post('/admin/tickets/{id}/reply', [AdminTicketController::class, 'reply'], [VerifyCsrf::class]);
            $x->post('/admin/tickets/{id}/status', [AdminTicketController::class, 'status'], [VerifyCsrf::class]);

            // Accounting export (Holoo / Mahak)
            $x->get('/admin/accounting', [AdminAccountingController::class, 'index']);
            $x->get('/admin/accounting/products.csv', [AdminAccountingController::class, 'exportProducts']);
            $x->get('/admin/accounting/orders.csv', [AdminAccountingController::class, 'exportOrders']);

            // SMS
            $x->get('/admin/sms', [AdminSmsController::class, 'index']);
            $x->post('/admin/sms/send', [AdminSmsController::class, 'send'], [VerifyCsrf::class]);
            $x->post('/admin/sms/templates', [AdminSmsController::class, 'templates'], [VerifyCsrf::class]);

            // Settings
            $x->get('/admin/settings', [AdminSettingController::class, 'index']);
            $x->post('/admin/settings', [AdminSettingController::class, 'update'], [VerifyCsrf::class]);
        });
    });
};
