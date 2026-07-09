-- Stock behavior toggles:
--   stock_enforce_enabled → 0 (default): stock counts are advisory and never
--                           block a purchase or show «ناموجود». Most products
--                           carry approximate/unentered counts.
--   show_stock_qty        → hidden from customers by default.

INSERT IGNORE INTO settings (setting_key, setting_value, setting_type, updated_at) VALUES
    ('stock_enforce_enabled', '0', 'bool', NOW());

UPDATE settings SET setting_value = '0', updated_at = NOW() WHERE setting_key = 'show_stock_qty';
