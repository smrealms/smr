-- Drinks are represented by their rows. player_id indexes the per-player
-- counts used by the bar without requiring a sequential drink identifier.
ALTER TABLE player_has_drinks
	DROP PRIMARY KEY,
	DROP COLUMN drink_id,
	ADD KEY (player_id);
