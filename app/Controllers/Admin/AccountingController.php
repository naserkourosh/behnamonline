<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Services\AccountingService;

final class AccountingController extends AdminController
{
    public function index(Request $request): Response
    {
        if ($r = $this->guard('accounting')) {
            return $r;
        }
        return $this->adminView('admin/accounting/index', [
            'counts' => (new AccountingService())->counts(),
        ], 'حسابداری');
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
