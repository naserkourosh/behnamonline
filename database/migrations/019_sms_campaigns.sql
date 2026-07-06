-- Phase: SMS completion — group/promotional campaigns.
--   1) sms_campaigns          → one row per bulk send (audience, counters)
--   2) sms_messages.campaign_id → links each logged message to its campaign

CREATE TABLE sms_campaigns (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(190) NOT NULL,
    body        TEXT NOT NULL,
    audience    VARCHAR(30) NOT NULL DEFAULT 'all',
    total       INT NOT NULL DEFAULT 0,
    sent        INT NOT NULL DEFAULT 0,
    failed      INT NOT NULL DEFAULT 0,
    status      VARCHAR(20) NOT NULL DEFAULT 'sending',
    created_by  INT UNSIGNED NULL,
    created_at  DATETIME NULL,
    finished_at DATETIME NULL,
    KEY idx_sms_campaigns_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

ALTER TABLE sms_messages
    ADD COLUMN campaign_id INT UNSIGNED NULL AFTER driver,
    ADD KEY idx_sms_campaign (campaign_id);
