<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\BaseRepository;

final class ChatRepository extends BaseRepository
{
    /** @return array<string,mixed>|null */
    public function find(int $id): ?array
    {
        return $this->selectOne('SELECT * FROM chat_conversations WHERE id = ? LIMIT 1', [$id]);
    }

    public function create(?int $userId, ?string $guestName): int
    {
        $this->execute(
            'INSERT INTO chat_conversations (user_id, guest_name, status, last_message_at, created_at) VALUES (?,?,?,?,?)',
            [$userId, $guestName, 'open', date('Y-m-d H:i:s'), date('Y-m-d H:i:s')]
        );
        return $this->lastInsertId();
    }

    /** Attach a user to a guest conversation (customer logged in mid-chat). */
    public function setUser(int $id, int $userId): void
    {
        $this->execute('UPDATE chat_conversations SET user_id = ? WHERE id = ? AND user_id IS NULL', [$userId, $id]);
    }

    public function addMessage(int $conversationId, string $sender, ?int $adminId, string $body): int
    {
        $this->execute(
            'INSERT INTO chat_messages (conversation_id, sender, admin_id, body, is_read, created_at) VALUES (?,?,?,?,0,?)',
            [$conversationId, $sender, $adminId, $body, date('Y-m-d H:i:s')]
        );
        $id = $this->lastInsertId();
        // Any new message bumps the thread; a customer message re-opens a closed one.
        $this->execute(
            $sender === 'customer'
                ? "UPDATE chat_conversations SET status = 'open', last_message_at = ? WHERE id = ?"
                : 'UPDATE chat_conversations SET last_message_at = ? WHERE id = ?',
            [date('Y-m-d H:i:s'), $conversationId]
        );
        return $id;
    }

    /** @return list<array<string,mixed>> Messages newer than $afterId (0 = full thread). */
    public function messagesAfter(int $conversationId, int $afterId): array
    {
        return $this->selectAll(
            'SELECT id, sender, body, created_at FROM chat_messages
              WHERE conversation_id = ? AND id > ? ORDER BY id LIMIT 200',
            [$conversationId, $afterId]
        );
    }

    /** Mark all of one side's messages in a conversation as read by the other side. */
    public function markRead(int $conversationId, string $sender): void
    {
        $this->execute(
            'UPDATE chat_messages SET is_read = 1 WHERE conversation_id = ? AND sender = ? AND is_read = 0',
            [$conversationId, $sender]
        );
    }

    public function close(int $id): void
    {
        $this->execute("UPDATE chat_conversations SET status = 'closed' WHERE id = ?", [$id]);
    }

    /* ── Admin side ── */

    /** @return list<array<string,mixed>> */
    public function adminList(string $status, int $limit = 50): array
    {
        $limit  = max(1, min(100, $limit));
        $where  = in_array($status, ['open', 'closed'], true) ? 'c.status = ?' : '1=1';
        $params = $where === '1=1' ? [] : [$status];

        return $this->selectAll(
            "SELECT c.*, u.first_name, u.last_name, u.mobile,
                    (SELECT COUNT(*) FROM chat_messages m
                      WHERE m.conversation_id = c.id AND m.sender = 'customer' AND m.is_read = 0) AS unread,
                    (SELECT m.body FROM chat_messages m
                      WHERE m.conversation_id = c.id ORDER BY m.id DESC LIMIT 1) AS last_body
               FROM chat_conversations c
          LEFT JOIN users u ON u.id = c.user_id
              WHERE {$where}
              ORDER BY (c.status = 'open') DESC, c.last_message_at DESC
              LIMIT {$limit}",
            $params
        );
    }

    /** Total customer messages not yet read by any admin (sidebar badge). */
    public function adminUnreadTotal(): int
    {
        return (int) $this->scalar(
            "SELECT COUNT(*) FROM chat_messages WHERE sender = 'customer' AND is_read = 0"
        );
    }

    /** @return array<string,mixed>|null Conversation + customer info for the admin thread. */
    public function findWithUser(int $id): ?array
    {
        return $this->selectOne(
            'SELECT c.*, u.first_name, u.last_name, u.mobile
               FROM chat_conversations c
          LEFT JOIN users u ON u.id = c.user_id
              WHERE c.id = ? LIMIT 1',
            [$id]
        );
    }
}
