-- Change how player being under attack is stored
ALTER TABLE `player` ADD COLUMN `under_attack` enum('TRUE','FALSE') NOT NULL DEFAULT 'FALSE';
ALTER TABLE `ship_has_hardware` DROP COLUMN `old_amount`;
