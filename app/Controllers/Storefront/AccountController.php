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
use App\Repositories\TicketRepository;
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

    public function invoice(Request $request): Response
    {
        $userId = (int) AuthService::id();
        $orders = new OrderRepository();
        $order  = $orders->find((int) $request->param('id'), $userId);
        if ($order === null) {
            return $this->notFound();
        }
        // Standalone print-optimized layout (no store chrome).
        return $this->view('storefront/account/invoice', [
            'order' => $order,
            'items' => $orders->items((int) $order['id']),
            'user'  => AuthService::user(),
        ], null);
    }

    public function wishlist(Request $request): Response
    {
        $userId = (int) AuthService::id();
        $ids    = (new WishlistRepository())->productIds($userId);
        return $this->view('storefront/account/wishlist', [
            'products' => (new ProductRepository())->cardsByIds($ids),
        ]);
    }

    /* ───────────────────────── Support tickets ───────────────────────── */

    public function tickets(Request $request): Response
    {
        $userId = (int) AuthService::id();
        return $this->view('storefront/account/tickets', [
            'tickets' => (new TicketRepository())->forUser($userId),
        ]);
    }

    public function ticketShow(Request $request): Response
    {
        $userId = (int) AuthService::id();
        $repo   = new TicketRepository();
        $ticket = $repo->findForUser((int) $request->param('id'), $userId);
        if ($ticket === null) {
            return $this->notFound();
        }
        return $this->view('storefront/account/ticket-show', [
            'ticket'   => $ticket,
            'messages' => $repo->messages((int) $ticket['id']),
        ]);
    }

    public function ticketStore(Request $request): Response
    {
        $userId   = (int) AuthService::id();
        $subject  = trim((string) $request->input('subject', ''));
        $body     = trim((string) $request->input('body', ''));
        $priority = in_array($request->input('priority'), ['low', 'normal', 'high'], true)
            ? (string) $request->input('priority') : 'normal';

        if (mb_strlen($subject) < 3 || mb_strlen($body) < 3) {
            Session::flash('error', 'موضوع و متن پیام را کامل وارد کنید.');
            return $this->redirect(url('/account/tickets'));
        }

        $repo = new TicketRepository();
        $id   = $repo->create($userId, mb_substr($subject, 0, 190), $priority);
        $repo->addMessage($id, 'customer', $userId, mb_substr($body, 0, 3000));

        Session::flash('success', 'تیکت شما ثبت شد. کارشناسان ما به‌زودی پاسخ می‌دهند.');
        return $this->redirect(url('/account/tickets/' . $id));
    }

    public function ticketReply(Request $request): Response
    {
        $userId = (int) AuthService::id();
        $repo   = new TicketRepository();
        $ticket = $repo->findForUser((int) $request->param('id'), $userId);
        if ($ticket === null) {
            return $this->notFound();
        }
        if ((string) $ticket['status'] === 'closed') {
            Session::flash('error', 'این تیکت بسته شده است.');
            return $this->redirect(url('/account/tickets/' . $ticket['id']));
        }
        $body = trim((string) $request->input('body', ''));
        if (mb_strlen($body) < 2) {
            Session::flash('error', 'متن پاسخ را وارد کنید.');
            return $this->redirect(url('/account/tickets/' . $ticket['id']));
        }
        $repo->addMessage((int) $ticket['id'], 'customer', $userId, mb_substr($body, 0, 3000));
        Session::flash('success', 'پاسخ شما ثبت شد.');
        return $this->redirect(url('/account/tickets/' . $ticket['id']));
    }
}
