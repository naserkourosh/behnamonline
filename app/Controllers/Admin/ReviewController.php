<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\ReviewRepository;

/**
 * Moderation for customer product reviews: pending reviews are approved or
 * rejected here; the product's cached rating stats refresh on every change.
 */
final class ReviewController extends AdminController
{
    private ReviewRepository $reviews;

    public function __construct()
    {
        $this->reviews = new ReviewRepository();
    }

    public function index(Request $request): Response
    {
        if ($r = $this->guard('reviews')) {
            return $r;
        }
        $status = (string) $request->query('status', 'pending');
        if (!in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $status = 'pending';
        }
        $perPage = 20;
        $page    = max(1, (int) $request->query('page', 1));
        $total   = $this->reviews->countByStatus($status);

        return $this->adminView('admin/reviews/index', [
            'items'  => $this->reviews->adminList($status, $perPage, ($page - 1) * $perPage),
            'status' => $status,
            'counts' => [
                'pending'  => $this->reviews->countByStatus('pending'),
                'approved' => $this->reviews->countByStatus('approved'),
                'rejected' => $this->reviews->countByStatus('rejected'),
            ],
            'total'  => $total,
            'page'   => $page,
            'pages'  => (int) ceil($total / $perPage),
        ], 'دیدگاه‌های محصولات');
    }

    public function setStatus(Request $request): Response
    {
        if ($r = $this->guard('reviews')) {
            return $r;
        }
        $id     = (int) $request->param('id');
        $review = $this->reviews->find($id);
        $status = (string) $request->input('status', '');
        if ($review === null || !in_array($status, ['approved', 'rejected', 'pending'], true)) {
            return $this->notFound();
        }
        $this->reviews->setStatus($id, $status);
        $this->reviews->recalcProduct((int) $review['product_id']);
        $this->audit($request, 'update', 'review', $id, 'status=' . $status);
        Session::flash('success', $status === 'approved' ? 'دیدگاه تایید و منتشر شد.' : 'وضعیت دیدگاه به‌روزرسانی شد.');
        return $this->redirect(url('/admin/reviews?status=' . ($review['status'] ?? 'pending')));
    }

    public function destroy(Request $request): Response
    {
        if ($r = $this->guard('reviews')) {
            return $r;
        }
        $id     = (int) $request->param('id');
        $review = $this->reviews->find($id);
        if ($review !== null) {
            $this->reviews->delete($id);
            $this->reviews->recalcProduct((int) $review['product_id']);
            $this->audit($request, 'delete', 'review', $id);
        }
        Session::flash('success', 'دیدگاه حذف شد.');
        return $this->redirect(url('/admin/reviews'));
    }
}
