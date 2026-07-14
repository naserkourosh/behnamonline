-- Sub/mega menus: menu_items already has parent_id (3 levels supported);
-- is_mega marks a TOP-LEVEL item whose children render as a wide multi-column
-- mega panel (each child = a column heading, grandchildren = column links).

ALTER TABLE menu_items
    ADD COLUMN is_mega TINYINT(1) NOT NULL DEFAULT 0 AFTER url;
