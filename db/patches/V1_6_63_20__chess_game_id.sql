-- Add `game_id` column to the `chess_game` table.
ALTER TABLE chess_game ADD COLUMN game_id int(10) unsigned NOT NULL DEFAULT '0';
