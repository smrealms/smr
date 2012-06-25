CREATE TABLE smr_new.player_has_mission
(
	game_id INT UNSIGNED NOT NULL,
	account_id INT UNSIGNED NOT NULL,
	mission_id INT UNSIGNED NOT NULL,
	on_step INT UNSIGNED NOT NULL,
	progress INT UNSIGNED NOT NULL,
	next_step INT UNSIGNED NOT NULL,
	total_steps INT UNSIGNED NOT NULL,
	starting_sector INT UNSIGNED NOT NULL,
	mission_sector INT UNSIGNED NOT NULL,
	step_fails INT UNSIGNED NOT NULL,
	PRIMARY KEY (game_id, account_id, mission_id)
) ENGINE = InnoDB;