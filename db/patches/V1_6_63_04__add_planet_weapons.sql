-- Add ability to store planet weapons
CREATE TABLE planet_has_weapon (
  game_id smallint unsigned NOT NULL,
  sector_id mediumint unsigned NOT NULL,
  order_id smallint unsigned NOT NULL,
  weapon_type_id smallint unsigned NOT NULL,
  PRIMARY KEY (game_id, sector_id, order_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
