-- Make changes needed to enable strict SQL mode
ALTER TABLE `player` DROP COLUMN `attack_warning`;

ALTER TABLE `player` MODIFY `last_port` int unsigned NOT NULL DEFAULT 0;
