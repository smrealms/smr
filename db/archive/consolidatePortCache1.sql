CREATE TABLE smr_new.`port_info_cache` (
  `game_id` int(3) unsigned NOT NULL,
  `sector_id` int(6) unsigned NOT NULL,
  `port_info_hash` char(32) NOT NULL,
  `port_info` mediumblob NOT NULL,
  PRIMARY KEY (`game_id`,`sector_id`,`port_info_hash`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
ALTER TABLE smr_new.`player_visited_port` ADD `port_info_hash` CHAR( 32 ) NOT NULL;