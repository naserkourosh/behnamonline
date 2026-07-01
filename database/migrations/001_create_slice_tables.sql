-- ─────────────────────────────────────────────────────────────
--  بهنام (Behnam) — storefront slice schema
--  MySQL 8, InnoDB, utf8mb4_persian_ci
-- ─────────────────────────────────────────────────────────────

CREATE TABLE settings (
    setting_key   VARCHAR(100) NOT NULL PRIMARY KEY,
    setting_value TEXT NULL,
    setting_type  VARCHAR(20) NOT NULL DEFAULT 'string',
    updated_at    DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE categories (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    parent_id       INT UNSIGNED NULL,
    name            VARCHAR(150) NOT NULL,
    slug            VARCHAR(160) NOT NULL,
    image           VARCHAR(255) NULL,
    sort            INT NOT NULL DEFAULT 0,
    is_active       TINYINT(1) NOT NULL DEFAULT 1,
    seo_title       VARCHAR(190) NULL,
    seo_description VARCHAR(300) NULL,
    created_at      DATETIME NULL,
    UNIQUE KEY uq_categories_slug (slug),
    KEY idx_categories_parent (parent_id),
    CONSTRAINT fk_categories_parent FOREIGN KEY (parent_id)
        REFERENCES categories (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE brands (
    id        INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name      VARCHAR(150) NOT NULL,
    slug      VARCHAR(160) NOT NULL,
    logo      VARCHAR(255) NULL,
    sort      INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    UNIQUE KEY uq_brands_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE products (
    id                   INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    category_id          INT UNSIGNED NULL,
    brand_id             INT UNSIGNED NULL,
    name                 VARCHAR(255) NOT NULL,
    slug                 VARCHAR(260) NOT NULL,
    sku                  VARCHAR(80) NULL,
    barcode              VARCHAR(80) NULL,
    short_desc           VARCHAR(500) NULL,
    description          LONGTEXT NULL,
    aparat_embed         VARCHAR(255) NULL,
    price                BIGINT UNSIGNED NOT NULL DEFAULT 0,
    old_price            BIGINT UNSIGNED NULL,
    stock                INT NOT NULL DEFAULT 0,
    reserved             INT NOT NULL DEFAULT 0,
    low_stock_threshold  INT NOT NULL DEFAULT 3,
    is_active            TINYINT(1) NOT NULL DEFAULT 1,
    is_new               TINYINT(1) NOT NULL DEFAULT 0,
    is_featured          TINYINT(1) NOT NULL DEFAULT 0,
    on_flash_sale        TINYINT(1) NOT NULL DEFAULT 0,
    expiration_date      DATE NULL,
    rating_avg           DECIMAL(3,2) NOT NULL DEFAULT 0,
    rating_count         INT NOT NULL DEFAULT 0,
    view_count           INT NOT NULL DEFAULT 0,
    seo_title            VARCHAR(190) NULL,
    seo_description      VARCHAR(300) NULL,
    og_image             VARCHAR(255) NULL,
    created_at           DATETIME NULL,
    updated_at           DATETIME NULL,
    UNIQUE KEY uq_products_slug (slug),
    KEY idx_products_category (category_id),
    KEY idx_products_brand (brand_id),
    KEY idx_products_active (is_active),
    KEY idx_products_flags (is_featured, is_new, on_flash_sale),
    CONSTRAINT fk_products_category FOREIGN KEY (category_id)
        REFERENCES categories (id) ON DELETE SET NULL,
    CONSTRAINT fk_products_brand FOREIGN KEY (brand_id)
        REFERENCES brands (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE product_images (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    path       VARCHAR(255) NOT NULL,
    alt        VARCHAR(190) NULL,
    title      VARCHAR(190) NULL,
    sort       INT NOT NULL DEFAULT 0,
    is_primary TINYINT(1) NOT NULL DEFAULT 0,
    is_hover   TINYINT(1) NOT NULL DEFAULT 0,
    KEY idx_pimg_product (product_id),
    CONSTRAINT fk_pimg_product FOREIGN KEY (product_id)
        REFERENCES products (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE product_attributes (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    attr_key   VARCHAR(120) NOT NULL,
    attr_value VARCHAR(255) NOT NULL,
    sort       INT NOT NULL DEFAULT 0,
    KEY idx_pattr_product (product_id),
    CONSTRAINT fk_pattr_product FOREIGN KEY (product_id)
        REFERENCES products (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE product_variants (
    id             INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    product_id     INT UNSIGNED NOT NULL,
    label          VARCHAR(120) NOT NULL,
    sku            VARCHAR(80) NULL,
    price_override BIGINT UNSIGNED NULL,
    stock          INT NOT NULL DEFAULT 0,
    sort           INT NOT NULL DEFAULT 0,
    KEY idx_pvar_product (product_id),
    CONSTRAINT fk_pvar_product FOREIGN KEY (product_id)
        REFERENCES products (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE reviews (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    product_id  INT UNSIGNED NOT NULL,
    user_id     INT UNSIGNED NULL,
    author_name VARCHAR(120) NOT NULL,
    rating      TINYINT NOT NULL DEFAULT 5,
    body        TEXT NOT NULL,
    is_verified TINYINT(1) NOT NULL DEFAULT 0,
    status      ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    created_at  DATETIME NULL,
    KEY idx_reviews_product (product_id),
    KEY idx_reviews_status (status),
    CONSTRAINT fk_reviews_product FOREIGN KEY (product_id)
        REFERENCES products (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE review_images (
    id        INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    review_id INT UNSIGNED NOT NULL,
    path      VARCHAR(255) NOT NULL,
    KEY idx_rimg_review (review_id),
    CONSTRAINT fk_rimg_review FOREIGN KEY (review_id)
        REFERENCES reviews (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE carts (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    token      VARCHAR(64) NOT NULL,
    user_id    INT UNSIGNED NULL,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    UNIQUE KEY uq_carts_token (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE cart_items (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    cart_id    INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    variant_id INT UNSIGNED NULL,
    qty        INT NOT NULL DEFAULT 1,
    unit_price BIGINT UNSIGNED NOT NULL DEFAULT 0,
    created_at DATETIME NULL,
    UNIQUE KEY uq_cart_line (cart_id, product_id, variant_id),
    KEY idx_citems_cart (cart_id),
    CONSTRAINT fk_citems_cart FOREIGN KEY (cart_id)
        REFERENCES carts (id) ON DELETE CASCADE,
    CONSTRAINT fk_citems_product FOREIGN KEY (product_id)
        REFERENCES products (id) ON DELETE CASCADE,
    CONSTRAINT fk_citems_variant FOREIGN KEY (variant_id)
        REFERENCES product_variants (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;
