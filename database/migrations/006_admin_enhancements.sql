-- Phase 5.1 — admin enhancements:
--   1) products.sort  → manual display ordering
--   2) sms_messages    → log of every SMS sent (OTP / order / manual)
--   3) sms_templates   → editable message templates used by order events

ALTER TABLE products
    ADD COLUMN sort INT NOT NULL DEFAULT 0 AFTER view_count,
    ADD KEY idx_products_sort (sort);

CREATE TABLE sms_messages (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    mobile     VARCHAR(20) NOT NULL,
    body       TEXT NOT NULL,
    kind       VARCHAR(30) NOT NULL DEFAULT 'system',
    status     ENUM('sent','failed') NOT NULL DEFAULT 'sent',
    driver     VARCHAR(30) NOT NULL DEFAULT 'mock',
    created_at DATETIME NOT NULL,
    KEY idx_sms_created (created_at),
    KEY idx_sms_kind (kind)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE sms_templates (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    tkey       VARCHAR(50) NOT NULL,
    title      VARCHAR(120) NOT NULL,
    body       TEXT NOT NULL,
    is_active  TINYINT(1) NOT NULL DEFAULT 1,
    updated_at DATETIME NULL,
    UNIQUE KEY uq_sms_tkey (tkey)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

INSERT IGNORE INTO sms_templates (tkey, title, body, is_active, updated_at) VALUES
    ('payment_confirmed', 'تایید پرداخت سفارش', 'بهنام\nپرداخت سفارش {order} تایید شد. ✅\nکد رهگیری: {tracking}', 1, NOW()),
    ('order_paid', 'پرداخت موفق آنلاین', 'بهنام\nسفارش {order} با موفقیت پرداخت شد. ✅\nکد رهگیری پستی: {tracking}', 1, NOW()),
    ('order_shipped', 'ارسال سفارش', 'بهنام\nسفارش {order} ارسال شد. 🚚\nکد رهگیری: {tracking}', 1, NOW());
