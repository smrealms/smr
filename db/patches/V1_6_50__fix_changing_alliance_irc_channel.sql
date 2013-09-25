ALTER TABLE irc_alliance_has_channel ADD UNIQUE (
	alliance_id,
	game_id
);
