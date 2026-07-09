-- ─────────────────────────────────────────────────────────────
--  بهنام (Behnam) —
--   • Tag grouping (a category label per tag) so large tag lists
--     stay findable in the admin + product form.
--   • Helper indexes for SKU lookups and price sorting.
--     (Search uses plain LIKE — production runs MariaDB, which has no
--      ngram FULLTEXT parser, so no FULLTEXT index is created.)
-- ─────────────────────────────────────────────────────────────

ALTER TABLE tags
    ADD COLUMN tag_group VARCHAR(80) NULL AFTER name,
    ADD KEY idx_tags_group (tag_group);

ALTER TABLE products
    ADD KEY idx_products_sku (sku),
    ADD KEY idx_products_price (price);
