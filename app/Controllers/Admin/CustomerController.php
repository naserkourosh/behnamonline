<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\AddressRepository;
use App\Repositories\OrderRepository;
use App\Repositories\UserRepository;

final class CustomerController extends AdminController
{
    private UserRepository $users;

    public function __construct()
    {
        $this->users = new UserRepository();
    }

    public function index(Request $request): Response
    {
        if ($r = $this->guard('customers')) {
            return $r;
        }
        $search  = trim((string) $request->query('q', ''));
        $perPage = 20;
        $page    = max(1, (int) $request->query('page', 1));
        $total   = $this->users->adminCount($search);

        return $this->adminView('admin/customers/index', [
            'items'  => $this->users->adminList($search, $perPage, ($page - 1) * $perPage),
            'total'  => $total,
            'page'   => $page,
            'pages'  => (int) ceil($total / $perPage),
            'search' => $search,
        ], 'مشتریان');
    }

    public function show(Request $request): Response
    {
        if ($r = $this->guard('customers')) {
            return $r;
        }
        $user = $this->users->find((int) $request->param('id'));
        if ($user === null) {
            return $this->notFound();
        }
        return $this->adminView('admin/customers/show', [
            'user'      => $user,
            'orders'    => (new OrderRepository())->forUser((int) $user['id'], 50),
            'addresses' => (new AddressRepository())->forUser((int) $user['id']),
        ], 'مشتری');
    }
}
