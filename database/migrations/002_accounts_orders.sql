-- ─────────────────────────────────────────────────────────────
--  بهنام (Behnam) — Phase 2: accounts, OTP, addresses, orders
-- ─────────────────────────────────────────────────────────────

CREATE TABLE users (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    first_name    VARCHAR(80) NULL,
    last_name     VARCHAR(80) NULL,
    mobile        VARCHAR(11) NOT NULL,
    email         VARCHAR(150) NULL,
    wallet_balance BIGINT NOT NULL DEFAULT 0,
    reward_points INT NOT NULL DEFAULT 0,
    is_active     TINYINT(1) NOT NULL DEFAULT 1,
    last_login_at DATETIME NULL,
    created_at    DATETIME NULL,
    updated_at    DATETIME NULL,
    UNIQUE KEY uq_users_mobile (mobile)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE otp_codes (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    mobile     VARCHAR(11) NOT NULL,
    code_hash  CHAR(64) NOT NULL,
    purpose    ENUM('login','checkout') NOT NULL DEFAULT 'login',
    expires_at DATETIME NOT NULL,
    attempts   TINYINT NOT NULL DEFAULT 0,
    consumed   TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NULL,
    KEY idx_otp_mobile (mobile, purpose)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE addresses (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id       INT UNSIGNED NOT NULL,
    receiver_name VARCHAR(160) NOT NULL,
    mobile        VARCHAR(11) NOT NULL,
    province      VARCHAR(80) NOT NULL,
    city          VARCHAR(80) NOT NULL,
    address       VARCHAR(500) NOT NULL,
    postal_code   VARCHAR(12) NULL,
    is_default    TINYINT(1) NOT NULL DEFAULT 0,
    created_at    DATETIME NULL,
    KEY idx_addr_user (user_id),
    CONSTRAINT fk_addr_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE orders (
    id             INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    order_number   VARCHAR(20) NOT NULL,
    user_id        INT UNSIGNED NULL,
    status         ENUM('pending','processing','shipped','delivered','canceled') NOT NULL DEFAULT 'processing',
    subtotal       BIGINT NOT NULL DEFAULT 0,
    discount       BIGINT NOT NULL DEFAULT 0,
    shipping_cost  BIGINT NOT NULL DEFAULT 0,
    total          BIGINT NOT NULL DEFAULT 0,
    shipping_method VARCHAR(80) NULL,
    payment_method VARCHAR(80) NULL,
    payment_status ENUM('unpaid','paid','failed') NOT NULL DEFAULT 'unpaid',
    receiver_name  VARCHAR(160) NULL,
    mobile         VARCHAR(11) NULL,
    province       VARCHAR(80) NULL,
    city           VARCHAR(80) NULL,
    address        VARCHAR(500) NULL,
    postal_code    VARCHAR(12) NULL,
    tracking_code  VARCHAR(40) NULL,
    note           VARCHAR(500) NULL,
    created_at     DATETIME NULL,
    updated_at     DATETIME NULL,
    UNIQUE KEY uq_orders_number (order_number),
    KEY idx_orders_user (user_id),
    CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE order_items (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    order_id      INT UNSIGNED NOT NULL,
    product_id    INT UNSIGNED NULL,
    variant_id    INT UNSIGNED NULL,
    name          VARCHAR(255) NOT NULL,
    variant_label VARCHAR(120) NULL,
    qty           INT NOT NULL DEFAULT 1,
    unit_price    BIGINT NOT NULL DEFAULT 0,
    line_total    BIGINT NOT NULL DEFAULT 0,
    KEY idx_oitems_order (order_id),
    CONSTRAINT fk_oitems_order FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE,
    CONSTRAINT fk_oitems_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE wishlists (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    created_at DATETIME NULL,
    UNIQUE KEY uq_wishlist (user_id, product_id),
    KEY idx_wishlist_user (user_id),
    CONSTRAINT fk_wishlist_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_wishlist_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;
