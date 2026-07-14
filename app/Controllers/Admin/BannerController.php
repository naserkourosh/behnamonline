<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\BannerRepository;
use App\Services\MediaService;

final class BannerController extends AdminController
{
    private BannerRepository $repo;

    public function __construct()
    {
        $this->repo = new BannerRepository();
    }

    public function index(Request $request): Response
    {
        if ($r = $this->guard('banners')) {
            return $r;
        }
        return $this->adminView('admin/banners/index', ['items' => $this->repo->all()], 'بنرها');
    }

    public function create(Request $request): Response
    {
        if ($r = $this->guard('banners')) {
            return $r;
        }
        return $this->adminView('admin/banners/form', ['item' => null], 'بنر جدید');
    }

    public function edit(Request $request): Response
    {
        if ($r = $this->guard('banners')) {
            return $r;
        }
        $item = $this->repo->find((int) $request->param('id'));
        if ($item === null) {
            return $this->notFound();
        }
        return $this->adminView('admin/banners/form', ['item' => $item], 'ویرایش بنر');
    }

    public function store(Request $request): Response
    {
        if ($r = $this->guard('banners')) {
            return $r;
        }
        $data = $this->collect($request);
        if ($data['title'] === '') {
            Session::flash('error', 'عنوان بنر الزامی است.');
            return $this->redirect(url('/admin/banners/create'));
        }
        $data['image'] = (new MediaService())->store($_FILES['image'] ?? [], 'banners');
        $id = $this->repo->insert($data);
        $this->audit($request, 'create', 'banner', $id, $data['title']);
        Session::flash('success', 'بنر ایجاد شد.');
        return $this->redirect(url('/admin/banners'));
    }

    public function update(Request $request): Response
    {
        if ($r = $this->guard('banners')) {
            return $r;
        }
        $id   = (int) $request->param('id');
        $item = $this->repo->find($id);
        if ($item === null) {
            return $this->notFound();
        }
        $data = $this->collect($request);
        $newImage = (new MediaService())->store($_FILES['image'] ?? [], 'banners');
        if ($newImage !== null) {
            (new MediaService())->delete((string) $item['image']);
            $data['image'] = $newImage;
        } elseif ($request->input('remove_image')) {
            (new MediaService())->delete((string) $item['image']);
            $data['image'] = null;
        } else {
            $data['image'] = $item['image'];
        }
        $this->repo->update($id, $data);
        $this->audit($request, 'update', 'banner', $id, $data['title']);
        Session::flash('success', 'تغییرات ذخیره شد.');
        return $this->redirect(url('/admin/banners/' . $id . '/edit'));
    }

    /** AJAX: quick on/off from the banners list. */
    public function toggle(Request $request): Response
    {
        if ($r = $this->guard('banners')) {
            return $r;
        }
        $id   = (int) $request->param('id');
        $item = $this->repo->find($id);
        if ($item === null) {
            return $this->json(['ok' => false], 404);
        }
        $on = !((int) $item['is_active'] === 1);
        $this->repo->setActive($id, $on);
        $this->audit($request, $on ? 'activate' : 'deactivate', 'banner', $id, (string) $item['title']);
        return $this->json(['ok' => true, 'active' => $on]);
    }

    public function destroy(Request $request): Response
    {
        if ($r = $this->guard('banners')) {
            return $r;
        }
        $id   = (int) $request->param('id');
        $item = $this->repo->find($id);
        if ($item !== null) {
            (new MediaService())->delete((string) $item['image']);
            $this->repo->delete($id);
            $this->audit($request, 'delete', 'banner', $id);
        }
        Session::flash('success', 'بنر حذف شد.');
        return $this->redirect(url('/admin/banners'));
    }

    /** @return array<string,mixed> */
    private function collect(Request $request): array
    {
        $placement = (string) $request->input('placement', 'hero');
        if (!in_array($placement, ['hero', 'promo', 'strip', 'inline', 'poster'], true)) {
            $placement = 'hero';
        }
        $date = static function ($v): ?string {
            $v = trim((string) $v);
            return preg_match('/^\d{4}-\d{2}-\d{2}/', $v) ? $v : null;
        };
        $url = trim((string) $request->input('link_url', ''));

        return [
            'title'     => trim((string) $request->input('title', '')),
            'subtitle'  => trim((string) $request->input('subtitle', '')) ?: null,
            'kicker'    => trim((string) $request->input('kicker', '')) ?: null,
            'image'     => null,
            'link_url'  => $url !== '' ? $url : '#',
            'cta_label' => trim((string) $request->input('cta_label', '')) ?: null,
            'placement' => $placement,
            'bg_color'  => trim((string) $request->input('bg_color', '')) ?: null,
            'sort'      => (int) en_num((string) $request->input('sort', 0)),
            'starts_at' => $date($request->input('starts_at')),
            'ends_at'   => $date($request->input('ends_at')),
            'is_active' => $request->input('is_active') ? 1 : 0,
        ];
    }
}
