<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\BrandRepository;
use App\Services\MediaService;

final class BrandController extends AdminController
{
    private BrandRepository $repo;

    public function __construct()
    {
        $this->repo = new BrandRepository();
    }

    public function index(Request $request): Response
    {
        if ($r = $this->guard('brands')) {
            return $r;
        }
        return $this->adminView('admin/brands/index', ['items' => $this->repo->allAdmin()], 'برندها');
    }

    public function create(Request $request): Response
    {
        if ($r = $this->guard('brands')) {
            return $r;
        }
        return $this->adminView('admin/brands/form', ['item' => null], 'برند جدید');
    }

    public function edit(Request $request): Response
    {
        if ($r = $this->guard('brands')) {
            return $r;
        }
        $item = $this->repo->find((int) $request->param('id'));
        if ($item === null) {
            return $this->notFound();
        }
        return $this->adminView('admin/brands/form', ['item' => $item], 'ویرایش برند');
    }

    public function store(Request $request): Response
    {
        if ($r = $this->guard('brands')) {
            return $r;
        }
        $data = $this->collect($request, 0);
        if ($data['name'] === '') {
            Session::flash('error', 'نام برند الزامی است.');
            return $this->redirect(url('/admin/brands/create'));
        }
        $data['logo'] = (new MediaService())->store($_FILES['logo'] ?? [], 'brands');
        $id = $this->repo->insert($data);
        $this->audit($request, 'create', 'brand', $id, $data['name']);
        Session::flash('success', 'برند ایجاد شد.');
        return $this->redirect(url('/admin/brands'));
    }

    public function update(Request $request): Response
    {
        if ($r = $this->guard('brands')) {
            return $r;
        }
        $id   = (int) $request->param('id');
        $item = $this->repo->find($id);
        if ($item === null) {
            return $this->notFound();
        }
        $data = $this->collect($request, $id);
        $newLogo = (new MediaService())->store($_FILES['logo'] ?? [], 'brands');
        $data['logo'] = $newLogo ?? $item['logo'];
        if ($newLogo !== null) {
            (new MediaService())->delete((string) $item['logo']);
        }
        $this->repo->update($id, $data);
        $this->audit($request, 'update', 'brand', $id, $data['name']);
        Session::flash('success', 'تغییرات ذخیره شد.');
        return $this->redirect(url('/admin/brands'));
    }

    public function destroy(Request $request): Response
    {
        if ($r = $this->guard('brands')) {
            return $r;
        }
        $id   = (int) $request->param('id');
        $item = $this->repo->find($id);
        if ($item !== null) {
            (new MediaService())->delete((string) $item['logo']);
            $this->repo->delete($id);
            $this->audit($request, 'delete', 'brand', $id);
        }
        Session::flash('success', 'برند حذف شد.');
        return $this->redirect(url('/admin/brands'));
    }

    /** @return array<string,mixed> */
    private function collect(Request $request, int $exceptId): array
    {
        $name = trim((string) $request->input('name', ''));
        $slug = slugify((string) $request->input('slug', ''), '') ?: slugify($name);
        $base = $slug;
        $n = 2;
        while ($this->repo->slugExists($slug, $exceptId)) {
            $slug = $base . '-' . $n++;
        }
        return [
            'name'      => $name,
            'slug'      => $slug,
            'sort'      => (int) en_num((string) $request->input('sort', 0)),
            'is_active' => $request->input('is_active') ? 1 : 0,
            'logo'      => null,
        ];
    }
}
