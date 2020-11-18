-- Add `home_sector_id` to `draft_leaders` table
ALTER TABLE draft_leaders ADD COLUMN home_sector_id int unsigned NOT NULL DEFAULT '0'
