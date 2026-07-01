<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\AdminAuthService;

final class AuthController extends Controller
{
    public function showLogin(Request $request): Response
    {
        if (AdminAuthService::check()) {
            return $this->redirect(url('/admin'));
        }
        return $this->view('admin/login', ['meta' => ['title' => 'ورود به پنل مدیریت']], null);
    }

    public function login(Request $request): Response
    {
        $username = trim((string) $request->input('username', ''));
        $password = (string) $request->input('password', '');

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
