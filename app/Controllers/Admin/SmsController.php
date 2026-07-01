<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\SmsMessageRepository;
use App\Repositories\SmsTemplateRepository;
use App\Services\Sms\SmsManager;

final class SmsController extends AdminController
{
    private SmsMessageRepository $messages;

    public function __construct()
    {
        $this->messages = new SmsMessageRepository();
    }

    public function index(Request $request): Response
    {
        if ($r = $this->guard('sms')) {
            return $r;
        }
        $filters = [
            'kind'   => (string) $request->query('kind', ''),
            'search' => trim((string) $request->query('q', '')),
        ];
        $perPage = 25;
        $page    = max(1, (int) $request->query('page', 1));
        $total   = $this->messages->count($filters);

        return $this->adminView('admin/sms/index', [
            'items'     => $this->messages->recent($filters, $perPage, ($page - 1) * $perPage),
            'templates' => (new SmsTemplateRepository())->all(),
            'filters'   => $filters,
            'driver'    => (string) \App\Core\Config::get('sms.driver', 'mock'),
            'total'     => $total,
            'page'      => $page,
            'pages'     => (int) ceil($total / $perPage),
        ], 'پیامک‌ها');
    }

    public function send(Request $request): Response
    {
        if ($r = $this->guard('sms')) {
            return $r;
        }
        $mobile  = preg_replace('/\D+/', '', en_num((string) $request->input('mobile', '')));
        $message = trim((string) $request->input('message', ''));

        if (!preg_match('/^09\d{9}$/', (string) $mobile)) {
            Session::flash('error', 'شمارهٔ موبایل نامعتبر است (مثال: 09121234567).');
            return $this->redirect(url('/admin/sms'));
        }
        if ($message === '') {
            Session::flash('error', 'متن پیامک را وارد کنید.');
            return $this->redirect(url('/admin/sms'));
        }

        $ok = (new SmsManager())->send((string) $mobile, $message, 'manual');
        $this->audit($request, 'send', 'sms', null, (string) $mobile);
        Session::flash($ok ? 'success' : 'error', $ok ? 'پیامک ارسال شد.' : 'ارسال پیامک ناموفق بود.');
        return $this->redirect(url('/admin/sms'));
    }

    public function templates(Request $request): Response
    {
        if ($r = $this->guard('sms')) {
            return $r;
        }
        $repo = new SmsTemplateRepository();
        foreach ($repo->all() as $tpl) {
            $key  = (string) $tpl['tkey'];
            $body = trim((string) $request->input('body_' . $key, (string) $tpl['body']));
            $on   = (bool) $request->input('active_' . $key);
            if ($body !== '') {
                $repo->update($key, $body, $on);
            }
        }
        $this->audit($request, 'update', 'sms_templates');
        Session::flash('success', 'قالب‌های پیامک ذخیره شد.');
        return $this->redirect(url('/admin/sms'));
    }
}
