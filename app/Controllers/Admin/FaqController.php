<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\FaqRepository;

final class FaqController extends AdminController
{
    private FaqRepository $faqs;

    public function __construct()
    {
        $this->faqs = new FaqRepository();
    }

    public function index(Request $request): Response
    {
        if ($r = $this->guard('support')) {
            return $r;
        }
        $editId = (int) $request->query('edit', 0);
        return $this->adminView('admin/faq/index', [
            'items' => $this->faqs->all(),
            'edit'  => $editId > 0 ? $this->faqs->find($editId) : null,
        ], 'سوالات متداول');
    }

    public function store(Request $request): Response
    {
        if ($r = $this->guard('support')) {
            return $r;
        }
        $id       = (int) $request->param('id');
        $question = trim((string) $request->input('question', ''));
        $answer   = trim((string) $request->input('answer', ''));
        if ($question === '' || $answer === '') {
            Session::flash('error', 'پرسش و پاسخ الزامی است.');
            return $this->redirect(url('/admin/faq'));
        }
        $data = [
            'category'  => trim((string) $request->input('category', '')) ?: 'عمومی',
            'question'  => $question,
            'answer'    => $answer,
            'sort'      => (int) en_num((string) $request->input('sort', 0)),
            'is_active' => $request->input('is_active') ? 1 : 0,
        ];
        if ($id > 0) {
            $this->faqs->update($id, $data);
        } else {
            $id = $this->faqs->insert($data);
        }
        $this->audit($request, $id ? 'update' : 'create', 'faq', $id, $question);
        Session::flash('success', 'سوال ذخیره شد.');
        return $this->redirect(url('/admin/faq'));
    }

    public function destroy(Request $request): Response
    {
        if ($r = $this->guard('support')) {
            return $r;
        }
        $id = (int) $request->param('id');
        $this->faqs->delete($id);
        $this->audit($request, 'delete', 'faq', $id);
        Session::flash('success', 'سوال حذف شد.');
        return $this->redirect(url('/admin/faq'));
    }
}
