-- move_id remains the per-game, player-visible move number.  InnoDB does
-- not support MyISAM's per-primary-key-prefix AUTO_INCREMENT behaviour.
-- Retain the composite (chess_game_id, move_id) key.
ALTER TABLE chess_game_moves
	MODIFY move_id SMALLINT UNSIGNED NOT NULL,
	ENGINE = InnoDB;
