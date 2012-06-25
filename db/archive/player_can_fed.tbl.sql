CREATE TABLE smr_new.player_can_fed
(
	account_id INT UNSIGNED NOT NULL
,	game_id INT UNSIGNED NOT NULL
,	race_id INT UNSIGNED NOT NULL
,	expiry INT UNSIGNED NOT NULL
,	allowed ENUM( 'TRUE', 'FALSE' ) NOT NULL
,	PRIMARY KEY ( account_id, game_id, race_id )
) ENGINE = MYISAM;