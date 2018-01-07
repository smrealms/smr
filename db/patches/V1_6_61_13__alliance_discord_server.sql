-- Add `discord_server` field to `alliance` table
ALTER TABLE `alliance` ADD COLUMN `discord_server` VARCHAR(32) DEFAULT NULL;
