-- ─────────────────────────────────────────────────────────────
--  بهنام (Behnam) — products ↔ categories many-to-many
--  A product can belong to several categories. `products.category_id`
--  is kept as the PRIMARY category (breadcrumb/SEO); this pivot holds
--  the full membership used for category listings & counts.
-- ─────────────────────────────────────────────────────────────

CREATE TABLE product_categories (
    product_id  INT UNSIGNED NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (product_id, category_id),
    KEY idx_pc_category (category_id),
    CONSTRAINT fk_pc_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE,
    CONSTRAINT fk_pc_category FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- Backfill membership from the existing primary category.
INSERT IGNORE INTO product_categories (product_id, category_id)
    SELECT id, category_id FROM products WHERE category_id IS NOT NULL;
