-- Keep a log of draft picks
CREATE TABLE IF NOT EXISTS `draft_history` (
  `draft_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `game_id` int(10) unsigned NOT NULL,
  `leader_account_id` smallint(6) unsigned NOT NULL,
  `picked_account_id` smallint(6) unsigned NOT NULL,
  `time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`draft_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
