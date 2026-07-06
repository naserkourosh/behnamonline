-- ─────────────────────────────────────────────────────────────
--  بهنام (Behnam) — Live chat (گفتگوی آنلاین): polling-based
--  admin ↔ customer conversations, separate from support tickets.
--  Guests can chat too (conversation is bound to the PHP session);
--  logged-in customers get their user attached.
-- ─────────────────────────────────────────────────────────────

CREATE TABLE chat_conversations (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED NULL,
    guest_name      VARCHAR(100) NULL,
    status          VARCHAR(10) NOT NULL DEFAULT 'open',
    last_message_at DATETIME NULL,
    created_at      DATETIME NOT NULL,
    KEY idx_chat_status (status, last_message_at),
    KEY idx_chat_user (user_id),
    CONSTRAINT fk_chat_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE chat_messages (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    conversation_id INT UNSIGNED NOT NULL,
    sender          VARCHAR(10) NOT NULL,
    admin_id        INT UNSIGNED NULL,
    body            VARCHAR(2000) NOT NULL,
    is_read         TINYINT(1) NOT NULL DEFAULT 0,
    created_at      DATETIME NOT NULL,
    KEY idx_chatmsg_conv (conversation_id, id),
    KEY idx_chatmsg_unread (conversation_id, sender, is_read),
    CONSTRAINT fk_chatmsg_conv FOREIGN KEY (conversation_id)
        REFERENCES chat_conversations (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- Widget on/off switch (admin-editable).
INSERT IGNORE INTO settings (setting_key, setting_value, setting_type, updated_at)
VALUES ('chat_enabled', '1', 'bool', NOW());
