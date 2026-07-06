<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\SettingsRepository;
use App\Repositories\SmsCampaignRepository;
use App\Repositories\SmsMessageRepository;
use App\Repositories\SmsTemplateRepository;
use App\Services\AdminAuthService;
use App\Services\Sms\SmsConfig;
use App\Services\Sms\SmsManager;

final class SmsController extends AdminController
{
    /** Safety cap on a single campaign (protects PHP runtime + panel credit). */
    private const MAX_RECIPIENTS = 10000;

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

        $campaigns = new SmsCampaignRepository();

        return $this->adminView('admin/sms/index', [
            'items'      => $this->messages->recent($filters, $perPage, ($page - 1) * $perPage),
            'templates'  => (new SmsTemplateRepository())->all(),
            'campaigns'  => $campaigns->recent(8),
            'audiences'  => SmsCampaignRepository::AUDIENCES,
            'filters'    => $filters,
            'driver'     => SmsConfig::driver(),
            'config'     => [
                'username' => SmsConfig::username(),
                'password' => SmsConfig::password() !== '',
                'from'     => SmsConfig::from(),
                'body_id'  => SmsConfig::otpBodyId(),
            ],
            'total'      => $total,
            'page'       => $page,
            'pages'      => (int) ceil($total / $perPage),
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

    /** Group/promotional send to a selected audience. */
    public function campaign(Request $request): Response
    {
        if ($r = $this->guard('sms')) {
            return $r;
        }
        $repo     = new SmsCampaignRepository();
        $audience = (string) $request->input('audience', 'all');
        if (!isset(SmsCampaignRepository::AUDIENCES[$audience])) {
            $audience = 'all';
        }
        $message = trim((string) $request->input('message', ''));
        if ($message === '') {
            Session::flash('error', 'متن پیامک را وارد کنید.');
            return $this->redirect(url('/admin/sms'));
        }

        $mobiles = $audience === 'custom'
            ? $this->parseNumbers((string) $request->input('numbers', ''))
            : $repo->mobilesFor($audience);

        if ($mobiles === []) {
            Session::flash('error', 'هیچ گیرندهٔ معتبری برای این گروه یافت نشد.');
            return $this->redirect(url('/admin/sms'));
        }
        if (count($mobiles) > self::MAX_RECIPIENTS) {
            Session::flash('error', 'تعداد گیرندگان بیش از سقف مجاز (' . fa(self::MAX_RECIPIENTS) . ') است.');
            return $this->redirect(url('/admin/sms'));
        }

        $title = trim((string) $request->input('title', ''))
            ?: 'کمپین ' . jdate(date('Y-m-d H:i:s'), 'Y/m/d H:i');

        // Chunked provider calls; a few thousand recipients ≈ seconds, but
        // don't let PHP's default 30s kill a large campaign midway.
        @set_time_limit(600);

        $campaignId = $repo->create($title, $message, $audience, count($mobiles), AdminAuthService::id());
        $result     = (new SmsManager())->sendBulk($mobiles, $message, 'campaign', $campaignId);
        $repo->finish($campaignId, $result['sent'], $result['failed']);

        $this->audit($request, 'send', 'sms_campaign', $campaignId, "audience={$audience} sent={$result['sent']} failed={$result['failed']}");
        Session::flash(
            $result['failed'] === 0 ? 'success' : 'error',
            'ارسال گروهی انجام شد: ' . fa($result['sent']) . ' موفق' . ($result['failed'] > 0 ? '، ' . fa($result['failed']) . ' ناموفق (جزئیات در storage/logs/sms.log)' : '') . '.'
        );
        return $this->redirect(url('/admin/sms'));
    }

    /** AJAX: recipient count for an audience (live preview in the form). */
    public function audienceCount(Request $request): Response
    {
        if (!AdminAuthService::can('sms')) {
            return $this->json(['ok' => false], 403);
        }
        $audience = (string) $request->query('audience', 'all');
        if (!isset(SmsCampaignRepository::AUDIENCES[$audience]) || $audience === 'custom') {
            return $this->json(['ok' => true, 'count' => 0]);
        }
        return $this->json(['ok' => true, 'count' => (new SmsCampaignRepository())->countFor($audience)]);
    }

    /** AJAX: remaining Melipayamak panel credit. */
    public function credit(Request $request): Response
    {
        if (!AdminAuthService::can('sms')) {
            return $this->json(['ok' => false], 403);
        }
        $manager = new SmsManager();
        $credit  = $manager->driverName() === 'melipayamak' ? $manager->credit() : null;
        return $this->json(['ok' => true, 'driver' => $manager->driverName(), 'credit' => $credit]);
    }

    /** Save panel-connection settings to the DB (overrides .env). */
    public function settings(Request $request): Response
    {
        if ($r = $this->guard('sms')) {
            return $r;
        }
        $repo = new SettingsRepository();

        $driver = (string) $request->input('sms_driver', 'mock');
        $repo->set('sms_driver', in_array($driver, ['mock', 'melipayamak'], true) ? $driver : 'mock', 'string');
        $repo->set('sms_username', trim((string) $request->input('sms_username', '')), 'string');
        $repo->set('sms_from', preg_replace('/\D+/', '', en_num((string) $request->input('sms_from', ''))) ?? '', 'string');
        $repo->set('sms_otp_body_id', preg_replace('/\D+/', '', en_num((string) $request->input('sms_otp_body_id', ''))) ?? '', 'string');

        // Password is write-only: an empty field means "keep the current one".
        $password = (string) $request->input('sms_password', '');
        if ($password !== '') {
            $repo->set('sms_password', $password, 'string');
        }

        $this->audit($request, 'update', 'sms_settings');
        Session::flash('success', 'تنظیمات اتصال پیامک ذخیره شد.');
        return $this->redirect(url('/admin/sms'));
    }

    public function templates(Request $request): Response
    {
        if ($r = $this->guard('sms')) {
            return $r;
        }
        $repo = new SmsTemplateRepository();
        foreach ($repo->all() as $tpl) {
            $key     = (string) $tpl['tkey'];
            $body    = trim((string) $request->input('body_' . $key, (string) $tpl['body']));
            $on      = (bool) $request->input('active_' . $key);
            $pattern = preg_replace('/\D+/', '', en_num((string) $request->input('pattern_' . $key, ''))) ?? '';
            if ($body !== '') {
                $repo->update($key, $body, $on, $pattern);
            }
        }
        $this->audit($request, 'update', 'sms_templates');
        Session::flash('success', 'قالب‌های پیامک ذخیره شد.');
        return $this->redirect(url('/admin/sms'));
    }

    /**
     * Parse pasted numbers (comma / newline / space separated, Persian digits
     * OK) into a deduped list of valid 09xxxxxxxxx mobiles.
     * @return list<string>
     */
    private function parseNumbers(string $raw): array
    {
        $out = [];
        foreach (preg_split('/[\s,،;]+/u', en_num($raw)) ?: [] as $token) {
            $n = preg_replace('/\D+/', '', $token);
            if (preg_match('/^09\d{9}$/', (string) $n)) {
                $out[(string) $n] = true;
            }
        }
        return array_keys($out);
    }
}
