-- Allow sharing info between accounts in chat
CREATE TABLE IF NOT EXISTS `account_shares_info` (
  `from_account_id` int unsigned NOT NULL,
  `to_account_id` int unsigned NOT NULL,
  `game_id` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (from_account_id, to_account_id, game_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
