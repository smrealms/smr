CREATE TABLE IF NOT EXISTS player_saved_combat_logs (
	account_id smallint unsigned NOT NULL,
	game_id tinyint unsigned NOT NULL,
	log_id int unsigned NOT NULL,
	PRIMARY KEY (account_id,game_id,log_id)
) ENGINE=InnoDB;

INSERT INTO player_saved_combat_logs
SELECT saved, game_id, log_id
FROM combat_logs
WHERE saved != 0;

ALTER TABLE combat_logs
DROP saved;
