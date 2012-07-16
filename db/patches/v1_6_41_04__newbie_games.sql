ALTER TABLE game
CHANGE game_type
	game_type ENUM( 'Default', 'Classic 1.6', 'Semi Wars', 'Draft', 'Newbie' ) NOT NULL DEFAULT 'Default';