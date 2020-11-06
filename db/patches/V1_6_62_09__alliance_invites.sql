-- Keep a log of pending alliance invitations
CREATE TABLE `alliance_invites_player` (
  `game_id` int unsigned NOT NULL,
  `account_id` smallint unsigned NOT NULL,
  `alliance_id` smallint unsigned NOT NULL,
  `invited_by_id` smallint unsigned NOT NULL,
  `expires` int unsigned NOT NULL,
  PRIMARY KEY (`game_id`,`account_id`,`alliance_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
