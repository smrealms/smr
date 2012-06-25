CREATE TABLE smr_new.weighted_random
(
	game_id INT UNSIGNED NOT NULL,
	account_id INT UNSIGNED NOT NULL,
	type ENUM('WEAPON') NOT NULL,
	type_id INT UNSIGNED NOT NULL,
	weighting INT NOT NULL,
	PRIMARY KEY (game_id, account_id, type, type_id)
) ENGINE = INNODB;