-- Table for tracking status of games while in development
CREATE TABLE `game_create_status` (
	`game_id` tinyint unsigned NOT NULL,
	`account_id` smallint unsigned NOT NULL,
	`create_date` DATE NOT NULL,
	`ready_date` DATE DEFAULT NULL,
	`all_edit` enum('TRUE','FALSE') NOT NULL DEFAULT 'FALSE',
	PRIMARY KEY (`game_id`)
) ENGINE=InnoDB;

-- Add an entry with dummy values for each existing non-enabled game
INSERT INTO `game_create_status` (`game_id`, `account_id`, `create_date`)
	SELECT `game_id`, 0, '2000-01-01'
	FROM `game`
	WHERE `enabled` = 'FALSE';
