-- ─────────────────────────────────────────────────────────────
--  بهنام (Behnam) — "remember me" for the admin panel.
--  Selector:validator scheme: the cookie carries selector + a random
--  validator; only the SHA-256 hash of the validator is stored, so a DB
--  leak cannot forge cookies. Tokens rotate on every use and expire.
-- ─────────────────────────────────────────────────────────────

CREATE TABLE admin_remember_tokens (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    admin_user_id INT UNSIGNED NOT NULL,
    selector      CHAR(18) NOT NULL,
    token_hash    CHAR(64) NOT NULL,
    expires_at    DATETIME NOT NULL,
    created_at    DATETIME NOT NULL,
    UNIQUE KEY uq_admin_remember_selector (selector),
    KEY idx_admin_remember_user (admin_user_id),
    CONSTRAINT fk_admin_remember_user FOREIGN KEY (admin_user_id)
        REFERENCES admin_users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;
