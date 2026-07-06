-- ─────────────────────────────────────────────────────────────
--  بهنام (Behnam) — new banner placement `inline`:
--  full-width image promo banners rendered BETWEEN the product rows
--  on the home page (بنر تصویری میان صفحه).
-- ─────────────────────────────────────────────────────────────

ALTER TABLE banners
    MODIFY COLUMN placement ENUM('hero','promo','strip','inline') NOT NULL DEFAULT 'hero';
