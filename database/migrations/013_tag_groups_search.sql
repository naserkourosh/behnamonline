-- ─────────────────────────────────────────────────────────────
--  بهنام (Behnam) —
--   • Tag grouping (a category label per tag) so large tag lists
--     stay findable in the admin + product form.
--   • Faster product search: an ngram FULLTEXT index on the product
--     name (substring search without a full-table LIKE scan) plus
--     helper indexes for SKU lookups and price sorting.
-- ─────────────────────────────────────────────────────────────

ALTER TABLE tags
    ADD COLUMN tag_group VARCHAR(80) NULL AFTER name,
    ADD KEY idx_tags_group (tag_group);

ALTER TABLE products
    ADD FULLTEXT INDEX idx_ft_product_name (name) WITH PARSER ngram;

ALTER TABLE products
    ADD KEY idx_products_sku (sku),
    ADD KEY idx_products_price (price);
