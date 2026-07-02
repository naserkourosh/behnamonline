<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\PopupRepository;
use App\Services\MediaService;

final class PopupController extends AdminController
{
    private PopupRepository $popups;

    public function __construct()
    {
        $this->popups = new PopupRepository();
    }

    public function index(Request $request): Response
    {
        if ($r = $this->guard('popups')) {
            return $r;
        }
        return $this->adminView('admin/popups/index', [
            'items' => $this->popups->all(),
        ], 'پاپ‌آپ‌ها');
    }

    public function create(Request $request): Response
    {
        if ($r = $this->guard('popups')) {
            return $r;
        }
        return $this->form(null);
    }

    public function edit(Request $request): Response
    {
        if ($r = $this->guard('popups')) {
            return $r;
        }
        $popup = $this->popups->find((int) $request->param('id'));
        if ($popup === null) {
            return $this->notFound();
        }
        return $this->form($popup);
    }

    public function store(Request $request): Response
    {
        if ($r = $this->guard('popups')) {
            return $r;
        }
        $data = $this->collect($request);
        if ($data['title'] === '') {
            Session::flash('error', 'عنوان الزامی است.');
            return $this->redirect(url('/admin/popups/create'));
        }
        $data['image'] = $this->handleImage() ?? null;
        $id = $this->popups->insert($data);
        $this->audit($request, 'create', 'popup', $id, $data['title']);
        Session::flash('success', 'پاپ‌آپ ایجاد شد.');
        return $this->redirect(url('/admin/popups/' . $id . '/edit'));
    }

    public function update(Request $request): Response
    {
        if ($r = $this->guard('popups')) {
            return $r;
        }
        $id    = (int) $request->param('id');
        $popup = $this->popups->find($id);
        if ($popup === null) {
            return $this->notFound();
        }
        $data          = $this->collect($request);
        $data['image'] = $this->handleImage() ?? ($popup['image'] ?: null);
        if ($data['title'] === '') {
            Session::flash('error', 'عنوان الزامی است.');
            return $this->redirect(url('/admin/popups/' . $id . '/edit'));
        }
        $this->popups->update($id, $data);
        $this->audit($request, 'update', 'popup', $id, $data['title']);
        Session::flash('success', 'تغییرات ذخیره شد.');
        return $this->redirect(url('/admin/popups/' . $id . '/edit'));
    }

    public function destroy(Request $request): Response
    {
        if ($r = $this->guard('popups')) {
            return $r;
        }
        $id    = (int) $request->param('id');
        $popup = $this->popups->find($id);
        if ($popup !== null && !empty($popup['image'])) {
            (new MediaService())->delete((string) $popup['image']);
        }
        $this->popups->delete($id);
        $this->audit($request, 'delete', 'popup', $id);
        Session::flash('success', 'پاپ‌آپ حذف شد.');
        return $this->redirect(url('/admin/popups'));
    }

    private function form(?array $popup): Response
    {
        return $this->adminView('admin/popups/form', [
            'popup' => $popup,
        ], $popup ? 'ویرایش پاپ‌آپ' : 'پاپ‌آپ جدید');
    }

    /** @return array<string,mixed> */
    private function collect(Request $request): array
    {
        $int  = static fn ($v): int => (int) en_num((string) $v);
        $date = static function ($v): ?string {
            $v = trim((string) $v);
            return preg_match('/^\d{4}-\d{2}-\d{2}/', $v) ? $v : null;
        };
        return [
            'title'         => trim((string) $request->input('title', '')),
            'body'          => html_clean((string) $request->input('body', '')) ?: null,
            'cta_label'     => trim((string) $request->input('cta_label', '')) ?: null,
            'cta_url'       => trim((string) $request->input('cta_url', '')) ?: null,
            'position'      => $request->input('position') === 'corner' ? 'corner' : 'center',
            'delay_seconds' => max(0, $int($request->input('delay_seconds', 3))),
            'frequency'     => in_array($request->input('frequency'), ['once_session', 'once_day', 'always'], true)
                ? (string) $request->input('frequency') : 'once_session',
            'target'        => trim((string) $request->input('target', 'all')) ?: 'all',
            'starts_at'     => $date($request->input('starts_at')),
            'ends_at'       => $date($request->input('ends_at')),
            'is_active'     => $request->input('is_active') ? 1 : 0,
            'sort'          => $int($request->input('sort', 0)),
        ];
    }

    private function handleImage(): ?string
    {
        if (empty($_FILES['image']) || ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return null;
        }
        return (new MediaService())->store($_FILES['image'], 'popups');
    }
}
