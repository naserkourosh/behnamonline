-- Per-product stock control (replaces the global stock_enforce_enabled /
-- show_stock_qty settings):
--   is_out_of_stock = 1                → never purchasable (absolute).
--   else track_stock = 1               → the numeric count governs: shown to
--                                        customers, low-stock warning, and
--                                        zero blocks the purchase.
--   else                               → always purchasable, no counts shown.

ALTER TABLE products
    ADD COLUMN track_stock TINYINT(1) NOT NULL DEFAULT 0 AFTER is_out_of_stock;
