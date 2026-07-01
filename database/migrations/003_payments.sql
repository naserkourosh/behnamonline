-- ─────────────────────────────────────────────────────────────
--  بهنام (Behnam) — Phase 3: payment transactions
-- ─────────────────────────────────────────────────────────────

CREATE TABLE payments (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    order_id   INT UNSIGNED NOT NULL,
    gateway    VARCHAR(40) NOT NULL,
    amount     BIGINT NOT NULL DEFAULT 0,
    authority  VARCHAR(120) NULL,
    ref_id     VARCHAR(120) NULL,
    status     ENUM('initiated','paid','failed','canceled') NOT NULL DEFAULT 'initiated',
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    KEY idx_payments_order (order_id),
    KEY idx_payments_authority (authority),
    CONSTRAINT fk_payments_order FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;
