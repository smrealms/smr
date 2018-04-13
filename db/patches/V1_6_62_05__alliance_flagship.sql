-- Add `flagship_id` field to `alliance` table
ALTER TABLE `alliance` ADD COLUMN `flagship_id` int(10) unsigned NOT NULL DEFAULT '0';
