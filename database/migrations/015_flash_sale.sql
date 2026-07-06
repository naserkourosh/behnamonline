-- ─────────────────────────────────────────────────────────────
--  بهنام (Behnam) — Flash-sale engine: a per-product sale window.
--  A product is "on flash sale" while on_flash_sale = 1 AND
--  flash_sale_ends_at > NOW(); the sale auto-expires (no cron) and the
--  storefront shows a live countdown to flash_sale_ends_at. The selling
--  price stays in products.price (already the discounted price), so the
--  cart/checkout money path is unchanged.
-- ─────────────────────────────────────────────────────────────

ALTER TABLE products
    ADD COLUMN flash_sale_ends_at DATETIME NULL AFTER on_flash_sale,
    ADD KEY idx_products_flash_ends (on_flash_sale, flash_sale_ends_at);
