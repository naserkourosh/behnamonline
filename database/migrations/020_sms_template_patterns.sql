-- SMS phase 2 — per-template Melipayamak pattern code (کد متن خدماتی).
-- When set, the template's variable VALUES are sent via BaseServiceNumber
-- (shared service line) instead of the rendered text from the regular line.

ALTER TABLE sms_templates
    ADD COLUMN pattern_body_id VARCHAR(30) NULL AFTER is_active;
