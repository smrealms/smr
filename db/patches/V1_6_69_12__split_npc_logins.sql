-- Keep NPC account defaults separate from state that belongs to a player in a
-- specific game.
CREATE TABLE `npc_players` (
	`account_id` smallint unsigned NOT NULL,
	`game_id` smallint unsigned NOT NULL,
	`active` enum('TRUE','FALSE') NOT NULL DEFAULT 'TRUE',
	`working` enum('TRUE','FALSE') NOT NULL DEFAULT 'FALSE',
	`lock_ship` enum('TRUE','FALSE') NOT NULL DEFAULT 'FALSE',
	PRIMARY KEY (`account_id`, `game_id`)
) ENGINE=InnoDB;

-- Existing NPC players in active games inherit their current account-wide state.
INSERT INTO `npc_players` (`account_id`, `game_id`, `active`, `working`)
	SELECT `player`.`account_id`, `player`.`game_id`, `npc_logins`.`active`, `npc_logins`.`working`
	FROM `npc_logins`
	JOIN `account` USING (`login`)
	JOIN `player` USING (`account_id`)
	JOIN `game` USING (`game_id`)
	WHERE `player`.`npc` = 'TRUE'
		AND `game`.`end_time` > UNIX_TIMESTAMP();

-- Replace the legacy login reference with the account ID.
ALTER TABLE `npc_logins`
	ADD COLUMN `account_id` smallint unsigned DEFAULT NULL AFTER `login`;

UPDATE `npc_logins`
	JOIN `account` USING (`login`)
	SET `npc_logins`.`account_id` = `account`.`account_id`;

-- Remove migrated columns and update primary key.
ALTER TABLE `npc_logins`
	RENAME TO `npc_accounts`,
	CHANGE COLUMN `player_name` `default_player_name` varchar(32) NOT NULL,
	CHANGE COLUMN `alliance_name` `default_alliance_name` varchar(32) NOT NULL,
	CHANGE COLUMN `account_id` `account_id` smallint unsigned NOT NULL,
	DROP PRIMARY KEY,
	DROP COLUMN `login`,
	DROP COLUMN `active`,
	DROP COLUMN `working`,
	DROP INDEX `player_name`,
	ADD PRIMARY KEY (`account_id`),
	ADD UNIQUE KEY `default_player_name` (`default_player_name`);
