<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\AdminReportRepository;

final class DashboardController extends AdminController
{
    public function index(Request $request): Response
    {
        $data = (new AdminReportRepository())->dashboard();

        return $this->adminView('admin/dashboard', $data, 'داشبورد');
    }
}
