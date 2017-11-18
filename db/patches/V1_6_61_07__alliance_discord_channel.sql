-- Add `discord_channel` field to `alliance` table
ALTER TABLE `alliance` ADD COLUMN `discord_channel` VARCHAR(32) DEFAULT NULL;
