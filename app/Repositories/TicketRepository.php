<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\BaseRepository;

final class TicketRepository extends BaseRepository
{
    /* ── Customer side ── */

    /** @return list<array<string,mixed>> */
    public function forUser(int $userId): array
    {
        return $this->selectAll(
            'SELECT * FROM tickets WHERE user_id = ? ORDER BY COALESCE(last_reply_at, created_at) DESC',
            [$userId]
        );
    }

    /** @return array<string,mixed>|null A ticket owned by the given user. */
    public function findForUser(int $id, int $userId): ?array
    {
        return $this->selectOne('SELECT * FROM tickets WHERE id = ? AND user_id = ? LIMIT 1', [$id, $userId]);
    }

    public function create(int $userId, string $subject, string $priority): int
    {
        $this->execute(
            'INSERT INTO tickets (user_id, subject, status, priority, last_reply_at, created_at) VALUES (?,?,?,?,?,?)',
            [$userId, $subject, 'open', $priority, date('Y-m-d H:i:s'), date('Y-m-d H:i:s')]
        );
        return $this->lastInsertId();
    }

    /** @return list<array<string,mixed>> */
    public function messages(int $ticketId): array
    {
        return $this->selectAll(
            'SELECT * FROM ticket_messages WHERE ticket_id = ? ORDER BY id',
            [$ticketId]
        );
    }

    public function addMessage(int $ticketId, string $sender, ?int $authorId, string $body): void
    {
        $this->execute(
            'INSERT INTO ticket_messages (ticket_id, sender, author_id, body, created_at) VALUES (?,?,?,?,?)',
            [$ticketId, $sender, $authorId, $body, date('Y-m-d H:i:s')]
        );
        // A customer reply re-opens; an admin reply marks it answered.
        $status = $sender === 'admin' ? 'answered' : 'open';
        $this->execute(
            'UPDATE tickets SET status = ?, last_reply_at = ? WHERE id = ?',
            [$status, date('Y-m-d H:i:s'), $ticketId]
        );
    }

    public function setStatus(int $id, string $status): void
    {
        if (!in_array($status, ['open', 'answered', 'closed'], true)) {
            return;
        }
        $this->execute('UPDATE tickets SET status = ? WHERE id = ?', [$status, $id]);
    }

    /* ── Admin side ── */

    /** @return list<array<string,mixed>> */
    public function adminList(string $status, int $limit, int $offset): array
    {
        $limit  = max(1, min(100, $limit));
        $offset = max(0, $offset);
        $where  = '1=1';
        $params = [];
        if (in_array($status, ['open', 'answered', 'closed'], true)) {
            $where = 't.status = ?';
            $params = [$status];
        }
        return $this->selectAll(
            "SELECT t.*, u.first_name, u.last_name, u.mobile,
                    (SELECT COUNT(*) FROM ticket_messages m WHERE m.ticket_id = t.id) AS message_count
               FROM tickets t
               JOIN users u ON u.id = t.user_id
              WHERE {$where}
              ORDER BY COALESCE(t.last_reply_at, t.created_at) DESC
              LIMIT {$limit} OFFSET {$offset}",
            $params
        );
    }

    public function adminCount(string $status): int
    {
        if (!in_array($status, ['open', 'answered', 'closed'], true)) {
            return (int) $this->scalar('SELECT COUNT(*) FROM tickets');
        }
        return (int) $this->scalar('SELECT COUNT(*) FROM tickets WHERE status = ?', [$status]);
    }

    public function openCount(): int
    {
        return (int) $this->scalar("SELECT COUNT(*) FROM tickets WHERE status = 'open'");
    }

    /** @return array<string,mixed>|null */
    public function findAny(int $id): ?array
    {
        return $this->selectOne(
            'SELECT t.*, u.first_name, u.last_name, u.mobile
               FROM tickets t JOIN users u ON u.id = t.user_id
              WHERE t.id = ? LIMIT 1',
            [$id]
        );
    }
}
