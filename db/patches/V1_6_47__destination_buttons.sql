CREATE TABLE IF NOT EXISTS player_stored_sector(
	account_id int UNSIGNED NOT NULL,
	game_id int UNSIGNED NOT NULL,
	sector_id int UNSIGNED NOT NULL,
	label varchar(64) NOT NULL,
	offset_top int UNSIGNED NOT NULL default 0,
	offset_left int UNSIGNED NOT NULL default 0,

	PRIMARY KEY(account_id, game_id, sector_id)
	#CONSTRAINT FOREIGN KEY (player_id) REFERENCES player (player_id) ON DELETE CASCADE ON UPDATE CASCADE,
	#CONSTRAINT FOREIGN KEY (sector_id) REFERENCES sector (sector_id) ON DELETE CASCADE ON UPDATE CASCADE,
) ENGINE=InnoDB;
