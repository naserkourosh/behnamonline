<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\AdminReportRepository;

/**
 * Sales & analytics reports for a chosen date range.
 * Read-only; scoped to the `reports` capability.
 */
final class ReportController extends AdminController
{
    /** Quick presets: label => days back (inclusive of today). */
    private const PRESETS = [
        '7'   => '۷ روز اخیر',
        '30'  => '۳۰ روز اخیر',
        '90'  => '۹۰ روز اخیر',
        '365' => 'یک سال اخیر',
    ];

    public function index(Request $request): Response
    {
        if ($r = $this->guard('reports')) {
            return $r;
        }

        $preset = (string) $request->query('range', '30');
        $from   = $this->normalizeDate((string) $request->query('from', ''));
        $to     = $this->normalizeDate((string) $request->query('to', ''));

        // Explicit from/to wins; otherwise fall back to a preset window.
        if ($from === null || $to === null) {
            $days = array_key_exists($preset, self::PRESETS) ? (int) $preset : 30;
            $to   = date('Y-m-d');
            $from = date('Y-m-d', strtotime('-' . ($days - 1) . ' days'));
        } elseif ($from > $to) {
            [$from, $to] = [$to, $from];
        }

        $data = (new AdminReportRepository())->report($from, $to);
        $data['presets'] = self::PRESETS;
        $data['preset']  = $preset;

        return $this->adminView('admin/reports/index', $data, 'گزارش‌ها و آمار');
    }

    private function normalizeDate(string $v): ?string
    {
        $v = trim(en_num($v));
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) ? $v : null;
    }
}
