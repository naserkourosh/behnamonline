-- Manual per-product availability: stock counts are approximate (see
-- migration 021), so «اتمام موجودی» is a simple checkbox the admin sets.
-- When 1 the product shows as out of stock and cannot be added to the cart.

ALTER TABLE products
    ADD COLUMN is_out_of_stock TINYINT(1) NOT NULL DEFAULT 0 AFTER stock,
    ADD KEY idx_products_oos (is_out_of_stock);
