<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\CategoryRepository;
use App\Repositories\MenuRepository;

final class MenuController extends AdminController
{
    private MenuRepository $repo;

    public function __construct()
    {
        $this->repo = new MenuRepository();
    }

    public function index(Request $request): Response
    {
        if ($r = $this->guard('menus')) {
            return $r;
        }
        return $this->adminView('admin/menus/index', ['menus' => $this->repo->allMenus()], 'منوها');
    }

    public function edit(Request $request): Response
    {
        if ($r = $this->guard('menus')) {
            return $r;
        }
        $menu = $this->repo->find((int) $request->param('id'));
        if ($menu === null) {
            return $this->notFound();
        }
        return $this->adminView('admin/menus/edit', [
            'menu'       => $menu,
            'items'      => $this->repo->items((int) $menu['id']),
            'categories' => (new CategoryRepository())->allAdmin(),
        ], 'ویرایش منو: ' . $menu['name']);
    }

    public function store(Request $request): Response
    {
        if ($r = $this->guard('menus')) {
            return $r;
        }
        $name = trim((string) $request->input('name', ''));
        if ($name === '') {
            Session::flash('error', 'نام منو الزامی است.');
            return $this->redirect(url('/admin/menus'));
        }
        $slug = slugify($name);
        $base = $slug;
        $n = 2;
        while ($this->repo->slugExists($slug)) {
            $slug = $base . '-' . $n++;
        }
        $id = $this->repo->insert($name, $slug);
        $this->audit($request, 'create', 'menu', $id, $name);
        Session::flash('success', 'منو ایجاد شد.');
        return $this->redirect(url('/admin/menus/' . $id));
    }

    public function addItem(Request $request): Response
    {
        if ($r = $this->guard('menus')) {
            return $r;
        }
        $menuId = (int) $request->param('id');
        if ($this->repo->find($menuId) === null) {
            return $this->notFound();
        }
        $label = trim((string) $request->input('label', ''));
        $url   = trim((string) $request->input('url', '')) ?: '#';
        if ($label === '') {
            Session::flash('error', 'عنوان آیتم الزامی است.');
            return $this->redirect(url('/admin/menus/' . $menuId));
        }

        // Optional parent → sub-menu (max 3 levels). Mega applies to
        // TOP-LEVEL items only: children become columns, grandchildren links.
        $parentId = (int) en_num((string) $request->input('parent_id', 0));
        $isMega   = (bool) $request->input('is_mega');
        if ($parentId > 0) {
            $parent = $this->repo->findItem($parentId);
            if ($parent === null || (int) $parent['menu_id'] !== $menuId) {
                Session::flash('error', 'آیتم والد نامعتبر است.');
                return $this->redirect(url('/admin/menus/' . $menuId));
            }
            if ($this->repo->itemDepth($parentId) >= 2) {
                Session::flash('error', 'حداکثر سه سطح منو مجاز است.');
                return $this->redirect(url('/admin/menus/' . $menuId));
            }
            $isMega = false;
        }

        $this->repo->addItem($menuId, $parentId > 0 ? $parentId : null, $label, $url, $this->repo->maxSort($menuId) + 1, $isMega);
        Session::flash('success', 'آیتم افزوده شد.');
        return $this->redirect(url('/admin/menus/' . $menuId));
    }

    public function deleteItem(Request $request): Response
    {
        if ($r = $this->guard('menus')) {
            return $r;
        }
        $item = $this->repo->findItem((int) $request->param('id'));
        if ($item !== null) {
            $this->repo->deleteItem((int) $item['id']);
        }
        Session::flash('success', 'آیتم حذف شد.');
        return $this->redirect(url('/admin/menus/' . ($item['menu_id'] ?? '')));
    }
}
