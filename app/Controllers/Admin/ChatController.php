<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\ChatRepository;
use App\Services\AdminAuthService;

/**
 * Admin side of the live chat (گفتگوی آنلاین). The thread page polls a JSON
 * endpoint for new customer messages and replies via AJAX — gated by the
 * same `support` capability as tickets.
 */
final class ChatController extends AdminController
{
    private ChatRepository $chats;

    public function __construct()
    {
        $this->chats = new ChatRepository();
    }

    public function index(Request $request): Response
    {
        if ($r = $this->guard('support')) {
            return $r;
        }
        $status = (string) $request->query('status', '');
        return $this->adminView('admin/chat/index', [
            'items'  => $this->chats->adminList($status),
            'status' => $status,
        ], 'گفتگوی آنلاین');
    }

    public function show(Request $request): Response
    {
        if ($r = $this->guard('support')) {
            return $r;
        }
        $conv = $this->chats->findWithUser((int) $request->param('id'));
        if ($conv === null) {
            return $this->notFound();
        }
        $convId   = (int) $conv['id'];
        $messages = $this->chats->messagesAfter($convId, 0);
        // Opening the thread marks the customer's messages as read.
        $this->chats->markRead($convId, 'customer');

        return $this->adminView('admin/chat/show', [
            'conv'     => $conv,
            'messages' => $messages,
        ], 'گفتگو #' . $convId);
    }

    /** JSON: new messages for the open thread (and mark them read). */
    public function poll(Request $request): Response
    {
        if ($r = $this->guard('support')) {
            return $r;
        }
        $conv = $this->chats->find((int) $request->param('id'));
        if ($conv === null) {
            return $this->json(['ok' => false], 404);
        }
        $convId = (int) $conv['id'];
        $rows   = $this->chats->messagesAfter($convId, max(0, (int) $request->query('after', 0)));
        $this->chats->markRead($convId, 'customer');

        $messages = [];
        foreach ($rows as $m) {
            $messages[] = [
                'id'     => (int) $m['id'],
                'sender' => (string) $m['sender'],
                'body'   => (string) $m['body'],
                'time'   => jdate((string) $m['created_at'], 'H:i'),
            ];
        }
        return $this->json(['ok' => true, 'status' => (string) $conv['status'], 'messages' => $messages]);
    }

    /** JSON: send an admin reply into the thread. */
    public function send(Request $request): Response
    {
        if ($r = $this->guard('support')) {
            return $r;
        }
        $conv = $this->chats->find((int) $request->param('id'));
        if ($conv === null) {
            return $this->json(['ok' => false], 404);
        }
        $body = trim((string) $request->input('message', ''));
        if ($body === '') {
            return $this->json(['ok' => false, 'error' => 'متن پیام را وارد کنید.'], 422);
        }
        $id = $this->chats->addMessage((int) $conv['id'], 'admin', AdminAuthService::id(), mb_substr($body, 0, 2000));
        return $this->json(['ok' => true, 'message_id' => $id]);
    }

    public function close(Request $request): Response
    {
        if ($r = $this->guard('support')) {
            return $r;
        }
        $id = (int) $request->param('id');
        $this->chats->close($id);
        $this->audit($request, 'close', 'chat', $id);
        Session::flash('success', 'گفتگو بسته شد.');
        return $this->redirect(url('/admin/chat'));
    }
}
