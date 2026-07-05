-- ─────────────────────────────────────────────────────────────
--  بهنام (Behnam) — Drop the retired loyalty (points) + wallet schema.
--  The customer-club (reward points) and wallet features were fully
--  removed from the application; these now-dormant columns and the
--  point_transactions ledger are dropped for good.
-- ─────────────────────────────────────────────────────────────

DROP TABLE IF EXISTS point_transactions;

ALTER TABLE orders
    DROP COLUMN points_earned;

ALTER TABLE users
    DROP COLUMN reward_points,
    DROP COLUMN wallet_balance;
