<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\RateLimiter;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\AdminAuthService;
use App\Services\CaptchaService;

final class AuthController extends Controller
{
    public function showLogin(Request $request): Response
    {
        if (AdminAuthService::check()) {
            return $this->redirect(url('/admin'));
        }
        return $this->view('admin/login', ['meta' => ['title' => 'ورود به پنل مدیریت']], null);
    }

    /** GET /admin/captcha — the login CAPTCHA image (no-store). */
    public function captcha(Request $request): Response
    {
        return (new Response())
            ->header('Content-Type', 'image/png')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->body(CaptchaService::png());
    }

    public function login(Request $request): Response
    {
        $username = trim((string) $request->input('username', ''));
        $password = (string) $request->input('password', '');

        // Brute-force throttle: 6 attempts / 10 minutes per IP.
        $limiter = new RateLimiter();
        if (!$limiter->attempt('admin_login', $request->ip(), 6, 600)) {
            Session::flash('error', 'تعداد تلاش‌های ناموفق زیاد است. چند دقیقه بعد دوباره تلاش کنید.');
            return $this->redirect(url('/admin/login'));
        }

        // CAPTCHA (one-shot; consumed on every attempt).
        if (!CaptchaService::verify((string) $request->input('captcha', ''))) {
            Session::flash('error', 'کد امنیتی نادرست است.');
            Session::flash('__old', ['username' => $username]);
            return $this->redirect(url('/admin/login'));
        }

        $result = AdminAuthService::attempt($username, $password);
        if (!$result['ok']) {
            Session::flash('error', $result['message'] ?? 'ورود ناموفق بود.');
            Session::flash('__old', ['username' => $username]);
            return $this->redirect(url('/admin/login'));
        }

        $intended = Session::get('admin_intended');
        Session::forget('admin_intended');
        return $this->redirect(url(is_string($intended) ? $intended : '/admin'));
    }

    public function logout(Request $request): Response
    {
        AdminAuthService::logout();
        return $this->redirect(url('/admin/login'));
    }
}
