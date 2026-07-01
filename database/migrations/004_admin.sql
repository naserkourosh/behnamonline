-- ─────────────────────────────────────────────────────────────
--  بهنام (Behnam) — Phase 5: admin panel (users, tags, menus, audit)
-- ─────────────────────────────────────────────────────────────

CREATE TABLE admin_users (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(60) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    name          VARCHAR(120) NOT NULL,
    email         VARCHAR(150) NULL,
    role          ENUM('super','manager','editor') NOT NULL DEFAULT 'manager',
    is_active     TINYINT(1) NOT NULL DEFAULT 1,
    last_login_at DATETIME NULL,
    created_at    DATETIME NULL,
    UNIQUE KEY uq_admin_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE tags (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(120) NOT NULL,
    slug       VARCHAR(140) NOT NULL,
    created_at DATETIME NULL,
    UNIQUE KEY uq_tags_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE product_tags (
    product_id INT UNSIGNED NOT NULL,
    tag_id     INT UNSIGNED NOT NULL,
    PRIMARY KEY (product_id, tag_id),
    KEY idx_ptags_tag (tag_id),
    CONSTRAINT fk_ptags_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE,
    CONSTRAINT fk_ptags_tag FOREIGN KEY (tag_id) REFERENCES tags (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE menus (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(120) NOT NULL,
    slug       VARCHAR(140) NOT NULL,
    created_at DATETIME NULL,
    UNIQUE KEY uq_menus_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE menu_items (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    menu_id    INT UNSIGNED NOT NULL,
    parent_id  INT UNSIGNED NULL,
    label      VARCHAR(120) NOT NULL,
    url        VARCHAR(255) NOT NULL DEFAULT '#',
    sort       INT NOT NULL DEFAULT 0,
    created_at DATETIME NULL,
    KEY idx_mitems_menu (menu_id),
    CONSTRAINT fk_mitems_menu FOREIGN KEY (menu_id) REFERENCES menus (id) ON DELETE CASCADE,
    CONSTRAINT fk_mitems_parent FOREIGN KEY (parent_id) REFERENCES menu_items (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE audit_logs (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    admin_id   INT UNSIGNED NULL,
    action     VARCHAR(40) NOT NULL,
    entity     VARCHAR(60) NOT NULL,
    entity_id  INT UNSIGNED NULL,
    meta       VARCHAR(500) NULL,
    ip         VARCHAR(45) NULL,
    created_at DATETIME NULL,
    KEY idx_audit_admin (admin_id),
    KEY idx_audit_entity (entity, entity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;
