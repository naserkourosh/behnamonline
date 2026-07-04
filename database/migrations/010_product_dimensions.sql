-- ─────────────────────────────────────────────────────────────
--  بهنام (Behnam) — product weight & dimensions for the National
--  Post (شرکت ملی پست) shipping-fee web service. Weight in grams,
--  dimensions in centimeters. Used to compute actual vs volumetric
--  weight when requesting a postal quote at checkout.
-- ─────────────────────────────────────────────────────────────

ALTER TABLE products
    ADD COLUMN weight_grams INT NOT NULL DEFAULT 0 AFTER stock,
    ADD COLUMN length_cm    INT NOT NULL DEFAULT 0 AFTER weight_grams,
    ADD COLUMN width_cm     INT NOT NULL DEFAULT 0 AFTER length_cm,
    ADD COLUMN height_cm    INT NOT NULL DEFAULT 0 AFTER width_cm;
