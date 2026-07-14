<?php

declare(strict_types=1);

namespace App\Controllers\Storefront;

use App\Controllers\Controller;
use App\Core\RateLimiter;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use App\Services\OtpService;

final class AuthController extends Controller
{
    public function show(Request $request): Response
    {
        if (AuthService::check()) {
            return $this->redirect(url('/account'));
        }
        $redirect = (string) $request->query('redirect', '/account');
        return $this->view('storefront/login', ['redirect' => $redirect], 'storefront');
    }

    public function sendOtp(Request $request): Response
    {
        $mobile = en_num((string) $request->input('mobile', ''));
        if (!preg_match('/^09\d{9}$/', $mobile)) {
            return $this->json(['ok' => false, 'error' => 'شماره موبایل معتبر نیست.'], 422);
        }

        // Per-IP flood guard: OtpService already throttles per mobile, but a
        // single IP could still SMS-bomb many DIFFERENT numbers.
        if (!(new RateLimiter())->attempt('otp_send', $request->ip(), 5, 600)) {
            return $this->json(['ok' => false, 'error' => 'درخواست‌های زیاد؛ چند دقیقه بعد دوباره تلاش کنید.'], 429);
        }

        Session::set('login_mobile', $mobile);
        $result = (new OtpService())->send($mobile, 'login');
        return $this->json($result, $result['ok'] ? 200 : 429);
    }

    public function verify(Request $request): Response
    {
        $mobile = (string) Session::get('login_mobile', '');
        if ($mobile === '') {
            return $this->json(['ok' => false, 'error' => 'نشست منقضی شده است.'], 419);
        }

        $code = en_num((string) $request->input('code', ''));
        $otp  = (new OtpService())->verify($mobile, $code, 'login');
        if (!$otp['ok']) {
            return $this->json(['ok' => false, 'error' => $otp['message']], 422);
        }

        $users = new UserRepository();
        $user  = $users->findByMobile($mobile);
        $userId = $user !== null ? (int) $user['id'] : $users->create($mobile);

        AuthService::login($userId);
        Session::forget('login_mobile');

        // Only same-site paths: "//evil.com" and "/\evil.com" are protocol-
        // relative URLs browsers would follow off-site (open redirect).
        $redirect = (string) $request->input('redirect', '/account');
        if (!str_starts_with($redirect, '/') || str_starts_with($redirect, '//') || str_starts_with($redirect, '/\\')) {
            $redirect = '/account';
        }

        return $this->json(['ok' => true, 'redirect' => url($redirect)]);
    }

    public function logout(Request $request): Response
    {
        AuthService::logout();
        return $this->redirect(url('/'));
    }
}
