-- Keep a log of pending alliance invitations
CREATE TABLE `alliance_invites_player` (
  `game_id` int(10) unsigned NOT NULL,
  `account_id` smallint(6) unsigned NOT NULL,
  `alliance_id` smallint(6) unsigned NOT NULL,
  `invited_by_id` smallint(6) unsigned NOT NULL,
  `expires` int(10) unsigned NOT NULL,
  PRIMARY KEY (`game_id`,`account_id`,`alliance_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
