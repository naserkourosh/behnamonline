<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\TagRepository;

final class TagController extends AdminController
{
    private TagRepository $repo;

    public function __construct()
    {
        $this->repo = new TagRepository();
    }

    public function index(Request $request): Response
    {
        if ($r = $this->guard('tags')) {
            return $r;
        }
        return $this->adminView('admin/tags/index', [
            'items'  => $this->repo->allWithCounts(),
            'groups' => $this->repo->groups(),
        ], 'برچسب‌ها');
    }

    public function store(Request $request): Response
    {
        if ($r = $this->guard('tags')) {
            return $r;
        }
        $name = trim((string) $request->input('name', ''));
        if ($name === '') {
            Session::flash('error', 'نام برچسب الزامی است.');
            return $this->redirect(url('/admin/tags'));
        }
        $slug  = $this->uniqueSlug($name, 0);
        $group = trim((string) $request->input('tag_group', '')) ?: null;
        $id = $this->repo->insert($name, $slug, $group);
        $this->audit($request, 'create', 'tag', $id, $name);
        Session::flash('success', 'برچسب افزوده شد.');
        return $this->redirect(url('/admin/tags'));
    }

    public function update(Request $request): Response
    {
        if ($r = $this->guard('tags')) {
            return $r;
        }
        $id   = (int) $request->param('id');
        $item = $this->repo->find($id);
        if ($item === null) {
            return $this->notFound();
        }
        $name = trim((string) $request->input('name', ''));
        if ($name === '') {
            Session::flash('error', 'نام برچسب الزامی است.');
            return $this->redirect(url('/admin/tags'));
        }
        $group = trim((string) $request->input('tag_group', '')) ?: null;
        $this->repo->update($id, $name, $this->uniqueSlug($name, $id), $group);
        Session::flash('success', 'برچسب ویرایش شد.');
        return $this->redirect(url('/admin/tags'));
    }

    public function destroy(Request $request): Response
    {
        if ($r = $this->guard('tags')) {
            return $r;
        }
        $id = (int) $request->param('id');
        $this->repo->delete($id);
        $this->audit($request, 'delete', 'tag', $id);
        Session::flash('success', 'برچسب حذف شد.');
        return $this->redirect(url('/admin/tags'));
    }

    private function uniqueSlug(string $name, int $exceptId): string
    {
        $slug = slugify($name);
        $base = $slug;
        $n = 2;
        while ($this->repo->slugExists($slug, $exceptId)) {
            $slug = $base . '-' . $n++;
        }
        return $slug;
    }
}
