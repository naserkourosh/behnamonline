<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\CouponRepository;

final class CouponController extends AdminController
{
    private CouponRepository $coupons;

    public function __construct()
    {
        $this->coupons = new CouponRepository();
    }

    public function index(Request $request): Response
    {
        if ($r = $this->guard('coupons')) {
            return $r;
        }
        return $this->adminView('admin/coupons/index', [
            'items' => $this->coupons->all(),
        ], 'کدهای تخفیف');
    }

    public function create(Request $request): Response
    {
        if ($r = $this->guard('coupons')) {
            return $r;
        }
        return $this->form(null);
    }

    public function edit(Request $request): Response
    {
        if ($r = $this->guard('coupons')) {
            return $r;
        }
        $coupon = $this->coupons->find((int) $request->param('id'));
        if ($coupon === null) {
            return $this->notFound();
        }
        return $this->form($coupon);
    }

    public function store(Request $request): Response
    {
        if ($r = $this->guard('coupons')) {
            return $r;
        }
        $data = $this->collect($request);
        if ($data['code'] === '' || $data['value'] <= 0) {
            Session::flash('error', 'کد و مقدار تخفیف معتبر الزامی است.');
            return $this->redirect(url('/admin/coupons/create'));
        }
        if ($this->coupons->codeExists($data['code'], 0)) {
            Session::flash('error', 'این کد قبلاً ثبت شده است.');
            return $this->redirect(url('/admin/coupons/create'));
        }
        $id = $this->coupons->insert($data);
        $this->audit($request, 'create', 'coupon', $id, $data['code']);
        Session::flash('success', 'کد تخفیف ایجاد شد.');
        return $this->redirect(url('/admin/coupons'));
    }

    public function update(Request $request): Response
    {
        if ($r = $this->guard('coupons')) {
            return $r;
        }
        $id = (int) $request->param('id');
        if ($this->coupons->find($id) === null) {
            return $this->notFound();
        }
        $data = $this->collect($request);
        if ($data['code'] === '' || $data['value'] <= 0) {
            Session::flash('error', 'کد و مقدار تخفیف معتبر الزامی است.');
            return $this->redirect(url('/admin/coupons/' . $id . '/edit'));
        }
        if ($this->coupons->codeExists($data['code'], $id)) {
            Session::flash('error', 'این کد برای مورد دیگری ثبت شده است.');
            return $this->redirect(url('/admin/coupons/' . $id . '/edit'));
        }
        $this->coupons->update($id, $data);
        $this->audit($request, 'update', 'coupon', $id, $data['code']);
        Session::flash('success', 'تغییرات ذخیره شد.');
        return $this->redirect(url('/admin/coupons/' . $id . '/edit'));
    }

    public function destroy(Request $request): Response
    {
        if ($r = $this->guard('coupons')) {
            return $r;
        }
        $id = (int) $request->param('id');
        $this->coupons->delete($id);
        $this->audit($request, 'delete', 'coupon', $id);
        Session::flash('success', 'کد تخفیف حذف شد.');
        return $this->redirect(url('/admin/coupons'));
    }

    private function form(?array $coupon): Response
    {
        return $this->adminView('admin/coupons/form', [
            'coupon' => $coupon,
        ], $coupon ? 'ویرایش کد تخفیف' : 'کد تخفیف جدید');
    }

    /** @return array<string,mixed> */
    private function collect(Request $request): array
    {
        $int = static fn ($v): int => (int) en_num((string) $v);
        $nullableInt = static function ($v) use ($int): ?int {
            $v = trim((string) $v);
            return $v === '' ? null : max(0, $int($v));
        };
        $date = static function ($v): ?string {
            $v = trim((string) $v);
            return preg_match('/^\d{4}-\d{2}-\d{2}/', $v) ? $v : null;
        };
        $type = $request->input('type') === 'fixed' ? 'fixed' : 'percent';
        $value = max(0, $int($request->input('value', 0)));
        if ($type === 'percent') {
            $value = min(100, $value);
        }

        return [
            'code'           => strtoupper(trim((string) $request->input('code', ''))),
            'description'    => trim((string) $request->input('description', '')) ?: null,
            'type'           => $type,
            'value'          => $value,
            'min_cart'       => max(0, $int($request->input('min_cart', 0))),
            'max_discount'   => $nullableInt($request->input('max_discount')),
            'usage_limit'    => $nullableInt($request->input('usage_limit')),
            'per_user_limit' => $nullableInt($request->input('per_user_limit')),
            'starts_at'      => $date($request->input('starts_at')),
            'ends_at'        => $date($request->input('ends_at')),
            'is_active'      => $request->input('is_active') ? 1 : 0,
        ];
    }
}
