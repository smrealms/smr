-- Add `flagship_id` field to `alliance` table
ALTER TABLE `alliance` ADD COLUMN `flagship_id` int unsigned NOT NULL DEFAULT '0';
