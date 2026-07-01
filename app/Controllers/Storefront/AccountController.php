<?php

declare(strict_types=1);

namespace App\Controllers\Storefront;

use App\Controllers\Controller;
use App\Core\Config;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Repositories\AddressRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Repositories\UserRepository;
use App\Repositories\WishlistRepository;
use App\Services\AuthService;

final class AccountController extends Controller
{
    public function dashboard(Request $request): Response
    {
        $user   = AuthService::user();
        $userId = (int) $user['id'];

        return $this->view('storefront/account/dashboard', [
            'user'   => $user,
            'stats'  => [
                'orders'   => (new OrderRepository())->countForUser($userId),
                'wishlist' => (new WishlistRepository())->count($userId),
                'wallet'   => (int) $user['wallet_balance'],
                'points'   => (int) $user['reward_points'],
            ],
            'orders' => (new OrderRepository())->forUser($userId, 3),
        ]);
    }

    public function orders(Request $request): Response
    {
        $userId = (int) AuthService::id();
        return $this->view('storefront/account/orders', [
            'orders' => (new OrderRepository())->forUser($userId, 50),
        ]);
    }

    public function orderShow(Request $request): Response
    {
        $userId = (int) AuthService::id();
        $orders = new OrderRepository();
        $order  = $orders->find((int) $request->param('id'), $userId);
        if ($order === null) {
            return $this->notFound();
        }
        return $this->view('storefront/account/order-show', [
            'order' => $order,
            'items' => $orders->items((int) $order['id']),
        ]);
    }

    public function addresses(Request $request): Response
    {
        $userId = (int) AuthService::id();
        return $this->view('storefront/account/addresses', [
            'addresses' => (new AddressRepository())->forUser($userId),
            'provinces' => Config::get('geo', []),
        ]);
    }

    public function addressStore(Request $request): Response
    {
        $userId = (int) AuthService::id();
        $data = [
            'receiver_name' => trim((string) $request->input('receiver_name', '')),
            'mobile'        => en_num((string) $request->input('mobile', '')),
            'province'      => trim((string) $request->input('province', '')),
            'city'          => trim((string) $request->input('city', '')),
            'address'       => trim((string) $request->input('address', '')),
            'postal_code'   => en_num((string) $request->input('postal_code', '')),
            'is_default'    => $request->input('is_default') ? 1 : 0,
        ];

        $validator = new Validator();
        $ok = $validator->validate($data, [
            'receiver_name' => 'required',
            'mobile'        => 'required|mobile',
            'province'      => 'required',
            'city'          => 'required',
            'address'       => 'required',
        ]);
        if (!$ok) {
            Session::flash('error', 'اطلاعات آدرس کامل یا صحیح نیست.');
            return $this->redirect(url('/account/addresses'));
        }

        $repo = new AddressRepository();
        $id   = (int) $request->input('id', 0);
        if ($id > 0 && $repo->find($id, $userId) !== null) {
            $repo->update($id, $userId, $data);
        } else {
            $repo->create($userId, $data);
        }
        Session::flash('success', 'آدرس ذخیره شد.');
        return $this->redirect(url('/account/addresses'));
    }

    public function addressDelete(Request $request): Response
    {
        $userId = (int) AuthService::id();
        (new AddressRepository())->delete((int) $request->param('id'), $userId);
        Session::flash('success', 'آدرس حذف شد.');
        return $this->redirect(url('/account/addresses'));
    }

    public function profile(Request $request): Response
    {
        return $this->view('storefront/account/profile', ['user' => AuthService::user()]);
    }

    public function profileUpdate(Request $request): Response
    {
        $userId    = (int) AuthService::id();
        $firstName = trim((string) $request->input('first_name', ''));
        $lastName  = trim((string) $request->input('last_name', ''));

        if ($firstName === '' || $lastName === '') {
            Session::flash('error', 'نام و نام خانوادگی الزامی است.');
            return $this->redirect(url('/account/profile'));
        }

        (new UserRepository())->updateProfile($userId, $firstName, $lastName);
        Session::flash('success', 'پروفایل به‌روزرسانی شد.');
        return $this->redirect(url('/account/profile'));
    }

    public function wishlist(Request $request): Response
    {
        $userId = (int) AuthService::id();
        $ids    = (new WishlistRepository())->productIds($userId);
        return $this->view('storefront/account/wishlist', [
            'products' => (new ProductRepository())->cardsByIds($ids),
        ]);
    }
}
