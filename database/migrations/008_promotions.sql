-- Phase 4 — Promotions & engagement:
--   coupons (+ usages), reward-point ledger, and the popup manager.

CREATE TABLE coupons (
    id             INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    code           VARCHAR(40) NOT NULL,
    description    VARCHAR(190) NULL,
    type           ENUM('percent','fixed') NOT NULL DEFAULT 'percent',
    value          BIGINT NOT NULL DEFAULT 0,
    min_cart       BIGINT NOT NULL DEFAULT 0,
    max_discount   BIGINT NULL,
    usage_limit    INT NULL,
    per_user_limit INT NULL,
    used_count     INT NOT NULL DEFAULT 0,
    starts_at      DATETIME NULL,
    ends_at        DATETIME NULL,
    is_active      TINYINT(1) NOT NULL DEFAULT 1,
    created_at     DATETIME NOT NULL,
    UNIQUE KEY uq_coupon_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE coupon_usages (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    coupon_id  INT UNSIGNED NOT NULL,
    user_id    INT UNSIGNED NULL,
    order_id   INT UNSIGNED NULL,
    discount   BIGINT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    KEY idx_cu_coupon (coupon_id),
    KEY idx_cu_user (coupon_id, user_id),
    CONSTRAINT fk_cu_coupon FOREIGN KEY (coupon_id) REFERENCES coupons (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE point_transactions (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    order_id   INT UNSIGNED NULL,
    points     INT NOT NULL,
    type       VARCHAR(20) NOT NULL DEFAULT 'earn',
    note       VARCHAR(190) NULL,
    created_at DATETIME NOT NULL,
    KEY idx_pt_user (user_id),
    CONSTRAINT fk_pt_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE popups (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    title         VARCHAR(160) NOT NULL,
    body          TEXT NULL,
    image         VARCHAR(255) NULL,
    cta_label     VARCHAR(80) NULL,
    cta_url       VARCHAR(255) NULL,
    position      ENUM('center','corner') NOT NULL DEFAULT 'center',
    delay_seconds INT NOT NULL DEFAULT 3,
    frequency     ENUM('once_session','once_day','always') NOT NULL DEFAULT 'once_session',
    target        VARCHAR(120) NOT NULL DEFAULT 'all',
    starts_at     DATETIME NULL,
    ends_at       DATETIME NULL,
    is_active     TINYINT(1) NOT NULL DEFAULT 1,
    sort          INT NOT NULL DEFAULT 0,
    created_at    DATETIME NOT NULL,
    KEY idx_popup_active (is_active, sort)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

ALTER TABLE orders
    ADD COLUMN coupon_code     VARCHAR(40) NULL AFTER discount,
    ADD COLUMN coupon_discount BIGINT NOT NULL DEFAULT 0 AFTER coupon_code,
    ADD COLUMN points_earned   INT NOT NULL DEFAULT 0 AFTER coupon_discount;
