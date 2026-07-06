<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Session;
use App\Repositories\ChatRepository;

/**
 * Storefront side of the live chat. The visitor's conversation id lives in the
 * session (guests included); the widget polls for new messages and posts
 * replies. A message to a closed conversation re-opens it.
 */
final class ChatService
{
    private const KEY = 'chat_conversation_id';

    private ChatRepository $chats;

    public function __construct()
    {
        $this->chats = new ChatRepository();
    }

    public function enabled(): bool
    {
        return (bool) \setting('chat_enabled', true);
    }

    /** The visitor's conversation row, or null when they never chatted. */
    public function conversation(): ?array
    {
        $id = (int) Session::get(self::KEY, 0);
        if ($id <= 0) {
            return null;
        }
        $conv = $this->chats->find($id);
        if ($conv === null) {
            Session::forget(self::KEY);
        }
        return $conv;
    }

    /**
     * Send a customer message, creating the conversation on first contact.
     * @return array{ok:bool,error:?string,conversation_id:int,message_id:int}
     */
    public function send(string $body, string $guestName = ''): array
    {
        $body = trim($body);
        if ($body === '' || mb_strlen($body) < 1) {
            return ['ok' => false, 'error' => 'متن پیام را وارد کنید.', 'conversation_id' => 0, 'message_id' => 0];
        }
        $body = mb_substr($body, 0, 2000);

        $conv   = $this->conversation();
        $userId = (int) AuthService::id();

        if ($conv === null) {
            $name  = $guestName !== '' ? mb_substr(trim($guestName), 0, 100) : null;
            $newId = $this->chats->create($userId > 0 ? $userId : null, $name);
            Session::set(self::KEY, $newId);
            $convId = $newId;
        } else {
            $convId = (int) $conv['id'];
            // Visitor logged in since starting the chat — attach their account.
            if ($userId > 0 && empty($conv['user_id'])) {
                $this->chats->setUser($convId, $userId);
            }
        }

        $msgId = $this->chats->addMessage($convId, 'customer', null, $body);

        return ['ok' => true, 'error' => null, 'conversation_id' => $convId, 'message_id' => $msgId];
    }

    /**
     * New messages for the widget (and mark admin replies as delivered/read).
     * @return array{active:bool,status:string,messages:list<array<string,mixed>>}
     */
    public function poll(int $afterId): array
    {
        $conv = $this->conversation();
        if ($conv === null) {
            return ['active' => false, 'status' => 'none', 'messages' => []];
        }
        $convId = (int) $conv['id'];
        $rows   = $this->chats->messagesAfter($convId, max(0, $afterId));
        $this->chats->markRead($convId, 'admin');

        $messages = [];
        foreach ($rows as $m) {
            $messages[] = [
                'id'     => (int) $m['id'],
                'sender' => (string) $m['sender'],
                'body'   => (string) $m['body'],
                'time'   => \jdate((string) $m['created_at'], 'H:i'),
            ];
        }

        return ['active' => true, 'status' => (string) $conv['status'], 'messages' => $messages];
    }
}
