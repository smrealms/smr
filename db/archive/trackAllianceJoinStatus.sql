CREATE TABLE player_joined_alliance (
  account_id smallint(5) unsigned NOT NULL,
  game_id tinyint(3) unsigned NOT NULL,
  alliance_id smallint(5) unsigned NOT NULL,
  status enum('NEWBIE','VETERAN') NOT NULL,
  PRIMARY KEY  (account_id,game_id,alliance_id)
)