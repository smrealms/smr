-- Replace column `account_id` with `colour` in `chess_game_pieces` table

-- Add the new column
ALTER TABLE `chess_game_pieces` ADD COLUMN `colour` enum('White','Black') NOT NULL;

-- Set the new column values based on which colour the account is
UPDATE `chess_game_pieces`
	INNER JOIN `chess_game` ON
		`chess_game_pieces`.`chess_game_id` = `chess_game`.`chess_game_id` AND
		`chess_game_pieces`.`account_id` = `chess_game`.`white_id`
	SET `chess_game_pieces`.`colour` = 'White';
UPDATE `chess_game_pieces`
	INNER JOIN `chess_game` ON
		`chess_game_pieces`.`chess_game_id` = `chess_game`.`chess_game_id` AND
		`chess_game_pieces`.`account_id` = `chess_game`.`black_id`
	SET `chess_game_pieces`.`colour` = 'Black';

-- Make the new column a primary key
ALTER TABLE `chess_game_pieces` DROP PRIMARY KEY,
  ADD PRIMARY KEY (`chess_game_id`,`colour`,`piece_id`,`piece_no`);

-- Remove the old column
ALTER TABLE `chess_game_pieces` DROP COLUMN `account_id`;
