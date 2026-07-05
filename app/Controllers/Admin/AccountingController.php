<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\SettingsRepository;
use App\Services\AccountingService;
use App\Services\Accounting\AccountingSyncService;

final class AccountingController extends AdminController
{
    public function index(Request $request): Response
    {
        if ($r = $this->guard('accounting')) {
            return $r;
        }
        $sync = new AccountingSyncService();
        return $this->adminView('admin/accounting/index', [
            'counts'       => (new AccountingService())->counts(),
            'apiKey'       => (string) setting('integration_api_key', ''),
            'torobEnabled' => (bool) setting('torob_enabled', true),
            'torobJson'    => abs_url('torob.json'),
            'torobXml'     => abs_url('torob.xml'),
            'apiBase'      => abs_url('api/integration'),
            'syncDriver'   => $sync->driverName(),
            'syncReady'    => $sync->isConfigured(),
        ], 'حسابداری و یکپارچه‌سازی');
    }

    /** Generate/rotate the integration API key (for accounting software). */
    public function regenerateKey(Request $request): Response
    {
        if ($r = $this->guard('accounting')) {
            return $r;
        }
        $key = bin2hex(random_bytes(24));
        (new SettingsRepository())->set('integration_api_key', $key, 'string');
        $this->audit($request, 'update', 'integration', null, 'api_key');
        Session::flash('success', 'کلید API جدید ساخته شد.');
        return $this->redirect(url('/admin/accounting'));
    }

    /** Toggle the public Torob feed. */
    public function saveTorob(Request $request): Response
    {
        if ($r = $this->guard('accounting')) {
            return $r;
        }
        (new SettingsRepository())->set('torob_enabled', $request->input('torob_enabled') ? '1' : '0', 'bool');
        $this->audit($request, 'update', 'integration', null, 'torob');
        Session::flash('success', 'تنظیمات ترب ذخیره شد.');
        return $this->redirect(url('/admin/accounting'));
    }

    /** Pull stock/price from the configured accounting web service (هلو/محک). */
    public function sync(Request $request): Response
    {
        if ($r = $this->guard('accounting')) {
            return $r;
        }
        $res = (new AccountingSyncService())->syncProducts();
        $this->audit($request, 'sync', 'accounting', null, $res['ok'] ? 'ok' : 'fail');
        if ($res['ok']) {
            Session::flash('success', 'همگام‌سازی انجام شد: ' . fa((int) ($res['updated'] ?? 0)) . ' کالا به‌روزرسانی شد.');
        } else {
            Session::flash('error', $res['error'] ?? 'همگام‌سازی ناموفق بود.');
        }
        return $this->redirect(url('/admin/accounting'));
    }

    public function exportProducts(Request $request): Response
    {
        if ($r = $this->guard('accounting')) {
            return $r;
        }
        $csv = (new AccountingService())->productsCsv();
        $this->audit($request, 'export', 'accounting', null, 'products');
        return $this->download($csv, 'behnam-products-' . date('Ymd') . '.csv');
    }

    public function exportOrders(Request $request): Response
    {
        if ($r = $this->guard('accounting')) {
            return $r;
        }
        $paidOnly = $request->query('all') !== '1';
        $csv = (new AccountingService())->ordersCsv($paidOnly);
        $this->audit($request, 'export', 'accounting', null, $paidOnly ? 'orders_paid' : 'orders_all');
        return $this->download($csv, 'behnam-orders-' . date('Ymd') . '.csv');
    }

    private function download(string $body, string $filename): Response
    {
        return (new Response())
            ->status(200)
            ->header('Content-Type', 'text/csv; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Cache-Control', 'no-store')
            ->body($body);
    }
}
