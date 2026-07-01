<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\TicketRepository;
use App\Services\AdminAuthService;

final class TicketController extends AdminController
{
    private TicketRepository $tickets;

    public function __construct()
    {
        $this->tickets = new TicketRepository();
    }

    public function index(Request $request): Response
    {
        if ($r = $this->guard('support')) {
            return $r;
        }
        $status  = (string) $request->query('status', '');
        $perPage = 20;
        $page    = max(1, (int) $request->query('page', 1));
        $total   = $this->tickets->adminCount($status);

        return $this->adminView('admin/tickets/index', [
            'items'  => $this->tickets->adminList($status, $perPage, ($page - 1) * $perPage),
            'status' => $status,
            'total'  => $total,
            'page'   => $page,
            'pages'  => (int) ceil($total / $perPage),
        ], 'تیکت‌های پشتیبانی');
    }

    public function show(Request $request): Response
    {
        if ($r = $this->guard('support')) {
            return $r;
        }
        $ticket = $this->tickets->findAny((int) $request->param('id'));
        if ($ticket === null) {
            return $this->notFound();
        }
        return $this->adminView('admin/tickets/show', [
            'ticket'   => $ticket,
            'messages' => $this->tickets->messages((int) $ticket['id']),
        ], 'تیکت #' . $ticket['id']);
    }

    public function reply(Request $request): Response
    {
        if ($r = $this->guard('support')) {
            return $r;
        }
        $id     = (int) $request->param('id');
        $ticket = $this->tickets->findAny($id);
        if ($ticket === null) {
            return $this->notFound();
        }
        $body = trim((string) $request->input('body', ''));
        if (mb_strlen($body) >= 2) {
            $this->tickets->addMessage($id, 'admin', AdminAuthService::id(), mb_substr($body, 0, 3000));
            $this->audit($request, 'reply', 'ticket', $id);
        }
        if ($request->input('close')) {
            $this->tickets->setStatus($id, 'closed');
        }
        Session::flash('success', 'پاسخ ثبت شد.');
        return $this->redirect(url('/admin/tickets/' . $id));
    }

    public function status(Request $request): Response
    {
        if ($r = $this->guard('support')) {
            return $r;
        }
        $id     = (int) $request->param('id');
        $status = (string) $request->input('status', '');
        $this->tickets->setStatus($id, $status);
        $this->audit($request, 'status', 'ticket', $id, $status);
        Session::flash('success', 'وضعیت تیکت تغییر کرد.');
        return $this->redirect(url('/admin/tickets/' . $id));
    }
}
