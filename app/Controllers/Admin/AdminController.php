<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\AuditLogRepository;
use App\Services\AdminAuthService;

/**
 * Base for admin controllers: renders with the admin layout, enforces
 * capabilities, and records audit-log entries.
 */
abstract class AdminController extends Controller
{
    /** @param array<string,mixed> $data */
    protected function adminView(string $template, array $data = [], string $title = 'پنل مدیریت'): Response
    {
        $data['meta'] = ['title' => $title];
        return $this->view($template, $data, 'admin');
    }

    /**
     * Ensure the current admin has a capability; returns a redirect Response
     * to block, or null to proceed.
     */
    protected function guard(string $capability): ?Response
    {
        if (!AdminAuthService::can($capability)) {
            \App\Core\Session::flash('error', 'شما به این بخش دسترسی ندارید.');
            return $this->redirect(url('/admin'));
        }
        return null;
    }

    protected function audit(Request $request, string $action, string $entity, ?int $entityId = null, string $meta = ''): void
    {
        (new AuditLogRepository())->log(AdminAuthService::id(), $action, $entity, $entityId, $meta, $request->ip());
    }
}
