-- Remove `old_account_id` from `active_session` table
ALTER TABLE active_session DROP COLUMN old_account_id;
