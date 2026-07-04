-- ─────────────────────────────────────────────────────────────
--  بهنام (Behnam) — SMS template sent right after the customer
--  confirms the shipping info (order is created, awaiting payment).
--  Placeholders: {name} customer name, {order} order number,
--  {products} comma-separated cart items.
-- ─────────────────────────────────────────────────────────────

INSERT IGNORE INTO sms_templates (tkey, title, body, is_active, updated_at) VALUES
    ('order_ready', 'سفارش آمادهٔ پرداخت', 'کاربر گرامی {name}،\nسفارش شما شامل {products} ثبت شد و آمادهٔ پرداخت است.\nلطفاً پرداخت را تکمیل کنید.\nبهنام', 1, NOW());
