-- MyISAM assigns bounty_id separately for each target player. Remap existing
-- bounties before making bounty_id the global InnoDB primary key.
ALTER TABLE bounty
	MODIFY bounty_id INT UNSIGNED NOT NULL;

ALTER TABLE bounty
	ADD new_bounty_id INT UNSIGNED NOT NULL AUTO_INCREMENT FIRST,
	ADD UNIQUE KEY (new_bounty_id);

-- Note that time was an unused column and is removed without replacement.
ALTER TABLE bounty
	DROP PRIMARY KEY,
	DROP bounty_id,
	DROP time,
	RENAME COLUMN new_bounty_id TO bounty_id,
	ADD PRIMARY KEY (bounty_id),
	DROP KEY new_bounty_id,
	ADD KEY (player_id),
	ADD KEY game_type_claimer_amount (game_id, type, claimer_player_id, amount),
	ADD KEY claimer_player_id_type (claimer_player_id, type),
	ENGINE = InnoDB;
