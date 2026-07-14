<?php

declare(strict_types=1);

namespace App\Controllers\Storefront;

use App\Controllers\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\OrderRepository;
use App\Repositories\ReviewRepository;
use App\Services\AuthService;
use App\Services\CatalogService;

final class ProductController extends Controller
{
    public function show(Request $request): Response
    {
        $slug = (string) $request->param('slug');
        $data = (new CatalogService())->product($slug);

        if ($data === null) {
            return $this->notFound();
        }

        return $this->view('storefront/product', $data);
    }

    /** Logged-in customers submit a review; it awaits admin approval. */
    public function review(Request $request): Response
    {
        $slug = (string) $request->param('slug');
        $back = url('/product/' . $slug) . '#reviews';

        $data = (new CatalogService())->product($slug);
        if ($data === null) {
            return $this->notFound();
        }
        $product = $data['product'];

        if (!AuthService::check()) {
            Session::flash('error', 'برای ثبت دیدگاه ابتدا وارد حساب خود شوید.');
            return $this->redirect(url('/login?redirect=' . rawurlencode('/product/' . $slug)));
        }

        $userId = (int) AuthService::id();
        $rating = min(5, max(1, (int) en_num((string) $request->input('rating', 5))));
        $body   = trim((string) $request->input('body', ''));

        if (mb_strlen($body) < 5) {
            Session::flash('error', 'متن دیدگاه خیلی کوتاه است.');
            return $this->redirect($back);
        }

        $reviews = new ReviewRepository();
        if ($reviews->existsForUser((int) $product['id'], $userId)) {
            Session::flash('error', 'شما قبلاً برای این محصول دیدگاه ثبت کرده‌اید.');
            return $this->redirect($back);
        }

        $user   = AuthService::user();
        $author = trim((string) ($user['first_name'] ?? '') . ' ' . (string) ($user['last_name'] ?? '')) ?: 'کاربر بهنام';
        $bought = (new OrderRepository())->userPurchasedProduct($userId, (int) $product['id']);

        $reviews->create((int) $product['id'], $userId, $author, $rating, $body, $bought);
        Session::flash('success', 'دیدگاه شما ثبت شد و پس از تایید مدیر نمایش داده می‌شود. سپاسگزاریم 🌸');
        return $this->redirect($back);
    }
}
