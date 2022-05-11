-- Add constraints to ensure uniqueness of account/player properties
ALTER TABLE `account`
	ADD UNIQUE (`email`),
	ADD UNIQUE (`hof_name`);

ALTER TABLE `player`
	ADD UNIQUE (`player_id`, `game_id`),
	ADD UNIQUE (`player_name`, `game_id`);
