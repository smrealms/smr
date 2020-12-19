-- Rename account.images Yes/No enum to TRUE/FALSE for consistency
ALTER TABLE `account` MODIFY `images` enum('Yes','No','TRUE','FALSE') NOT NULL DEFAULT 'Yes';
UPDATE `account` SET `images` = 'TRUE' WHERE `images` = 'Yes';
UPDATE `account` SET `images` = 'FALSE' WHERE `images` = 'No';
ALTER TABLE `account` MODIFY `images` enum('TRUE','FALSE') NOT NULL DEFAULT 'TRUE';
