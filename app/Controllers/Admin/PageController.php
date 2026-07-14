<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\PageRepository;
use App\Support\Html;

/**
 * مدیریت صفحات — CMS pages served on the storefront at /page/{slug}.
 * The body is sanitized on save (allowlist) exactly like blog posts.
 */
final class PageController extends AdminController
{
    private PageRepository $repo;

    public function __construct()
    {
        $this->repo = new PageRepository();
    }

    public function index(Request $request): Response
    {
        if ($r = $this->guard('pages')) {
            return $r;
        }
        return $this->adminView('admin/pages/index', ['items' => $this->repo->all()], 'صفحات');
    }

    public function create(Request $request): Response
    {
        if ($r = $this->guard('pages')) {
            return $r;
        }
        return $this->adminView('admin/pages/form', ['item' => null], 'صفحه جدید');
    }

    public function edit(Request $request): Response
    {
        if ($r = $this->guard('pages')) {
            return $r;
        }
        $item = $this->repo->find((int) $request->param('id'));
        if ($item === null) {
            return $this->notFound();
        }
        return $this->adminView('admin/pages/form', ['item' => $item], 'ویرایش صفحه: ' . $item['title']);
    }

    public function store(Request $request): Response
    {
        if ($r = $this->guard('pages')) {
            return $r;
        }
        $data = $this->collect($request);
        if ($data['title'] === '') {
            Session::flash('error', 'عنوان صفحه الزامی است.');
            return $this->redirect(url('/admin/pages/create'));
        }
        $data['slug'] = $this->uniqueSlug($data['slug'] !== '' ? $data['slug'] : $data['title']);
        $id = $this->repo->insert($data);
        $this->audit($request, 'create', 'page', $id, $data['title']);
        Session::flash('success', 'صفحه ایجاد شد.');
        return $this->redirect(url('/admin/pages/' . $id . '/edit'));
    }

    public function update(Request $request): Response
    {
        if ($r = $this->guard('pages')) {
            return $r;
        }
        $id   = (int) $request->param('id');
        $item = $this->repo->find($id);
        if ($item === null) {
            return $this->notFound();
        }
        $data = $this->collect($request);
        if ($data['title'] === '') {
            Session::flash('error', 'عنوان صفحه الزامی است.');
            return $this->redirect(url('/admin/pages/' . $id . '/edit'));
        }
        $data['slug'] = $this->uniqueSlug($data['slug'] !== '' ? $data['slug'] : $data['title'], $id);
        $this->repo->update($id, $data);
        $this->audit($request, 'update', 'page', $id, $data['title']);
        Session::flash('success', 'تغییرات ذخیره شد.');
        return $this->redirect(url('/admin/pages/' . $id . '/edit'));
    }

    public function destroy(Request $request): Response
    {
        if ($r = $this->guard('pages')) {
            return $r;
        }
        $id   = (int) $request->param('id');
        $item = $this->repo->find($id);
        if ($item !== null) {
            $this->repo->delete($id);
            $this->audit($request, 'delete', 'page', $id, (string) $item['title']);
        }
        Session::flash('success', 'صفحه حذف شد.');
        return $this->redirect(url('/admin/pages'));
    }

    /** @return array<string,mixed> */
    private function collect(Request $request): array
    {
        return [
            'title'           => trim((string) $request->input('title', '')),
            'slug'            => trim((string) $request->input('slug', '')),
            'body'            => Html::sanitize((string) $request->input('body', '')),
            'seo_title'       => trim((string) $request->input('seo_title', '')) ?: null,
            'seo_description' => mb_substr(trim((string) $request->input('seo_description', '')), 0, 300) ?: null,
            'show_in_footer'  => $request->input('show_in_footer') ? 1 : 0,
            'sort'            => (int) en_num((string) $request->input('sort', 0)),
            'is_active'       => $request->input('is_active') ? 1 : 0,
        ];
    }

    private function uniqueSlug(string $source, int $exceptId = 0): string
    {
        $slug = slugify($source);
        $base = $slug !== '' ? $slug : 'page';
        $slug = $base;
        $n    = 2;
        while ($this->repo->slugExists($slug, $exceptId)) {
            $slug = $base . '-' . $n++;
        }
        return $slug;
    }
}
