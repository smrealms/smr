-- Make changes needed to enable strict SQL mode
ALTER TABLE `player` DROP COLUMN `attack_warning`;

ALTER TABLE `player` MODIFY `last_port` int unsigned NOT NULL DEFAULT 0;

ALTER TABLE `account` MODIFY `password_reset` char(32) DEFAULT NULL;
UPDATE `account` SET `password_reset` = NULL WHERE `password_reset` = '';

ALTER TABLE `account` MODIFY `mail_banned` int unsigned NOT NULL DEFAULT 0;

ALTER TABLE `alliance` MODIFY `img_src` varchar(255) NOT NULL DEFAULT '';

ALTER TABLE `planet` MODIFY `password` varchar(32) NOT NULL DEFAULT '';

ALTER TABLE `planet` DROP COLUMN `last_updated`;
