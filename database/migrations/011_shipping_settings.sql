-- ─────────────────────────────────────────────────────────────
--  بهنام (Behnam) — shipping refinements:
--   • Postal fees now come from the National Post web service, so the
--     old nationwide '*' post/tipax rows in shipping_zones are obsolete
--     (تیپاکس is disabled). Only city-specific courier rules remain
--     (e.g. گرگان → پیک موتوری).
--   • Seed the shipping toggles/ETA texts (managed at /admin/shipping).
--   • Postal tracking is sent by the admin AFTER the parcel ships, so the
--     payment-time SMS templates must NOT contain a tracking code.
-- ─────────────────────────────────────────────────────────────

DELETE FROM shipping_zones WHERE city = '*';

INSERT IGNORE INTO settings (setting_key, setting_value, setting_type, updated_at) VALUES
    ('shipping_post_enabled',    '1', 'bool', NOW()),
    ('shipping_collect_enabled', '0', 'bool', NOW()),
    ('shipping_eta_enabled',     '1', 'bool', NOW()),
    ('shipping_eta_gorgan',      'کمتر از یک روز کاری', 'string', NOW()),
    ('shipping_eta_default',     '۲ تا ۴ روز کاری', 'string', NOW());

UPDATE sms_templates SET body = 'بهنام\nپرداخت سفارش {order} تایید شد. ✅\nسفارش شما در حال آماده‌سازی است.' WHERE tkey = 'payment_confirmed';
UPDATE sms_templates SET body = 'بهنام\nسفارش {order} با موفقیت پرداخت شد. ✅\nپس از ارسال مرسوله، کد رهگیری پستی برای شما پیامک می‌شود.' WHERE tkey = 'order_paid';
