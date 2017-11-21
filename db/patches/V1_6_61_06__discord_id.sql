-- Add `discord_id` field to `account` table
ALTER TABLE `account` ADD COLUMN `discord_id` VARCHAR(32) DEFAULT NULL AFTER `hof_name`;
