CREATE TABLE IF NOT EXISTS player_saved_combat_logs (
	account_id smallint(5) unsigned NOT NULL,
	game_id tinyint(3) unsigned NOT NULL,
	log_id int(10) unsigned NOT NULL,
	PRIMARY KEY (account_id,game_id,log_id)
) ENGINE=InnoDB;

INSERT INTO player_saved_combat_logs
SELECT saved, game_id, log_id
FROM combat_logs
WHERE saved != 0;

ALTER TABLE combat_logs
DROP saved;