-- Prepare columns for uniqueness constraints
ALTER TABLE `account`
	MODIFY `old_account_id` smallint unsigned DEFAULT NULL,
	MODIFY `old_account_id2` smallint unsigned DEFAULT NULL;
UPDATE `account` SET `old_account_id` = NULL WHERE `old_account_id` = 0;
UPDATE `account` SET `old_account_id2` = NULL WHERE `old_account_id2` = 0;
UPDATE `alliance` SET `discord_channel` = NULL WHERE `discord_channel` = '';

-- Add constraints to ensure uniqueness of lookup columns
ALTER TABLE `account`
	ADD UNIQUE (`discord_id`),
	ADD UNIQUE (`irc_nick`),
	ADD UNIQUE (`old_account_id`),
	ADD UNIQUE (`old_account_id2`);

ALTER TABLE `alliance` ADD UNIQUE (`discord_channel`, `game_id`);
