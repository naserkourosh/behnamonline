-- ─────────────────────────────────────────────────────────────
--  بهنام (Behnam) — Admin polish: banners, shipping zones,
--  and per-user capability overrides for granular RBAC.
-- ─────────────────────────────────────────────────────────────

-- Homepage / storefront banners (hero slider, promo strips, sidebars).
CREATE TABLE banners (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    title        VARCHAR(160) NOT NULL,
    subtitle     VARCHAR(255) NULL,
    kicker       VARCHAR(120) NULL,
    image        VARCHAR(255) NULL,
    link_url     VARCHAR(255) NOT NULL DEFAULT '#',
    cta_label    VARCHAR(80) NULL,
    placement    ENUM('hero','promo','strip') NOT NULL DEFAULT 'hero',
    bg_color     VARCHAR(120) NULL,
    sort         INT NOT NULL DEFAULT 0,
    starts_at    DATETIME NULL,
    ends_at      DATETIME NULL,
    is_active    TINYINT(1) NOT NULL DEFAULT 1,
    created_at   DATETIME NOT NULL,
    KEY idx_banner_place (placement, is_active, sort)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- Shipping zones: city-specific rules + editable nationwide methods.
-- A row with city = '*' represents a default nationwide method.
CREATE TABLE shipping_zones (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    city         VARCHAR(80) NOT NULL,
    method_key   VARCHAR(40) NOT NULL,
    method_label VARCHAR(120) NOT NULL,
    note         VARCHAR(160) NULL,
    cost         BIGINT NOT NULL DEFAULT 0,
    free_over    BIGINT NULL,
    sort         INT NOT NULL DEFAULT 0,
    is_active    TINYINT(1) NOT NULL DEFAULT 1,
    created_at   DATETIME NOT NULL,
    KEY idx_zone_city (city, is_active, sort)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- Seed the shipping zones that previously lived in config/shipping.php:
-- two nationwide methods (city '*') plus the Gorgan motorbike-courier rule.
INSERT INTO shipping_zones (city, method_key, method_label, note, cost, free_over, sort, is_active, created_at) VALUES
    ('*',    'post',    'پست پیشتاز',      '۲ تا ۳ روز کاری', 45000, 500000, 0, 1, NOW()),
    ('*',    'tipax',   'تیپاکس (سریع)',   '۱ روز کاری',      45000, NULL,   1, 1, NOW()),
    ('گرگان', 'courier', 'پیک موتوری',      'تحویل امروز',     35000, NULL,   0, 1, NOW());

-- Granular RBAC: per-admin capability override (comma-separated caps).
-- NULL / empty means "use the role's default capability set".
ALTER TABLE admin_users
    ADD COLUMN capabilities VARCHAR(1000) NULL AFTER role;
