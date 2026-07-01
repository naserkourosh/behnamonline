-- Phase 6 — Content, support & integrations:
--   blog (categories/posts/comments), FAQ, and support tickets.

CREATE TABLE blog_categories (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(120) NOT NULL,
    slug       VARCHAR(160) NOT NULL,
    sort       INT NOT NULL DEFAULT 0,
    is_active  TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL,
    UNIQUE KEY uq_blogcat_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE blog_posts (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    category_id     INT UNSIGNED NULL,
    title           VARCHAR(200) NOT NULL,
    slug            VARCHAR(220) NOT NULL,
    excerpt         VARCHAR(400) NULL,
    body            MEDIUMTEXT NULL,
    cover_image     VARCHAR(255) NULL,
    author_name     VARCHAR(120) NULL,
    status          ENUM('draft','published') NOT NULL DEFAULT 'draft',
    is_featured     TINYINT(1) NOT NULL DEFAULT 0,
    view_count      INT NOT NULL DEFAULT 0,
    seo_title       VARCHAR(190) NULL,
    seo_description VARCHAR(300) NULL,
    published_at    DATETIME NULL,
    created_at      DATETIME NOT NULL,
    updated_at      DATETIME NOT NULL,
    UNIQUE KEY uq_blogpost_slug (slug),
    KEY idx_blogpost_status (status, published_at),
    KEY idx_blogpost_category (category_id),
    CONSTRAINT fk_blogpost_category FOREIGN KEY (category_id)
        REFERENCES blog_categories (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE blog_comments (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    post_id     INT UNSIGNED NOT NULL,
    user_id     INT UNSIGNED NULL,
    author_name VARCHAR(120) NOT NULL,
    body        TEXT NOT NULL,
    status      ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    created_at  DATETIME NOT NULL,
    KEY idx_blogcomment_post (post_id, status),
    CONSTRAINT fk_blogcomment_post FOREIGN KEY (post_id)
        REFERENCES blog_posts (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE faqs (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    category   VARCHAR(120) NOT NULL DEFAULT 'عمومی',
    question   VARCHAR(255) NOT NULL,
    answer     TEXT NOT NULL,
    sort       INT NOT NULL DEFAULT 0,
    is_active  TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL,
    KEY idx_faq_active (is_active, sort)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE tickets (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id       INT UNSIGNED NOT NULL,
    subject       VARCHAR(200) NOT NULL,
    status        ENUM('open','answered','closed') NOT NULL DEFAULT 'open',
    priority      ENUM('low','normal','high') NOT NULL DEFAULT 'normal',
    last_reply_at DATETIME NULL,
    created_at    DATETIME NOT NULL,
    KEY idx_ticket_user (user_id),
    KEY idx_ticket_status (status),
    CONSTRAINT fk_ticket_user FOREIGN KEY (user_id)
        REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE ticket_messages (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    ticket_id  INT UNSIGNED NOT NULL,
    sender     ENUM('customer','admin') NOT NULL,
    author_id  INT UNSIGNED NULL,
    body       TEXT NOT NULL,
    created_at DATETIME NOT NULL,
    KEY idx_tmsg_ticket (ticket_id),
    CONSTRAINT fk_tmsg_ticket FOREIGN KEY (ticket_id)
        REFERENCES tickets (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;
