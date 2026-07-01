<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\CategoryRepository;
use App\Services\MediaService;

final class CategoryController extends AdminController
{
    private CategoryRepository $repo;

    public function __construct()
    {
        $this->repo = new CategoryRepository();
    }

    public function index(Request $request): Response
    {
        if ($r = $this->guard('categories')) {
            return $r;
        }
        return $this->adminView('admin/categories/index', ['items' => $this->repo->allAdmin()], 'دسته‌بندی‌ها');
    }

    public function create(Request $request): Response
    {
        if ($r = $this->guard('categories')) {
            return $r;
        }
        return $this->adminView('admin/categories/form', ['item' => null, 'categories' => $this->repo->allAdmin()], 'دسته جدید');
    }

    public function edit(Request $request): Response
    {
        if ($r = $this->guard('categories')) {
            return $r;
        }
        $item = $this->repo->find((int) $request->param('id'));
        if ($item === null) {
            return $this->notFound();
        }
        return $this->adminView('admin/categories/form', ['item' => $item, 'categories' => $this->repo->allAdmin()], 'ویرایش دسته');
    }

    public function store(Request $request): Response
    {
        if ($r = $this->guard('categories')) {
            return $r;
        }
        $data = $this->collect($request, 0);
        if ($data['name'] === '') {
            Session::flash('error', 'نام دسته الزامی است.');
            return $this->redirect(url('/admin/categories/create'));
        }
        $data['image'] = (new MediaService())->store($_FILES['image'] ?? [], 'categories');
        $id = $this->repo->insert($data);
        $this->audit($request, 'create', 'category', $id, $data['name']);
        Session::flash('success', 'دسته ایجاد شد.');
        return $this->redirect(url('/admin/categories'));
    }

    public function update(Request $request): Response
    {
        if ($r = $this->guard('categories')) {
            return $r;
        }
        $id   = (int) $request->param('id');
        $item = $this->repo->find($id);
        if ($item === null) {
            return $this->notFound();
        }
        $data = $this->collect($request, $id);
        $newImage = (new MediaService())->store($_FILES['image'] ?? [], 'categories');
        $data['image'] = $newImage ?? $item['image'];
        if ($newImage !== null) {
            (new MediaService())->delete((string) $item['image']);
        }
        $this->repo->update($id, $data);
        $this->audit($request, 'update', 'category', $id, $data['name']);
        Session::flash('success', 'تغییرات ذخیره شد.');
        return $this->redirect(url('/admin/categories'));
    }

    public function destroy(Request $request): Response
    {
        if ($r = $this->guard('categories')) {
            return $r;
        }
        $id   = (int) $request->param('id');
        $item = $this->repo->find($id);
        if ($item !== null) {
            (new MediaService())->delete((string) $item['image']);
            $this->repo->delete($id);
            $this->audit($request, 'delete', 'category', $id);
        }
        Session::flash('success', 'دسته حذف شد.');
        return $this->redirect(url('/admin/categories'));
    }

    /** @return array<string,mixed> */
    private function collect(Request $request, int $exceptId): array
    {
        $name = trim((string) $request->input('name', ''));
        $slug = slugify((string) $request->input('slug', ''), '') ?: slugify($name);
        // ensure unique
        $base = $slug;
        $n = 2;
        while ($this->repo->slugExists($slug, $exceptId)) {
            $slug = $base . '-' . $n++;
        }
        $parent = (int) en_num((string) $request->input('parent_id', 0));
        return [
            'name'            => $name,
            'slug'            => $slug,
            'parent_id'       => ($parent > 0 && $parent !== $exceptId) ? $parent : null,
            'sort'            => (int) en_num((string) $request->input('sort', 0)),
            'is_active'       => $request->input('is_active') ? 1 : 0,
            'seo_title'       => trim((string) $request->input('seo_title', '')) ?: null,
            'seo_description' => trim((string) $request->input('seo_description', '')) ?: null,
            'image'           => null,
        ];
    }
}
