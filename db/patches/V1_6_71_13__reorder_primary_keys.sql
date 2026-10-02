-- Reverse backwards composite primary keys
ALTER TABLE alliance
	DROP PRIMARY KEY,
	ADD PRIMARY KEY (game_id, alliance_id);

ALTER TABLE changelog
	DROP PRIMARY KEY,
	ADD PRIMARY KEY (version_id, changelog_id);
