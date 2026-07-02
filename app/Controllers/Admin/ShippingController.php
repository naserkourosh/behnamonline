<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\ShippingZoneRepository;

final class ShippingController extends AdminController
{
    private ShippingZoneRepository $repo;

    public function __construct()
    {
        $this->repo = new ShippingZoneRepository();
    }

    public function index(Request $request): Response
    {
        if ($r = $this->guard('shipping')) {
            return $r;
        }
        return $this->adminView('admin/shipping/index', ['items' => $this->repo->all()], 'ارسال و مناطق');
    }

    public function create(Request $request): Response
    {
        if ($r = $this->guard('shipping')) {
            return $r;
        }
        return $this->adminView('admin/shipping/form', ['item' => null], 'روش ارسال جدید');
    }

    public function edit(Request $request): Response
    {
        if ($r = $this->guard('shipping')) {
            return $r;
        }
        $item = $this->repo->find((int) $request->param('id'));
        if ($item === null) {
            return $this->notFound();
        }
        return $this->adminView('admin/shipping/form', ['item' => $item], 'ویرایش روش ارسال');
    }

    public function store(Request $request): Response
    {
        if ($r = $this->guard('shipping')) {
            return $r;
        }
        $data = $this->collect($request);
        if ($data['method_label'] === '' || $data['method_key'] === '') {
            Session::flash('error', 'نام و کلید روش ارسال الزامی است.');
            return $this->redirect(url('/admin/shipping/create'));
        }
        $id = $this->repo->insert($data);
        $this->audit($request, 'create', 'shipping_zone', $id, $data['city'] . '/' . $data['method_key']);
        Session::flash('success', 'روش ارسال ایجاد شد.');
        return $this->redirect(url('/admin/shipping'));
    }

    public function update(Request $request): Response
    {
        if ($r = $this->guard('shipping')) {
            return $r;
        }
        $id = (int) $request->param('id');
        if ($this->repo->find($id) === null) {
            return $this->notFound();
        }
        $data = $this->collect($request);
        if ($data['method_label'] === '' || $data['method_key'] === '') {
            Session::flash('error', 'نام و کلید روش ارسال الزامی است.');
            return $this->redirect(url('/admin/shipping/' . $id . '/edit'));
        }
        $this->repo->update($id, $data);
        $this->audit($request, 'update', 'shipping_zone', $id, $data['city'] . '/' . $data['method_key']);
        Session::flash('success', 'تغییرات ذخیره شد.');
        return $this->redirect(url('/admin/shipping/' . $id . '/edit'));
    }

    public function destroy(Request $request): Response
    {
        if ($r = $this->guard('shipping')) {
            return $r;
        }
        $id = (int) $request->param('id');
        $this->repo->delete($id);
        $this->audit($request, 'delete', 'shipping_zone', $id);
        Session::flash('success', 'روش ارسال حذف شد.');
        return $this->redirect(url('/admin/shipping'));
    }

    /** @return array<string,mixed> */
    private function collect(Request $request): array
    {
        $int = static fn ($v): int => max(0, (int) en_num((string) $v));
        $city = trim((string) $request->input('city', ''));
        // Empty city means the nationwide default bucket.
        if ($city === '' || $city === '*') {
            $city = '*';
        }
        $freeOver = trim((string) $request->input('free_over', ''));
        $key = preg_replace('/[^a-z0-9_]/i', '', strtolower(trim((string) $request->input('method_key', '')))) ?? '';

        return [
            'city'         => $city,
            'method_key'   => $key,
            'method_label' => trim((string) $request->input('method_label', '')),
            'note'         => trim((string) $request->input('note', '')) ?: null,
            'cost'         => $int($request->input('cost', 0)),
            'free_over'    => $freeOver === '' ? null : $int($freeOver),
            'sort'         => (int) en_num((string) $request->input('sort', 0)),
            'is_active'    => $request->input('is_active') ? 1 : 0,
        ];
    }
}
