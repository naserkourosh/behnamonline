-- New banner placement `poster`: large ad posters on the home page
-- (rendered as an image-first grid of 1–4 items after the flash-sale section).

ALTER TABLE banners
    MODIFY COLUMN placement ENUM('hero','promo','strip','inline','poster') NOT NULL DEFAULT 'hero';
