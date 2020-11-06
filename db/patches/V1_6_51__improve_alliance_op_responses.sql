CREATE TABLE alliance_has_op_response (
	alliance_id smallint unsigned NOT NULL,
	game_id tinyint unsigned NOT NULL,
	account_id smallint unsigned NOT NULL,
	response ENUM('YES','NO','MAYBE') NOT NULL,
	PRIMARY KEY (alliance_id, game_id, account_id)
) ENGINE=InnoDB;

ALTER TABLE alliance_has_op
  DROP yes,
  DROP no,
  DROP maybe;
