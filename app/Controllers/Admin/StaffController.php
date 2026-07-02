<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\AdminUserRepository;
use App\Services\AdminAuthService;

/**
 * Staff & role management (granular RBAC). Super-admin only.
 */
final class StaffController extends AdminController
{
    private const ROLES = ['super' => 'مدیر کل', 'manager' => 'مدیر', 'editor' => 'ویرایشگر'];

    private AdminUserRepository $repo;

    public function __construct()
    {
        $this->repo = new AdminUserRepository();
    }

    public function index(Request $request): Response
    {
        if ($r = $this->guard('staff')) {
            return $r;
        }
        return $this->adminView('admin/staff/index', [
            'items' => $this->repo->all(),
            'roles' => self::ROLES,
        ], 'کاربران مدیریت');
    }

    public function create(Request $request): Response
    {
        if ($r = $this->guard('staff')) {
            return $r;
        }
        return $this->form(null);
    }

    public function edit(Request $request): Response
    {
        if ($r = $this->guard('staff')) {
            return $r;
        }
        $item = $this->repo->find((int) $request->param('id'));
        if ($item === null) {
            return $this->notFound();
        }
        return $this->form($item);
    }

    public function store(Request $request): Response
    {
        if ($r = $this->guard('staff')) {
            return $r;
        }
        $username = $this->username($request);
        $password = (string) $request->input('password', '');
        $name     = trim((string) $request->input('name', ''));

        if ($username === '' || $name === '') {
            Session::flash('error', 'نام و نام کاربری الزامی است.');
            return $this->redirect(url('/admin/staff/create'));
        }
        if (strlen($password) < 6) {
            Session::flash('error', 'رمز عبور باید حداقل ۶ کاراکتر باشد.');
            return $this->redirect(url('/admin/staff/create'));
        }
        if ($this->repo->usernameExists($username, 0)) {
            Session::flash('error', 'این نام کاربری قبلاً ثبت شده است.');
            return $this->redirect(url('/admin/staff/create'));
        }

        $data = $this->collect($request, $username);
        $data['password_hash'] = password_hash($password, PASSWORD_BCRYPT);
        $id = $this->repo->insert($data);
        $this->audit($request, 'create', 'admin_user', $id, $username);
        Session::flash('success', 'کاربر مدیریت ایجاد شد.');
        return $this->redirect(url('/admin/staff'));
    }

    public function update(Request $request): Response
    {
        if ($r = $this->guard('staff')) {
            return $r;
        }
        $id   = (int) $request->param('id');
        $item = $this->repo->find($id);
        if ($item === null) {
            return $this->notFound();
        }

        $username = $this->username($request);
        $name     = trim((string) $request->input('name', ''));
        if ($username === '' || $name === '') {
            Session::flash('error', 'نام و نام کاربری الزامی است.');
            return $this->redirect(url('/admin/staff/' . $id . '/edit'));
        }
        if ($this->repo->usernameExists($username, $id)) {
            Session::flash('error', 'این نام کاربری برای کاربر دیگری ثبت شده است.');
            return $this->redirect(url('/admin/staff/' . $id . '/edit'));
        }

        $data = $this->collect($request, $username);

        // Guard: don't demote or deactivate the last active super-admin.
        $wasSuper = (string) $item['role'] === 'super' && (int) $item['is_active'] === 1;
        $staysSuper = $data['role'] === 'super' && $data['is_active'] === 1;
        if ($wasSuper && !$staysSuper && $this->repo->activeSuperCount() <= 1) {
            Session::flash('error', 'حداقل یک مدیر کل فعال باید باقی بماند.');
            return $this->redirect(url('/admin/staff/' . $id . '/edit'));
        }

        $this->repo->update($id, $data);

        $password = (string) $request->input('password', '');
        if ($password !== '') {
            if (strlen($password) < 6) {
                Session::flash('error', 'رمز عبور باید حداقل ۶ کاراکتر باشد؛ بقیه تغییرات ذخیره شد.');
                return $this->redirect(url('/admin/staff/' . $id . '/edit'));
            }
            $this->repo->updatePassword($id, password_hash($password, PASSWORD_BCRYPT));
        }

        $this->audit($request, 'update', 'admin_user', $id, $username);
        Session::flash('success', 'تغییرات ذخیره شد.');
        return $this->redirect(url('/admin/staff/' . $id . '/edit'));
    }

    public function destroy(Request $request): Response
    {
        if ($r = $this->guard('staff')) {
            return $r;
        }
        $id   = (int) $request->param('id');
        $item = $this->repo->find($id);
        if ($item === null) {
            return $this->notFound();
        }
        if ($id === AdminAuthService::id()) {
            Session::flash('error', 'نمی‌توانید حساب خودتان را حذف کنید.');
            return $this->redirect(url('/admin/staff'));
        }
        if ((string) $item['role'] === 'super' && (int) $item['is_active'] === 1 && $this->repo->activeSuperCount() <= 1) {
            Session::flash('error', 'حداقل یک مدیر کل فعال باید باقی بماند.');
            return $this->redirect(url('/admin/staff'));
        }
        $this->repo->delete($id);
        $this->audit($request, 'delete', 'admin_user', $id, (string) $item['username']);
        Session::flash('success', 'کاربر حذف شد.');
        return $this->redirect(url('/admin/staff'));
    }

    /** @param array<string,mixed>|null $item */
    private function form(?array $item): Response
    {
        return $this->adminView('admin/staff/form', [
            'item'    => $item,
            'roles'   => self::ROLES,
            'allCaps' => AdminAuthService::ALL_CAPS,
            'checked' => $item !== null ? AdminAuthService::effectiveCaps($item) : [],
            'custom'  => $item !== null && trim((string) ($item['capabilities'] ?? '')) !== '',
        ], $item ? 'ویرایش کاربر' : 'کاربر مدیریت جدید');
    }

    private function username(Request $request): string
    {
        return preg_replace('/[^a-z0-9_.-]/i', '', trim((string) $request->input('username', ''))) ?? '';
    }

    /** @return array<string,mixed> */
    private function collect(Request $request, string $username): array
    {
        $role = (string) $request->input('role', 'editor');
        if (!array_key_exists($role, self::ROLES)) {
            $role = 'editor';
        }

        // Custom capability override applies only to non-super roles.
        $capabilities = null;
        if ($role !== 'super' && $request->input('custom_caps')) {
            $selected = (array) $request->input('caps', []);
            $valid = array_values(array_filter(
                array_map('strval', $selected),
                static fn (string $c): bool => array_key_exists($c, AdminAuthService::ALL_CAPS)
            ));
            $capabilities = $valid === [] ? null : implode(',', $valid);
        }

        return [
            'username'     => $username,
            'name'         => trim((string) $request->input('name', '')),
            'email'        => trim((string) $request->input('email', '')) ?: null,
            'role'         => $role,
            'capabilities' => $capabilities,
            'is_active'    => $request->input('is_active') ? 1 : 0,
        ];
    }
}
