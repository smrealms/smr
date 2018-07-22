-- Change `game_type` column datatype from enum to tinyint
ALTER TABLE game MODIFY game_type tinyint unsigned NOT NULL;

-- Convert to 0-based index, whereas enum is 1-based
UPDATE game SET game_type = game_type - 1;
