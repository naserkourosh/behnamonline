<?php

declare(strict_types=1);

namespace App\Controllers\Storefront;

use App\Controllers\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\PageRepository;
use App\Services\ChatService;

/**
 * Static-ish pages: درباره ما and تماس با ما. Their content comes from the
 * settings table (editable in پنل ادمین → تنظیمات); the contact form feeds
 * the live-chat inbox so the admin answers from گفتگوی آنلاین.
 */
final class PageController extends Controller
{
    /** CMS page (مدیریت صفحات) served at /page/{slug}. */
    public function show(Request $request): Response
    {
        $page = (new PageRepository())->findActiveBySlug((string) $request->param('slug'));
        if ($page === null) {
            return $this->notFound();
        }
        return $this->view('storefront/page', ['page' => $page], 'storefront');
    }

    public function about(Request $request): Response
    {
        return $this->view('storefront/about', [], 'storefront');
    }

    public function contact(Request $request): Response
    {
        return $this->view('storefront/contact', [], 'storefront');
    }

    public function contactSubmit(Request $request): Response
    {
        // Flood guard: the form feeds the live-chat inbox — cap per IP.
        if (!(new \App\Core\RateLimiter())->attempt('contact_form', $request->ip(), 5, 600)) {
            Session::flash('error', 'تعداد پیام‌های ارسالی زیاد است؛ لطفاً چند دقیقه بعد دوباره تلاش کنید.');
            return $this->redirect(url('/contact'));
        }

        $name    = trim((string) $request->input('name', ''));
        $mobile  = preg_replace('/\D+/', '', en_num((string) $request->input('mobile', ''))) ?? '';
        $message = trim((string) $request->input('message', ''));

        if ($name === '' || mb_strlen($message) < 5) {
            Session::flash('error', 'نام و متن پیام را کامل وارد کنید.');
            return $this->redirect(url('/contact'));
        }

        $body = "📮 پیام از فرم تماس با ما\nنام: {$name}" .
                ($mobile !== '' ? "\nموبایل: {$mobile}" : '') .
                "\n———\n{$message}";
        (new ChatService())->send(mb_substr($body, 0, 1900), $name);

        Session::flash('success', 'پیام شما دریافت شد؛ در اولین فرصت از طریق همین صفحه یا گفتگوی آنلاین پاسخ می‌دهیم. 🌸');
        return $this->redirect(url('/contact'));
    }
}
