CREATE TABLE smr_new.chess_game (
	chess_game_id	INT UNSIGNED NOT NULL AUTO_INCREMENT
,	start_time		INT UNSIGNED NOT NULL
,	end_time		INT UNSIGNED NULL
,	black_id		INT UNSIGNED NOT NULL
,	white_id		INT UNSIGNED NOT NULL
,	winner_id		INT UNSIGNED NOT NULL
,	PRIMARY KEY ( chess_game_id )
) ENGINE = INNODB;

CREATE TABLE smr_new.chess_game_pieces (
	chess_game_id	INT UNSIGNED NOT NULL
,	account_id		INT UNSIGNED NOT NULL
,	piece_id		INT UNSIGNED NOT NULL
,	piece_no		INT UNSIGNED NOT NULL AUTO_INCREMENT
,	x				INT UNSIGNED NOT NULL
,	y				INT UNSIGNED NOT NULL
,	PRIMARY KEY ( chess_game_id, account_id, piece_id, piece_no )
) ENGINE = MYISAM;

CREATE TABLE smr_new.chess_game_moves (
	chess_game_id	INT UNSIGNED NOT NULL
,	move_id			INT UNSIGNED NOT NULL AUTO_INCREMENT
,	piece_id		INT UNSIGNED NOT NULL
,	start_x			INT UNSIGNED NOT NULL
,	start_y			INT UNSIGNED NOT NULL
,	end_x			INT UNSIGNED NOT NULL
,	end_y			INT UNSIGNED NOT NULL
,	checked			ENUM( 'CHECK', 'MATE' ) NULL
,	piece_taken		INT NULL
,	PRIMARY KEY ( chess_game_id, move_id )
) ENGINE = MYISAM ;