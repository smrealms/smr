SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;


CREATE TABLE IF NOT EXISTS `account` (
  `account_id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `login` varchar(32) NOT NULL,
  `password` char(32) NOT NULL,
  `email` varchar(128) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `address` varchar(255) NOT NULL,
  `city` varchar(50) NOT NULL,
  `postal_code` varchar(10) NOT NULL,
  `country_code` char(2) NOT NULL,
  `icq` varchar(15) DEFAULT NULL,
  `validation_code` varchar(32) NOT NULL,
  `validated` enum('TRUE','FALSE') NOT NULL DEFAULT 'FALSE',
  `cell_phone` varchar(30) DEFAULT NULL,
  `last_login` int(10) unsigned NOT NULL DEFAULT '0',
  `veteran` enum('TRUE','FALSE') NOT NULL DEFAULT 'FALSE',
  `logging` enum('TRUE','FALSE') NOT NULL DEFAULT 'FALSE',
  `offset` tinyint(4) NOT NULL DEFAULT '0',
  `images` enum('Yes','No') NOT NULL DEFAULT 'Yes',
  `fontsize` tinyint(3) unsigned NOT NULL DEFAULT '100',
  `password_reset` char(32) NOT NULL,
  `use_ajax` enum('TRUE','FALSE') NOT NULL DEFAULT 'TRUE',
  `mail_banned` int(10) unsigned NOT NULL,
  `referral_id` int(10) unsigned NOT NULL,
  `hof_name` varchar(32) NOT NULL,
  `irc_nick` varchar(32) DEFAULT NULL,
  `css_link` varchar(255) DEFAULT NULL,
  `default_css_enabled` enum('TRUE','FALSE') NOT NULL DEFAULT 'TRUE',
  `center_galaxy_map_on_player` enum('TRUE','FALSE') NOT NULL DEFAULT 'TRUE',
  `old_account_id` smallint(6) unsigned NOT NULL,
  `old_account_id2` smallint(6) unsigned NOT NULL,
  `date_short` varchar(20) NOT NULL DEFAULT 'j/n/Y',
  `time_short` varchar(20) NOT NULL DEFAULT 'g:i:s A',
  `max_rank_achieved` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `template` enum('Default','Freon22') NOT NULL,
  `colour_scheme` varchar(30) NOT NULL DEFAULT 'Default',
  `message_notifications` varchar(100) NOT NULL,
  `hotkeys` text NOT NULL,
  PRIMARY KEY (`account_id`),
  FULLTEXT KEY `login` (`login`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `account_auth` (
  `account_id` smallint(5) unsigned NOT NULL,
  `login_type` varchar(100) NOT NULL,
  `auth_key` varchar(100) NOT NULL,
  PRIMARY KEY (`account_id`,`login_type`,`auth_key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `account_donated` (
  `account_id` smallint(6) unsigned NOT NULL DEFAULT '0',
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  `amount` smallint(6) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`account_id`,`time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `account_exceptions` (
  `account_id` int(10) unsigned NOT NULL DEFAULT '0',
  `reason` varchar(255) NOT NULL,
  PRIMARY KEY (`account_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `account_has_closing_history` (
  `account_id` smallint(6) unsigned NOT NULL DEFAULT '0',
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  `admin_id` smallint(6) unsigned NOT NULL DEFAULT '0',
  `action` enum('Closed','Opened') NOT NULL DEFAULT 'Closed',
  PRIMARY KEY (`account_id`,`time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `account_has_credits` (
  `account_id` smallint(6) unsigned NOT NULL DEFAULT '0',
  `credits_left` smallint(6) unsigned NOT NULL DEFAULT '0',
  `reward_credits` smallint(6) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`account_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `account_has_ip` (
  `account_id` smallint(6) unsigned NOT NULL DEFAULT '0',
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  `ip` varchar(16) NOT NULL,
  `host` varchar(64) NOT NULL,
  PRIMARY KEY (`account_id`,`time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `account_has_logs` (
  `account_id` smallint(6) unsigned NOT NULL DEFAULT '0',
  `log_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `microtime` bigint(16) unsigned NOT NULL,
  `log_type_id` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `message` varchar(255) NOT NULL,
  `sector_id` smallint(6) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`account_id`,`log_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `account_has_permission` (
  `account_id` int(10) unsigned NOT NULL DEFAULT '0',
  `permission_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`account_id`,`permission_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `account_has_points` (
  `account_id` int(10) unsigned NOT NULL DEFAULT '0',
  `points` tinyint(5) unsigned NOT NULL DEFAULT '0',
  `last_update` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`account_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `account_has_stats` (
  `account_id` int(10) unsigned NOT NULL DEFAULT '0',
  `HoF_name` varchar(24) NOT NULL,
  `games_joined` int(10) unsigned NOT NULL DEFAULT '0',
  `planet_busts` int(10) unsigned NOT NULL DEFAULT '0',
  `planet_bust_levels` int(10) unsigned NOT NULL DEFAULT '0',
  `port_raids` int(10) unsigned NOT NULL DEFAULT '0',
  `port_raid_levels` int(10) unsigned NOT NULL DEFAULT '0',
  `sectors_explored` int(10) unsigned NOT NULL DEFAULT '0',
  `deaths` int(10) unsigned NOT NULL DEFAULT '0',
  `kills` int(10) unsigned NOT NULL DEFAULT '0',
  `goods_traded` int(10) unsigned NOT NULL DEFAULT '0',
  `experience_traded` int(10) unsigned NOT NULL DEFAULT '0',
  `bounties_claimed` int(10) unsigned NOT NULL DEFAULT '0',
  `bounty_amount_claimed` int(10) unsigned NOT NULL DEFAULT '0',
  `military_claimed` int(10) unsigned NOT NULL DEFAULT '0',
  `bounty_amount_on` int(10) unsigned NOT NULL DEFAULT '0',
  `player_damage` int(10) unsigned NOT NULL DEFAULT '0',
  `port_damage` int(10) unsigned NOT NULL DEFAULT '0',
  `planet_damage` int(10) unsigned NOT NULL DEFAULT '0',
  `turns_used` int(10) unsigned NOT NULL DEFAULT '0',
  `kill_exp` int(10) unsigned NOT NULL DEFAULT '0',
  `traders_killed_exp` int(10) unsigned NOT NULL DEFAULT '0',
  `lotto` int(10) unsigned NOT NULL DEFAULT '0',
  `blackjack_win` int(10) unsigned NOT NULL DEFAULT '0',
  `blackjack_lose` int(10) unsigned NOT NULL DEFAULT '0',
  `drinks` int(10) unsigned NOT NULL DEFAULT '0',
  `trade_profit` int(10) unsigned NOT NULL DEFAULT '0',
  `trade_sales` int(10) unsigned NOT NULL DEFAULT '0',
  `mines` int(10) unsigned NOT NULL DEFAULT '0',
  `combat_drones` int(10) unsigned NOT NULL DEFAULT '0',
  `scout_drones` int(10) unsigned NOT NULL DEFAULT '0',
  `money_gained` int(10) unsigned NOT NULL DEFAULT '0',
  `killed_ships` int(10) unsigned NOT NULL DEFAULT '0',
  `died_ships` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`account_id`,`kills`,`experience_traded`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `account_is_closed` (
  `account_id` int(10) unsigned NOT NULL DEFAULT '0',
  `reason_id` int(10) unsigned NOT NULL DEFAULT '0',
  `suspicion` varchar(255) NOT NULL,
  `expires` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`account_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `account_sms_blacklist` (
  `account_id` smallint(6) unsigned NOT NULL,
  `reason` varchar(255) NOT NULL,
  PRIMARY KEY (`account_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `account_sms_dlr` (
  `message_id` int(10) unsigned NOT NULL,
  `send_time` int(10) unsigned NOT NULL,
  `receive_time` int(10) unsigned NOT NULL,
  `status` varchar(255) NOT NULL,
  `announce` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`message_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `account_sms_log` (
  `log_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `account_id` smallint(6) unsigned NOT NULL,
  `time` int(10) unsigned NOT NULL,
  `receiver_id` smallint(6) unsigned NOT NULL,
  `receiver_cell` varchar(32) NOT NULL,
  `response_code` tinyint(3) unsigned NOT NULL,
  `message_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`log_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `account_sms_response` (
  `response_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `message_id` int(10) unsigned NOT NULL,
  `message` int(160) NOT NULL,
  `from` int(32) NOT NULL,
  `announce` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`response_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `account_votes_for_feature` (
  `account_id` int(10) unsigned NOT NULL,
  `feature_request_id` int(10) unsigned NOT NULL,
  `vote_type` enum('FAVOURITE','YES','NO') NOT NULL DEFAULT 'FAVOURITE',
  PRIMARY KEY (`account_id`,`feature_request_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `active_session` (
  `session_id` char(32) NOT NULL,
  `account_id` smallint(6) unsigned NOT NULL DEFAULT '0',
  `game_id` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `last_accessed` int(10) unsigned NOT NULL DEFAULT '0',
  `session_var` mediumblob NOT NULL,
  `last_sn` char(8) NOT NULL,
  `ajax_returns` mediumblob NOT NULL,
  `old_account_id` smallint(6) unsigned NOT NULL,
  PRIMARY KEY (`session_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `album` (
  `account_id` smallint(6) unsigned NOT NULL DEFAULT '0',
  `location` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `website` varchar(128) DEFAULT NULL,
  `day` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `month` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `year` smallint(6) unsigned NOT NULL DEFAULT '0',
  `other` text NOT NULL,
  `page_views` int(10) unsigned NOT NULL DEFAULT '0',
  `created` int(10) unsigned NOT NULL DEFAULT '0',
  `last_changed` int(10) unsigned NOT NULL DEFAULT '0',
  `approved` enum('YES','NO','TBC') NOT NULL DEFAULT 'TBC',
  `disabled` enum('TRUE','FALSE') NOT NULL DEFAULT 'FALSE',
  PRIMARY KEY (`account_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `album_has_comments` (
  `album_id` smallint(6) unsigned NOT NULL DEFAULT '0',
  `comment_id` smallint(6) unsigned NOT NULL DEFAULT '0',
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  `post_id` smallint(6) NOT NULL DEFAULT '0',
  `msg` varchar(255) NOT NULL,
  PRIMARY KEY (`album_id`,`comment_id`),
  KEY `comment_id` (`comment_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `alliance` (
  `alliance_id` smallint(6) unsigned NOT NULL DEFAULT '0',
  `game_id` int(10) unsigned NOT NULL DEFAULT '0',
  `alliance_name` varchar(36) DEFAULT NULL,
  `alliance_description` varchar(255) DEFAULT NULL,
  `alliance_password` varchar(32) DEFAULT NULL,
  `leader_id` int(10) unsigned DEFAULT '0',
  `mod` text NOT NULL,
  `img_src` varchar(255) NOT NULL,
  `alliance_account` int(10) unsigned DEFAULT '0',
  `alliance_kills` int(10) unsigned NOT NULL DEFAULT '0',
  `alliance_deaths` int(10) unsigned NOT NULL DEFAULT '0',
  `hill_summit_cumulative` int(10) unsigned NOT NULL DEFAULT '0',
  `hill_heights_cumulative` int(10) unsigned NOT NULL DEFAULT '0',
  `hill_foothills_cumulative` int(10) unsigned NOT NULL DEFAULT '0',
  `hill_kills` smallint(6) unsigned NOT NULL DEFAULT '0',
  `hill_points` int(10) unsigned NOT NULL DEFAULT '0',
  `recruiting` enum('TRUE','FALSE') NOT NULL,
  PRIMARY KEY (`alliance_id`,`game_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `alliance_bank_transactions` (
  `alliance_id` int(10) unsigned NOT NULL DEFAULT '0',
  `game_id` int(10) unsigned NOT NULL DEFAULT '0',
  `transaction_id` int(10) unsigned NOT NULL DEFAULT '0',
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  `payee_id` int(10) unsigned NOT NULL DEFAULT '0',
  `reason` varchar(255) NOT NULL,
  `transaction` enum('Payment','Deposit') NOT NULL DEFAULT 'Deposit',
  `amount` int(10) unsigned NOT NULL DEFAULT '0',
  `exempt` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `request_exempt` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`alliance_id`,`game_id`,`transaction_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `alliance_has_op` (
  `alliance_id` smallint(6) unsigned NOT NULL,
  `game_id` tinyint(3) unsigned NOT NULL,
  `time` int(10) unsigned NOT NULL,
  `yes` varchar(4096) NOT NULL,
  `no` varchar(4096) NOT NULL,
  `maybe` varchar(4096) NOT NULL,
  PRIMARY KEY (`alliance_id`,`game_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `alliance_has_roles` (
  `alliance_id` smallint(6) unsigned NOT NULL DEFAULT '0',
  `game_id` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `role_id` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `role` varchar(32) NOT NULL,
  `with_per_day` int(11) NOT NULL DEFAULT '0',
  `positive_balance` enum('TRUE','FALSE') NOT NULL DEFAULT 'FALSE',
  `remove_member` enum('TRUE','FALSE') NOT NULL DEFAULT 'FALSE',
  `change_pass` enum('TRUE','FALSE') NOT NULL DEFAULT 'FALSE',
  `change_mod` enum('TRUE','FALSE') NOT NULL DEFAULT 'FALSE',
  `change_roles` enum('TRUE','FALSE') NOT NULL DEFAULT 'FALSE',
  `planet_access` enum('TRUE','FALSE') NOT NULL DEFAULT 'FALSE',
  `exempt_with` enum('TRUE','FALSE') NOT NULL DEFAULT 'FALSE',
  `mb_messages` enum('TRUE','FALSE') NOT NULL DEFAULT 'FALSE',
  `send_alliance_msg` enum('TRUE','FALSE') NOT NULL DEFAULT 'FALSE',
  `treaty_entry` enum('TRUE','FALSE') NOT NULL DEFAULT 'FALSE',
  `treaty_created` enum('TRUE','FALSE') NOT NULL DEFAULT 'FALSE',
  PRIMARY KEY (`alliance_id`,`game_id`,`role_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `alliance_has_seedlist` (
  `alliance_id` smallint(6) unsigned NOT NULL,
  `game_id` tinyint(3) unsigned NOT NULL,
  `sector_id` smallint(6) unsigned NOT NULL,
  PRIMARY KEY (`alliance_id`,`game_id`,`sector_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `alliance_thread` (
  `game_id` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `alliance_id` smallint(6) unsigned NOT NULL DEFAULT '0',
  `thread_id` smallint(6) unsigned NOT NULL DEFAULT '0',
  `reply_id` smallint(6) unsigned NOT NULL DEFAULT '0',
  `text` text NOT NULL,
  `sender_id` mediumint(9) NOT NULL DEFAULT '0',
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`game_id`,`alliance_id`,`thread_id`,`reply_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `alliance_thread_topic` (
  `game_id` int(10) unsigned NOT NULL DEFAULT '0',
  `alliance_id` int(10) unsigned NOT NULL DEFAULT '0',
  `thread_id` int(10) unsigned NOT NULL DEFAULT '0',
  `topic` varchar(255) NOT NULL,
  `alliance_only` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`game_id`,`alliance_id`,`thread_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `alliance_treaties` (
  `alliance_id_1` smallint(6) NOT NULL DEFAULT '0',
  `alliance_id_2` smallint(6) NOT NULL DEFAULT '0',
  `game_id` smallint(6) NOT NULL DEFAULT '0',
  `trader_assist` enum('TRUE','FALSE') NOT NULL,
  `trader_defend` enum('TRUE','FALSE') NOT NULL,
  `trader_nap` enum('TRUE','FALSE') NOT NULL,
  `raid_assist` enum('TRUE','FALSE') NOT NULL,
  `planet_nap` enum('TRUE','FALSE') NOT NULL,
  `planet_land` enum('TRUE','FALSE') NOT NULL,
  `forces_nap` enum('TRUE','FALSE') NOT NULL,
  `aa_access` enum('TRUE','FALSE') NOT NULL,
  `mb_read` enum('TRUE','FALSE') NOT NULL,
  `mb_write` enum('TRUE','FALSE') NOT NULL,
  `mod_read` enum('TRUE','FALSE') NOT NULL,
  `official` enum('TRUE','FALSE') NOT NULL DEFAULT 'FALSE',
  PRIMARY KEY (`alliance_id_1`,`alliance_id_2`,`game_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `alliance_vs_alliance` (
  `game_id` int(11) NOT NULL DEFAULT '0',
  `alliance_id_1` int(11) NOT NULL DEFAULT '0',
  `alliance_id_2` int(11) NOT NULL DEFAULT '0',
  `kills` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`game_id`,`alliance_id_1`,`alliance_id_2`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `announcement` (
  `announcement_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  `admin_id` smallint(6) unsigned NOT NULL DEFAULT '0',
  `msg` text NOT NULL,
  PRIMARY KEY (`announcement_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `anon_bank` (
  `game_id` int(10) unsigned NOT NULL DEFAULT '0',
  `anon_id` int(10) unsigned NOT NULL DEFAULT '0',
  `owner_id` int(10) unsigned NOT NULL DEFAULT '0',
  `password` varchar(20) NOT NULL,
  `amount` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`game_id`,`anon_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `anon_bank_transactions` (
  `account_id` int(10) unsigned NOT NULL DEFAULT '0',
  `game_id` int(10) unsigned NOT NULL DEFAULT '0',
  `anon_id` int(10) unsigned NOT NULL DEFAULT '0',
  `transaction_id` int(10) unsigned NOT NULL DEFAULT '0',
  `transaction` enum('Payment','Deposit') NOT NULL DEFAULT 'Payment',
  `amount` int(10) unsigned NOT NULL DEFAULT '0',
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`account_id`,`game_id`,`anon_id`,`transaction_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `bar_drink` (
  `drink_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `drink_name` varchar(255) NOT NULL,
  PRIMARY KEY (`drink_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `bar_tender` (
  `game_id` int(10) unsigned NOT NULL DEFAULT '0',
  `message_id` int(10) unsigned NOT NULL DEFAULT '0',
  `message` varchar(255) NOT NULL,
  PRIMARY KEY (`game_id`,`message_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `bar_wall` (
  `sector_id` int(10) unsigned NOT NULL DEFAULT '0',
  `game_id` int(10) unsigned NOT NULL DEFAULT '0',
  `message_id` int(10) unsigned NOT NULL DEFAULT '0',
  `message` varchar(255) NOT NULL,
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`sector_id`,`game_id`,`message_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `beta_key` (
  `code` char(5) NOT NULL DEFAULT '0',
  `used` enum('TRUE','FALSE') NOT NULL DEFAULT 'FALSE',
  PRIMARY KEY (`code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `blackjack` (
  `game_id` int(11) NOT NULL DEFAULT '0',
  `account_id` int(11) NOT NULL DEFAULT '0',
  `last_hand` varchar(255) NOT NULL,
  PRIMARY KEY (`game_id`,`account_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `bounty` (
  `account_id` int(10) unsigned NOT NULL DEFAULT '0',
  `game_id` int(10) unsigned NOT NULL DEFAULT '0',
  `bounty_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('HQ','UG') NOT NULL DEFAULT 'HQ',
  `amount` int(10) unsigned NOT NULL DEFAULT '0',
  `claimer_id` int(10) unsigned NOT NULL DEFAULT '0',
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  `smr_credits` int(10) unsigned NOT NULL,
  PRIMARY KEY (`account_id`,`game_id`,`bounty_id`),
  KEY `account_id` (`account_id`,`game_id`,`claimer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `cached_dummys` (
  `type` varchar(60) NOT NULL,
  `id` varchar(100) NOT NULL,
  `info` blob NOT NULL,
  PRIMARY KEY (`type`,`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `changelog` (
  `version_id` smallint(6) unsigned NOT NULL DEFAULT '0',
  `changelog_id` smallint(6) unsigned NOT NULL DEFAULT '0',
  `change_title` varchar(255) NOT NULL,
  `change_message` text NOT NULL,
  `affected_db` text NOT NULL,
  PRIMARY KEY (`changelog_id`,`version_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `chess_game` (
  `chess_game_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `start_time` int(10) unsigned NOT NULL,
  `end_time` int(10) unsigned DEFAULT NULL,
  `black_id` int(10) unsigned NOT NULL,
  `white_id` int(10) unsigned NOT NULL,
  `winner_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`chess_game_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `chess_game_moves` (
  `chess_game_id` int(10) unsigned NOT NULL,
  `move_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `piece_id` int(10) unsigned NOT NULL,
  `start_x` int(10) unsigned NOT NULL,
  `start_y` int(10) unsigned NOT NULL,
  `end_x` int(10) unsigned NOT NULL,
  `end_y` int(10) unsigned NOT NULL,
  `checked` enum('CHECK','MATE') DEFAULT NULL,
  `piece_taken` int(11) DEFAULT NULL,
  `castling` enum('King','Queen') DEFAULT NULL,
  `en_passant` enum('TRUE','FALSE') DEFAULT 'FALSE',
  `promote_piece_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`chess_game_id`,`move_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `chess_game_pieces` (
  `chess_game_id` int(10) unsigned NOT NULL,
  `account_id` int(10) unsigned NOT NULL,
  `piece_id` int(10) unsigned NOT NULL,
  `piece_no` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `x` int(10) unsigned NOT NULL,
  `y` int(10) unsigned NOT NULL,
  PRIMARY KEY (`chess_game_id`,`account_id`,`piece_id`,`piece_no`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `closing_reason` (
  `reason_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `reason` varchar(255) NOT NULL,
  PRIMARY KEY (`reason_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `combat_logs` (
  `log_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `game_id` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `type` enum('PLAYER','FORCE','PORT','PLANET') NOT NULL DEFAULT 'PLAYER',
  `sector_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `attacker_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `attacker_alliance_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `defender_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `defender_alliance_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `result` blob NOT NULL,
  `saved` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`log_id`,`game_id`,`type`,`sector_id`,`timestamp`,`attacker_id`,`attacker_alliance_id`,`defender_id`,`defender_alliance_id`),
  KEY `game_id` (`game_id`,`type`,`sector_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `cpl_tag` (
  `account_id` int(11) NOT NULL DEFAULT '0',
  `tag` varchar(128) NOT NULL,
  `custom` tinyint(1) NOT NULL DEFAULT '0',
  `custom_rank` varchar(60) NOT NULL,
  `expires` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`account_id`,`custom`,`expires`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `debug` (
  `debug_type` varchar(100) NOT NULL,
  `account_id` int(10) unsigned NOT NULL,
  `value` double NOT NULL,
  `value_2` double NOT NULL DEFAULT '0',
  KEY `value` (`value`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `draft_leaders` (
  `game_id` int(10) unsigned NOT NULL,
  `account_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`game_id`,`account_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `feature_request` (
  `feature_request_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fav` int(11) NOT NULL,
  `yes` int(11) NOT NULL,
  `no` int(11) NOT NULL,
  `status` enum('Opened','Implemented','Rejected','Deleted') NOT NULL,
  PRIMARY KEY (`feature_request_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `feature_request_comments` (
  `feature_request_id` int(10) unsigned NOT NULL,
  `comment_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `poster_id` int(10) unsigned NOT NULL,
  `posting_time` int(10) unsigned NOT NULL,
  `anonymous` enum('TRUE','FALSE') NOT NULL DEFAULT 'TRUE',
  `text` text NOT NULL,
  PRIMARY KEY (`feature_request_id`,`comment_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `federal_permits` (
  `game_id` int(10) unsigned NOT NULL DEFAULT '0',
  `account_id` int(10) unsigned NOT NULL DEFAULT '0',
  `timeout` int(10) unsigned NOT NULL DEFAULT '0',
  `race_id` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`game_id`,`account_id`,`race_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `force_refresh` (
  `game_id` int(11) NOT NULL DEFAULT '0',
  `owner_id` int(11) NOT NULL DEFAULT '0',
  `sector_id` int(11) NOT NULL DEFAULT '0',
  `num_forces` smallint(6) NOT NULL DEFAULT '0',
  `refresh_at` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`game_id`,`owner_id`,`sector_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `galactic_post_applications` (
  `account_id` int(10) unsigned NOT NULL DEFAULT '0',
  `game_id` int(10) unsigned NOT NULL DEFAULT '0',
  `description` varchar(255) NOT NULL,
  `written_before` enum('YES','NO') NOT NULL DEFAULT 'YES',
  `articles_per_day` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`account_id`,`game_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `galactic_post_article` (
  `game_id` int(10) unsigned NOT NULL DEFAULT '0',
  `article_id` int(10) unsigned NOT NULL DEFAULT '0',
  `writer_id` int(10) unsigned NOT NULL DEFAULT '0',
  `title` varchar(100) NOT NULL,
  `text` text NOT NULL,
  `last_modified` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`game_id`,`article_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `galactic_post_online` (
  `paper_id` int(10) unsigned NOT NULL DEFAULT '0',
  `game_id` int(10) unsigned NOT NULL DEFAULT '0',
  `online_since` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`paper_id`,`game_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `galactic_post_paper` (
  `game_id` int(10) unsigned NOT NULL DEFAULT '0',
  `paper_id` int(10) unsigned NOT NULL DEFAULT '0',
  `title` varchar(100) NOT NULL,
  PRIMARY KEY (`game_id`,`paper_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `galactic_post_paper_content` (
  `game_id` int(10) unsigned NOT NULL DEFAULT '0',
  `paper_id` int(10) unsigned NOT NULL DEFAULT '0',
  `article_id` int(10) unsigned NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `galactic_post_writer` (
  `account_id` int(10) unsigned NOT NULL DEFAULT '0',
  `game_id` int(10) unsigned NOT NULL DEFAULT '0',
  `position` enum('editor','writer') NOT NULL DEFAULT 'editor',
  `last_wrote` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`account_id`,`game_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `galaxy` (
  `galaxy_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `galaxy_name` varchar(32) DEFAULT NULL,
  `galaxy_size` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`galaxy_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `game` (
  `game_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `game_name` varchar(32) NOT NULL,
  `game_description` text NOT NULL,
  `start_date` int(10) unsigned NOT NULL,
  `start_turns_date` int(10) unsigned NOT NULL,
  `end_date` int(10) unsigned NOT NULL,
  `max_turns` int(10) unsigned NOT NULL,
  `start_turns` int(10) unsigned NOT NULL DEFAULT '15',
  `max_players` int(10) unsigned NOT NULL DEFAULT '0',
  `game_type` enum('Default','Classic','Classic 1.6','1.6.3','Semi Wars','Draft') NOT NULL DEFAULT 'Default',
  `credits_needed` smallint(6) unsigned NOT NULL DEFAULT '0',
  `game_speed` float unsigned NOT NULL DEFAULT '1',
  `enabled` enum('TRUE','FALSE') NOT NULL DEFAULT 'FALSE',
  `alliance_max_players` smallint(5) unsigned NOT NULL,
  `alliance_max_vets` smallint(5) unsigned NOT NULL,
  `ignore_stats` enum('TRUE','FALSE') NOT NULL DEFAULT 'FALSE',
  `starting_credits` int(10) unsigned NOT NULL,
  PRIMARY KEY (`game_id`),
  UNIQUE KEY `game_name` (`game_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 PACK_KEYS=1 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `game_disable` (
  `reason` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `game_galaxy` (
  `game_id` int(10) unsigned NOT NULL,
  `galaxy_id` int(10) unsigned NOT NULL,
  `galaxy_name` varchar(32) NOT NULL,
  `width` int(10) unsigned NOT NULL,
  `height` int(10) unsigned NOT NULL,
  `galaxy_type` enum('Racial','Neutral','Planet') NOT NULL,
  `max_force_time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`game_id`,`galaxy_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `game_news` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `admin_name` varchar(32) NOT NULL,
  `message` text NOT NULL,
  `time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `good` (
  `good_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `good_name` varchar(32) DEFAULT NULL,
  `base_price` int(10) unsigned DEFAULT NULL,
  `max_amount` int(10) unsigned NOT NULL DEFAULT '5000',
  `good_class` int(10) unsigned NOT NULL DEFAULT '1',
  `align_restriction` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`good_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `hardware_type` (
  `hardware_type_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `hardware_name` varchar(32) DEFAULT NULL,
  `cost` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`hardware_type_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `hidden_players` (
  `account_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`account_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `hill_timer` (
  `game_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `sector_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `time_start` int(10) unsigned NOT NULL DEFAULT '0',
  `alliance_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `hill_type` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`game_id`,`sector_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `hof_visibility` (
  `type` varchar(255) NOT NULL,
  `visibility` enum('PUBLIC','ALLIANCE','PRIVATE') NOT NULL,
  PRIMARY KEY (`type`),
  KEY `visibility` (`visibility`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `irc_alliance_has_channel` (
  `channel` varchar(30) NOT NULL,
  `alliance_id` smallint(6) unsigned NOT NULL,
  `game_id` smallint(6) unsigned NOT NULL,
  PRIMARY KEY (`channel`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `irc_seen` (
  `seen_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nick` varchar(30) NOT NULL,
  `registered` tinyint(1) DEFAULT NULL,
  `user` varchar(50) NOT NULL,
  `host` varchar(100) NOT NULL,
  `channel` varchar(32) NOT NULL,
  `signed_on` int(10) unsigned NOT NULL DEFAULT '0',
  `signed_off` int(10) unsigned NOT NULL DEFAULT '0',
  `seen_count` smallint(6) NOT NULL DEFAULT '0',
  `seen_by` varchar(30) DEFAULT NULL,
  `registered_nick` varchar(30) NOT NULL,
  PRIMARY KEY (`seen_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `kills` (
  `game_id` int(10) unsigned NOT NULL DEFAULT '0',
  `dead_id` int(10) unsigned NOT NULL DEFAULT '0',
  `killer_id` int(10) unsigned NOT NULL DEFAULT '0',
  `dead_exp` int(10) unsigned NOT NULL DEFAULT '0',
  `kill_exp` int(10) unsigned NOT NULL DEFAULT '0',
  `sector_id` smallint(6) unsigned NOT NULL DEFAULT '0',
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  `processed` enum('TRUE','FALSE') NOT NULL DEFAULT 'FALSE',
  PRIMARY KEY (`game_id`,`dead_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `level` (
  `level_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `level_name` varchar(32) DEFAULT NULL,
  `requirement` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`level_id`),
  KEY `requirement` (`requirement`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `location` (
  `game_id` int(10) unsigned NOT NULL DEFAULT '0',
  `sector_id` int(10) unsigned NOT NULL DEFAULT '0',
  `location_type_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`game_id`,`sector_id`,`location_type_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `location_is_bank` (
  `location_type_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`location_type_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `location_is_bar` (
  `location_type_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`location_type_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `location_is_fed` (
  `location_type_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`location_type_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `location_is_hq` (
  `location_type_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`location_type_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `location_is_ug` (
  `location_type_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`location_type_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `location_sells_hardware` (
  `location_type_id` int(10) unsigned NOT NULL DEFAULT '0',
  `hardware_type_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`location_type_id`,`hardware_type_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `location_sells_ships` (
  `location_type_id` int(10) unsigned NOT NULL DEFAULT '0',
  `ship_type_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`location_type_id`,`ship_type_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `location_sells_weapons` (
  `location_type_id` int(10) unsigned NOT NULL DEFAULT '0',
  `weapon_type_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`location_type_id`,`weapon_type_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `location_type` (
  `location_type_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `location_name` varchar(55) DEFAULT NULL,
  `location_image` varchar(32) DEFAULT NULL,
  `location_processor` varchar(32) DEFAULT NULL,
  `smc_type_id` int(3) unsigned NOT NULL DEFAULT '0',
  `mgu_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`location_type_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `locks_queue` (
  `lock_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `game_id` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `account_id` smallint(5) unsigned NOT NULL,
  `sector_id` smallint(6) unsigned NOT NULL DEFAULT '0',
  `timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`lock_id`,`game_id`,`sector_id`),
  KEY `timestamp` (`timestamp`),
  KEY `account_id` (`account_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `log_has_notes` (
  `account_id` smallint(6) unsigned NOT NULL DEFAULT '0',
  `notes` text NOT NULL,
  PRIMARY KEY (`account_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `log_type` (
  `log_type_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `log_type_entry` varchar(20) NOT NULL,
  PRIMARY KEY (`log_type_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `macro_check` (
  `account_id` int(11) NOT NULL DEFAULT '0',
  `good` int(11) NOT NULL DEFAULT '0',
  `bad` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`account_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `manual` (
  `topic_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_topic_id` int(10) unsigned NOT NULL DEFAULT '0',
  `order_id` int(10) unsigned NOT NULL DEFAULT '0',
  `topic` varchar(100) NOT NULL,
  `text` longtext NOT NULL,
  PRIMARY KEY (`topic_id`,`parent_topic_id`,`order_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `mb_exceptions` (
  `type` varchar(16) NOT NULL,
  `value` varchar(128) NOT NULL,
  PRIMARY KEY (`type`,`value`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mb_keywords` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `keyword` varchar(32) NOT NULL,
  `type` enum('find','ignore') NOT NULL DEFAULT 'find',
  `assoc` int(11) NOT NULL DEFAULT '0',
  `use` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `message` (
  `message_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `game_id` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `account_id` smallint(6) unsigned NOT NULL DEFAULT '0',
  `message_type_id` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `message_text` text NOT NULL,
  `sender_id` smallint(6) unsigned NOT NULL DEFAULT '0',
  `send_time` int(10) unsigned NOT NULL DEFAULT '0',
  `msg_read` enum('TRUE','FALSE') NOT NULL DEFAULT 'FALSE',
  `expire_time` int(10) unsigned NOT NULL DEFAULT '0',
  `reciever_delete` enum('TRUE','FALSE') NOT NULL DEFAULT 'FALSE',
  `sender_delete` enum('TRUE','FALSE') NOT NULL DEFAULT 'FALSE',
  PRIMARY KEY (`message_id`),
  KEY `send_time` (`send_time`),
  KEY `game_id` (`game_id`,`account_id`,`message_type_id`,`reciever_delete`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `message_blacklist` (
  `entry_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `game_id` smallint(5) unsigned NOT NULL,
  `account_id` smallint(5) unsigned NOT NULL,
  `blacklisted_id` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`entry_id`),
  KEY `game_id` (`game_id`,`account_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `message_boxes` (
  `message_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `game_id` int(10) unsigned NOT NULL,
  `sender_id` int(10) unsigned NOT NULL,
  `send_time` int(10) unsigned NOT NULL,
  `box_type_id` tinyint(3) unsigned NOT NULL,
  `message_text` text NOT NULL,
  PRIMARY KEY (`message_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `message_box_types` (
  `box_type_id` tinyint(3) unsigned NOT NULL,
  `box_type_name` varchar(32) NOT NULL,
  PRIMARY KEY (`box_type_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `message_notify` (
  `notify_id` int(10) unsigned NOT NULL DEFAULT '0',
  `game_id` int(10) unsigned NOT NULL DEFAULT '0',
  `from_id` int(10) unsigned NOT NULL DEFAULT '0',
  `to_id` int(10) unsigned NOT NULL DEFAULT '0',
  `text` text NOT NULL,
  `sent_time` int(11) NOT NULL DEFAULT '0',
  `notify_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`notify_id`,`game_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `message_type` (
  `message_type_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `message_type_name` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`message_type_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `multi_checking` (
  `ips` longtext NOT NULL,
  `accounts` longtext NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `multi_checking_cookie` (
  `account_id` int(11) NOT NULL DEFAULT '0',
  `array` text NOT NULL,
  `use` varchar(32) NOT NULL DEFAULT 'TRUE',
  PRIMARY KEY (`account_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `news` (
  `news_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `game_id` int(10) unsigned NOT NULL DEFAULT '0',
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  `news_message` text NOT NULL,
  `type` enum('breaking','regular','lotto') NOT NULL DEFAULT 'regular',
  `killer_id` smallint(6) unsigned DEFAULT NULL,
  `killer_alliance` smallint(6) unsigned DEFAULT NULL,
  `dead_id` smallint(6) unsigned DEFAULT NULL,
  `dead_alliance` smallint(6) unsigned DEFAULT NULL,
  PRIMARY KEY (`news_id`),
  KEY `time` (`time`),
  KEY `game_id` (`game_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `newsletter` (
  `newsletter_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `newsletter_html` longtext NOT NULL,
  `newsletter_text` longtext NOT NULL,
  PRIMARY KEY (`newsletter_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `notification` (
  `notification_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `notification_type` enum('validation_code','inactive') DEFAULT NULL,
  `account_id` int(10) unsigned DEFAULT NULL,
  `time` int(10) DEFAULT NULL,
  PRIMARY KEY (`notification_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='1 - validation code.' AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `npc_logins` (
  `login` varchar(32) NOT NULL,
  `player_name` varchar(32) NOT NULL,
  `alliance_name` varchar(32) NOT NULL,
  `active` enum('TRUE','FALSE') NOT NULL DEFAULT 'FALSE',
  `working` enum('TRUE','FALSE') NOT NULL DEFAULT 'FALSE',
  PRIMARY KEY (`login`),
  UNIQUE KEY `player_name` (`player_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `npc_logs` (
  `log_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `script_id` int(10) unsigned NOT NULL,
  `npc_id` int(6) unsigned NOT NULL,
  `time` datetime NOT NULL,
  `message` varchar(100) NOT NULL,
  `debug_info` text NOT NULL,
  `var` text NOT NULL,
  PRIMARY KEY (`log_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `npc_long_term_goal` (
  `account_id` smallint(3) unsigned NOT NULL DEFAULT '0',
  `game_id` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `type` enum('scout') NOT NULL DEFAULT 'scout',
  `task` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`account_id`,`game_id`),
  UNIQUE KEY `type` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `npc_short_term_goal` (
  `account_id` smallint(3) unsigned NOT NULL DEFAULT '0',
  `game_id` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `type` enum('follow_course') NOT NULL DEFAULT 'follow_course',
  `task` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`account_id`,`game_id`),
  UNIQUE KEY `type` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `open_forms` (
  `type` enum('FEATURE','BETA') NOT NULL,
  `open` enum('TRUE','FALSE') NOT NULL DEFAULT 'FALSE',
  PRIMARY KEY (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `permission` (
  `permission_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `permission_name` varchar(32) NOT NULL,
  `link_to` varchar(50) NOT NULL,
  PRIMARY KEY (`permission_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `planet` (
  `game_id` int(10) unsigned NOT NULL DEFAULT '0',
  `sector_id` int(10) unsigned NOT NULL DEFAULT '0',
  `owner_id` int(10) unsigned NOT NULL DEFAULT '0',
  `password` varchar(32) NOT NULL,
  `planet_name` varchar(32) NOT NULL DEFAULT 'Unknown',
  `inhabitable_time` int(10) unsigned NOT NULL DEFAULT '0',
  `shields` int(10) unsigned NOT NULL DEFAULT '0',
  `drones` int(10) unsigned NOT NULL DEFAULT '0',
  `credits` int(10) unsigned NOT NULL DEFAULT '0',
  `bonds` int(10) unsigned NOT NULL DEFAULT '0',
  `maturity` int(10) unsigned NOT NULL DEFAULT '0',
  `busted_time` int(10) unsigned NOT NULL DEFAULT '0',
  `last_updated` int(10) unsigned NOT NULL,
  PRIMARY KEY (`game_id`,`sector_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `planet_attack` (
  `game_id` int(10) unsigned NOT NULL DEFAULT '0',
  `sector_id` int(10) unsigned NOT NULL DEFAULT '0',
  `trigger_id` int(10) unsigned NOT NULL DEFAULT '0',
  `time_attack` int(10) unsigned NOT NULL DEFAULT '0',
  `attacker_damage` int(10) unsigned NOT NULL DEFAULT '0',
  `planet_damage` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`game_id`,`sector_id`,`trigger_id`,`time_attack`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `planet_construction` (
  `construction_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `construction_name` varchar(32) DEFAULT NULL,
  `construction_description` varchar(32) DEFAULT NULL,
  `max_construction` int(10) unsigned NOT NULL DEFAULT '0',
  `exp_gain` int(10) unsigned NOT NULL,
  PRIMARY KEY (`construction_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `planet_cost_credits` (
  `construction_id` int(10) unsigned NOT NULL DEFAULT '0',
  `amount` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`construction_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `planet_cost_good` (
  `construction_id` int(10) unsigned NOT NULL DEFAULT '0',
  `good_id` int(10) unsigned NOT NULL DEFAULT '0',
  `amount` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`construction_id`,`good_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `planet_cost_time` (
  `construction_id` int(10) unsigned NOT NULL DEFAULT '0',
  `amount` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`construction_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `planet_has_building` (
  `game_id` int(10) unsigned NOT NULL DEFAULT '0',
  `sector_id` int(10) unsigned NOT NULL DEFAULT '0',
  `construction_id` int(10) unsigned NOT NULL DEFAULT '0',
  `amount` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`game_id`,`sector_id`,`construction_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `planet_has_cargo` (
  `game_id` int(10) unsigned NOT NULL DEFAULT '0',
  `sector_id` int(10) unsigned NOT NULL DEFAULT '0',
  `good_id` int(10) unsigned NOT NULL DEFAULT '0',
  `amount` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`game_id`,`sector_id`,`good_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `planet_has_goods` (
  `game_id` smallint(6) unsigned NOT NULL,
  `sector_id` int(11) unsigned NOT NULL,
  `good_id` tinyint(4) unsigned NOT NULL,
  `transaction` enum('Buy','Sell') NOT NULL,
  `amount` smallint(6) unsigned NOT NULL,
  PRIMARY KEY (`game_id`,`sector_id`,`good_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `planet_is_building` (
  `game_id` int(10) unsigned NOT NULL DEFAULT '0',
  `sector_id` int(10) unsigned NOT NULL DEFAULT '0',
  `building_slot_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `construction_id` int(10) unsigned NOT NULL DEFAULT '0',
  `constructor_id` int(10) unsigned NOT NULL DEFAULT '0',
  `time_complete` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`game_id`,`sector_id`,`building_slot_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `player` (
  `account_id` smallint(6) unsigned NOT NULL DEFAULT '0',
  `game_id` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `player_id` int(10) unsigned NOT NULL DEFAULT '0',
  `player_name` varchar(32) NOT NULL,
  `ship_type_id` int(10) unsigned NOT NULL DEFAULT '28',
  `turns` int(10) unsigned NOT NULL DEFAULT '0',
  `race_id` int(10) unsigned NOT NULL DEFAULT '1',
  `newbie_turns` int(10) unsigned NOT NULL DEFAULT '500',
  `credits` int(10) unsigned NOT NULL DEFAULT '5000',
  `experience` int(10) unsigned NOT NULL DEFAULT '0',
  `alignment` int(10) NOT NULL DEFAULT '0',
  `alliance_id` smallint(6) unsigned NOT NULL DEFAULT '0',
  `sector_id` int(10) unsigned NOT NULL DEFAULT '1',
  `last_sector_id` int(10) unsigned NOT NULL DEFAULT '0',
  `controlled` int(11) NOT NULL DEFAULT '0',
  `dead` enum('TRUE','FALSE') NOT NULL DEFAULT 'FALSE',
  `last_turn_update` int(10) unsigned NOT NULL DEFAULT '0',
  `bank` int(10) unsigned NOT NULL DEFAULT '0',
  `military_payment` int(10) unsigned NOT NULL DEFAULT '0',
  `land_on_planet` enum('TRUE','FALSE') NOT NULL DEFAULT 'FALSE',
  `newbie_warning` enum('TRUE','FALSE') NOT NULL DEFAULT 'TRUE',
  `last_cpl_action` int(10) unsigned NOT NULL DEFAULT '0',
  `last_news_update` int(10) unsigned NOT NULL DEFAULT '0',
  `last_ticker_update` int(10) unsigned NOT NULL DEFAULT '0',
  `ticker` enum('FALSE','SCOUT','NEWS','BLOCK') NOT NULL DEFAULT 'FALSE',
  `kills` int(10) unsigned NOT NULL DEFAULT '0',
  `deaths` int(10) unsigned NOT NULL DEFAULT '0',
  `ignore_globals` enum('TRUE','FALSE') NOT NULL DEFAULT 'FALSE',
  `out_of_game` enum('TRUE','FALSE') NOT NULL DEFAULT 'FALSE',
  `sector_change` int(10) unsigned NOT NULL DEFAULT '0',
  `safe_exit` smallint(5) unsigned NOT NULL DEFAULT '0',
  `detected` enum('TRUE','FALSE') NOT NULL DEFAULT 'FALSE',
  `name_changed` enum('TRUE','FALSE') NOT NULL DEFAULT 'FALSE',
  `last_active` int(10) unsigned NOT NULL,
  `last_ship_mod` int(10) unsigned NOT NULL,
  `stunned` int(10) unsigned NOT NULL,
  `fleed` enum('TRUE','FALSE') NOT NULL,
  `attack_warning` char(6) NOT NULL,
  `kicked` enum('TRUE','FALSE') NOT NULL,
  `last_shield_update` int(10) unsigned NOT NULL,
  `government_help` int(10) unsigned NOT NULL,
  `zoom` tinyint(3) unsigned NOT NULL DEFAULT '2',
  `zoom_on` enum('TRUE','FALSE') NOT NULL,
  `display_missions` enum('TRUE','FALSE') NOT NULL,
  `last_port` int(10) unsigned NOT NULL,
  `combat_drones_kamikaze_on_mines` enum('TRUE','FALSE') NOT NULL DEFAULT 'FALSE',
  `display_weapons` enum('TRUE','FALSE') NOT NULL DEFAULT 'TRUE',
  `force_drop_messages` enum('TRUE','FALSE') NOT NULL DEFAULT 'TRUE',
  `alliance_join` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`account_id`,`game_id`),
  KEY `game_id` (`game_id`,`sector_id`,`land_on_planet`,`last_cpl_action`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `player_attacks_planet` (
  `game_id` int(11) NOT NULL DEFAULT '0',
  `account_id` int(11) NOT NULL DEFAULT '0',
  `sector_id` int(11) NOT NULL DEFAULT '0',
  `time` int(11) NOT NULL DEFAULT '0',
  `level` float unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`game_id`,`account_id`,`sector_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `player_attacks_port` (
  `game_id` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `account_id` smallint(6) unsigned NOT NULL DEFAULT '0',
  `sector_id` smallint(6) unsigned NOT NULL DEFAULT '0',
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  `level` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`game_id`,`account_id`,`sector_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `player_cache` (
  `account_id` smallint(6) unsigned NOT NULL DEFAULT '0',
  `game_id` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `experience` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`account_id`,`game_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `player_can_fed` (
  `account_id` int(10) unsigned NOT NULL,
  `game_id` int(10) unsigned NOT NULL,
  `race_id` int(10) unsigned NOT NULL,
  `expiry` int(10) unsigned NOT NULL,
  `allowed` enum('TRUE','FALSE') NOT NULL,
  PRIMARY KEY (`account_id`,`game_id`,`race_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `player_has_alliance_role` (
  `account_id` smallint(6) unsigned NOT NULL DEFAULT '0',
  `game_id` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `role_id` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `alliance_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`account_id`,`game_id`,`alliance_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `player_has_drinks` (
  `account_id` int(10) unsigned NOT NULL DEFAULT '0',
  `game_id` int(10) unsigned NOT NULL DEFAULT '0',
  `drink_id` int(10) unsigned NOT NULL DEFAULT '0',
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`account_id`,`game_id`,`drink_id`,`time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `player_has_gadget` (
  `game_id` int(10) unsigned NOT NULL,
  `account_id` int(10) unsigned NOT NULL,
  `gadget_id` int(10) unsigned NOT NULL,
  `cooldown` int(10) unsigned NOT NULL DEFAULT '0',
  `equipped` int(11) NOT NULL DEFAULT '0',
  `lasts_until` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`game_id`,`account_id`,`gadget_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `player_has_notes` (
  `note_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `game_id` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `account_id` int(10) unsigned NOT NULL DEFAULT '0',
  `note` mediumblob NOT NULL,
  PRIMARY KEY (`note_id`),
  KEY `game_id` (`game_id`,`account_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `player_has_relation` (
  `account_id` int(10) unsigned NOT NULL DEFAULT '0',
  `game_id` int(10) unsigned NOT NULL DEFAULT '0',
  `race_id` int(10) unsigned NOT NULL DEFAULT '0',
  `relation` int(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`account_id`,`game_id`,`race_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `player_has_special` (
  `account_id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL,
  `stat_type_id` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  PRIMARY KEY (`account_id`,`game_id`,`stat_type_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `player_has_stats` (
  `account_id` int(11) NOT NULL DEFAULT '0',
  `game_id` int(11) NOT NULL DEFAULT '0',
  `planet_busts` int(11) NOT NULL DEFAULT '0',
  `planet_bust_levels` int(11) NOT NULL DEFAULT '0',
  `port_raids` int(11) NOT NULL DEFAULT '0',
  `port_raid_levels` int(11) NOT NULL DEFAULT '0',
  `sectors_explored` int(11) NOT NULL DEFAULT '0',
  `deaths` int(11) NOT NULL DEFAULT '0',
  `kills` int(11) NOT NULL DEFAULT '0',
  `goods_traded` int(11) NOT NULL DEFAULT '0',
  `experience_traded` int(11) NOT NULL DEFAULT '0',
  `bounties_claimed` int(11) NOT NULL DEFAULT '0',
  `bounty_amount_claimed` int(11) NOT NULL DEFAULT '0',
  `military_claimed` int(11) NOT NULL DEFAULT '0',
  `bounty_amount_on` int(11) NOT NULL DEFAULT '0',
  `player_damage` int(11) NOT NULL DEFAULT '0',
  `port_damage` int(11) NOT NULL DEFAULT '0',
  `planet_damage` int(11) NOT NULL DEFAULT '0',
  `turns_used` int(11) NOT NULL DEFAULT '0',
  `kill_exp` int(11) NOT NULL DEFAULT '0',
  `traders_killed_exp` int(11) NOT NULL DEFAULT '0',
  `blackjack_win` int(11) NOT NULL DEFAULT '0',
  `blackjack_lose` int(11) NOT NULL DEFAULT '0',
  `lotto` int(11) NOT NULL DEFAULT '0',
  `drinks` int(11) NOT NULL DEFAULT '0',
  `trade_profit` int(11) NOT NULL DEFAULT '0',
  `trade_sales` int(11) NOT NULL DEFAULT '0',
  `mines` int(11) NOT NULL DEFAULT '0',
  `combat_drones` int(11) NOT NULL DEFAULT '0',
  `scout_drones` int(11) NOT NULL DEFAULT '0',
  `money_gained` int(11) NOT NULL DEFAULT '0',
  `killed_ships` int(11) NOT NULL DEFAULT '0',
  `died_ships` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`account_id`,`game_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `player_has_stats_cache` (
  `account_id` int(11) NOT NULL DEFAULT '0',
  `game_id` int(11) NOT NULL DEFAULT '0',
  `planet_busts` int(11) NOT NULL DEFAULT '0',
  `planet_bust_levels` int(11) NOT NULL DEFAULT '0',
  `port_raids` int(11) NOT NULL DEFAULT '0',
  `port_raid_levels` int(11) NOT NULL DEFAULT '0',
  `sectors_explored` int(11) NOT NULL DEFAULT '0',
  `deaths` int(11) NOT NULL DEFAULT '0',
  `kills` int(11) NOT NULL DEFAULT '0',
  `goods_traded` int(11) NOT NULL DEFAULT '0',
  `experience_traded` int(11) NOT NULL DEFAULT '0',
  `bounties_claimed` int(11) NOT NULL DEFAULT '0',
  `bounty_amount_claimed` int(11) NOT NULL DEFAULT '0',
  `military_claimed` int(11) NOT NULL DEFAULT '0',
  `bounty_amount_on` int(11) NOT NULL DEFAULT '0',
  `player_damage` int(11) NOT NULL DEFAULT '0',
  `port_damage` int(11) NOT NULL DEFAULT '0',
  `planet_damage` int(11) NOT NULL DEFAULT '0',
  `turns_used` int(11) NOT NULL DEFAULT '0',
  `kill_exp` int(11) NOT NULL DEFAULT '0',
  `traders_killed_exp` int(11) NOT NULL DEFAULT '0',
  `blackjack_win` int(11) NOT NULL DEFAULT '0',
  `blackjack_lose` int(11) NOT NULL DEFAULT '0',
  `lotto` int(11) NOT NULL DEFAULT '0',
  `drinks` int(11) NOT NULL DEFAULT '0',
  `trade_profit` int(11) NOT NULL DEFAULT '0',
  `trade_sales` int(11) NOT NULL DEFAULT '0',
  `mines` int(11) NOT NULL DEFAULT '0',
  `combat_drones` int(11) NOT NULL DEFAULT '0',
  `scout_drones` int(11) NOT NULL DEFAULT '0',
  `money_gained` int(11) NOT NULL DEFAULT '0',
  `killed_ships` int(11) NOT NULL DEFAULT '0',
  `died_ships` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`game_id`,`account_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `player_has_ticker` (
  `game_id` int(11) NOT NULL DEFAULT '0',
  `account_id` int(11) NOT NULL DEFAULT '0',
  `type` enum('NEWS','SCOUT','BLOCK') NOT NULL DEFAULT 'NEWS',
  `expires` int(11) NOT NULL DEFAULT '0',
  `recent` varchar(255) NOT NULL DEFAULT '',
  `time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`game_id`,`account_id`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `player_has_ticket` (
  `game_id` int(11) NOT NULL DEFAULT '0',
  `account_id` int(11) NOT NULL DEFAULT '0',
  `time` int(11) NOT NULL DEFAULT '0',
  `prize` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`game_id`,`account_id`,`time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `player_has_unread_messages` (
  `account_id` int(10) unsigned NOT NULL DEFAULT '0',
  `game_id` int(10) unsigned NOT NULL DEFAULT '0',
  `message_type_id` int(10) unsigned NOT NULL DEFAULT '0',
  KEY `account_id` (`account_id`,`game_id`,`message_type_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='A entry for a player means he has unread messages in this fo';

CREATE TABLE IF NOT EXISTS `player_hof` (
  `account_id` smallint(5) unsigned NOT NULL,
  `game_id` smallint(5) unsigned NOT NULL,
  `type` varchar(255) NOT NULL,
  `amount` double unsigned NOT NULL,
  PRIMARY KEY (`account_id`,`game_id`,`type`),
  KEY `type` (`type`,`game_id`,`account_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `player_is_president` (
  `account_id` int(10) unsigned NOT NULL DEFAULT '0',
  `game_id` int(10) unsigned NOT NULL DEFAULT '0',
  `race_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`game_id`,`race_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `player_joined_alliance` (
  `account_id` smallint(5) unsigned NOT NULL,
  `game_id` tinyint(3) unsigned NOT NULL,
  `alliance_id` smallint(5) unsigned NOT NULL,
  `status` enum('NEWBIE','VETERAN') NOT NULL,
  PRIMARY KEY (`account_id`,`game_id`,`alliance_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `player_plotted_course` (
  `account_id` int(10) unsigned NOT NULL DEFAULT '0',
  `game_id` int(10) unsigned NOT NULL DEFAULT '0',
  `distance` int(10) unsigned NOT NULL DEFAULT '0',
  `course` text NOT NULL,
  PRIMARY KEY (`account_id`,`game_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `player_read_thread` (
  `account_id` smallint(6) unsigned NOT NULL DEFAULT '0',
  `game_id` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `alliance_id` smallint(6) NOT NULL DEFAULT '0',
  `thread_id` smallint(6) unsigned NOT NULL DEFAULT '0',
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`account_id`,`game_id`,`alliance_id`,`thread_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `player_repaired` (
  `account_id` int(10) unsigned NOT NULL,
  `game_id` int(10) unsigned NOT NULL,
  `time` int(10) unsigned NOT NULL,
  `amount` int(10) NOT NULL,
  `source` enum('Normal','Breakdown') NOT NULL,
  PRIMARY KEY (`account_id`,`game_id`,`time`,`amount`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `player_visited_port` (
  `account_id` smallint(6) unsigned NOT NULL DEFAULT '0',
  `game_id` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `sector_id` smallint(6) unsigned NOT NULL DEFAULT '0',
  `visited` int(10) unsigned NOT NULL DEFAULT '0',
  `port_info_hash` char(32) CHARACTER SET ascii NOT NULL,
  PRIMARY KEY (`account_id`,`game_id`,`sector_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `player_visited_sector` (
  `account_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `game_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `sector_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`account_id`,`game_id`,`sector_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `player_votes_pact` (
  `account_id` int(10) unsigned NOT NULL DEFAULT '0',
  `game_id` int(10) unsigned NOT NULL DEFAULT '0',
  `race_id_1` int(10) unsigned NOT NULL DEFAULT '0',
  `race_id_2` int(10) unsigned NOT NULL DEFAULT '0',
  `vote` enum('YES','NO') NOT NULL DEFAULT 'NO',
  PRIMARY KEY (`account_id`,`game_id`,`race_id_1`,`race_id_2`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `player_votes_relation` (
  `account_id` int(10) unsigned NOT NULL DEFAULT '0',
  `game_id` int(10) unsigned NOT NULL DEFAULT '0',
  `race_id_1` int(10) unsigned NOT NULL DEFAULT '0',
  `race_id_2` int(10) unsigned NOT NULL DEFAULT '0',
  `action` enum('INC','DEC') NOT NULL DEFAULT 'INC',
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`account_id`,`game_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `plot_cache` (
  `game_id` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `sector_id_1` smallint(6) unsigned NOT NULL DEFAULT '0',
  `sector_id_2` smallint(6) unsigned NOT NULL DEFAULT '0',
  `length` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `timeout` int(10) unsigned NOT NULL DEFAULT '0',
  `route` text NOT NULL,
  PRIMARY KEY (`sector_id_1`,`sector_id_2`,`game_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `port` (
  `game_id` int(10) unsigned NOT NULL DEFAULT '0',
  `sector_id` int(10) unsigned NOT NULL DEFAULT '0',
  `level` int(10) unsigned NOT NULL DEFAULT '1',
  `race_id` int(10) unsigned NOT NULL DEFAULT '1',
  `experience` int(10) unsigned NOT NULL DEFAULT '0',
  `credits` int(10) unsigned NOT NULL DEFAULT '0',
  `last_update` int(10) unsigned NOT NULL DEFAULT '0',
  `shields` int(10) unsigned NOT NULL DEFAULT '0',
  `armour` int(10) unsigned NOT NULL DEFAULT '0',
  `combat_drones` int(10) unsigned NOT NULL DEFAULT '0',
  `attack_started` int(10) unsigned NOT NULL DEFAULT '0',
  `reinforce_time` int(10) unsigned NOT NULL DEFAULT '0',
  `upgrade` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`game_id`,`sector_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `port_attack_times` (
  `game_id` tinyint(4) NOT NULL DEFAULT '0',
  `sector_id` int(4) NOT NULL DEFAULT '0',
  `time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`game_id`,`sector_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `port_has_goods` (
  `game_id` int(10) unsigned NOT NULL DEFAULT '0',
  `sector_id` int(10) unsigned NOT NULL DEFAULT '0',
  `good_id` int(10) unsigned NOT NULL DEFAULT '0',
  `transaction_type` enum('Buy','Sell') DEFAULT NULL,
  `amount` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`game_id`,`sector_id`,`good_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `port_info_cache` (
  `game_id` int(3) unsigned NOT NULL,
  `sector_id` int(6) unsigned NOT NULL,
  `port_info_hash` char(32) CHARACTER SET ascii NOT NULL,
  `port_info` mediumblob NOT NULL,
  PRIMARY KEY (`game_id`,`sector_id`,`port_info_hash`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `profile` (
  `account_id` int(10) unsigned NOT NULL DEFAULT '0',
  `nick` varchar(20) NOT NULL DEFAULT '',
  `webpage` varchar(100) NOT NULL DEFAULT '',
  `location` varchar(50) NOT NULL DEFAULT '',
  `birthdate` varchar(10) NOT NULL DEFAULT '',
  `comment` longtext NOT NULL,
  PRIMARY KEY (`account_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `race` (
  `race_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `race_name` varchar(32) DEFAULT NULL,
  `race_description` text,
  PRIMARY KEY (`race_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `race_has_relation` (
  `game_id` int(10) unsigned NOT NULL DEFAULT '0',
  `race_id_1` int(10) unsigned NOT NULL DEFAULT '0',
  `race_id_2` int(10) unsigned NOT NULL DEFAULT '0',
  `relation` int(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`race_id_1`,`race_id_2`,`game_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `race_has_voting` (
  `game_id` int(10) unsigned NOT NULL DEFAULT '0',
  `race_id_1` int(10) unsigned NOT NULL DEFAULT '0',
  `race_id_2` int(10) unsigned NOT NULL DEFAULT '0',
  `type` enum('WAR','PEACE') NOT NULL DEFAULT 'WAR',
  `end_time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`game_id`,`race_id_1`,`race_id_2`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `rankings` (
  `rankings_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rankings_name` varchar(50) NOT NULL,
  `kills_needed` int(10) unsigned NOT NULL DEFAULT '0',
  `experience_needed` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`rankings_id`,`kills_needed`,`experience_needed`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `route_cache` (
  `game_id` int(10) unsigned NOT NULL,
  `max_ports` int(11) NOT NULL,
  `goods_allowed` varchar(150) NOT NULL,
  `races_allowed` varchar(100) NOT NULL,
  `start_sector_id` int(10) unsigned NOT NULL,
  `end_sector_id` int(10) unsigned NOT NULL,
  `routes_for_port` int(11) NOT NULL,
  `max_distance` int(11) NOT NULL,
  `routes` blob NOT NULL,
  PRIMARY KEY (`game_id`,`max_ports`,`goods_allowed`,`races_allowed`,`start_sector_id`,`end_sector_id`,`routes_for_port`,`max_distance`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `sector` (
  `sector_id` int(10) unsigned NOT NULL DEFAULT '0',
  `game_id` int(10) unsigned NOT NULL DEFAULT '0',
  `galaxy_id` int(10) unsigned DEFAULT NULL,
  `link_up` int(10) unsigned DEFAULT NULL,
  `link_down` int(10) unsigned DEFAULT NULL,
  `link_left` int(10) unsigned DEFAULT NULL,
  `link_right` int(10) unsigned DEFAULT NULL,
  `battles` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`game_id`,`sector_id`),
  KEY `game_id` (`game_id`,`galaxy_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `sector_has_forces` (
  `game_id` int(10) unsigned NOT NULL DEFAULT '0',
  `sector_id` int(10) unsigned NOT NULL DEFAULT '0',
  `owner_id` int(10) unsigned NOT NULL DEFAULT '0',
  `combat_drones` int(10) unsigned NOT NULL DEFAULT '0',
  `scout_drones` int(10) unsigned NOT NULL DEFAULT '0',
  `mines` int(10) unsigned NOT NULL DEFAULT '0',
  `expire_time` int(10) unsigned NOT NULL DEFAULT '0',
  `refresh_at` int(10) unsigned NOT NULL,
  `refresher` int(10) unsigned NOT NULL,
  PRIMARY KEY (`game_id`,`sector_id`,`owner_id`),
  KEY `refresher` (`refresher`),
  KEY `expire_time` (`expire_time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `sector_message` (
  `account_id` int(11) NOT NULL DEFAULT '0',
  `game_id` int(11) NOT NULL DEFAULT '0',
  `message` text NOT NULL,
  PRIMARY KEY (`account_id`,`game_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `ship_has_cargo` (
  `account_id` int(10) unsigned NOT NULL DEFAULT '0',
  `game_id` int(10) unsigned NOT NULL DEFAULT '0',
  `good_id` int(10) unsigned NOT NULL DEFAULT '0',
  `amount` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`account_id`,`game_id`,`good_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `ship_has_hardware` (
  `account_id` int(10) unsigned NOT NULL DEFAULT '0',
  `game_id` int(10) unsigned NOT NULL DEFAULT '0',
  `hardware_type_id` int(10) unsigned NOT NULL DEFAULT '0',
  `amount` int(10) unsigned NOT NULL DEFAULT '0',
  `old_amount` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`account_id`,`game_id`,`hardware_type_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `ship_has_illusion` (
  `account_id` int(10) unsigned NOT NULL DEFAULT '0',
  `game_id` int(10) unsigned NOT NULL DEFAULT '0',
  `ship_type_id` int(10) unsigned NOT NULL DEFAULT '0',
  `attack` int(10) unsigned NOT NULL DEFAULT '0',
  `defense` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`account_id`,`game_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `ship_has_name` (
  `game_id` int(11) NOT NULL DEFAULT '0',
  `account_id` int(11) NOT NULL DEFAULT '0',
  `ship_name` varchar(128) NOT NULL,
  PRIMARY KEY (`game_id`,`account_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ship_has_weapon` (
  `account_id` int(10) unsigned NOT NULL DEFAULT '0',
  `game_id` int(10) unsigned NOT NULL DEFAULT '0',
  `order_id` int(10) unsigned NOT NULL DEFAULT '0',
  `weapon_type_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`account_id`,`game_id`,`order_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `ship_is_cloaked` (
  `account_id` int(10) unsigned NOT NULL DEFAULT '0',
  `game_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`account_id`,`game_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ship_type` (
  `ship_type_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ship_name` varchar(32) NOT NULL,
  `speed` int(10) unsigned NOT NULL DEFAULT '0',
  `race_id` int(10) unsigned NOT NULL DEFAULT '1',
  `cost` int(10) unsigned NOT NULL DEFAULT '0',
  `hardpoint` int(10) unsigned NOT NULL DEFAULT '0',
  `lvl_needed` int(10) unsigned NOT NULL DEFAULT '0',
  `buyer_restriction` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ship_type_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `ship_type_support_hardware` (
  `ship_type_id` int(10) unsigned NOT NULL DEFAULT '0',
  `hardware_type_id` int(10) unsigned NOT NULL DEFAULT '0',
  `max_amount` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ship_type_id`,`hardware_type_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `temp` (
  `text` varchar(100) NOT NULL,
  `intid` int(11) NOT NULL AUTO_INCREMENT,
  `amount` int(11) NOT NULL,
  PRIMARY KEY (`text`,`intid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `user_rankings` (
  `rank` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `rank_name` varchar(32) NOT NULL,
  PRIMARY KEY (`rank`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `version` (
  `version_id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `major_version` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `minor_version` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `patch_level` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `went_live` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`version_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `vote_links` (
  `account_id` smallint(6) unsigned NOT NULL DEFAULT '0',
  `link_id` tinyint(3) NOT NULL DEFAULT '0',
  `timeout` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`account_id`,`link_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `voting` (
  `vote_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `question` text NOT NULL,
  `end` int(10) unsigned NOT NULL,
  PRIMARY KEY (`vote_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `voting_options` (
  `vote_id` int(11) NOT NULL,
  `option_id` int(11) NOT NULL AUTO_INCREMENT,
  `text` text NOT NULL,
  PRIMARY KEY (`vote_id`,`option_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `voting_results` (
  `account_id` int(10) unsigned NOT NULL,
  `vote_id` int(10) unsigned NOT NULL,
  `option_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`account_id`,`vote_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `warp` (
  `game_id` int(10) unsigned NOT NULL DEFAULT '0',
  `sector_id_1` int(10) unsigned NOT NULL DEFAULT '0',
  `sector_id_2` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`game_id`,`sector_id_1`,`sector_id_2`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `weapon_type` (
  `weapon_type_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `weapon_name` varchar(32) DEFAULT NULL,
  `race_id` int(10) unsigned DEFAULT NULL,
  `cost` int(10) unsigned DEFAULT NULL,
  `shield_damage` int(10) unsigned DEFAULT NULL,
  `armour_damage` int(10) unsigned DEFAULT NULL,
  `accuracy` int(10) unsigned DEFAULT NULL,
  `power_level` int(10) unsigned DEFAULT NULL,
  `buyer_restriction` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`weapon_type_id`),
  KEY `weapon_name` (`weapon_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `weighted_random` (
  `game_id` int(10) unsigned NOT NULL,
  `account_id` int(10) unsigned NOT NULL,
  `type` enum('WEAPON') NOT NULL,
  `type_id` int(10) unsigned NOT NULL,
  `weighting` int(11) NOT NULL,
  PRIMARY KEY (`game_id`,`account_id`,`type`,`type_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `word_filter` (
  `word_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `word_value` varchar(255) NOT NULL,
  `word_replacement` varchar(255) NOT NULL,
  PRIMARY KEY (`word_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

INSERT INTO `bar_drink` (`drink_id`, `drink_name`) VALUES
(1, 'Spooky Midnight Special'),
(2, 'Jack Daniels'),
(3, 'Rastapus'),
(4, 'Bud-Ice'),
(5, 'Thevian Vodka Vortex'),
(6, 'Small Pot o Ale'),
(7, 'Salvene Swamp Water'),
(8, 'Nyquill'),
(9, 'Martian Martini'),
(10, 'The Wild West'),
(11, 'Azoolian Midnight Special'),
(12, 'taquilla sunrise'),
(13, 'Corona'),
(14, 'MoonSpinner'),
(15, 'PodGiver'),
(16, 'DeadRock');

INSERT INTO `changelog` (`version_id`, `changelog_id`, `change_title`, `change_message`, `affected_db`) VALUES
(1, 1, 'Combat Rewrite', 'Player/Port/Planet/Force combat has been rewritten.<br />The most noticeable change should be that the attacking team shoots first, and if some dies before they can shoot, they cannot shoot.<br />\r\nExperience gained is also shown when killing a player.', ''),
(1, 2, 'Evil goods', 'These can now have different alignment requirements to be visible, currently Slaves < -100, Weapons < -125, Narcotics < -150', ''),
(1, 3, 'Hall of Fame', 'The hall of fame has been rewritten so that it is much easier to add extra stats to track.  This has also resulted in a rewritten interface for viewing stats.', ''),
(1, 4, 'Weapon Reordering', 'Weapons can now be drag and dropped to reorder, this requires javascript to be enabled, however for those with javascript disabled it just defaults to the old-style reordering.', ''),
(1, 5, 'Forces Combat Logs', 'Combat logs are now stored for force combats.', ''),
(1, 6, 'CDs vs Mines', 'There is now an option under preferences to either have CDs kamikaze on mines, killing 1 mine per CD.  Or have CDs just shoot at mines causing 2 damage per CD (10 CDs have to shoot to kill 1 mine)', ''),
(1, 7, 'Change Back Button Detection', 'The back button detection is now more relaxed, so you should get less back button errors, however it will not allow you to access any links that are not available on the latest page you loaded. (For instance pressing back then clicking Current Sector should never result in a back button error as you always have Current Sector available)', ''),
(1, 8, 'Encrypted Passwords', 'Passwords are encrypted in 1.6, which has meant that the password resend form has been changed to a password reset form.', ''),
(1, 9, 'AJAX', 'Pages will now auto-refresh every 1 second when not under protection, and every 2 seconds when under protection (landed on a planet counts).  The actual time may vary depending upon connection and computer speeds.<br />Currently there is a link in the top-right to disable AJAX should anyone feel the need to.', ''),
(1, 10, 'Word Filter', 'Alliance names can no longer contain any filtered words.<br />Alliance descriptions are now passed through the word filter.<br />Feature requests are now passed through the world filter.', ''),
(1, 11, 'Force Names', 'Enemy forces are now anonymous.', ''),
(1, 12, 'Bounties', 'Bounties can now be placed using SMR Credits.', ''),
(1, 13, 'Rescan', 'There is now a rescan button when scanning a sector.', ''),
(1, 14, 'Sending Message Confirmation Message', 'There is now a message that confirms your message has been sent.', ''),
(1, 15, 'User Ranking', 'There is now a new system to calculate user ranking, preliminary numbers are in place but they will still be changed.', ''),
(1, 16, 'View Messages', 'There are now quick links to reply to a message and blacklist the sender of a message.<br />Added "Manage Blacklists" link to the messages menu bar.', ''),
(1, 17, 'Newbies', 'Newbies are now shown with italics on the Current Sector and Alliance Roster pages, like they are on the CPL.<br />\r\nWhen a vet kills a newbie they no longer gain a kill in rankings/HoF, instead they get a "Newbie Kill" in HoF.<br />\r\nNewbies get an NMV on death.', ''),
(1, 18, 'Alliance Limits', 'Separate alliance limits for vets/total.  Currently it is 18/25.<br />This means you can have a 18 vets and 7 newbies, 10 vets and 15 newbies, 18 vets and 0 newbies, but not 25 vets and 0 newbies.', ''),
(1, 19, 'Mines Price', 'Mines have been changed to cost 20,000 each in order to reduce minefields on the current map.', ''),
(1, 20, 'Map Buying', 'Maps can no longer be bought in the first 3 days of the game.', ''),
(1, 21, 'Code Cleanup/Rewrite', 'A lot of the code has been cleaned up/rewritten, it shouldn''t be noticeable to you but it is a lot easier for me to work with and as such should result in less bugs.<br />\r\nAlso a part of this is support for templates - just need some new templates to use now.', ''),
(1, 22, 'SMR Sectors File', 'Can now be downloaded, with an updated format.', ''),
(1, 23, 'Trading', 'Only gain 1 relation per trade after hitting 500 personal relations.', ''),
(1, 24, 'Bans', 'Turns can no longer be gained whilst banned.', ''),
(1, 25, 'Voting', 'Add a voting system to play game screen for admins to ask questions of the players.', ''),
(1, 26, 'Ship Comparison', 'Added a comparison column when viewing a new ship to buy.', ''),
(1, 27, 'Trade Experience', 'The experience gained from accepting bargain is now a more gradual curve as relations increase, rather than a sudden jump from nothing to max.', ''),
(2, 1, 'Course Plot', 'A new, faster course plotter that can handle intragalactic warps for standard plotting and distance index calculation.<br />\r\nA plot to nearest feature to plot to the nearest instance of a given hardware/ship/weapon/location/good.', ''),
(2, 2, 'Turn Cost For Hitting Mines', 'Hitting mines when moving sectors now has a turn cost related to the number of mines being hit, this means that smaller stacks take less turns than before, but big stacks remain the same.', ''),
(2, 3, 'Request A Feature', 'This has been changed to allow voting yes/no to each feature and to have a single favourite that you would most like to see in the game.', ''),
(3, 1, 'Advanced News', 'There is now an advanced news option which allows you to search the news by player, player vs player, alliance, alliance vs alliance.', ''),
(3, 2, 'Custom CSS', 'It is now possible to specify a custom css link so that you more easily can use custom css styling within the game.', ''),
(3, 3, 'Per Galaxy Force Expiry', 'It is now possible for galaxies to have separate expiry times.', ''),
(3, 4, 'Message Formatting', 'BBCode can now be used in messages/message board/MOTD/alliance description.  [img] tags cannot be used.', ''),
(4, 1, 'Centering Galaxy Map', 'There is now a preference to automatically center the galaxy map on your player or on the center of the galaxy.<br />\r\nYou can now click on a sector in the galaxy map to center on that sector.', ''),
(4, 2, 'User Ranking', 'Made the user ranking page a bit clearer.', ''),
(4, 3, 'Alignment For Narcotics', 'Changed the required alignment for Narcotics to -145', ''),
(4, 4, 'Plot To Nearest', 'You can now plot to any bank/bar/fed/hq/ug/hardware shop/ship shop/weapon shop without having to choose a specific one.', ''),
(4, 5, 'Combat Logs', 'These are now shown in pages for when you have loads.', ''),
(4, 6, 'Course Plot', 'Lined up follow course button in course plot with the one in current sector.', ''),
(4, 7, 'Galaxy Map', 'Now defaults to current galaxy with a drop down list to change galaxy.', ''),
(4, 8, 'Feature Request Comments', 'You can now comment on feature requests.', ''),
(4, 9, 'Ranks', 'Adjusted rank boundaries to be easier to get out of newbie, but harder to get to the higher ranks.', ''),
(4, 10, 'Messages', 'These are now paged when you have lots to make it easier to display.', ''),
(4, 11, 'Voting', 'Added a dedicated voting screen to see previous votes and their results.', ''),
(5, 1, 'Port Raids', 'Summary results should now be displayed to each player participating in the raid.', ''),
(5, 2, 'Date Format', 'You can now customise your date format if you want.', ''),
(5, 3, 'Local Map Forces', 'You can now tell the difference between friendly and enemy forces in local map.', ''),
(5, 4, 'Planet Attacks', 'Summary results should now be displayed to each player participating in the attack.', ''),
(5, 5, 'Stats', 'Added force killing stats.', ''),
(5, 6, 'Alliance Member Limits', 'This has been changed to a max of 15 vets and a max 25 with newbies.', ''),
(4, 12, 'Stats', 'Adder a User Score stat.', ''),
(5, 7, 'Feature Requests', 'Feature requests with the last comment over 7 days ago are now hidden by default.', ''),
(5, 8, 'Rejoining Alliance', 'The state players were when first joining an alliance is tracked so that when trying to rejoin they will always be able to unless other players have joined since they left.', ''),
(5, 9, 'Error Messages', 'All error messages that can be are now displayed in the context of an actual page (usually Current Sector) so as to avoid interrupting gameplay as much as possible.', ''),
(5, 10, 'Referral Reward', 'There is now a 1 credit in place whenever someone you have referred gains a user rank.  To find your referral link look under Preferences.', ''),
(5, 11, 'Mines', 'They now correctly detect the number of exits from a sector to use in their accuracy formula.', ''),
(5, 12, 'Galaxy Map', 'Now automatically switches galaxy when you choose a different galaxy in the dropdown.', ''),
(5, 13, 'Per Game Alliance Limits', 'Alliance limits can now be set on game creation allowing for multiple games to be run at once with different alliance limits in each.', ''),
(5, 14, 'Per Game Stat Ignoring', 'Stats can now be ignored from specific games allowing the creation of games whose stats do not affect the main hall of fame.', ''),
(5, 15, 'Right Panel Improvements', 'Level, Alliance and Ship Name have now been turned into links to relevant places.', ''),
(5, 16, 'Newbie Killing', 'Killing a newbie with an attack rating of 5 or more is now counted as killing a vet.', ''),
(5, 17, 'Semi Wars', 'Semi wars games have everyone counted as veteran and also have bounties disabled.', ''),
(5, 18, 'Human Photon Torpedo', 'Accuracy has been upgraded from 44 to 48', ''),
(6, 1, 'Plot Course', 'When you choose an option on the plot to nearest drop-downs it now automatically submits the page.', ''),
(6, 2, 'Local/Galaxy Map', 'You can now tell between friendly and enemy traders/forces on local map, and you can see friendly traders and forces on the galaxy map, regardless of where they are.', ''),
(6, 3, 'Templates', 'There is now a new template called "Freon22" (can be chosen under preferences) along with a lot of improvements to the html of many pages.', ''),
(6, 4, 'Planet Main', 'The planet level is now formatted to display more nicely.', ''),
(6, 5, 'Port Info Cache', 'The cached port info is now shared whenever possible which greatly reduces the space required in the database.', ''),
(6, 6, 'Messages', 'The Manage Blacklist link has now been fixed.', ''),
(6, 7, 'Race Selection', 'On game joining a race is randomly selected to be the initial selection (players can still manually choose a different race as always).', ''),
(6, 8, 'Trader Status', 'The trader planets link has now been fixed.', ''),
(6, 9, 'Game Order', 'Games are now order by their start date so the newest game is shown at the top of the list.', ''),
(6, 10, 'Previous Game Stats/Acounts', 'The old game stats from 1.2 are now available for viewing on the main game page, and old accounts can now be logged into and will automatically be upgraded to 1.6 accounts (if you already have a newer account you can link to the old one under the preferences page)', ''),
(6, 11, 'Newbie Turns Used Stat', 'Fixed the newbie turns used stat being increased incorrectly.', ''),
(6, 12, 'Cloak', 'You can now see through friendly cloaks at all times and ships that you can see which are cloaked will have a [Cloaked] indicator on the Current Sector screen.', ''),
(7, 1, 'Galaxy Name', 'You can now see the name of the galaxy a warp connects to when hovering over the icon.', ''),
(7, 2, 'Mines', 'You now hit the largest mine stack in a sector when entering.<br />\r\nMines formula has been changed to PercentHitting = (100 - (PlayerLevel + Random(1-7) * Random(1-7)) / Connections^0.6 from (100 - (PlayerLevel + Random(1-7) * Random(1-7)) / Connections.<br />\r\nThis means that more mines will hit when entering sector (with more of an increase for sectors with lots of connections than those with less).', ''),
(7, 3, 'Combat', 'All combat is limited to 10 players shooting per fleet (previously this was only in effect for trader combat).', ''),
(7, 4, 'Custom CSS', 'This is now used for the galaxy map as well.', ''),
(7, 5, 'Left Hand Menu', 'This now includes a link to politics.', ''),
(7, 6, 'Exploration Experience', 'You now gain 2 experience for each sector you explore (there is a stat for this too under Movement)', ''),
(7, 7, 'Max Turns', 'This is now set in the universe generator rather than being calculated based upon game speed.', ''),
(7, 8, 'Racial Beacons', 'Each race now uses it''s own protective beacons, watch out, they might not look after you in future if they don''t like the looks of you ;)', ''),
(7, 9, 'Bugfixes', 'Various bugfixes around the place, most of them you may never notice, just feel comforted by the fact there are less to bug you :)', ''),
(7, 10, 'Alskant Trade-Master', 'Trading ability has been increased such that the trading race now has the best trading ship.', ''),
(7, 11, 'Cloaking', 'Cloak now costs 1 turn to activate.', ''),
(7, 12, 'Ship Naming', 'You can no longer name your ship "Please Enter Here", as many people were being very silly and forgetting to choose a name for their ship before painting it on..', ''),
(8, 1, 'Combat Drones Firing', 'Combat drones now fire along with mines when entering a mined sector.', ''),
(8, 2, 'Cloaking', 'It now takes 1 turns to cloak your ship.', ''),
(8, 3, 'Ship Naming', 'You can no longer name your ship "Enter Name Here".', ''),
(8, 4, 'Port Money', 'Ports now get (Level-1)*2,000,000 credits per level upon creation (eg level 9''s start with 16mil credits, level 8 14mil)', ''),
(8, 5, 'Hall of Fame', 'There are now different levels of visibility for some stats.<br />\r\nPublic: Visible to everyone at all times<br />\r\nAlliance: Visible to everyone for games that have ended and visible to alliance members in active games.<br />\r\nPrivate: Only every visible to the current player (Not currently used by any stats)', ''),
(8, 6, 'Max Traders In Combat', 'Traders are now limited to a maximum of 10 for all types of combat.', ''),
(8, 7, '.smr File', 'There is now a FriendlyForces field included in the downloaded .smr file.', ''),
(8, 8, 'Newbie Kills', 'The attack rating for newbie kills is now taken as if they had max CDs at time of death (ie. killing a newbie Mothership will result in a real kill rather than a newbie kill)', ''),
(8, 9, 'BBCode', 'There is now a [player] bbcode, it can be used as [player=3] to add a link to the player with id 3 in the current game. If you want to include their alliance name in the link you can use [player id=3 showalliance=yes].<br />\r\nThere is also an [alliance] bbcode which can be used as [alliance=2] to show a link to the alliance with alliance id 2, it will link to the MotD for members of that alliance and to the roster for everyone else.', ''),
(8, 10, 'Player Name Links', 'Player names are now links in a lot more places, most noticeably in the news, however they are also now clickable for bounties and other places as well.', ''),
(8, 11, 'Joining Messages', 'Alliance leaders now get a message when a player joins their alliance.', ''),
(8, 12, 'Name Changing', 'You can no longer change your player name to the same as it already was.', ''),
(8, 13, 'SMR Credit Transfer', 'When transferring credits to a player you are no longer shown a list of previous names for that player.<br />\r\nAlso only players that have validated their email address are shown in the list of players to whom you can transfer credits.', ''),
(8, 14, 'Mines', 'The formula for number of mines that hit now takes into account the total number of mines in the sector.<br />\r\nShooting at mines clears your safe (green) sector, so make sure that''s a risk you can afford to take when you start shooting. If an alliance member clears your green sector by shooting on mines then the changes to your green sector will be updated by the autorefresh.<br />\r\nFed ships now take 75% damage from mines rather than 50%.', ''),
(8, 15, 'Cargo Jettison', 'You can no longer jettison cargo whilst landed on a planet.', ''),
(8, 16, '3rd Party Login', 'You can now log in or registor via Facebook, Twitter and Google accounts. The first time you do this you will be prompted to login with an existing account or register a new account as need be.', ''),
(8, 17, 'Draft Mode', 'There is now a draft mode where only specific players can create accounts and they are given a "Pick Members" option under Alliance Options, which will allow them to see various info on a player and pick them to be a member of their alliance. Being picked by a draft leader is the only way to become part of an alliance.', ''),
(8, 18, 'Back Button Error? No more!', 'Well kinda, you can now use the back button in a lot more places, for instance on alliance message board and in messages, you cannot however use the back button and then click to move sectors.  If you find yourself getting a back button error when trying to load a page you feel should not have the issue then please report this to an admin.', ''),
(8, 19, 'Multi Tools', 'These have seen quite a lot of improvement both in terms of features and usability.', ''),
(8, 20, 'MPOGD Vote Link', 'Changed to a slightly different URL which is easier to vote from.', ''),
(8, 21, 'Universe Generator', 'There are now options for number of start turns and the date for turns to start generating, this was added with draft mode in mind however will work for any game type.', ''),
(8, 22, 'Registration', 'Entering a CAPTCHA is now required for registration.', ''),
(8, 23, 'Newbie Turns', 'Newbie turns lost when hitting mines now matches the number of turns lost.<br />\r\nThis change is viable since number of turns taken varies based upon number of mines in sector, whereas it used to be a flat 3 turns which meant even single mines could burn loads of newbie turns.  Now it means that it is possible to limit the potential of newbie planet stocking by putting down 50mine stacks on the path to the planet.', ''),
(8, 24, 'Alliance Roles', 'The "Leader" and "New Member" alliance roles can no longer be deleted, bad things happened to those who deleted them.', ''),
(8, 25, 'Character Set', 'The game is now served using the UTF-8 character set, this removes some issues with the use of "smart quotes" and various non-standard characters.', ''),
(8, 26, 'Race Descriptions', 'Fix race descriptions no longer appearing on join game screen.', ''),
(8, 27, 'Planet Building', 'Experience is now gained from building planets.<br />\r\nGenerator: 30<br />\r\nHangar: 60<br />\r\nTurret: 180', ''),
(8, 28, 'Freon22 Layout', 'Added styling for unordered lists to make things such as the changelog display more nicely.', ''),
(8, 29, 'Accuracy', 'Player level now has more effect on accuracy, this increase is greater the higher level you are (so the increase in accuracy compared to previously is greater for a level 40 than it would be for a level 20)<br />\r\nThe effect on accuracy still relates to the base weapon accuracy so a level 40 player will gain more accuracy bonus for a weapon with 80% accuracy than a weapon with 40% accuracy (however the weapon with 80% accuracy may well go above 100% and have wasted potential).', ''),
(9, 1, 'Galactic Post', 'Anyone can now write for the galactic post.<br />\r\nAlso you can now use BBCode in the galactic post and preview submissions.', ''),
(9, 2, 'Alliance Messageboard', 'Player names are now links.<br />\r\nYou can now delete individual posts.', ''),
(9, 3, 'Port Upgrading/Downgrading', 'Fixed a bug where the cached versions of ports were not updated until leaving sector if you were in the sector whilst the port upgraded or downgraded.', ''),
(10, 1, 'Voting Turns', 'There is now a countdown for when your voting turns will next be ready.', ''),
(10, 2, 'Galaxy/Local Map', 'Reduced the memory usage for loading galaxy and local maps which should stop the issues that were sometimes occurring with large galaxy maps.', ''),
(11, 1, 'Plot Course', 'Trim input for sector ids to stop error messages if the sector id contains spaces at the start or the end.', ''),
(11, 2, 'Forces', 'Trim input to stop error messages if there are spaces at the start or the end.<br />\r\nIf trying to drop more than the max allowed forces onto a stack it will just fill the stack rather than give an error message.', ''),
(11, 3, 'Bugs', 'Squished a nasty critter that spawned recently and allowed people to sneak into places they shouldn''t.', ''),
(12, 1, 'Messages', 'There is now a per-game preference to turn off messages when an alliance member adds or removes forces from your stacks.<br />\r\nWhen viewing messages there are now previous/next messages at the bottom as well as the top of the page.<br />\r\nExpiry times for messages have been raised.<br />', ''),
(12, 2, 'Scout/News Tickers', 'These are now visible on the planet main screen.', ''),
(12, 3, 'Planet Kicking', 'Players no longer have their last CPL action updated when kicked from a planet (this was previously to avoid them being hidden in sector)', ''),
(12, 4, 'Hidden Inactives', 'Inactives are only hidden if they have newbie turns now, this means that people in the open and unprotected will be killable regardless of how long they have been inactive.', ''),
(12, 5, 'Alliance Roster', 'This is now sortable by player name, race name and experience.', ''),
(12, 6, 'Bar', 'Fixed some display bugs in the bar.', ''),
(12, 7, 'Hall of Fame', 'Fixed a bug with donation amount and user score mistakenly showing as 0 in some circumstances.', ''),
(13, 1, 'Freon22 Layout', 'Make it so that the main content area stays in the same place on larger screens where previously it could move up and down depending upon how much content was on the page.', ''),
(13, 2, 'Message Notification Emails', 'You can now purchase notification emails for messages that you receive under the Donate link on the left hand side.', ''),
(13, 3, 'Alliance Switching', 'You now have to wait 24 hours to join a new alliance after leaving your previous alliance (except for those people leaving the Newbie Help Aliiance).', ''),
(13, 4, 'Alliance Vet Limit', 'Fixed to work as intended when vets leave/rejoin an alliance and when newbies become vets after joining the alliance.', ''),
(13, 5, 'Name Changes', 'Name changes after the first free change are now available at a cost of 2 SMR Credits each.', ''),
(13, 6, 'Planets', 'Added default transfer values for defences, the default value is the smallest value between your ship''s shields/CDs and the remaining shields/CDs the planet requires.<br />\r\nAdded links for player names on the trader/alliance planet pages.', ''),
(13, 7, 'Combat Log View', 'Player names for attacker/defender are now links.', ''),
(14, 1, 'Unicode Characters', 'All areas of the game should now support unicode characters, allowing you to use characters like �dcc�.', ''),
(14, 2, 'Dying', 'When a player dies they will no longer have their last CPL action updated, meaning that killing an offline player will not cause them to appear on the CPL.', ''),
(15, 1, 'Alliance Joining', 'Fixed a bug where newbies that became vets whilst in an alliance were being counted as vets for the purposes of alliance limits (they now continue to count as newbies even after upgrading).', ''),
(15, 2, 'Lottery', 'Fixed a bug with claiming lottery winnings.', ''),
(15, 3, 'Hall of Fame', 'Fixed a bug with tracking the exp gained from planet buildings built.', ''),
(15, 4, 'Plotted Course/Jumping', 'If you have a plotted course and jump to a sector partway along the path it will now update the plotted course rather than clearing it.', ''),
(15, 5, 'Alliance Force List', 'Player names are now links.', ''),
(15, 6, 'Performance', 'Changes have been made to various parts of the game so that all pages should load quicker and result in less/no white screens.', ''),
(16, 1, 'AJAX', 'Fixed AJAX for pages containing a &amp;nbsp; entity.', ''),
(16, 2, 'Planet Main', 'Updated the planet players list to match the style of the Current Sector players list.', ''),
(16, 3, 'Alliance Roster/Joining', 'Added a message to the alliance roster page in place of the password box stating the reason why you are not able to join the alliance.', ''),
(17, 1, 'Voting Turns', 'Voting now gives exactly 20 minutes worth of turn per site (it works by changing the last turn update time), before it was 20 minutes rounded to the nearest whole number.<br />\r\nOMGN voting has been removed due to them no longer having voting.<br />\r\nTWG has been removed due to a serious lack of voting.<br />\r\nIf you want more sites to vote at and gain turns then please suggest good sites on the webboard.', ''),
(17, 2, 'Planet Main', 'You no longer see yourself in the list of players on the planet (carried through from testing).<br />\r\nLaunch button is now above the player list.', ''),
(17, 3, 'Cloaking', 'You can no longer cloak if you have 0 turns.', ''),
(17, 4, 'Alliance Roles', 'Fixed changing the ability of a role to exempt bank withdrawals.', ''),
(17, 5, 'AJAX', 'Current ship CDs are now included in the AJAX.', ''),
(17, 6, 'Galaxy Map', 'There is now a proper error message when entering an invalid sector id.', ''),
(18, 1, 'Planet Goods Icons', 'For trader/alliance player list and planet construction page the icons for goods are used instead of the name.', ''),
(18, 2, 'Sector Forces', 'The ordering of sector forces is now consistently by expiry time, previously it would be order by number of mines on initial entry and on expiry time on any subsequent page loads.', ''),
(18, 3, 'Force Change Messages', 'Fixed a bug where it was possible for the number of forces added to be shown as higher than the actual number added.', ''),
(18, 4, 'Sector Scan', 'The page header for scanning now includes the sector id and galaxy name for the sector being scanned.', ''),
(18, 5, 'Coloured Defences', 'Defences are now coloured on a scale from red to green depending on how close to max/none that they are.', ''),
(18, 6, 'Planet Construction Experience', 'Fixed a bug where this was not always given correctly.', ''),
(18, 7, 'Forces Lists', 'Fixed a bug where expired forces could show up in the trader/alliance force lists (display bug only - the forces otherwise expired as intended)', ''),
(18, 8, 'Universe Generator', 'Fixed a bug with adding a small number of ports distributed between a large number of races.', ''),
(18, 9, 'Logging In', 'Fixed a couple of rare bugs that might occur at random when logging in.', ''),
(18, 10, 'Planet Building', 'There are now extra checks when starting a building on a planet, this will stop hard errors occurring in the very rare circumstances that could trigger them.', ''),
(18, 11, 'Smuggling Fines', 'The message saying how much you were fined will now display correctly in the case that you were fined more money than you had on hand.', ''),
(18, 12, 'Configure Hardware', 'Cloak Active/Inactive message will now link to the Configure Hardware page, as will the illusion ship details.<br />\r\nAdded the ability to use your jump drive from the Configure Hardware page.', ''),
(18, 13, 'Previous Game Info', 'Clicking the game name for previous games now works.', ''),
(19, 1, 'Current Sector Illusion', 'You can now see through allied illusion generators in current sector.', ''),
(19, 2, 'Bugfixes', 'Couple of bugfixes around the place.', ''),
(20, 1, 'Coloured CDs', 'The number of CDs you have is now only coloured if you can hold some.', ''),
(20, 2, 'Ranking', 'Rankings now show newbie players in italics.', ''),
(20, 3, 'Display Errors', 'A lot of possible display errors in various pages have been fixed (when using " for certain values - mostly noticeable within GP).', ''),
(20, 4, 'Galactic Post', 'Can now use the preview function when editing an article as well as when submitting.', ''),
(20, 5, 'HoF Names', 'HoF names can no longer consist purely of whitespace.', ''),
(20, 6, 'Universe Generator', 'Manually setting a sector to have a lower level port should now work properly.', ''),
(21, 1, 'Alliance Joining', 'New players now use the initial status of players in the alliance when joining, before the initial status was only used for players who were rejoining.<br />\r\nThe wait to switch alliance now also applies when a player is kicked.<br />\r\nThe wait to switch alliance now applies for creating alliances.<br />\r\nThe wait to switch alliance now applies from the moment the second to last player from an alliance leaves (people can instantly leave a solo alliance and switch without a wait, as long as no one else was recently in the alliance with them).', ''),
(21, 2, 'Jump Drive', 'Fixed an issue with not misjumping properly when jumping within a single galaxy.', ''),
(21, 3, 'Admin Tools', 'Various improvements were made to the admin tools, those who need to know specifics already do :P', ''),
(22, 1, 'Plot Course', 'Plot course and plot to nearest will now use the same path whichever direction you plot and whichever method you use.<br />\r\nPlot to nearest now starts with the technology option automatically expanded.<br />\r\nThere is now an "Any Safe Fed" option under plot to nearest locations, this will find the nearest fed at which you will be protected.', ''),
(22, 2, 'Preferences', 'New field ''irc nick'' which links your SMR account to your irc nick for the bot to recognize you<br />\r\nNew field for cell phone number. With caretaker you will be able to send out text messages to other players. Your phone number won''t be visible to others.', ''),
(22, 3, 'Federal Protection', 'You can no longer park in the fed of races for which your total relations are less than 0.<br />\r\nWhen races declare war upon each other any member of those races that are safely parked in the other race''s federal space are given 3 days of safety in which to leave and are sent a message detailing this.', ''),
(22, 4, 'Universe Generator', 'Automatically added federal space now defaults to a radius of 1 (ie HQ + 1 sector in each direction)', ''),
(22, 5, 'Planet Building', 'Experience increased by 1.5x to 45 for a generator, 90 for a hangar and 270 for a turret.', ''),
(22, 6, 'DCS', 'The DCS now decreases damage taken from ship drones to 66% rather than the previous 75%.  Damage remains at 75% for sector/planet/port CDs.', ''),
(22, 7, 'Politics', 'Peace/war votes now run for a fixed 3 days regardless of game speed.', ''),
(22, 8, 'Trade Ship Changes', 'Planetary Trader has been changed to 5,283,335 credits and 300 holds.<br />\r\nAdvanced Courier Vessel has been changed to 4,560,740 credits, 235 holds and 225 shields/armour.<br />\r\nInter-Stellar Trader has been changed to 5,314,159 credits.<br />\r\nFreighter has been changed to 4,791,393 credits, 10 speed, 250 holds, 325 armour and 0 scouts/mines.<br />\r\nPlanetary Freighter has been changed to 6,215,028 credits.<br />\r\nPlanetary Super Freighter has been changed to 7,035,792 credits and 425 holds.', ''),
(22, 9, 'Killing', 'Killing a newbie player no longer counts as an alliance kill (it does still count in alliance vs alliance however).<br />\r\nKilling an NPC gives separate HoF stats.', ''),
(22, 10, 'NPCs', 'They trade, yay!  I''m sure some meanies will try to kill them :(', ''),
(22, 11, 'Typos', 'Some minor typos fixed around various game pages', ''),
(22, 12, 'SQL Injection', 'Fixed a possible (very unlikely) SQL injection', ''),
(23, 1, 'Ship Name', 'It is now possible to name your ship on the "Donate" page without needing to visit a bar.<br />\r\nThere are now slightly stricter HTML rules to avoid problems - you shouldn''t hit these though :)', ''),
(23, 2, 'Vote Turns', 'You no longer lose the star for voting too early and are instead given a countdown.', ''),
(23, 3, 'Buying Maps', 'There is now a countdown for how long until you can buy galaxy maps.', ''),
(23, 4, 'Game Stats', 'Game speed is no longer rounded.', ''),
(23, 5, 'Alliance IRC Channel', 'It is now possible to add an official alliance IRC channel under the alliance stats (where you set MotD).<br />\r\nIf you set an official channel then members of your alliance can use all the available Caretaker alliance functions in your channel.<br />\r\nAlso all players joining using the in-game chat client will automatically join your alliance channel as well as #SMR', ''),
(23, 6, 'Preferences', 'You can now set an IRC nick under preferences, this is used to verify who you are by Caretaker.', ''),
(23, 7, 'Log Console', 'The log console has been improved :)', ''),
(23, 8, 'Play Game Page', 'Previous games have been moved to the bottom of the list so they do not push everything else off of the screen.', ''),
(23, 9, 'IRC bot', 'Several bugfixes to caretaker to be less annoying', ''),
(24, 1, 'Port Upgrading', 'Fixed an edge case where a port would sometimes not gain a good on upgrading if that good was the last of it''s tier to be added.', ''),
(24, 2, 'Ship Images', 'The ship image size limit is now enforced for all players and has been increased from 20k to 30k.', ''),
(25, 1, 'Combat Logs/Attack Screen', 'Experience lost is now shown for people who die.', ''),
(25, 2, 'Weapon Accuracy', 'Weapon accuracy should now feel more consistent.', ''),
(25, 3, 'Starting Turns', 'Starting turns are now calculated as a number of hours worth of turns.', ''),
(25, 4, 'Hall of Fame', 'Improved performance of the HoF.', ''),
(25, 5, 'Experience Ranking', 'Upper bound is now center aligned correctly.', ''),
(25, 6, 'Alliance vs Alliance Rankings', 'Alliance list is now displayed properly.', ''),
(25, 7, 'Planets', 'Accuracy is now rounded to 2dp.', ''),
(25, 8, 'Bounties', 'Removed the ability to use bounties to find out who a player is.<br />\r\nRemoved the ability to place bounties on yourself.', ''),
(25, 9, 'Mines', 'Mines have had their cost reduced to 15,000 each.', ''),
(25, 10, 'Game Ending', 'Players are now kicked out of the game when the game ends.', ''),
(25, 11, 'Attack Rating', 'Combat drones now follow the same formula as weapons for attack rating, so total damage / 40, simplified as CDs / 20.', ''),
(25, 12, 'Trade Ship Changes', '* Ambassador increased to 4,292,187 credits and 235 holds.<br />\r\n* Blockade Runner increased to 200 holds<br />\r\n* Deal-Maker increased to 200 holds.<br />\r\n* Deep-Spacer increased to 300 holds.<br />\r\n* Drudge increased to 260 holds and has been given an illusion generator.<br />\r\n* Expediter has had a brand new turbo fitted into its holds and is now 12 speed but down to just 160 available holds remaining.<br />\r\n* Favoured Offspring increased to 250 holds.<br />\r\n* Freighter reduced to 300 armour.<br />\r\n* Leviathan increased to 255 holds.<br />\r\n* Merchant Vessel now has a scanner and the Newbie Merchant Vessel now has 5 scouts, making the two ships equivalent.<br />\r\n* Planetary Trader increased to 355 shields and reduced to 10 combat drones, for the same total defence.<br />\r\n* Small-Timer increased to 100 holds.<br />\r\nStellar Freighter increased to to 7,508,455 credits, 8 speed and 225 holds.<br />\r\n* Thief reduced to 9,802,157 credits and increased to 160 holds.<br />\r\n* Trade-Master reduced to 7,095,764 credits and is now equipped with a brand new drone scrambling system which the Alskant were finally able to acquire from the Nijarin.<br />\r\n&nbsp; &nbsp;The old illusion generator has now been given to the Trip-Maker.<br />\r\n* Trip-Maker increased to 4,354,955 credits and 225 holds.<br />\r\n* Vengeance increased to 10 speed and 200 holds.[/list]', ''),
(25, 13, 'Jump Drive', 'Can now jump to anywhere in the universe but will cost more turns the further you jump, with a minimum of 10 turns, as well as the possibility of much higher misjumps.<br />\r\nCan now misjump across warps.', ''),
(26, 1, 'Error messages', 'Removed the extra ! that was showing up for a lot of error messages.', ''),
(26, 2, 'Alliance Description/MotD/Image', 'These are now all checked to make sure they do not contain html.', ''),
(26, 3, 'Experience Loss', 'Experience lost for dying to other players increased by a flat 2% at all levels.<br />\r\nExperience lost to planets is now (27 - PlanetLevel/10)% down from 33%. This means you lose 20% when dying to a level 70.<br />\r\nExperience lost to ports is now (31 - PortLevel)% down from 33%. This means you lose 30% when dying to a level 1 but only 21% to a level 9.<br />\r\nExperience lost to forces has been reduced to 30% from 33%.', ''),
(27, 1, 'Jump Drive', 'Fixed a bug with jump drive miscalculating misjumps.', ''),
(28, 1, 'Politics', 'You can no longer start a peace vote if the other race if voting to go to war with you.', ''),
(28, 2, 'Trader Search', 'View news link is now available for all results.', ''),
(28, 3, 'Combat Logs', 'Experience lost to ports/planets/forces should now display properly.', ''),
(28, 4, 'Planet Message Count', 'This will no longer display incorrectly if you had unread planet messages whilst logging in.', ''),
(28, 5, 'Vote Links', 'The delay between voting is now 23h30 rather than 24h.', ''),
(28, 6, 'Force Examine', 'You will now see the real details of allied ships when examining forces, rather than just to the nearest 100.', ''),
(28, 7, 'Race Name Links', 'Various places where race names used to be static are now instead links to the council page (or trader relations page if that is more relevant in the context)', ''),
(28, 8, 'Race BBCode', 'You can now use [race=Salvene] in messages/announcements/alliance message board to point to the given race, the name must be in full and spelt correctly.  Alternately you can use the race id instead of name.', ''),
(28, 9, 'Mass Messages', 'Alliance/council/global messages will no longer send a copy to the sender.', ''),
(28, 10, 'Send Council/Global Message Links', 'The send council message link will now show after the global message link when you are on the council, this means that the send global message link will always appear in the same place regardless of whether or not you are on the council.', ''),
(28, 11, 'BBCode - servertimetouser', 'You can now use the [servertimetouser=21 September 2012 10pm] style bbcode to specify a time in server time which will be translated into the reader''s local time (as specified by offset under preferences).<br />\r\nThe date formats accepted are those of <a href="http://php.net/manual/en/function.strtotime.php" target="_blank">http://php.net/manual/en/function.strtotime.php</a>.', ''),
(28, 12, 'Alskant Personal Relations', 'Those Alskants have been busily bribing everyone and now start with +250 personal relations with everyone.', ''),
(28, 13, 'NPCs', 'NPCs will still start trading again after dying, but will stop once running low on newbie turns to stop people camping them so easily.', ''),
(28, 14, 'Custom CSS Link', 'This is now properly used on the galaxy map page.', ''),
(28, 15, 'Jump Drive', 'Fixed a bug where you could not jump with any enemy forces IS, you are now only stopped from jumping if there are enemy mines IS.<br />\r\nAlso reduced the maximum misjump across all levels, this should particularly help keep misjumps manageable at lower levels.  More info available at the <a href="http://wiki.smrealms.de/index.php?title=Various_Formulae#Jumping" target="_blank">Wiki</a>.', ''),
(28, 16, 'Code Cleanup', 'There has been a huge cleanup of almost every section of the code, this should not be noticeable in-game for the most part but it does make maintaining the code a lot easier.  In the short term it may introduce some bugs, in the long term it should be a lot less :)', ''),
(28, 17, 'Trader/Alliance Planet Lists', 'The sector id is now a link to plot a course to that sector.', ''),
(29, 1, 'Ship Changes', '* Trip-Maker changed to 230 holds<br />\r\n* Advanced Courier Vessel changed to 250 shields/armour<br />\r\n* Goliath changed to 700 armour, 10 scouts, 5 mines and 5 hardpoints<br />\r\n* Retribution changed to 215 shields/armour, 3,235,800 credits, 5 hardpoints and 10 speed<br />\r\n* Predator changed to 415 shields, 375 armour, 15 scouts, 5 hardpoints and 10 speed<br />\r\n* Bounty Hunter changed to 250 shields/armour, 2,568,634 credits, 5 hardpoints and 13 speed<br />\r\n* Rogue changed to 6,574,860 credits and 5 hardpoints<br />\r\n* Border Cruiser changed to 5 hardpoints<br />\r\n* Advanced Carrier changed to 700 shields<br />\r\n* Destroyer changed to 12,719,505 credits<br />\r\n* Fury changed to 13,724,001 credits<br />\r\n* Dark Mirage changed to 11,088,764 credits<br />\r\n* Federal Ultimatum changed to 38,675,738 credits', ''),
(30, 1, 'Bugfixes', 'Fixed a bunch of bugs that appeared due to the code cleanup.', ''),
(30, 2, 'Peace/War Votes', 'Peace votes can no longer decrease relations and war votes can no longer increase relations.', ''),
(30, 3, '1.2 Alliance Stats', 'Viewing alliance details page for the old games on play game page now works.', ''),
(30, 4, 'Mass Messages', 'A copy is now sent to yourself however it does not add to the unread message count.', ''),
(30, 5, 'Number formatting', 'Changed numbers in various places to be formatted with comma separated thousands.', ''),
(30, 6, 'Force Examine', 'Always show current player when examining forces even if you cannot fire.', ''),
(30, 7, 'Jump Drive', 'Will no longer cause an error if you cannot misjump by the number of sectors it wants, instead it drops the misjump to the furthest misjump you can manage (this happens where nowhere in the same gal is far enough away, and the warp is too far of a misjump).<br />\r\nNow has smaller misjump at the cost of using slightly more turns for the initial jump, average turn usage should remain the same.', ''),
(30, 8, 'Chess', 'Messages are now sent when turn changes and when the game ends.', ''),
(31, 1, 'Chess', 'There are now stats for chess.\r\nMove list now displays correctly coloured pieces.', ''),
(31, 2, 'Combat', 'Delay increased to 0.65s to reduce effect of attack scripts.<br />\r\nAttack button is now stationary so that normal players are at less of a disadvantage in comparison.<br />\r\nMade examine screen info in italics for players who are newbie status (as in not veterans).', ''),
(32, 1, 'Session Timeout', 'Fixed a bug where people were not being automatically logged out after a period of inactivity.', ''),
(32, 2, 'Javascript Changes', 'Switched to using JQuery for AJAX stuff, this reduces the amount of custom code for AJAX queries quite significantly.<br />\r\nAdd effects when hiding/unhiding weapons.. because I could! :P', ''),
(32, 3, 'Feature Requests', 'The view previous implemented/old requests now behave a little more sensibly.', ''),
(32, 4, 'Attack Spamming', 'Fixed a bug that was brought to light with the changes to delay handling that would allow you to spam attack to get multiple attacks very quickly using a single SN.', ''),
(33, 1, 'Chess', 'Added castling and en passant.<br />\r\nNo longer shows move options that would leave you in check when moving king or pawns.<br />\r\nImproved the movement interface.', ''),
(33, 2, 'Hotkeys', 'Added hotkeys for navigation and scanning, as well as switching between CS/LM/Plot Course.  These are all fully customisable under preferences (and the defaults are listed there as well).<br />\r\nAs an added bonus to the hotkeys they give the ability to scan your current sector as well as surrounding sectors.', ''),
(33, 3, 'Planet List', 'Galaxy name is now listed next to sector id and links to the appropriate galaxy on the galaxy map.', ''),
(33, 4, 'Attack Rating', 'Attack rating on the right is now coloured red or green based upon whether it is a safe attack rating in fed.', ''),
(34, 1, 'Trader Search Result', 'Now shows whether a player is a newbie or not.', ''),
(34, 2, 'Request A Feature', 'Features can now be listed as rejected so that they stop cluttering up the list when they are infeasible or very unpopular.', ''),
(34, 3, 'Chess', 'Fixed piece taken stat for individual pieces.', ''),
(34, 4, 'Racial Council', 'Removed NPCs from racial councils.', ''),
(35, 1, 'Galaxy Map', 'Clicking a warp on the galaxy map will load the corresponding galaxy and center on the other side of the warp.', ''),
(35, 2, 'Planet List', 'Added the planet level to planet list.', ''),
(35, 3, 'Alliance Bank Access', 'Added the ability to set a maximum negative balance when using the "Positive Balance" option, this means you can set a leeway of how far overdrawn they can go before they have to pay back some.<br />\r\nFor instance setting a limit of 10,000,000 will allow them to go to an overall balance of -10,000,000 before they are forced to deposit to withdraw more.', ''),
(36, 1, 'Request a Feature', 'Favourite votes now also count as yes votes.', ''),
(36, 2, 'Registering', 'The captcha now has a white background to make reading entered text easier.', ''),
(36, 3, 'Chess', 'Fixed various bugs and reversed the board for the black player.', ''),
(36, 4, 'Combat Results', 'Fixed a bug with showing experience lost to forces/planets/ports.', ''),
(36, 5, 'Hotkeys', 'Fixed a bug where hotkeys would not always work (occurred for Chrome on Mac OS X for sure).', ''),
(36, 6, 'Cloak', 'Cloak is now based on experience rather than level so you can see through the cloak if you have the same or greater experience.<br/>\r\nDropping mines now decloaks.', ''),
(36, 7, 'Forces', 'Forces now have their owners visible.<br/>\r\nShooting on forces is now done individually rather than as a team.<br/>\r\nMines now only do 50% damage to federal ships.', ''),
(36, 8, 'Planets', 'You can no longer land on a planet whilst under newbie protection.', ''),
(36, 9, 'Alliance Joining', 'There is no longer a delay between leaving and joining an alliance.', ''),
(36, 10, 'Ship Changes', 'Loads of them, check out the differences <a href="https://docs.google.com/spreadsheet/ccc?key=0AnI63M8rRpOtdE1sMVdQLVNsQlRrQXFsN01hWlRfM2c&hl=en_GB#gid=3">here</a> and view the entire ship list <a href="http://smrealms.de/ship_list.php">here</a>.', ''),
(37, 1, 'Hotkeys', 'Added hotkeys for CPL, enter port and attack trader, check preferences for the key bindings and to set alternatives.<br/>\r\nAlso made it so that only one hotkey press is recognised per page, this should cause less back button/multiple actions errors from holding down hotkeys or pressing multiple.', ''),
(37, 2, 'Chess', 'You are now able to take your move after autorefresh occurs, no more needing to refresh the page.<br />\r\nCheck is now detected for purposes of the move list when you cause check by moving a piece out of the way, rather than only when the piece you move puts the other player into check.', ''),
(38, 1, 'Chess', 'Added a chess BBCode so you can now do [chess=x] where x is the game id for the game you want to link to (game ids can be found in casino messages in the brackets).  You can use this bbcode to view games for other people and also to view ended games.<br />\r\nChess messages now include a link to the game, which remains active after the game has ended to allow viewing of old games.<br />\r\nPawn promotion is provisionally implemented, currently you always promote to a queen, in future bishops/rooks/knights will also be supported (yes, there are valid situations where each of those is a better choice than a queen).', ''),
(39, 1, 'Previous Games', 'These are now defaulted to hidden behind a show/hide button.', ''),
(39, 2, 'Messages', 'Fixed a bug where new message notifications were not appearing if you were reading messages with AJAX on (most noticeable with scout pings).', ''),
(39, 3, 'Port Refresh', 'Ports now refresh at 100 * game speed per hour rather than 250 * game speed per hour.<br />\r\nTier 1/2 goods now restock tier 2/3 goods at a ratio of 1:1 compared to the 4:1 ratio previously.', ''),
(39, 4, 'Ship Refund', 'You now get a 75% refund when trading in your ship, up from 50%.', ''),
(39, 5, 'Mines', 'Only as many mines will explode as are needed to cause the damage, rather than the 50 that previously could have.', ''),
(39, 6, 'Ship Changes', 'Lowered the prices of the Federal Warrant, Federal Ultimatum, Assassin and Death Cruiser.', ''),
(40, 1, 'Combat Drones', 'Fix the message for number launched.', ''),
(40, 2, 'Chess', 'Fixed an issue if a rook is taken before ever having moved, also fixed some related issues which are unlikely to occur in many games.', ''),
(40, 3, 'Kicking Players', 'No longer show ourselves on the list of players to kick.', ''),
(40, 4, 'Port Refresh', 'Goods now restock at the following rates:<br />\r\nTier 1: 150 * game speed per hour.<br />\r\nTier 2: 110 * game speed per hour.<br />\r\nTier 3: 70 * game speed per hour.<br />\r\nThe restock ratio is now 10:9, for every 10 goods of a lower tier you trade then 9 of the the higher tier will be restocked.', ''),
(40, 5, 'Forces', 'Fixed an empty message when trying to add extra forces when a stack is already at max.', ''),
(40, 6, 'Starting Newbie Turns', 'Vets now start with 250 newbie turns.<br />\r\nNewbies still start with 500 newbie turns.', ''),
(41, 1, 'Ship Logos', 'You can now have different logos in two concurrently running games.', '');

INSERT INTO `closing_reason` (`reason_id`, `reason`) VALUES
(2, 'Your account has been tagged as a multi.  Please contact an admin (multi@smrealms.de) for details.  Allow time for response.'),
(7, 'Inappropriate In-Game Language/Behaviour');

INSERT INTO `galaxy` (`galaxy_id`, `galaxy_name`, `galaxy_size`) VALUES
(1, 'Alskant', 15),
(2, 'Creonti', 15),
(3, 'Human', 15),
(4, 'Ik''Thorne', 15),
(5, 'Salvene', 15),
(6, 'Thevian', 15),
(7, 'WQ Human', 15),
(8, 'Nijarin', 15),
(9, 'Omar', 15),
(10, 'Salzik', 15),
(11, 'Manton', 15),
(12, 'Livstar', 15),
(13, 'Teryllia', 15),
(14, 'Doriath', 15),
(15, 'Anconus', 15),
(16, 'Valheru', 15),
(17, 'Sardine', 15),
(18, 'Clacher', 15),
(19, 'Tangeria', 15),
(20, 'Panumbra', 15),
(21, 'Schattenreich', 15),
(22, 'Dinrepkalap', 15),
(23, 'Pseudphilus', 15),
(24, 'Besidkibilo', 15),
(25, 'Besidterop', 15),
(26, 'Quinnrenite', 15),
(27, 'Gloebwyn', 15),
(28, 'Groagan', 15),
(29, 'Lu', 15),
(30, 'Theraseth', 15),
(31, 'Ybyld', 15),
(32, 'Braresean', 0),
(33, 'Zili', 15),
(34, 'Ybirejan', 15),
(35, 'Qirekin', 15),
(36, 'Yedric', 15),
(37, 'Ybelacan', 15),
(38, 'Zelijar', 15),
(39, 'Ziavudd', 15),
(40, 'Qohaw', 15),
(41, 'Mirardojan', 15),
(42, 'Lothalilin', 15),
(43, 'Preiw', 15),
(44, 'Clacher', 15),
(45, 'Dinrepyeter', 15),
(46, 'Andromeda', 15),
(47, 'Apus', 15),
(48, 'Aquarius', 15),
(49, 'Aquila', 15),
(50, 'Ara', 15),
(51, 'Aries', 15),
(52, 'Auriga', 15),
(53, 'Bootes', 15),
(54, 'Cancer', 15),
(55, 'Canis Major', 15),
(56, 'Canis Minor', 15),
(57, 'Capricornus', 15),
(58, 'Cassiopeia', 15),
(59, 'Centarus', 15),
(60, 'Cetus', 15),
(61, 'Corvus', 15),
(62, 'Crux', 15),
(63, 'Draco', 15),
(64, 'Eridanus', 15),
(65, 'Gemini', 15),
(66, 'Grus', 15),
(67, 'Hydra', 15),
(68, 'Lacerta', 15),
(69, 'Leo', 15),
(70, 'Lepus', 15),
(71, 'Lupus', 15),
(72, 'Monoceros', 15),
(73, 'Ophiuchus', 15),
(74, 'Orion', 15),
(75, 'Pegasus', 15),
(76, 'Phoenix', 15),
(77, 'Pyxis', 15),
(78, 'Sagitta', 15),
(79, 'Scorpius', 15),
(80, 'Scutum', 15),
(81, 'Taurus', 15),
(82, 'Ursa Major', 15),
(83, 'Virgo', 15),
(84, 'Belligerent Old Barnacle', 15),
(85, 'Billions Over Budget', 15),
(86, 'Baffled Old Barracuda', 15),
(87, 'Bent Over Backwards', 15),
(88, 'Bounced Outta Bounds', 15),
(89, 'Battery Operated Boyfriend', 15),
(90, 'Bombs Over Baghdad', 15),
(91, 'Battle Of Bulge', 15),
(92, 'Kthxbai', 15),
(93, 'Bellagio', 15),
(94, 'Monte Carlo', 15),
(95, 'Mirage', 15),
(96, 'Mandalay Bay', 15),
(97, 'MGM Grand', 15),
(98, 'Tropicana', 15),
(99, 'Frost', 15),
(100, 'Caesars Palace', 15),
(101, 'David', 15),
(102, 'Antilles', 15),
(103, 'Twilight', 15),
(104, 'Riddickor', 15),
(105, 'Froschteich', 15),
(106, 'Legacy', 15),
(107, 'Allizom', 15);

INSERT INTO `good` (`good_id`, `good_name`, `base_price`, `max_amount`, `good_class`, `align_restriction`) VALUES
(1, 'Wood', 19, 6000, 1, 0),
(2, 'Food', 25, 6000, 1, 0),
(3, 'Ore', 42, 6000, 1, 0),
(4, 'Precious Metals', 62, 6000, 1, 0),
(5, 'Slaves', 89, 6000, 1, -100),
(6, 'Textiles', 112, 5000, 2, 0),
(7, 'Machinery', 126, 5000, 2, 0),
(8, 'Circuitry', 141, 5000, 2, 0),
(9, 'Weapons', 168, 5000, 2, -115),
(10, 'Computer', 196, 4000, 3, 0),
(11, 'Luxury Items', 231, 4000, 3, 0),
(12, 'Narcotics', 259, 4000, 3, -125);

INSERT INTO `hardware_type` (`hardware_type_id`, `hardware_name`, `cost`) VALUES
(1, 'Shields', 350),
(2, 'Armor', 250),
(3, 'Cargo Holds', 350),
(4, 'Combat Drones', 10000),
(5, 'Scout Drones', 5000),
(6, 'Mines', 15000),
(7, 'Scanner', 120000),
(8, 'Cloaking Device', 500000),
(9, 'Illusion Generator', 300000),
(10, 'Jump Drive', 500000),
(11, 'Drone Scrambler', 400000);

INSERT INTO `level` (`level_id`, `level_name`, `requirement`) VALUES
(1, 'Newbie', 0),
(2, 'Recruit', 25),
(3, 'Freshman Recruit', 116),
(4, 'Sophomore Recruit', 294),
(5, 'Junior Recruit', 589),
(6, 'Navigator', 1016),
(7, 'Petty Officer', 1600),
(8, 'Chief Petty Officer', 2360),
(9, 'Master Petty Officer', 3318),
(10, 'Ensign', 4517),
(11, 'Pilot Apprentice', 5962),
(12, 'Pilot Graduate', 7673),
(13, 'Pilot', 9674),
(14, 'Scout', 11986),
(15, 'Ranger', 14663),
(16, 'Chief Ranger', 17696),
(17, 'Wing Leader', 21109),
(18, 'Warrant Officer', 24923),
(19, 'Chief Warrant Officer', 29160),
(20, 'Master Warrant Officer', 33885),
(21, 'Lieutenant Jr. Grade', 39080),
(22, 'Lieutenant 2nd Class', 44765),
(23, 'Lieutenant 1st Class', 50964),
(24, 'Lieutenant Commander', 57698),
(25, 'Commander', 65045),
(26, 'Staff Commander', 72972),
(27, 'Senior Commander', 81503),
(28, 'Force Commander', 90659),
(29, 'Jr. Executive Officer', 100462),
(30, 'Executive Officer', 111001),
(31, 'Explorer Captain', 122234),
(32, 'Privateer Captain', 134181),
(33, 'Special Forces Captain', 146865),
(34, 'Military Captain', 160310),
(35, 'Flagship Captain', 174615),
(36, 'Lt. Commodore', 189724),
(37, 'Commodore', 205660),
(38, 'Vice Admiral, 3 stars', 222450),
(39, 'Vice Admiral, 4 stars', 240104),
(40, 'Vice Admiral, 5 stars', 258745),
(41, 'Admiral', 278304),
(42, 'Fleet Admiral', 298801),
(43, 'Grand Admiral', 320260),
(44, 'Galaxy Admiral', 342700),
(45, 'Universal Admiral', 366255),
(46, 'Hero', 390330),
(47, 'Local Legend', 416463),
(48, 'Galactic Legend', 443167),
(49, 'Universal Legend', 470970),
(50, 'Spooky', 500000);

INSERT INTO `location_is_bank` (`location_type_id`) VALUES
(701),
(702),
(703),
(704);

INSERT INTO `location_is_bar` (`location_type_id`) VALUES
(801),
(802),
(803),
(804);

INSERT INTO `location_is_fed` (`location_type_id`) VALUES
(201),
(203),
(204),
(205),
(206),
(207),
(208),
(209),
(210);

INSERT INTO `location_is_hq` (`location_type_id`) VALUES
(101),
(103),
(104),
(105),
(106),
(107),
(108),
(109),
(110),
(111),
(112);

INSERT INTO `location_is_ug` (`location_type_id`) VALUES
(102);

INSERT INTO `location_sells_hardware` (`location_type_id`, `hardware_type_id`) VALUES
(601, 1),
(601, 2),
(601, 3),
(602, 4),
(602, 5),
(602, 6),
(603, 10),
(604, 7),
(605, 8),
(606, 9),
(607, 1),
(607, 2),
(607, 3),
(607, 4),
(607, 5),
(607, 6),
(607, 7),
(607, 8),
(607, 9),
(607, 10),
(607, 11),
(608, 11),
(609, 4),
(901, 1),
(901, 2),
(901, 3),
(901, 7),
(902, 1),
(902, 2),
(902, 3),
(902, 7),
(903, 1),
(903, 2),
(903, 3),
(903, 7),
(903, 10),
(904, 1),
(904, 2),
(904, 3),
(904, 4),
(904, 7),
(905, 1),
(905, 2),
(905, 3),
(905, 7),
(905, 9),
(906, 1),
(906, 2),
(906, 3),
(906, 7),
(907, 1),
(907, 2),
(907, 3),
(907, 7),
(907, 8),
(908, 1),
(908, 2),
(908, 3),
(908, 7),
(908, 11),
(2000, 1);

INSERT INTO `location_sells_ships` (`location_type_id`, `ship_type_id`) VALUES
(401, 29),
(401, 30),
(401, 31),
(401, 32),
(401, 33),
(402, 34),
(402, 35),
(402, 36),
(402, 37),
(402, 38),
(403, 39),
(403, 40),
(403, 41),
(403, 42),
(403, 43),
(404, 44),
(404, 45),
(404, 46),
(404, 47),
(404, 48),
(404, 49),
(405, 50),
(405, 51),
(405, 52),
(405, 53),
(405, 54),
(405, 55),
(406, 56),
(406, 57),
(406, 58),
(406, 59),
(406, 60),
(406, 61),
(407, 62),
(407, 63),
(407, 64),
(407, 65),
(407, 66),
(407, 67),
(408, 70),
(408, 71),
(408, 72),
(408, 73),
(408, 74),
(408, 75),
(501, 1),
(501, 2),
(501, 3),
(501, 10),
(501, 13),
(501, 14),
(502, 7),
(502, 8),
(502, 9),
(503, 15),
(503, 16),
(503, 17),
(504, 20),
(504, 21),
(504, 22),
(505, 9),
(505, 10),
(505, 11),
(505, 12),
(506, 17),
(506, 18),
(506, 19),
(507, 1),
(507, 4),
(507, 26),
(507, 27),
(508, 2),
(508, 5),
(508, 6),
(508, 13),
(508, 17),
(509, 23),
(509, 24),
(509, 25),
(510, 68),
(511, 1),
(512, 1),
(512, 2),
(512, 3),
(512, 4),
(512, 5),
(512, 6),
(512, 7),
(512, 8),
(512, 9),
(512, 10),
(512, 11),
(512, 12),
(512, 13),
(512, 14),
(512, 15),
(512, 16),
(512, 17),
(512, 18),
(512, 19),
(512, 20),
(512, 21),
(512, 22),
(512, 23),
(512, 24),
(512, 25),
(512, 26),
(512, 27),
(512, 28),
(512, 29),
(512, 30),
(512, 31),
(512, 32),
(512, 33),
(512, 34),
(512, 35),
(512, 36),
(512, 37),
(512, 38),
(512, 39),
(512, 40),
(512, 41),
(512, 42),
(512, 43),
(512, 44),
(512, 45),
(512, 46),
(512, 47),
(512, 48),
(512, 49),
(512, 50),
(512, 51),
(512, 52),
(512, 53),
(512, 54),
(512, 55),
(512, 56),
(512, 57),
(512, 58),
(512, 59),
(512, 60),
(512, 61),
(512, 62),
(512, 63),
(512, 64),
(512, 65),
(512, 66),
(512, 67),
(512, 69),
(512, 70),
(512, 71),
(512, 72),
(512, 73),
(512, 74),
(512, 75),
(513, 1000),
(513, 1001);

INSERT INTO `location_sells_weapons` (`location_type_id`, `weapon_type_id`) VALUES
(301, 6),
(301, 12),
(301, 13),
(301, 14),
(301, 32),
(302, 6),
(302, 10),
(302, 14),
(302, 33),
(303, 35),
(303, 38),
(304, 16),
(304, 18),
(304, 40),
(305, 24),
(305, 26),
(305, 27),
(306, 26),
(306, 48),
(307, 8),
(307, 9),
(308, 20),
(308, 21),
(308, 28),
(308, 36),
(308, 44),
(309, 3),
(310, 17),
(310, 31),
(310, 42),
(311, 7),
(311, 14),
(311, 42),
(312, 12),
(312, 13),
(313, 4),
(313, 19),
(313, 47),
(314, 40),
(314, 43),
(314, 46),
(314, 50),
(315, 34),
(315, 37),
(315, 41),
(315, 47),
(316, 33),
(316, 34),
(316, 45),
(317, 34),
(317, 39),
(317, 50),
(318, 11),
(318, 25),
(318, 29),
(319, 5),
(319, 8),
(319, 33),
(319, 37),
(320, 30),
(320, 34),
(320, 47),
(320, 49),
(321, 22),
(321, 23),
(321, 25),
(321, 50),
(322, 2),
(323, 1),
(324, 51),
(324, 52),
(324, 53),
(324, 54),
(325, 55),
(326, 1),
(326, 2),
(326, 3),
(326, 4),
(326, 5),
(326, 6),
(326, 7),
(326, 8),
(326, 9),
(326, 10),
(326, 11),
(326, 12),
(326, 13),
(326, 14),
(326, 15),
(326, 16),
(326, 17),
(326, 18),
(326, 19),
(326, 20),
(326, 21),
(326, 22),
(326, 23),
(326, 24),
(326, 25),
(326, 26),
(326, 27),
(326, 28),
(326, 29),
(326, 30),
(326, 31),
(326, 32),
(326, 33),
(326, 34),
(326, 35),
(326, 36),
(326, 37),
(326, 38),
(326, 39),
(326, 40),
(326, 41),
(326, 42),
(326, 43),
(326, 44),
(326, 45),
(326, 46),
(326, 47),
(326, 48),
(326, 49),
(326, 50),
(326, 51),
(326, 52),
(326, 53),
(326, 54),
(326, 55),
(10000, 10000),
(10000, 10001),
(10000, 10002),
(10000, 10003);

INSERT INTO `location_type` (`location_type_id`, `location_name`, `location_image`, `location_processor`, `smc_type_id`, `mgu_id`) VALUES
(101, 'Federal Headquarters', 'images/government.gif', 'government.php', 100, 100),
(102, 'Underground Headquarters', 'images/underground.gif', 'underground.php', 101, 101),
(103, 'Alskant Headquarters', 'images/government.gif', 'government.php', 102, 102),
(104, 'Creonti Headquarters', 'images/government.gif', 'government.php', 103, 103),
(105, 'Human Headquarters', 'images/government.gif', 'government.php', 104, 104),
(106, 'Ik''Thorne Headquarters', 'images/government.gif', 'government.php', 105, 105),
(107, 'Salvene Headquarters', 'images/government.gif', 'government.php', 106, 106),
(108, 'Thevian Headquarters', 'images/government.gif', 'government.php', 107, 107),
(109, 'WQ Human Headquarters', 'images/government.gif', 'government.php', 108, 108),
(110, 'Nijarin Headquarters', 'images/government.gif', 'government.php', 100, 109),
(201, 'Federal Beacon', 'images/beacon.gif', NULL, 1, 1),
(301, 'Advanced Missile Concepts 1', 'images/weapon_shop.gif', 'shop_weapon.php', 501, 501),
(302, 'Advanced Missile Concepts 2', 'images/weapon_shop.gif', 'shop_weapon.php', 502, 502),
(303, 'Big Guns, Inc.', 'images/weapon_shop.gif', 'shop_weapon.php', 503, 503),
(304, 'Cannon-O-Rama', 'images/weapon_shop.gif', 'shop_weapon.php', 504, 504),
(305, 'Creonti Weapons 1', 'images/weapon_shop.gif', 'shop_weapon.php', 505, 505),
(306, 'Creonti Weapons 2', 'images/weapon_shop.gif', 'shop_weapon.php', 506, 506),
(307, 'Flux Systems - Weaponry Division', 'images/weapon_shop.gif', 'shop_weapon.php', 507, 507),
(308, 'Land of Lasers', 'images/weapon_shop.gif', 'shop_weapon.php', 508, 508),
(309, 'Monastery of the Iron Maiden', 'images/weapon_shop.gif', 'shop_weapon.php', 509, 509),
(310, 'No Shields Inc 1', 'images/weapon_shop.gif', 'shop_weapon.php', 510, 510),
(311, 'No Shields Inc 2', 'images/weapon_shop.gif', 'shop_weapon.php', 511, 511),
(312, 'Rapid-Fire Sales Office', 'images/weapon_shop.gif', 'shop_weapon.php', 512, 512),
(313, 'Shotgun Shack', 'images/weapon_shop.gif', 'shop_weapon.php', 513, 513),
(314, 'The General Store - Weapons Division 1', 'images/weapon_shop.gif', 'shop_weapon.php', 514, 514),
(315, 'The General Store - Weapons Division 2', 'images/weapon_shop.gif', 'shop_weapon.php', 515, 515),
(316, 'The General Store - Weapons Division 3', 'images/weapon_shop.gif', 'shop_weapon.php', 516, 516),
(317, 'The General Store - Weapons Division 4', 'images/weapon_shop.gif', 'shop_weapon.php', 517, 517),
(318, 'The One-Stop-Weapons-Shop 1', 'images/weapon_shop.gif', 'shop_weapon.php', 518, 518),
(319, 'The One-Stop-Weapons-Shop 2', 'images/weapon_shop.gif', 'shop_weapon.php', 519, 519),
(320, 'The One-Stop-Weapons-Shop 3', 'images/weapon_shop.gif', 'shop_weapon.php', 520, 520),
(321, 'Torpedo Outlet', 'images/weapon_shop.gif', 'shop_weapon.php', 521, 521),
(322, 'Underground Weapons', 'images/weapon_shop.gif', 'shop_weapon.php', 522, 522),
(323, 'Check Your Pulse', 'images/weapon_shop.gif', 'shop_weapon.php', 523, 523),
(324, 'Nijarin Weaponry', 'images/weapon_shop.gif', 'shop_weapon.php', 500, 524),
(401, 'Alskant Ship Dealer', 'images/shipdealer.gif', 'shop_ship.php', 601, 601),
(402, 'Creonti Ship Dealer', 'images/shipdealer.gif', 'shop_ship.php', 602, 602),
(403, 'Human Ship Dealer', 'images/shipdealer.gif', 'shop_ship.php', 603, 603),
(404, 'Ik''Thorne Ship Dealer', 'images/shipdealer.gif', 'shop_ship.php', 604, 604),
(405, 'Salvene Ship Dealer', 'images/shipdealer.gif', 'shop_ship.php', 605, 605),
(406, 'Thevian Ship Dealer', 'images/shipdealer.gif', 'shop_ship.php', 606, 606),
(407, 'WQ Human Ship Dealer', 'images/shipdealer.gif', 'shop_ship.php', 607, 607),
(408, 'Nijarin Ship Dealer', 'images/shipdealer.gif', 'shop_ship.php', 600, 608),
(501, 'Cheap Used Ships', 'images/shipdealer.gif', 'shop_ship.php', 608, 609),
(502, 'Cross World Transit Ships', 'images/shipdealer.gif', 'shop_ship.php', 609, 610),
(503, 'Cruiser Central', 'images/shipdealer.gif', 'shop_ship.php', 610, 611),
(504, 'Federation Shipyard', 'images/shipdealer.gif', 'shop_ship.php', 611, 612),
(505, 'Huge Ship Central', 'images/shipdealer.gif', 'shop_ship.php', 612, 613),
(506, 'Military Vehicle Outlet', 'images/shipdealer.gif', 'shop_ship.php', 613, 614),
(507, 'Refurbished Ships', 'images/shipdealer.gif', 'shop_ship.php', 614, 616),
(508, 'Ship-O-Rama', 'images/shipdealer.gif', 'shop_ship.php', 615, 618),
(509, 'The Smuggler''s Craft', 'images/shipdealer.gif', 'shop_ship.php', 616, 619),
(510, 'Test Shipyard', 'images/shipdealer.gif', 'shop_ship.php', 617, 620),
(511, 'Semi Wars', 'images/shipdealer.gif', 'shop_ship.php', 618, 617),
(601, 'Uno Hardware', 'images/hardware.png', 'shop_hardware.php', 414, 414),
(602, 'Combat Accessories', 'images/hardware.png', 'shop_hardware.php', 410, 410),
(603, 'Accelerated Systems', 'images/hardware.png', 'shop_hardware.php', 408, 415),
(604, 'Advanced Communications', 'images/hardware.png', 'shop_hardware.php', 409, 409),
(605, 'Hidden Technology', 'images/hardware.png', 'shop_hardware.php', 411, 411),
(606, 'Image Systems, Inc', 'images/hardware.png', 'shop_hardware.php', 412, 412),
(701, 'Bank of the Stars', 'images/bank.png', 'bank_personal.php', 201, 201),
(702, 'Last Galactic Bank', 'images/bank.png', 'bank_personal.php', 202, 202),
(703, 'Piggy Bank', 'images/bank.png', 'bank_personal.php', 204, 204),
(704, 'Federal Mint', 'images/bank.png', 'bank_personal.php', 203, 203),
(801, 'The Stellar Dance Club', 'images/bar.png', 'bar_main.php', 301, 301),
(802, 'Bottoms-Up Bar and Grill', 'images/bar.png', 'bar_main.php', 302, 302),
(803, 'Chug-A-Mug', 'images/bar.png', 'bar_main.php', 303, 303),
(804, 'Starlite Saloon', 'images/bar.png', 'bar_main.php', 304, 304),
(901, 'Alskant Trading Base', 'images/hardware.png', 'shop_hardware.php', 401, 401),
(902, 'Creonti Depot', 'images/hardware.png', 'shop_hardware.php', 402, 402),
(903, 'Human Hardware', 'images/hardware.png', 'shop_hardware.php', 403, 403),
(904, 'Ik''Thorne Drone Farm', 'images/hardware.png', 'shop_hardware.php', 404, 404),
(905, 'Salvene Supply &amp; Plunder', 'images/hardware.png', 'shop_hardware.php', 405, 405),
(906, 'The Thevian Bounty', 'images/hardware.png', 'shop_hardware.php', 406, 406),
(907, 'West-Quadrant Hardware', 'images/hardware.png', 'shop_hardware.php', 407, 407),
(908, 'Nijarin Hardware', 'images/hardware.png', 'shop_hardware.php', 400, 408),
(325, 'Pulse of the Universe', 'images/weapon_shop.gif', 'shop_weapon.php', 500, 525),
(326, 'Race Wars Weapons', 'images/weapon_shop.gif', 'shop_weapon.php', 500, 526),
(512, 'Race Wars Ships', 'images/shipdealer.gif', 'shop_ship.php', 600, 615),
(607, 'Race Wars Hardware', 'images/hardware.png', 'shop_hardware.php', 400, 417),
(608, 'Crone Dronfusion', 'images/hardware.png', 'shop_hardware.php', 400, 416),
(203, 'Alskant Beacon', 'images/beacon.gif', NULL, 1, 1),
(204, 'Creonti Beacon', 'images/beacon.gif', NULL, 1, 1),
(205, 'Human Beacon', 'images/beacon.gif', NULL, 1, 1),
(206, 'Ik''Thorne Beacon', 'images/beacon.gif', NULL, 1, 1),
(207, 'Salvene Beacon', 'images/beacon.gif', NULL, 1, 1),
(208, 'Thevian Beacon', 'images/beacon.gif', NULL, 1, 1),
(209, 'WQ Human Beacon', 'images/beacon.gif', NULL, 1, 1),
(210, 'Nijarin Beacon', 'images/beacon.gif', NULL, 1, 1),
(609, 'Backyard Drone Farm', 'images/hardware.png', 'shop_hardware.php', 0, 0);

INSERT INTO `log_type` (`log_type_id`, `log_type_entry`) VALUES
(1, 'Logging in/out'),
(2, 'Game enter/leave'),
(3, 'Alliance'),
(4, 'Bank Transaction'),
(5, 'Moving'),
(6, 'Trading'),
(7, 'Port Raiding'),
(8, 'Player Attacking'),
(9, 'Forces'),
(11, 'Planets'),
(10, 'Transactions'),
(12, 'Planet Attacking'),
(13, 'Account Changes');

INSERT INTO `manual` (`topic_id`, `parent_topic_id`, `order_id`, `topic`, `text`) VALUES
(1, 0, 1, 'Introduction', 'Welcome to the Space Merchant Realms Help Files. In these files we will be explaining different facets of the game, from the differences between the races, to the game interface itself, to several tips on how to survive. The help files are intended to be a guide only, not a step by step manual on how to do everything in the game. Some aspects are better learned by experience.<br/>\r\n<br/>\r\nPlease note that these files are currently under revision and a lot of information that is already available will be added too, or moved around.<br/>\r\n<br/>\r\nA big thanks goes out to all the players who have helped with the information found within. The helpfiles would not exist without all of your time and effort. <br/>\r\n<br/>\r\nThe table of contents may be accessed by clicking on the ToC box located on the top and bottom right of this screen.  This will allow you to jump from topic to topic without having to scroll through everything.\r\n<br/>\r\n<br/>\r\nSpace Merchant Realms is hosted by <a href="http://www.fem.tu-ilmenau.de/index.php?id=93&L=1" target="fem">FeM</a>'),
(2, 0, 2, 'The Beginning', 'MrSpock''s rendition of one of the first web games on the world wide web, Space Merchant Realms, brings back many of the features, styles and memories of Shareplay''s Space Merchant. <br/>\r\n<br/>\r\nOf course, the game cannot exist with only one person in charge. MrSpock has gathered together a team to help with the day to day duties within SMR. While their duties may overlap in many instances, listed below are the areas that each are in charge of. <br/>\r\n<br/>\r\n<table><tr><th>Name</th><th>Position</th></tr>\r\n<tr><td>MrSpock</td><td>Creator/Coder</td></tr>\r\n<tr><td>Curufir</td><td>Coder</td></tr>\r\n<tr><td>Prince Valiant&nbsp;&nbsp;&nbsp;</td><td>All-Purpose Admin</td></tr>\r\n<tr><td>EstoyLoco</td><td>Marketing/Newbie Helper Admin</td></tr>\r\n<tr><td>B.O.B.</td><td>Communication Admin</td></tr>\r\n<tr><td>Siege</td><td>Multi Admin</td></tr>\r\n<tr><td>Strike</td><td>Multi Admin</td></tr></table>\r\n\r\n<br/>\r\nIf you have any questions or suggestions for them, you can email them at support@smrealms.de Most queries are answered within 24 hours, but please remain patient if it takes longer.  To reach a specific admin directly, just insert their name in front of @smrealms.de  \r\n'),
(3, 0, 3, 'Races', 'Race plays an important part in SMR.  There are advantages/disadvantages to each one.<br/>\r\n<br/>\r\nIn the following sections, you will get a brief overview of each race and information on their special technology, if they have one.'),
(4, 3, 1, 'Alskant', '<img src="images/race2.gif" align="left">This race of tall, thin humanoids have just recently (in the last 100 years) developed the technology that allows\r\n inter-stellar travel. However, in the last 100 years, their \r\nfriendly nature has allowed them to trade for much of the technology \r\nthe other races had achieved. They do not focus on combat, but they \r\nhave been preparing themselves in case it arrives. Their ships tend\r\n to be geared more towards commerce than combat, and this matches \r\ntheir enterprising nature. They continue to seek the knowledge of the\r\n other races, and to explore to the edges of space. They tend to have \r\nrelatively good relationships with most of the other races.'),
(5, 3, 2, 'Creonti', '<img src="images/race3.gif" align="left">The Creonti are an introverted race that has little to do with the other races, except for trade which has become vital to all of the races. Their small stature also has led them to feel inferior to some of the other races. However, these feelings are easily overcome with the weapons they usually carry. They have moderately good to neutral \r\nrelationships with the other races, but most of them live by a \r\nCreonti First motto. They are very team oriented, and unite \r\nquickly to defend their own. While they do not start conflicts often, \r\nthey have been involved in several and are viewed as proficient pilots.'),
(6, 3, 3, 'Human', '<img src="images/race4.gif" align="left">These humanoids tend to be the first to jump into the different wars that develop. They are often the first to take sides when a conflict ignites, even if it does not affect them directly. They were the \r\noriginal founders of several attempts to unify the races, all of which \r\nfailed. However, they are the most courageous (and outspoken) of the \r\nraces. They tend to roam the entire galaxy, and team up with various \r\ntraders of many races if it suits their goals. They also have the \r\nbroadest knowledge of the universe as a whole, due to their extensive\r\n exploration and the fact that they were the second race to develop \r\nthe technology for interstellar travel. Their relationships with other\r\n races tend to vary a lot. The only relationship with any level of \r\nconsistency is their irritation with the West-Quadrant Humans, a \r\ndivision of their race that broke away over 300 years ago.<br/>\r\n<br/>\r\nHumans have the special technology of the jump drive.  This allows a ship to jump from one sector to another without having to pass through all the sectors in between.  It costs 15 turns to use, and for any distances over 15 sectors away there is a possibility of a misjump of up to 8 sectors.'),
(7, 3, 4, 'Ik''Thorne', '<img src="images/race5.gif" align="left">Considering the average Ik''Thorne stands about 11 feet tall, it is no surprise that they are the designers and builders of the large cruisers throughout the galaxy. While there are several other models available, the Ik''Thorne line of battle cruisers and carriers are commonly \r\nregarded as the strongest of the ships. However, their weapons are not\r\n exceedingly strong, and they usually are forced to go to others to \r\nequip the ship with weapons and combat drones. The Ik''Thorne like \r\nstability. They don''t care as much if they are at war with someone or \r\nat peace, but just like things to be consistent. Races who are \r\nconstantly changing their views on things annoy them. This is probably\r\n due to their extremely long life spans. While they have butted heads \r\nwith the Thevians and the Salvene over the centuries, they maintain no \r\noutstanding "enemies." However, their empire is often attacked and \r\nplundered for its wealth and knowledge, so they are drawn into many \r\nconflicts.<br/>\r\n<br/>\r\nThe specialty of the Ik''Thorne race is the use of combat drones.  Instead of depending upon a lot of armor to protect their ships, they rely instead on large quantity of combat drones to protect their ships in case of an attack.'),
(9, 3, 6, 'Salvene', '<img src="images/race6.gif" align="left">This race of quadripeds has a strong focus on conquest. They are not concerned with honor and justice and other such "trivialities", but rather they focus on the wealth, power, and extent of their own empire. They tend to alter their relations with the other races to whatever \r\nsuits them best at a given time. While they are very trusting and \r\ndependent on those of their own race, relationships with others are \r\nslow to form, and they distrust most of the other races. While they \r\nare involved in a lot of trading, they put a large focus on combat. \r\nIt is rare that they are not at war with somebody. While their ships \r\nare decent trade ships, they excel in combat situations.<br/>\r\n<br/>\r\nBecause of their small stature, the Salvene have developed the technology of Illusion Generators.  These generators allow them to mask the appearance of their ships.  They can pose as a helpless escape pod, a massive warbird, or anything in between.  However, they are not foulproof.  A decent scanner is able to see through the illusion.\r\n'),
(10, 3, 7, 'Thevian', '<img src="images/race7.gif" align="left">The strong focus on reputation is what distinguishes Thevian culture from most of the other races. This race lives its entire life inside of a robotic shell. They use the shell for all movement and interaction. This shell is shaped like a humanoid, but the Thevians themselves \r\nare quite indescribable. Their desire for reputation is what causes \r\nthem to be extremists. The good Thevians will be extremely good, \r\nhunting down evil throughout the galaxy, even if no formal bounty is \r\nset. If there is a bounty on a person, they view it as an even better \r\nchance to make a name for themselves. The evil Thevians take the exact\r\n opposite route, becoming the most notorious criminals in the galaxy \r\nfor their acts of destruction and cruelty. They wander around raiding\r\n ports and planets and destroying all they encounter. Thus, their need\r\n to distinguish themselves leads them to contributing to most admirable\r\n police/bounty hunters and the most notorious criminals.'),
(11, 3, 8, 'WQ Human', '<img src="images/race8.gif" align="left">While they are of the same race as the Humans, the WQ Humans want nothing to do with them. When the Humans were attempting to unite the races, the WQ Humans began colonizing the Western Quadrant of the galaxy. Here they developed their communities and began extensive trading with other races, specifically the Thevians. The unification \r\nattempt failed due to a war that broke out between the Thevians and \r\nthe Ik\\''Thorne. The main Human forces almost immediately joined forces \r\nwith the Ik\\''Thorne, for several reasons that are not clearly \r\nunderstood. The WQ Humans looked upon this as being unjust, and lobbied several times to withdraw Human involvement from the conflict. Finally, they \r\ndeclared themselves a separate entity. Immediately, the Humans \r\nwithdrew from the Thevian-Ik\\''Thorne conflict, and attempted to \r\nsuppress the rebellion. The civil war continued for over 50 years, \r\nand ended in an unstable peace treaty. Since then there have been \r\nseveral conflicts between the two human groups, and their relations \r\nseem to be worsening.<br/>\r\n<br/>\r\nBecause of the worsening relations, the WQ Humans have developed cloaking technology for their top ships.  This allows them to move about space and hunt or trade without detection.  However, the cloak technology has one exploitable flaw.  If a cloaked ship comes in contact with a space mine, the cloak switches itself off and must be manually reset, leaving the ship vulnerable to attack.\r\n'),
(8, 3, 5, 'Nijarin', 'The Nijarin are a race of six-limbed reptilian creatures. The Nijarin race has existed just as long if not longer than the other races but has only just recently come out of hiding. The Nijarin have become very powerful contenders in the war for resources even though they only recently resurfaced. Their focus is on offensive power which has caused the creation of high-HP ships and very powerful weaponry. This has caused the reduction of shields and armour to allow their ships to support such a heavy payload. To make up for this the Nijarin use a technology called the Drone Communications Scrambler. This device causes enemy drones to be much less effective. The Nijarin fleet cannot be held back from taking back their part of this universe of war.'),
(12, 1000, 0, 'Game Interface', 'The following subsections will give you an overview to some of the main game screens.  The first section will describe each of the menu options on your currect sector screen.  The second will go in depth about the current sector itself, and the third will discuss the trader screen.'),
(13, 12, 1, 'Menu Details', 'On the left side of your main screen are several links that are useful in the game.\r\n<p>\r\nCurrent Sector - clicking this will refresh the sector you are in.  Useful to keep an eye on the sector and to report if anyone else enters.\r\n<p>\r\nLocal Map - This will bring up a 5x5 map of the area you are in.  You can click on the sector numbers and move through the sectors on this map.  Useful to see where traders/forces are located.\r\n<p>\r\nPlot a Course - This will bring up a screen that will allow you to enter in your destination and will plot the course for you.  Once you have a course plotted, all you have to do is click on continue plot course through each section until you reach your destination.  If you have a jumpdrive enabled ship, there will be a seperate box for you to enter your location into, then you just click on jump.\r\n<p>\r\nGalaxy Map - Clicking on this link will bring up a second screen with a list of all the galaxies.  Select the galaxy you wish to view and it will open up the entire galaxy for you to look at.\r\n<p>\r\nTrader - This will be explained in a separate section.\r\n<p>\r\nAlliance - If you have not joined an alliance, this will bring up a listing of all the current alliances in the game as well as a link for you to create your own.  If you are in an alliance, this will bring up your alliance messaging center.  Here you can check the alliance roster, read the message of the day from your leader, read the webboard, check on the status of alliance planets and forces, and view other alliances.\r\n<p>\r\nPlanet - If you own a planet, clicking on this link will allow you to check on the status of your planet.\r\n<p>\r\nForces - If you have any forces laid, this link will tell you where they are and when they expire.\r\n<p>\r\nMessages - This link takes you into your game messaging center.  All global, personal, alliance, planet, and scout messages can be accessed from here.\r\n<p>\r\nRead News - Keep up to date on what is happening in the game.  All poddings and port raids are listed here.  Breaking News reports any major events in game, such as a large planet bust.\r\n<p>\r\nGalactic Post - Regular news not exciting enough?  The GP is your source for entertaining stories of mishaps and successes in game, written by several anonymous writers.\r\n<p>\r\nSearch for Trader - Useful to find someone in game to send them a message.\r\n<p>\r\nCurrent Players - One of the most important links in the game.  This will tell you who is currently logged into the game, though beware!  Many players remain inactive yet logged in, so they drop off the CPL.  If you trigger one of their scouts, they may just pop up and give you a heady pod ride back to your HQ.\r\n<p>\r\nRanking - Come here to find out how you rank against the other players in the game.  This list goes by current game only.\r\n<p>\r\nHall of Fame - This is the place of all the rankings combined from all the games.  How do you rate against others?\r\n<p>\r\nPlay Game - If you have joined more than one game, you can click on this to switch to another game to play.\r\n<p>\r\nLogoff - This is selfexplainatory.  You click it, you log out of the game.\r\n<p>\r\nPreferences - Here you can change your password and email address, as well as check how many smr credits you have remaining.\r\n<p>\r\nReport a Bug - Found something that isn''t right in the game?  Click on this link and send an email to the Admins for a quick answer.\r\n<p>\r\nWebBoard - This will open a second screen to the game''s webboard.  Learn what is going on in the SMR community.\r\n<p>\r\nDonate - Enjoy the game and wish to see improvements?  This link will allow you to donate money to Spock to help him better the game.\r\n<p>\r\nAnd now....to the right side of your screen....\r\n<p>\r\nThis side of the screen is pretty simple.  It lists your name, as well as your stats...turns, exp, cash and such.  Below your trader stats is your ship and its stats.  Keep a very close eye on your shields and armor, if you lose them, you lose your ship.  If you are in a ship that can be equipted with a cloak, illusion generator, jump drive,  scanner, or drone scrambler, clicking on CIJSD will allow you to access them.\r\n<p>\r\nForces - This lists how many of each force you can carry on your ship and how many you currently have.  By clicking on forces you can select how many to drop in a sector.\r\n<p>\r\nCargo Holds - This tells you how many holds are currently on your ship, plus how many goods you may currently be carrying.\r\n<p>\r\nWeapons - This is a list of what weapons you have on your ship and the order they will fire.  You can click on weapons and change the firing order. \r\n\r\n\r\n<!doctype html public "-//W3C//DTD HTML 4.0 Transitional//EN"><html><head><link rel="stylesheet" type="text/css" href="default.css"><title>Space Merchant Realms</title><meta http-equiv="pragma" content="no-cache"><script type="text/javascript" language="JavaScript"> function OpenWindow(linkName) { popUpWindow = window.open(linkName, "chat", "resizeable, toolbar=no, location=no, directories=no, status=no, menubar=no, copyhistory=no, width=575, height=425"); popUpWindow.focus(); } </script></head><body><table cellspacing="0" cellpadding="0" border="0" width="90%" height="100%"><tr><td></td><td colspan="3" height="1" bgcolor="#0B8D35"></td><td></td></tr><tr><td align="right" valign="top" width="135"><!-- menu --><table cellspacing="5" cellpadding="5" border="0"><tr><td align=right><small><span style="color:yellow">11/8/2002 2:29:41 AM</span><p><big><b>Current Sector</b></big><br><big><b>Local Map</b></big><br><big><b>Plot a Course</b></big><br>Galaxy Map</p><p>Trader<br>Alliance<p>Planet<br>Forces<p>Messages<br>Read News<br>Galactic Post</p><p>Search for Trader<br>Current Players</p><p>Rankings<br>Hall of Fame</p><p>Play Game<br>Logoff</p><p>Manual<br>Preferences<br>Report a Bug</p><b>WebBoard</b><br>Donate</p></small></td></tr></table><!-- end menu --></td><td width="1" bgcolor="#0B8D35"></td><td align="left" valign="top" bgcolor="#06240E"><table width="90%" height="100%" border="0" cellspacing="5" cellpadding="5"><tr><td valign="top"><h1>CURRENT SECTOR: 1357</h1><p><table border="0" cellpadding="0" cellspacing="1" width="100%"><tr><td bgcolor="#0B8D35"><table border="0" cellpadding="3" cellspacing="1" width="90%"><tr bgcolor="#0B2121"><td><table border="0" cellpadding="3" cellspacing="2" width="90%"><tr bgcolor="#0B8D35"><td align="center"><small>Plot a Course | Local Map | Galaxy Map</small></td></tr></table></td></tr></table></td></tr></table></p><p><small>WQ Human</small></p><p><table border="1" bordercolor="#0b8d35" cellspacing="0"><tr> <th>Location</th> <th>Option</th></tr><tr><form name="FORM" method="POST" action="http://www.smrealms.de/beta/loader.php"><input type="hidden" name="sn" value="3430ac64f82faca0fe1898ef72395736"><td width="250"><img src="images/government.gif">&nbsp;WQ Human HQ</td><td align="center" valign="middle"><input type="submit" name="action" value="Examine" id="InputFields"></td></form></tr><tr><form name="FORM" method="POST" action="http://www.smrealms.de/beta/loader.php"><input type="hidden" name="sn" value="43181d7dd85c165817fd7d11041a11bc"><td width="250"><img src="images/beacon.gif">&nbsp;Beacon of Ultimate Protection</td><td align="center" valign="middle">&nbsp;</td></form></tr><tr><form name="FORM" method="POST" action="http://www.smrealms.de/beta/loader.php"><input type="hidden" name="sn" value="f3feae7634cffe9d7c5caff30cc469b0"><td width="250"><img src="images/shipdealer.gif">&nbsp;WQ Human Ship Dealer</td><td align="center" valign="middle"><input type="submit" name="action" value="Examine" id="InputFields"></td></form></tr><tr><form name="FORM" method="POST" action="http://www.smrealms.de/beta/loader.php"><input type="hidden" name="sn" value="a1d443ec000a885bf91a3b2d0aa4685c"><td width="250"><img src="images/hardware.png">&nbsp;West-Quadrant Hardware</td><td align="center" valign="middle"><input type="submit" name="action" value="Examine" id="InputFields"></td></form></tr></table></p><p><table border="1" bordercolor="#0b8d35" cellspacing="0"><tr><th colspan=3 align="center">Move to</th></tr><tr><td>&nbsp;</td><td align="center" width="80" height="40">&nbsp;</td><td>&nbsp;</td></tr><tr><td align="center" width="80" height="40">&nbsp;</td><td>&nbsp;</td><td align="center" width="80" height="40"><a href="http://www.smrealms.de/beta/loader.php?sn=736e738bf295323640da23cf5d39effa"><span style="color:yellow;">1358 (1)</span></a></td></tr><tr><td>&nbsp;</td><td align="center" width="80" height="40"><a href="http://www.smrealms.de/beta/loader.php?sn=6444b521983a8e376e02a8396281897f"><span style="color:yellow;">1372 (1)</span></a></td><td align="center" width="80" height="40"><a href="http://www.smrealms.de/beta/loader.php?sn=b228cacf37059e4521005dc5b8bcb20b"><span style="color:yellow;">1780 (5)</span></a></td></tr></table></p></td></tr><tr><td valign="bottom"><!-- copyright --><table border="0" width="100%"><tr><td valign="middle"><a href="http://www.mpogd.com/gotm/vote.asp" target="_twg"><img border="0" src="images/game_sites/mpogd.png" width="88" height="35"></a>&nbsp;&nbsp;<a href="http://www.topwebgames.com/in.asp?id=136" target="_twg"><img border="0" src="images/game_sites/twg.png" width="88" height="31"></a></td><td align="right" width="200"><span style="font-size:75%;">Space Merchant Realms<br>&copy; 2001-2002<br>Script runtime: 2.384 sec</span></td></tr></table><!-- end copyright --></td></tr></table></td><td width="1" bgcolor="#0B8D35"></td><td align="left" valign="top" width="175"><!-- user data --><table cellspacing="5" cellpadding="5" border="0"><tr><td></td></tr><tr><td><p align="left"><small>Newbie</small><br><big><span style="color:yellow;">Trader&nbsp;(6)</span></big></p><p align="left"><small>Race : WQ Human<br>Turns : 200<br>Newbie Turns Left: <span style="color:#00BB00;">500</span><br>Cash : 5,000<br>Experience : 0<br>Level : 1<br>Alignment : <span style="color:yellow;">0</span><br>Alliance : none</small></p><p align="left"><small><b style="color:yellow;">Newbie Merchant Vessel</b><br>Rating : 1/2<br>Shields : 75/250<br>Armor : 150/325<br><strong>CIJSD</strong> : -----</p></small><p align="left"><small><b>Forces</b><br>Mines : 0/5<br>Combat : 0/15<br>Scout : 0/0</small></p><p align="left"><small><b>Cargo Holds</b></a>&nbsp;(40/120)<br>Empty : 40<br></small></p><p align="left"><small><b>Weapons</b><br>Laser<br>Open : 1</small></p></td></tr></table><!-- end user data --></td></tr><tr><td></td><td colspan="3" height="1" bgcolor="#0b8d35"></td><td></td></tr></table></body></html>'),
(39, 38, 1, 'The Art of Leadership', 'The Hall of Fame is full of many different names (well was, until it reset :) )expert in one or more of the individual facets of the game but one area that is lacking is the recognition of a good leader.\r\n<p>\r\nThe majority of players are content or best able to perform the role of a grunt. Most like to at least have their say but very few (as in life) have the ability, time or energy to become a top leader.\r\n<p>\r\nA good leader has many strings in their bow and I want to discuss some of them and maybe comment on a few particular leaders and my thought on what makes them or made them tick.\r\n<p>\r\n<b>- Time and lots of it</b>\r\n<p>\r\nA leader has to do everything his/her people do plus have an abundance of time to fulfill their role as leader. Depending on the nature and structure of the alliance, this time can far outweigh the time spent actually playing the mechanics of the game. A well oiled experienced alliance will lessen this load and we see the likes of HoA coming back time and again with the same doctrine and largely the same members. The members of HoA all know what their role is and act more like a partnership. Cowboy has the luxury of being able to do what he does best and only lead for top end decisions.\r\n<p>\r\n<b>- Game ability</b>\r\n<p>\r\nA good leader will lead from the front and by example. There are very few top alliances that have a leader in the lower half of their roster. Its very hard to expect anyone to respect someone as a leader if they''re seen as inferior in the mechanics of the game.  Izzanods is a good example to me of someone leading from the front, always at the top or near of SMR''s list until the fighting kicked in and the traders took over.\r\n<p>\r\n<b>- Communication</b>\r\n<p>\r\nThis aspect goes further than purely dictating policy and relaying information. How many times has war broken out where many of one of the participant alliances have no idea of what happened. Communication channels need to be not only open but working and the leader has to shoulder this responsibility. If you don''t ensure this you are blindfolding your people. It&#8217;s a bit hard to expect them to stick together in that situation. HoA is probably a good example of this. They perhaps don''t need to communicate as much as some alliances but their people always seem to be on the same wavelength and any major moves involve everyone.\r\n<p>\r\n<b>- Personality and appeal</b>\r\n<p>\r\nWhatever else you''re good at, you''re not going to draw people to you and have their loyalty without this. As the person doing the most or alot of the talking, your personality is going to be exposed. If someone doesn''t like what they see then you''ll never get the best from them. If there''s a clash then that alliance is a timebomb waiting to happen. There are many people in the game that I couldn''t work with, not out of dislike but just because I know that sooner or later trouble would arise.\r\n<p>\r\n<b>- Vision</b><p>\r\nA person who concentrates on his/her alliance and his/her game is going to be surprised at some point. Looking outside the square is essential. You need to be able to look at what everyone else is doing and see patterns. The more information you have the easier this is and the easiest way to get this info is to tickle the right people. Establish good relationships with as many as you can. A little bit of gentle manipulation will tease out something of import almost every time. Get to know the other personalities about you, work out what makes them tick. Good intelligence is not only the way of the hunter but also a tool of a good leader.\r\n<p>\r\n<b>- People Management</b><p>\r\nA leader will never be successful unless he/she gets to know his/her people and learns how to get the best out of them. Be a good chatter and don''t limit it to SMR. Take an interest in your people, you''ll make friends and have someone you can rely on.\r\n<p>\r\n<b>- Style and consistency</b><p>\r\nA leader should develop their own style of leadership and this style should be something the alliance members know and can depend upon. Much of this will probably come out in a persons personality but its a base for a good working relationship and shouldn''t be changeable.\r\n<p>\r\n<b>- Enjoy the role</b><p>\r\nNothing takes down an alliance quicker than the loss of drive and enjoyment by the leader. If it happens a leader will let down his/her people, simple as that. To lead an alliance is a big commitment and all the hard work can be undone very quickly.\r\n<p>\r\nThere are many other areas I could touch on but won''t for the sake of the readers sanity. The main message is not to take on an alliance as leader unless you know you have the right stuff. Play the game, build up relationships and think long and hard about the role. In many many ways its a thankless task but those who have the ability and the necessary strengths will find it most rewarding as well.\r\n<p>\r\nFor the newer player I''d suggest making a name for yourself first, work in an established alliance and look and learn how its leader works. Have a think about how much time and effort you are prepared to put in and then double it. That''s about how much or more you''ll need to be successful. Don''t be fooled into thinking its easy and full of glory, the likes of Cowboy do much more than you''d ever know and have had to devote an awful amount of energy into getting where they are today. They''ve done the hard work and become good at it. \r\n<p>\r\nMost importantly though, consider long and hard whether you are one of the very small number of people who has the ability to be a leader. The vast majority of us aren''t cut out for it and will enjoy the game more without the burden.\r\n'),
(14, 12, 2, 'Current Sector', 'The Current Sector screen shows the information for that sector.\r\n\r\n<br>Locations in a current sector appear first, and at the top of the sector. if more than one location is in sector, locations go in order from\r\n<p>1)Head Quarter (Or Under Ground)\r\n<p>2)Beacon of Ultimate Protection\r\n<p>3)Ship dealer\r\n<p>4)Uno/Equipment dealer\r\n<p>5)Weapons Dealer\r\n<p>6)Port\r\n<br><p>Following the locations is the sector cross. The sector cross shows the 4 sectors above, below, to the left, and to the right of the current sector, if you can reach those sectors from the current sector.\r\n<br><p>Below the sector cross is the pilot/ship info. If other ships are in the sector, pilots, and their ship type and attack/defense rating show up here.\r\n<br><p>Finally, following the players info is the forces info. Any scouts, mines, or combat drones in sector will show up here.'),
(15, 12, 3, 'Trader', 'You can check your status at any time by clicking on the trader link on the left side of your screen.  This page will tell you whether or not you have Federal Protection, your personal relations with other races, whether or not you are a member of your ruling council, the amount of cash in your personal account (from the last time you visited), what ship you are in, what technology your ship supports,and your user rankings.\r\n<p><!doctype html public "-//W3C//DTD HTML 4.0 Transitional//EN"><html><head><link rel="stylesheet" type="text/css" href="default.css"><title>Space Merchant Realms</title><meta http-equiv="pragma" content="no-cache"><script type="text/javascript" language="JavaScript"> function OpenWindow(linkName) { popUpWindow = window.open(linkName, "chat", "resizeable, toolbar=no, location=no, directories=no, status=no, menubar=no, copyhistory=no, width=575, height=425"); popUpWindow.focus(); } </script></head><body><table cellspacing="0" cellpadding="0" border="0" width="100%" height="100%"><tr><td></td><td colspan="3" height="1" bgcolor="#0B8D35"></td><td></td></tr><tr><td align="right" valign="top" width="135"><!-- menu --><table cellspacing="5" cellpadding="5" border="0"><tr><td align=right><small><span style="color:yellow">11/8/2002 2:11:58 AM</span><p><big><b>Planet Main</b></big><br><b>Plot a Course</b></big><br>Galaxy Map</p><p>Trader<br>Alliance<p>Planet<br>Forces<p>Messages<br>Read News<br><i>Galactic Post</i></p><p>Search for Trader<br>Current Players</p><p>Rankings<br>Hall of Fame</p><p>Play Game<br>Logoff</p><p><big>WebBoard</big></b><br><b><big>Donate</big></b><br>Preferences</p></small></td></tr></table><!-- end menu --></td><td width="1" bgcolor="#0B8D35"></td><td align="left" valign="top" bgcolor="#06240E"><table width="100%" height="100%" border="0" cellspacing="5" cellpadding="5"><tr><td valign="top"><h1>TRADER STATUS</h1><p><table border="0" cellpadding="0" cellspacing="1" width="100%"><tr><td bgcolor="#0B8D35"><table border="0" cellpadding="3" cellspacing="1" width="100%"><tr bgcolor="#0B2121"><td><table border="0" cellpadding="3" cellspacing="2" width="100%"><tr bgcolor="#0B8D35"><td align="center"><small>Trader Status | Planet | Alliance | Politics | Relations</small></td></tr></table></td></tr></table></td></tr></table></p><p align="center"><table bordercolor="#0b8d35" cellspacing="0" cellpadding="5" width="95%" border="1"><tr><td valign="top" width="50%"><p><b style="color:#cccc33;">Protection</b><br>You are <span style="color:red;">NOT</span> under protection.</p><p><b>Relations (Personal)</b><br>Alskant : <span style="color:#FFFF00;">0</span><br>Creonti : <span style="color:#FFFF00;">0</span><br>Human : <span style="color:green;">500</span><br>Ik''Thorne : <span style="color:#FFFF00;">0</span><br>Salvene : <span style="color:green;">500</span><br>Thevian : <span style="color:#FFFF00;">0</span><br>WQ Human : <span style="color:#FFFF00;">0</span><br></p><p><b>Politics</b><br>You are a Member of the ruling council.</p></td><td valign="top"><p><b>Savings</b>You have <span style="color:yellow;">1,059,125,161</span> credits in your personal savings account.</p><p><big style="color:yellow;">Inter-Stellar Trader</big><br>Speed: 10.5 turns/hour. Max of 600.<br></p><p><b style="color:yellow;">Your Ship Supports</b><br>Scanner<br>Jump Drive<br></p><p><b>User Ranking</b><br>You are ranked as a <font size="4" color="greenyellow">Beginner</font> player.<p></p></td></tr></table></p></td></tr><tr><td valign="bottom"><!-- copyright --><table border="0" width="100%"><tr><td valign="middle"><a href="http://www.mpogd.com/gotm/vote.asp" target="_twg"><img border="0" src="images/game_sites/mpogd.png" width="88" height="35"></a>&nbsp;&nbsp;<a href="http://www.topwebgames.com/in.asp?id=136" target="_twg"><img border="0" src="images/game_sites/twg.png" width="88" height="31"></a></td><td align="right" width="200"><span style="font-size:75%;">Space Merchant Realms<br>&copy; 2001-2002<br>Script runtime: 0.726 sec</span></td></tr></table><!-- end copyright --></td></tr></table></td><td width="1" bgcolor="#0B8D35"></td><td align="left" valign="top" width="175"><!-- user data --><table cellspacing="5" cellpadding="5" border="0"><tr><td><a href="http://www.smrealms.de/loader.php?sn=5baf982cceb313f864320adf9d3875cf"><img src="images/council_msg.gif" border="0" alt="Political Messages"></a></td></tr><tr><td><p align="left"><small>Lieutenant Jr. Grade</small><br><big><span style="color:#7FFF7F;">Happy_Trader&nbsp;(132)</span></big></p><p align="left"><small>Race : Human<br>Turns : 600<br>Cash : 590,238<br>Experience : 43,381<br>Level : 21<br>Alignment : <span style="color:yellow;">164</span><br>Alliance : Happy Traders, Inc. (5)</small></p><p align="left"><small><b style="color:yellow;">Inter-Stellar Trader</b><br>Rating : 3/6<br>Shields : 375/375<br>Armor : 250/250<br><strong>CIJS</strong> : --**</p></small><p align="left"><small><b>Forces</b><br>Mines : 0/25<br>Combat : 0/15<br>Scout : 0/5</small></p><p align="left"><small><b>Cargo Holds</b>&nbsp;(300/300)<br>Empty : 300<br></small></p><p align="left"><small><b>Weapons</b><br>Laser<br>Laser<br>Open : 0</small></p></td></tr></table><!-- end user data --></td></tr><tr><td></td><td colspan="3" height="1" bgcolor="#0b8d35"></td><td></td></tr></table></body></html>\r\n<p>\r\nAs you can see, this trader does not have Federal Protection, which means that they can be attacked and even podded if they are not online to move to safety.  \r\n<p>\r\nThis trader is also a member of their ruling council, which will allow them to cast votes to increase/decrease relations with other races.'),
(16, 0, 4, 'Locations', 'In Space Merchant Realms, space is divided into sectors. Sectors of space can be empty or they can contain locations. Location is the general term for racial headquarters, federal beacons, and shops that buy and sell items. Sectors can also contain ports, planets, warps. This section will describe the different types of places you will find as you fly through space and how they are used.'),
(17, 16, 1, 'Ports', 'Ports are places where goods are bought and sold for various prices. Players gain money and experience by bargaining with port masters for the best possible price. Ports are ranked from level 1 to level 9. Low level ports sell cheaper goods, while high level ports sell more expensive and more profitable goods. Some ports sell illegal goods too, but these can only be traded by those traders who have joined the UnderGround and are of evil alignment. \r\n\r\n<p>In the example shown below, the port is a level 8 port that buys food, precious metals (pm), textiles, circuitry, and weapons (an illegal good), and it sells wood, ore, slaves (an illegal good), machinery, luxuries, and narcotics (an illegal good).  You cannot see illegal goods unless your alignment is below -99.  Being evil provides both advantages and disadvantages, such as more goods trade, thus more routes.\r\n\r\n<P><TABLE borderColor=#0b8d35 cellSpacing=0 border=1>\r\n              <TBODY>\r\n              <TR>\r\n                <TH width=150>Port</TH>\r\n                <TH width=100>Option</TH></TR>\r\n              <TR>\r\n                <TD><SMALL><A \r\n                  href=\\"javascript:alert(''This would take you to screen with information about the Thevian race.'');\\"><SPAN \r\n                  style=\\"COLOR: green\\">Thevian</SPAN></A>&nbsp;Port&nbsp;1646&nbsp;(Level&nbsp;8)</SMALL><BR><IMG \r\n                  src=\\"images/port/buy.gif\\"><IMG \r\n                  height=16 alt=Food \r\n                  src=\\"images/port/2.png\\" width=13 \r\n                  border=0><IMG height=16 alt=\\"Precious Metals\\" \r\n                  src=\\"images/port/4.png\\" width=13 \r\n                  border=0><IMG height=16 alt=Textiles \r\n                  src=\\"images/port/6.png\\" width=13 \r\n                  border=0><IMG height=16 alt=Circuitry \r\n                  src=\\"images/port/8.png\\" width=13 \r\n                  border=0><IMG height=16 alt=Weapons \r\n                  src=\\"images/port/9.png\\" width=13 \r\n                  border=0><BR><IMG \r\n                  src=\\"images/port/sell.gif\\"><IMG \r\n                  height=16 alt=Wood \r\n                  src=\\"images/port/1.png\\" width=13 \r\n                  border=0><IMG height=16 alt=Ore \r\n                  src=\\"images/port/3.png\\" width=13 \r\n                  border=0><IMG height=16 alt=Slaves \r\n                  src=\\"images/port/5.png\\" width=13 \r\n                  border=0><IMG height=16 alt=Machinery \r\n                  src=\\"images/port/7.png\\" width=13 \r\n                  border=0><IMG height=16 alt=\\"Luxury Items\\" \r\n                  src=\\"images/port/11.png\\" width=13 \r\n                  border=0><IMG height=16 alt=Narcotics \r\n                  src=\\"images/port/12.png\\" width=13 \r\n                  border=0><BR></TD>\r\n                <TD vAlign=center align=middle>\r\n                  <TABLE border=0>\r\n                    <TBODY>\r\n                    <TR>\r\n                      <FORM name=FORM action=\\"javascript:alert(\\''This would take you into the port.'');\\" method=post>\r\n                      <TD align=middle><INPUT id=InputFields type=submit value=Trade name=action></TD></FORM></TR>\r\n                    <TR>\r\n                      <FORM name=FORM action=\\"javascript:alert(\\''This would take you to a warning screen, warning of the dangers of raiding a port.'');\\" method=post>\r\n                      <TD align=middle><INPUT id=InputFields type=submit value=Raid \r\n\r\nname=action></TD></FORM></TR></TBODY></TABLE></TD></TR></TBODY></TABLE></P>\r\n\r\n<p>Trading at a port is done by clicking the TRADE button and bargaining with the port master. Tips on trading can be found elsewhere in this manual, or by asking a fellow player who has learned the ropes. \r\n\r\n<p>Ports can also be raided by clicking the RAID button, but be warned: ports are heavily defended by a dozen laser turrets and thousands of combat drones. Only a fleet of well-armed warships can hope to raid a port successfully, but the payoff is often worth the risk.'),
(18, 16, 2, 'Planets', 'Planets are located in the outer galaxies, outside racial space. They are places to park ships, stockpile trade goods, and store cash. A player may take ownership of a planet and set a password so that other players in their alliance cannot take it. Only players from the owner''s alliance may land on the planet. All others must attack and destroy a planet''s defenses before landing.  Once a planet''s defenses are destroyed, a player can land and take ownership of the planet. \r\n\r\n<p>Planets start out with no buildings at all, but with proper supplies stockpiled, construction of generators, hangars, and turrets can begin. As more buildings are constructed, the planet''s defense level increases. Planets begin at level zero and finish at level 70. \r\n\r\n<p>Generators protect a planet with shields. Each generator can hold 100 shields and planets can have a maximum of 100 generators, or 10,000 shields. \r\n\r\n<p>Hangars house and launch combat drones. Each hangar holds 20 drones and planets can have a maximum of 100 hangars, or 2,000 CDs. \r\n\r\n<p>Turrets fire heavy laser beams. Each planet can have a maximum of 10 turrets.  These laser beams do 250/250 damage.  Basically, when they fire at someone they can destroy 250 shields, or 250 armor (but not both on the same shot).\r\n\r\n<p>The construction center shows what goods are stockpiled on a planet and which ones are needed for building each type of defense. Once started, it shows when construction will be completed. Only one building can be constructed at a time. \r\n\r\n<p>Planets have a bank vault which can store cash. The vault can be accessed by anyone who is parked on the planet. The vault can also be used to bond cash. A bond cannot be touched once it has started and will pay interest on the principal amount when it comes due. \r\n\r\n<p>Planets can serve as a relatively safe place to park your ship, but no planet is 100% safe and even level 70 planets can fall to a well organized attacking fleet of warships. '),
(19, 16, 3, 'Shops', 'Shops are locations where players can buy and sell different types of items besides trade goods. Many of these items are only available at certain shops. \r\n\r\n<p><img src="images/shipdealer.gif">Ships - racial ships are available only in that race''s headquarters sector. Other types of ships can be bought at ship shops scattered across the universe. \r\n\r\n<p><img src="images/weapon_shop.gif">Weapons - there are many different shops that sell weapons. \r\n\r\n<p><img src="images/hardware.png">Combat Accessories - this shop exclusively sells mines, combat drones, and scouts \r\n\r\n<p><img src="images/hardware.png">UNO - this shop sells shields, armor, and cargo holds \r\n\r\n<p><img src="images/bar.png">Bars - these shops sell drinks and allow players to gamble and do other fun things.'),
(20, 16, 4, 'Warps', 'While you are exploring the galaxies, you will occasionally come across warps.  These will take you into a new galaxy.  They also cost 5 turns to move through them.  Warps always appear in the bottom right hand corner of the move to sector listing.\r\n<br/>\r\n\r\n<p><table border="1" bordercolor="#0b8d35" cellspacing="0"><tr><th colspan=3 align="center">Move to</th></tr><tr><td>&nbsp;</td><td align="center" width="80" height="40"><a href=""><span style="color:lime;">1549 (1)</span></a></td><td>&nbsp;</td></tr><tr><td align="center" width="80" height="40"><a href=""><span style="color:green;">1563 (1)</span></a></td><td>&nbsp;</td><td align="center" width="80" height="40"><a href=""><span style="color:green;">1565 (1)</span></a></td></tr><tr><td>&nbsp;</td><td align="center" width="80" height="40"><a href=""><span style="color:green;">1354 (1)</span></a></td><td align="center" width="80" height="40"><a href=""><span style="color:green;">1622 (5)</span></a></td></tr></table></p>'),
(21, 0, 6, 'Technology', 'In the following subsections, each of the five different racial technologies will be discussed and explained.'),
(22, 21, 1, 'Scanner', 'Scanners give pilots information about the ships and forces (mines, combat drones, and scouts) in surrounding sectors of space. The information is rather general, but it can often mean the difference between survival and a pod ride home to HQ. Scanners can be purchased and installed at any racial HQ or Advanced Communications shop. Once purchased, they are always ON. '),
(23, 21, 2, 'Cloak', 'The Cloaking device hides the pilots ships from other ships in sector with them.\r\n\r\n<br/>Being Cloaked doesn''t mean that you are truly invincible, just that anyone who is the same level as yourself or has a lower level can not attack you.\r\n<br/>A cloaked ship in a sector with out anyone else in it will show a "Shadow" in the current sector screen.\r\n<br/>Cloak is only effective against someone who is a lower level than the cloaked ship. A level 20 person can hide from a level 19 person, but be seen by a level 21 person.\r\n<br/>A cloaked person In Sector with an alliance mate that is not cloaked can still be attacked with their ally, although they can not be targetted themselves.\r\n<br/>If you enter a sector an are uncloaked, another ship in that sector can pull up an attack screen on you even if you recloak.'),
(24, 21, 3, 'Illusion Generator', 'The Illusion Generator allows players to camoflague their ships as another ship. Any other pilot doing a quick flyby will see just the Illusion Generator''s settings.\r\n\r\n<br/><p>Although this equipment allows players to pretend to be in a different ship than what they''re really in, any seasoned hunter or pilot can tell what the illusioned ship really is by doing an examine and checking the ships ratings.'),
(25, 21, 4, 'Jump Drive', 'The jump drive allows you to jump across several sectors without the need to go through each one.  It does cost 15 turns to use however, so it is best to use when you need to reach a location that is over 15 sectors away.\r\n<br/>\r\nIf the location is exactly 15 sectors away, you will land exactly where you were aiming.  If your location is over 15 sectors away, there is a random chance of a misjump...leaving you one to eight sectors away from where you were heading.\r\n<br/>\r\nYou can use the jump drive to jump from one galaxy to another, however you have a greater chance of misjump, and you can only jump through one galaxy at a time.\r\n<br/>\r\nThe jump drive is good for reaching a location that is surrounded by enemy forces, such as a Combat Accessories store.'),
(26, 21, 5, 'Drone Scrambler', 'The Drone Communications Scrambler or DCS is a device used to disrupt coordination of enemy drone attacks.\r\nThe DCS, when used against ports and planets is able to prevent 1/4 of the enemy drones from attacking your team.\r\n Against other ships, the DCS reduces drone damage by 1/4 causing them to do only 1.5 damage to your team instead of 2.\r\nThe DCS is not manually enabled/disabled like other hardware. It is automatically used at all times. It also does not require any turns to use.\r\nThe Drone Scrambler is available only at the Nijarin Hardware shop, which is usually located at the Nijarin Government HQ. The price of the DCS is 500 000 credits.'),
(27, 0, 7, 'Trading', 'The essence of trading is to buy a good at one port and sell it at another making both money and Exp for your effort. Often there is a trade off to be made if you wish to maximize both benefits (Cash and Experience). If you''re very lucky and or work hard you can have your cake and eat it too. \r\n<p>\r\nThere are three tiers of goods in SMR;<p> \r\nTier #1<br/>\r\nFood, Wood, Ore, Precious Metals and Slaves \r\n<p>\r\nTier #2<br/> \r\nTextiles, Machinery, Circuitry and Weapons \r\n<p>\r\nTier #3<br/> \r\nComputers, Luxuries and Narcotics \r\n<p>\r\nAs you trade at a port you reduce the supply of the port by the number of holds on your ship. Tier one goods start at 6000, Tier two at 5000 and Tier three at 4000. If you purchase a tier one good and the supply of a tier two good is less than max you will add 25% of you holds traded to the supple of any and all tier two goods that are not at their max. The same thing occurs when trading tier two goods, you increase the supply of tier three goods. \r\n<p>\r\nYou can not trade "evil" goods unless your alignment is less than -100 \r\nEvil goods are Slaves, Weapons and Narcotics.\r\n<p> \r\nIn order to become evil you must locate and visit Underground HQ and have an alignment of less than +99. You can then join the underground which sets your alignment at -150. \r\n<p>\r\nThe value of a given good is based on three things;<p> \r\nDistance to the closest port that SELLS the item (the further away the better).<br/> \r\nThe base value of the item (the list above is ordered from least to most valuable).<br/> \r\nThe supply/demand level of the good at the time you make the trade (the lower the supply the more you get) \r\n<p>\r\nSo if you buy some Narcotics and sell them in a port that is 10 sectors away AND there is NO other port selling Narcotics with in 10 sectors of the port you are selling at, you get 10 x value per ton plus bonuses for supply/demand level. You will get the max cash for the good if you sell the last 100 tons that port needs. Give the preceding set of facts if your driving a PSF (600 holds) you would receive in excess of 1.75 million credits for that single trade. \r\n<p>\r\nSupply/demand level of ports resets much like your per hour turn gain. In a speed factor one game a drained port will regenerate its supply/demand for goods in about 20 hours. \r\n<p>\r\nNow lets discuss your racial relations. You will notice that you can bargain with ports by offering them a little less than what they want when you buy and a little more when you are selling. There is a perfect amount based on a random factor that will give you the maximum cash and experience. The closer you get to 1000 total relations to the race who owns the port you''re trading at the smaller that difference between the port offer and the perfect number becomes and at 1000 you automatically get the best possible buy sell number. \r\n<p>\r\nTwo thing go into determining racial relations your personal number and the global relations that a race has with yours. The global relations between you and your race will always be 500 your personal will start at 0 and can go from -500 to +500. Every time you make a successful trade your personal relations increase. If you offer less than they will take or ask more then they will give your relations suffer. The amount that relations move per trade is based on the number of cargo holds on your ship the more holds you have the more you can move personal relations. Your relations with a race will also suffer if you kill a trader of that race or attack a port owned by that race. \r\n<p>\r\nGlobal relations with races other than your own will fluctuate based on the political maneuvering between the councils of those races. You can get on your races council by being one of the top 20 traders rated by experience. If combined relations are less than +300 that race will not allow you to trade with them. At the start of the game global relations between all races are set to -500 so the only place you can trade initially is at ports your race owns. \r\n<p>\r\nThe best way to maximize experience while trading is to find two ports that have a set of goods that you can buy and sell between them. IE port A sells textiles and buys computers, port B buys computers and sells textiles. The greater the distance between these two ports the more exp you can gain. It is important to understand that the multiplier for distance is based solely on the distance to the closest port trading the same good. In the above example if A and B are seven sector apart you will not get a 7x bonus if port C sells computers two sectors from port A in that case you would only get 2x bonus for that trade. \r\n<p>\r\nThe best trade ships are listed here in order based on those ships trading the same route. Notice that having more holds does not necessarily make a ship the best, the ships speed is also an important consideration. This list only takes into account maximizing exp defense, offense and cash are not considered. \r\n<p>\r\n4636 - Trade Master<br> \r\n4536 - Planetary Super Freighter<br> \r\n4276 - Inter-Stellar Trader <br>\r\n3830 - Planetary Freighter <br>\r\n3715 - Thief <br>\r\n<p>\r\nAll of the above are available to all traders except two ships, the Trade Master, which you must be Alskant to purchase, and the Thief, in which you must be evil to purchase. The numbers next to the ships are the amount of exp that ship would make per day based on trading a perfect 3x route located 3 sectors from where you park your ship.\r\n<p>\r\nThese figures (and therefore the rankings) of the ships change a bit when you take into account some more variations on route length and distance from where you park, and distance to the bank. \r\n<p>\r\nBasically, the more non trading moves you need to make each day, the better the faster ships become. Non trading moves include moving to and from fed space, or between routes, or to visit the bank. For example, if you make 20 non trading moves each day, and trade on a perfect 3* route, the PSF drops down the rankings to 4th best, behind the TM, the IST and the Thief. \r\n<p>\r\nOn a strictly trading basis, if you are making more than 16 non trading moves a day the IST is ahead of the PSF for experience earned. Of course the PSF is much more defendable, but the IST can trade anywhere in the universe quite easily, so is much less predictable.\r\n<p>\r\nOne final note:  In order to gain Federal protection at the end of your trading session, you CANNOT have any illegal goods on board.  So make sure that you have all of them sold off before heading to Fed, and make sure that you have sold off your weapons to get your attack rating down.  (See Alignment for more detail on attack ratings and Federal Protection.)<p>\r\nThe next four subsections will give a few more detailed tidbits of information to help you on your quest to be a top trader.'),
(28, 27, 1, 'Experience Levels', '<CENTER><P><TABLE bgcolor="000000" Border="1" width="60%" cellspacing="1" cellpadding="1"><TR><FONT Face="Times New Roman" Size="3"><TD>\r\n<p>\r\n\r\n\r\n<tr>\r\n<th>Rank Level</th><th>Rank Name</th><th>Required Experience</th>\r\n<tr>\r\n\r\n<tr><td><center>1</center></td><td><center>Newbie</center></td><td><center>0</center></td></tr>\r\n<tr><td><center>2</center></td><td><center>Recruit</center></td><td><center>25</center></td></tr>\r\n<tr><td><center>3</center></td><td><center>Freshman Recruit</center></td><td><center>116</center></td></tr>\r\n<tr><td><center>4</center></td><td><center>Sophomore Recruit</center></td><td><center>294</center></td></tr>\r\n<tr><td><center>5</center></td><td><center>Junior Recruit</center></td><td><center>568</center></td></tr>\r\n<tr><td><center>6</center></td><td><center>Navigator</center></td><td><center>1016</center></td></tr>\r\n<tr><td><center>7</center></td><td><center>Petty Officer</center></td><td><center>1600</center></td></tr>\r\n<tr><td><center>8</center></td><td><center>Chief Petty Officer</center></td><td><center>2360</center></td></tr>\r\n<tr><td><center>9</center></td><td><center>Master Petty Officer</center></td><td><center>3318</center></td></tr>\r\n<tr><td><center>10</center></td><td><center>Ensign</center></td><td><center>4517</center></td></tr>\r\n<tr><td><center>11</center></td><td><center>Pilot Apprentice</center></td><td><center>5962</center></td></tr>\r\n<tr><td><center>12</center></td><td><center>Pilot Graduate</center></td><td><center>7673</center></td></tr>\r\n<tr><td><center>13</center></td><td><center>Pilot</center></td><td><center>9674</center></td></tr>\r\n<tr><td><center>14</center></td><td><center>Scout</center></td><td><center>11986</center></td></tr>\r\n<tr><td><center>15</center></td><td><center>Ranger</center></td><td><center>14663</center></td></tr>\r\n<tr><td><center>16</center></td><td><center>Chief Ranger</center></td><td><center>17696</center></td></tr>\r\n<tr><td><center>17</center></td><td><center>Wing Leader</center></td><td><center>21109</center></td></tr>\r\n<tr><td><center>18</center></td><td><center>Warrant Officer</center></td><td><center>24923</center></td></tr>\r\n<tr><td><center>19</center></td><td><center>Chief Warrant Officer</center></td><td><center>29160</center></td></tr>\r\n<tr><td><center>20</center></td><td><center>Master Warrant Officer</center></td><td><center>33885</center></td></tr>\r\n<tr><td><center>21</center></td><td><center>Lieutenant Jr. Grade</center></td><td><center>39080</center></td></tr>\r\n<tr><td><center>22</center></td><td><center>Lieutenant 2nd Class</center></td><td><center>44765</center></td></tr>\r\n<tr><td><center>23</center></td><td><center>Lieutenant 1st Class</center></td><td><center>50964</center></td></tr>\r\n<tr><td><center>24</center></td><td><center>Lieutenant Commander</center></td><td><center>57698</center></td></tr>\r\n<tr><td><center>25</center></td><td><center>Commander</center></td><td><center>65045</center></td></tr>\r\n<tr><td><center>26</center></td><td><center>Staff Commander</center></td><td><center>72972</center></td></tr>\r\n<tr><td><center>27</center></td><td><center>Senior Commander</center></td><td><center>81503</center></td></tr>\r\n<tr><td><center>28</center></td><td><center>Force Commander</center></td><td><center>90659</center></td></tr>\r\n<tr><td><center>29</center></td><td><center>Jr. Executive Officer</center></td><td><center>100462</center></td></tr>\r\n<tr><td><center>30</center></td><td><center>Executive Officer</center></td><td><center>111001</center></td></tr>\r\n<tr><td><center>31</center></td><td><center>Explorer Captain</center></td><td><center>122234</center></td></tr>\r\n<tr><td><center>32</center></td><td><center>Privateer Captain</center></td><td><center>134181</center></td></tr>\r\n<tr><td><center>33</center></td><td><center>Special Forces Captain</center></td><td><center>146865</center></td></tr>\r\n<tr><td><center>34</center></td><td><center>Military Captain</center></td><td><center>160310</center></td></tr>\r\n<tr><td><center>35</center></td><td><center>Flagship Captain</center></td><td><center>174615</center></td></tr>\r\n<tr><td><center>36</center></td><td><center>Lt. Commodore</center></td><td><center>189724</center></td></tr>\r\n<tr><td><center>37</center></td><td><center>Commodore</center></td><td><center>205660</center></td></tr>\r\n<tr><td><center>38</center></td><td><center>Vice Admiral, 3 stars</center></td><td><center>222450</center></td></tr>\r\n<tr><td><center>39</center></td><td><center>Vice Admiral, 4 stars</center></td><td><center>240104</center></td></tr>\r\n<tr><td><center>40</center></td><td><center>Vice Admiral, 5 stars</center></td><td><center>258745</center></td></tr>\r\n<tr><td><center>41</center></td><td><center>Admiral</center></td><td><center>278304</center></td></tr>\r\n<tr><td><center>42</center></td><td><center>Fleet Admiral</center></td><td><center>298801</center></td></tr>\r\n<tr><td><center>43</center></td><td><center>Grand Admiral</center></td><td><center>320260</center></td></tr>\r\n<tr><td><center>44</center></td><td><center>Galaxy Admiral</center></td><td><center>342700</center></td></tr>\r\n<tr><td><center>45</center></td><td><center>Universal Admiral</center></td><td><center>366255</center></td></tr>\r\n<tr><td><center>46</center></td><td><center>Hero</center></td><td><center>390330</center></td></tr>\r\n<tr><td><center>47</center></td><td><center>Local Legend</center></td><td><center>416463</center></td></tr>\r\n<tr><td><center>48</center></td><td><center>Galactic Legend</center></td><td><center>443167</center></td></tr>\r\n<tr><td><center>49</center></td><td><center>Universal Legend</center></td><td><center>470970</center></td></tr>\r\n<tr><td><center>50</center></td><td><center>Spooky</center></td><td><center>500000</center></td></tr>\r\n<p>\r\n</td></font></tr></table></center>'),
(29, 27, 2, 'Difference in Levels', '<b>Advantages</b>\r\n<p>\r\nOne of the advantages to gaining experience is that as you gain rank your weapons get closer to the maximum accuracy that is listed when you buy the weapon.\r\n\r\nAnother example of an advantage to experience is if you have a ship that has a Cloaking Device then anyone that is a lower rank than you will not be able to see you. \r\n\r\nAlso if you have a Jump Drive then you will be able to jump closer to the sector that you program into the Jump Computer.\r\nSo the higher the experience the better your ship will do.\r\n<p>\r\n<b>Disadvantages</b>\r\n<p>\r\nOne of the main disadvantages to low experience is that your weapons are less accurate. \r\n\r\nFor example: You might miss a crucial shot when attacking someone of a higher level and they make that crucial shot, because of their higher experience.\r\n\r\nFor those with a Cloaking Device; those people that have a higher rank than you would be able to see you and might decide to attack.\r\n\r\nFor those with Jump Drive; you will have to travel through more space to get to where you were going.\r\n\r\n<p>\r\nTo figure out how accurate your weapons will be based off of your experience level, use this simple formula:\r\n<p>\r\nThe accuracy of weapons is (Base Accuracy + Level) - DefendersLevel/2\r\n<p>\r\nFor example: If Sterling at level 25 goes to pod Nariis at level 20, his LJTL will have an accuracy of 89%\r\n\r\n'),
(30, 27, 3, 'Staying Alive', 'Most people know how to trade but the problem is staying alive while doing so. Some easy steps to staying alive while trading. \r\n<p>\r\n1. Do not trade a route with scouts on it while the owner of the scouts is online.\r\n <p>\r\n2. Do not think that destroying a scout on your route will make it safe to trade.\r\n Because the owner will know this is your route and will make a point of trying to pod you because you destroyed his scout.\r\n <p>\r\n3. Vary your trading times. Trade at 12:00 AM one day and 3 AM the next. It will make hunting you MUCH harder because hunters do not know what times to be online to catch you trading. \r\n<p>\r\n4. Have scouts on and around your route. The hunting tactic most hunters use is sitting one outside of your route and wait for you to come in. If you have a scout outside it you will know the hunter is coming. \r\n<p>\r\nAlso, remember to pick up your scouts when you are done trading.  To not do so just announces that this is your route, so more people will come looking to catch you.\r\n<p>\r\n5. 1 mine on your route could mean life or death to you. It means the hunter has to load 2 extra screens before he can pull a attack button on you.\r\n<p>\r\nRemember to pick up mines when you are done trading as well.\r\n <p>\r\n6. Don''t piss people off. That just makes people want to hunt you.\r\n<p>\r\n7. Don''t trade when there are a lot of known hunters online.  If you are in an alliance and you are at war with another alliance, it is a good idea to trade when members of that other alliance are not online.  '),
(31, 27, 4, 'General Tips', '1. Never ever trade in a route with a scout drone in it, if the drone''s owner is online. Some guys use them for specific targets, others use them to find easy kills. A good habit to get into is check the current player list every couple of minutes to make sure a potential threat comes online. If the owner is not on, trade there only as a last resort, there are a lot of trade routes out there. \r\n<p>\r\n2. If your ship can hold drones, ALWAYS carry at least one drone. It may not seem like a lot but it may be the difference in the attacker having to hit you once or multiple times. If you are zipping through your trade routine you might just be able to run. \r\n<p>\r\n3. Don''t do anything else while you are trading, don''t send messages, read messages, read news, check the webboard, etc. It is good to have at least one other browser open to check the current players while you trade. \r\n<p>\r\n4. Before you start you days trading, read the news, if there has been a lot of action in or around your trade rounds within the past couple of hours, either wait or use a different route. Hunters usually have a set pattern that they check for kills on routes. The make their "rounds" so to speak just hoping to find a nice juicy kill. If you avoid heavy traffic areas, you''ll be that much less likely to be put in a pod. \r\n<p>\r\n5. Under no circumstances should you EVER run out of turns while unprotected, my policy is to save at least 15 turns extra(in a PSF) in case of an emergency where I have to flee my route. If you logged out unprotected and you aren''t number 1 in the game, go ahead and kiss your ship good bye. Never take anything for granted, like thinking "oh I''ll be safe here for tonight, no-one ever comes around here" wishful thinking. \r\n<p>\r\n6. Set yourself up scout drones a few sectors around your trade lanes(not in them), if while your trading you get a message, don''t read it, run! So what if you waste a few turns, your alive, in your ship, with your cash, consider yourself lucky. \r\n<p>\r\n7. Use mines to your advantage, by this I mean only have them planted in a route while you are there trading, never when your not using the route. You can piss people off if they are just passing through and they run into a minefield, there goes one more person you have to look out for. \r\n<p>\r\n8. Get a cloakable ship, even if you only have a few thousand exp. that''s a whole lot less people you have to look out for. Again, never assume your invincible(unless your number 1, then this advice probably isn''t any good to you anyhow :)), I was number 2 in Arms Race, I got cocky and didn''t think the one person who could see me would find me, I was wrong and I paid the price (Thanks Warrior, I learned a lot from that). The cloak is powerful, but even it has a weakness. \r\n<p>\r\n9. Vary your trade lanes and times, skilled hunters will study when and where you trade, if you make it random it will be a lot harder for them to calculate where you will be the next day. Always keep them guessing, that''s why you should always have a few back up route''s you can use, so what if they don''t trade anything except low level goods, they will upgrade eventually (quicker than most think), if your "main" port is drained, you have someplace to fall back on. \r\n<p>\r\n10. Last but not least, use your head. If your trading on a route with a handful of level 9 ports that all compliment each other, expect hunters to find you and find you often, we look for stuff like that. Why bother wasting turns checking the small ports a bunch of sectors away from fed when all the traders we can kill trade right outside of fed space. \r\n'),
(32, 0, 8, 'Federal Protection', 'Now then, just because you are in a Fed zone does not mean you are automatically protected. The Federation will not protect any traders that have illegal goods on board their ship. So if you trade in slave, weapons, or narcotics, make sure your holds are empty of these items before logging off in Fed. \r\n<p>\r\nAnother overlooked cause of death in Fed zones is your attack rating being higher than what your alignment will allow protection for. Here is a simple chart that outlines what your attack rating can be for each level of alignment: \r\n<p>\r\nIf your alignment is +300 or higher, your attack rating can be 5/ in Fed Space.<br> \r\nIf your alignment is between +150 to +299, your attack rating can be 4/. <br>\r\nIf your alignment is between -149 to +149, your attack rating can be 3/. <br>\r\nIf your alignment is between -150 to -299, your attack rating can be 2/. <br>\r\nIf your alignment is -300 or lower, your attack rating can be 1/. \r\n<p>\r\nAs you can tell, traders that have deputized are able to keep more weapons on board, while those traders that have joined the underground must sell off the majority of their weapons in ORDER to take advantage of Federal Protection. If you are in doubt as to whether or not you are protected, before logging out of the game, click on ''Trader'' on the left side of your screen and CHECK your status. If you are protected, you will see a message letting you know that you are under Federal Protection. If not, double CHECK for illegal goods or too many weapons on your ship. \r\n'),
(33, 0, 9, 'Alignment', 'Alignment is a key 	factor in this game. When you begin the game, you start with zero alignment. You can click 	on your race''s Headquarters and choose to "become a Deputy". In doing this, you gain 150 	alignment. You need at least 150 alignment to buy Federal ships, or -150 to purchase Underground ships. Your alignment 	will go up and down throughout the game and can be difficult to maintain a positive 	alignment. Some players Opt to "Go Evil". Going evil requires a negative alignment of 	atleast -150. Some players will tell you that going evil is more difficult of a task, as 	when you are trading illegal goods you take the chance of being caught. When you get caught, 	depending on how many holds of what item you have, it costs you money. Your alignment goes 	down further and faster this way, but requires a lot of money to do so.\r\n<p>\r\nAlignment also plays a role in how many weapons you may safely have on your ship and still have Federal protection.  Below is a simple chart to help you determine whether or not you will be protected in Fed space.\r\n<p>\r\nIf your alignment is between +750 or higher, your attack rating can be 8/ in Fed Space.<br/>\r\nIf your alignment is between +600 to +749, your attack rating can be 7/ in Fed Space.<br/>\r\nIf your alignment is between +450 to +599, your attack rating can be 6/ in Fed Space.<br/>\r\nIf your alignment is between +300 to +449, your attack rating can be 5/ in Fed Space.<br/>\r\nIf your alignment is between +150 to +299, your attack rating can be 4/.<br/>\r\nIf your alignment is between -149 to +149, your attack rating can be 3/.<br/>\r\nIf your alignment is between -150 to -299, your attack rating can be 2/.<br/>\r\nIf your alignment is is between -300 to -449, your attack rating can be 1/ in Fed Space.<br/>\r\nIf your alignment is -300 or lower, your attack rating can be 0/.<br/>\r\nBut remember, you can NEVER be protected in fed space while you have illegal goods in your cargo holds'),
(34, 0, 10, 'Forces', 'Using forces is easy. To lay forces you''ve bought in a sector, go to \r\nthe Force Management screen and enter the number of mines or drones you \r\nwant \r\nto leave in the text box next to the kind of force you want to drop in \r\nthe \r\nsector. Then click the "Drop/Take" button and the number of forces you \r\nentered is left active in the sector. If you just want to lay one force \r\nat a \r\ntime , there is an "[x]" beside every type of force that, when clicked, \r\ndrops one force of the selected kind in your current sector.  To pick \r\nup \r\nforces you previously laid, go to the force management page, edit the \r\nnumbers to fit your needs, and press the "Drop/Take" button.\r\n\r\n<p>\r\nYou can also refresh your own and your alliance''s forces that are in sector to keep them from expiring.  To do this, all you need to do is just click on the ''refresh'' link on the current sector screen for the forces you wish to refresh.\r\n<p>\r\nThere are three differents forces employed by ships to attack, defend, and warn.  Mines, Combat Drones, and Scout Drones.  Each will be covered here to give you a better idea of how to put them to good use.'),
(35, 34, 1, 'Mines', 'Mines are used in Space Merchant Realms to slow down opponents, to do damage to their ships, and to drain turns. Dealing with enemy mines is one of the biggest challenges facing new players. Understanding the basic rules of how mines work and knowing what to expect when you hit mines will help you to stay safe and keep your ship from being destroyed if you stray into an enemy minefield.'),
(58, 35, 1, 'Basic Rules of Mines', 'When a ship enters a sector that contains mines, the ship will hit the mines and an attack screen will be shown to that player as his guns automatically fire on the mines. The attack screen shows the number of mines that hit the ship, the damage done to the ship and the amount of damage the ship''s guns did to the mines. The player who hit the mines must click the Current Sector link to view the sector again and see what is there and continue play. \r\n\r\n<p>Sometimes no mines will hit a ship entering a mined sector, but the player who enters the sector will ALWAYS get an attack screen as his guns fire on the mine(s). \r\n\r\n<p>Hitting mines in a sector costs 3 turns. \r\n\r\n<p>Hitting mines will stop your plotted course if you were following your plot computer and you will have to re-plot to your destination. \r\n\r\n<p>Hitting mines will uncloak a cloaked ship. \r\n\r\n<p>Each mine that hits will do 20 damage.\r\n\r\n<p>Federal ships take half damage from mines (10 damage). \r\n\r\n<p>If you run into mines (you enter a sector and they attack), and you have shields, the mines will always stop when you no longer have shields.\r\n\r\n<p>If you attack mines (you are already in sector, and instead of moving, you fire on the mines), and you have shields left, the damage from mines will wrap around and do armor damage too.\r\n\r\n<p>You can exit a mined sector safely using the bright <b><span style="color:lime;">Green</span></b> exit. '),
(59, 35, 2, 'What to Do When You Hit Mines', 'When you hit mines and get the attack screen, click Current Sector to see what or who is in sector with you. Often times as you fly around in your ship, you hit a single mine left behind by a trader or hunter who was recently in the area. When you look at the current sector, there are no mines left because you just shot them or they crashed against your hull. So you can then continue on to your destination. \r\n\r\n<p>When you enter a sector with a large number of mines, your guns will destroy some of them, some will blow up on your ship, and the rest will be left sitting in the sector untouched. The current sector view will reveal the mines that are left and you will see your choices of exits. Back out of a mined sector the way you came in using the bright <b><span style="color:lime;">GREEN</span></b> exit, and you will move safely. If you try going out using a non-green exit, your ship does not move and you hit the remaining mines in the sector. This can be useful, however, because using the non-green exit "clears the way" in that direction and that exit then becomes bright green and thus safe to move toward. If your ship can withstand the damage of doing this you can pass through a mined sector in this way. \r\n\r\n<p>Remember: the <b><span style="color:lime;">Green</span></b> exit is always the safe way out of a mined sector, and clicking a non-green exit turns that exit into the new Green exit.'),
(36, 34, 2, 'Combat Drones', 'Combat Drones are very useful as they serve as both defense and offense.  \r\n<p>\r\nWhen Combat Drones are on your ship, they are basically robot fighters that \r\nattack enemy ships in combat and will defend your ship in an attack.  In \r\ncombat, a random number of Combat Drones launch from your ship and do \r\ndamage to your opponent. When an enemy ship returns fire it may destroy some \r\nof your drones.  Drones will also attack mines if you run into them. \r\n<p>\r\nWhen laid in a sector, Combat Drones work much like mines do, but don''t \r\nautomatically explode when an enemy enters the sector. However if you attack any forces in a sector,\r\n they will do 20 damage each to your ship.\r\n<p>\r\nYou can drop a maximum of 50 combat drones per player in a sector.'),
(37, 34, 3, 'Scout Drones', 'Scout drones are passive sensors that, when dropped in a sector, alert you to the comings and goings of all ships in that sector.  You can place a maximum of five scout drones in a single sector.  While they will do no damage to a ship entering or leaving the sector, they will do 20 damage each if attacked.'),
(38, 0, 11, 'Alliances', 'Now many people will say don''t make your own alliance. This is a good tip. To be a leader you need to be able to support and teach your alliance.  Being new to the game, you may not be able to do this and can lead to a bad ending. So find an alliance that already has a strong base and stronger players to support you. Though, a warning to the wise: do not send global messages to everyone in-game.  This only annoys people and often leads to your death. Send the leader of the alliance, or even better, a few alliances and await their response. \r\nIf a leader does not respond, don''t continue to message them as they can''t be bothered to reply so the alliance may not be worth joining anyway. Joining a smaller alliance may be a good ticket to learning the game and the way things are done before taking on larger tasks such as powerful alliances.  While you are not required to join an alliance, it is usually a good idea so that you can learn the teamwork and skills required to succeed in the game.'),
(40, 0, 12, 'Fighting', 'Fighting is one of the most adrenaline-filled events you can find in SMR. While the most intense fighting can be found against another online ship, joining with other people attacking planets and ports also carry their own thrill. Read on to learn more about the similarities and differences of fighting in SMR. '),
(41, 40, 1, 'Ship to Ship', 'Probably the most exciting part of SMR. Many say the thrill of the kill is unmatched. Once you get your first kill you will probably feel you have gone past your first mile stone. <br/>\r\n<br/>\r\nNow you see traders (other players) as time goes on, and the newbie turns will disappear and the news will be full of pods and Port Raids and you''ll be wondering how to do it. When you examine the trader, the next screen will say under newbie protection and others will have a button that says ''Attack Trader(3)'' <br/>\r\n<br/>\r\nIf you decide to attack, the next screen will give you attack results. When you attack, watch carefully and know how much damage your ship can dish out and how much it can take. If you''re attacking another hunting or fighting ship, you will need to be careful to make sure you don''t die before you kill them. <br/>\r\n<br/>\r\nIf you have destroyed your target (or, alternatively, if they kill you), you will receive a message from them. You will give and take pods if you play SMR for a decent amount of time, so don''t get down if you end up dying - it happens to everyone. '),
(42, 40, 2, 'Planets', 'Now, to discuss one of SMR''s most famous aspects: The Planet Bust <br/><br/>\r\nPut simply, a planet bust (or PB), is most often carried out by a relatively large group of ships from a single alliance. This helps spread out the damage that the planet deals, in addition to doing more damage to the planet. In order for a planet bust to be successful, the attacking team must destroy all shields and drones on the planet, allowing one of the members to land and claim the planet for their alliance. <br/>\r\n<br/>\r\nMost alliances will agree that it is best to nominate one player as the ''trigger'', that is, the one person who is allowed to actually hit the attack button for the planet. Once the trigger presses the attack button, ALL ships that are in sector and in the alliance will fire on the planet, and the planet will attack back at all ships, although some of the ships may not take damage. The following is the initial attack screen you see for a planet when you hit ''examine'': <br/>\r\n<br/>\r\nEXAMINE PLANET <br/>\r\nPlanet Name:Don''t Shoot!<br/> \r\nLevel:41.00 <br/>\r\nOwner:Lazy Trader <br/>\r\nAlliance:The Good Guys <br/>\r\n<br/>\r\nPlanet Results <br/>\r\n<br/>\r\nThe planet fires a turret at N and misses <br/>\r\n\r\nThe planet fires a turret at N and destroys 250 shields <br/>\r\n\r\nThe planet fires a turret at N and destroys 250 shields <br/>\r\nThe planet fires a turret at N and destroys 250 shields <br/>\r\n\r\nThe planet fires a turret at N and destroys 75 shields <br/>\r\nThe planet fires a turret at N and destroys 250 armor <br/>\r\nThe planet fires a turret at N and destroys 250 armor <br/>\r\nThe planet fires a turret at N and destroys 250 armor <br/>\r\nThe planet fires a turret at N and destroys 75 armor <br/>\r\nN has been DESTROYED by planetary forces <br/>\r\nThe planet fires a turret at N and misses <br/>\r\nPlanetary drones launch at N <br/>\r\nThis planet does a total of 1650 damage in this round of combat. <br/>\r\n<br/>\r\nAttacker Results <br/>\r\n<br/>\r\nN fires a Holy Hand Grenade and misses. <br/>\r\nN fires a Salvene EM Flux Cannon and misses. <br/>\r\nN fires a Salvene EM Flux Cannon and misses. <br/>\r\nN fires a Salvene EM Flux Cannon destroying 20 planetary shields. <br/>\r\nN fires a Big Momma Torpedo Launcher destroying 2 planetary drones. <br/>\r\nN fires a Big Momma Torpedo Launcher and misses. <br/>\r\nN does a total of 26 damage. <br/>\r\n\r\n<br/>\r\nThis team does a total of 26 damage in this round of combat. <br/>\r\n<br/>\r\nAs you can see from this attack result screen, the solo attacker was quickly podded by the planetary defenses. If there had been more people attacking, each of their attacks would have shown up on this attack result page, as well as the damage they each dealt and received. The more people an alliance has participating in a planet bust, the less likely the chances are that a ship will be podded outright on one attack. Although there have been occurances where a planet has unluckily targeted all turrets on one of the attackers, resulting in a pod. \r\n'),
(43, 40, 3, 'Ports', 'The Port Raid is one of SMR&#8217;s most coveted and enjoyable features. It requires a bit of patience and is better to do with alliance mates. People attack ports for many reasons, a common reason is to raise or lower their alignment.  To raise your alignment, one must successfully raid an enemy port, meaning that your race must be at war with the port&#8217;s race. To lower your alignment, you must attack a friendly port (e.g. attack your own race&#8217;s ports). The final reason is to improve upon or to destroy a trade route or to claim money that has been traded in the port.<br/>\r\n <br/>\r\nBy degrading the port, the ports nearby will raise their purchasing prices on goods that are no longer easily accessible. So if the port sold machinery and your raid caused it to downgrade, meaning it lost its ability to sell machinery, then the ports in its vicinity, which purchase machinery, will buy machinery for a higher price. You will also gain more experience points from the new trade route. But do not forget, raiding a port is very dangerous. Be well prepared.<br/>\r\n<br/>\r\n \r\nNote: It is most unwise to raid a port above level 3 alone. Try to always raid with friends. <br/><br/>\r\nWhen you enter a sector with a port you will notice there are two buttons. <br/>\r\n1) Trade <br/>\r\n2) Raid <br/><br/>\r\nNote: If your race is at war with the race of the port in question you will only see the &#8220;Raid&#8221; button. Also note that it is wise to be in a ship equipped for battle. Do not attack a port if you are in a trade ship. <br/><br/>\r\nNow as you click the raid button you will see the following: <br/><br/>\r\nWARNING WARNING port assault about to commence!! <br/>\r\nAre you sure you want to attack this port? <br/><br/>\r\nIf you have a scanner you will also see the following (this is the scan of a level 1 port): <br/><br/>\r\nYour scanners detect that there are 2000 shields, 200 combat drones, and 2000 plates of armor. <br/><br/>\r\nPorts have 15 turrets, each doing 250 shield damage and 250 armour damage. The accuracy of the port depends on its level and there are 9 levels. The accuracy of the port turrets rise by 10% depending on its level so the amount of damage you receive can be slightly random. Ports also have different amounts of shields, combat drones, and armor. They differ through each port level. <br/><br/>\r\nYou will still have the option to back out after hitting raid but after pressing yes you will do your initial attack. Raiding a port takes 3 turns per shot, the same amount of turns for attacking a trader or planet. You also hit for the full amount of damage specified on your weapons, not a fraction of the damage like when you attack a trader or a planet. After your initial attack you will have a limited time period to raid it before it replenishes its defenses. Remember it takes more than one attack to raid a port. <br/><br/>\r\nYou must destroy all the ports shields, armour, and drones before you can loot it. \r\nWhen you fire your initial shot you will see something like this: <br/><br/>\r\nThe port fires a turret at Raider and misses <br/>\r\nThe port fires a turret at Raider and misses <br/>\r\nThe port fires a turret at Raider and misses <br/>\r\nThe port fires a turret at Raider and misses <br/>\r\nThe port fires a turret at Raider and misses <br/>\r\nThe port fires a turret at Raider and misses <br/>\r\nThe port fires a turret at Raider and misses <br/>\r\nThe port fires a turret at Raider and misses <br/>\r\nThe port fires a turret at Raider and misses <br/>\r\nThe port fires a turret at Raider and misses <br/>\r\nThe port fires a turret at Raider and misses <br/>\r\nThe port fires a turret at Raider and misses <br/>\r\nThe port fires a turret at Raider and misses <br/>\r\nThe port fires a turret at Raider and misses <br/>\r\nThe port fires a turret at Raider and misses <br/>\r\nThe port launches 200 drones at Raider and destroys 150 shields. <br/>\r\nThis port does a total of 150 damage in this round of combat. <br/><br/>\r\nNote: With higher experience the less are the chances of the port hitting you. In this case the port missed altogether. Do not expect that to happen! You will likely get hit badly. It is best to be with your fellow alliance mates when attacking a port so the damage is distributed evenly. While raiding the port you may notice that you are taking a vast amount of damage, the remedy for this is to go an &#8220;uno hardware shop&#8221; and replenish your shields (and armor if it too has been destroyed). While raiding a port higher than level 1 you may downgrade it. Downgrading causes the port to level down a level and to lose one trading good. <br/><br/>\r\nIf you successfully destroy the port&#8217;s defences you will see the following options: <br/>\r\n1) Claim this port for your race <br/>\r\n2) Loot the port <br/><br/>\r\nNote: Claiming the port for your race still allows you to loot it also. Also beware of hunters; place a scout in the sector and possibly a mine (if you can). \r\nLooting the port allows you to loot the port, but without having to claim it \r\n<br/><br/>\r\nNote: The attacker who fires the final shot will always receive the money from the port&#8217;s treasury reguardless of whether you loot the port or not. \r\n'),
(44, 0, 13, 'Life After Death', 'You were merrily trading back and forth, unaware that a hunter lurked off your port bow.  Suddenly, you experienced the gut-wrenching thrust of your escape pod taking you back to Federal Headquarters.  Your mind reels.  Your life, all your money, and that precious hoard of Tivrenian gemstones....gone in an instant.   For a while, the thought of quitting dominates your thinking.\r\n<p>\r\nFear not!  Even though taking a pod, and losing everything you had, is a large blow -- not all hope is lost.  While being a member of an alliance would be better, since you could request help from your alliancemates to get back into a ship, each race has a free racial ship.  It''s not the best of ships, only capable of thirty holds, but it''s a start.  \r\n<p>\r\nYour escape pod only has 5 cargo holds, so as hard as it may seem, you''ll have to trade with those five holds until you earn enough to buy more holds.   \r\n<p>\r\nJoin in the chat, and ask around quietly to see if someone would be willing to help you out.  Be polite about it and don''t storm around if someone tells you no.  While there are a lot of players that will help you out, there are a lot more who won''t.  Another tactic is to politely talk to the person who podded you.  Ask them what you did wrong, how to improve, and if they would be willing to give you a little cash back to help you out again.  Again, there are several players who will be willing, and more who are not.  \r\n<p>\r\nAs a final hint, remember to go to the bank often, put your money in your personal account.  This will keep your cash safe in case you take another pod.\r\n'),
(46, 0, 14, 'Further Explanation on Weapon Damage and Power Ratings', 'One of the most, if not the most important factor in combat or even self-defense is weapon choice. Each weapon in the game has its own unique properties but they all follow the same general rules. \r\n\r\n\r\n<p>\r\nShield Damage: This is how much damage the weapon will do to the targets shields if the weapon hits. \r\n<p>\r\n\r\n\r\nArmor Damage: Damage that will be done to the targets armor if the weapon hits. \r\n<p>\r\n\r\n\r\nAccuracy: The general probability of the weapon hitting the target. But a weapons accuracy is only one of many factors that determine if a weapon hits or not. \r\n<p>\r\n\r\n\r\nRace: Each weapon has a patron race, or is neutral. Neutral weapons can be bought by anyone regardless of race or alignment with 2 exceptions. The holy hand grenade can only be bought by &#8220;good&#8221; players and the Nuke can only be purchased by &#8220;evil&#8221; players. (See the section on alignment). Weapons made by a specific race can be bought by players of that race or players whose relations with that race are above 500. \r\n<p>\r\n\r\n\r\nPower Level: Each weapon is rated at a certain power, a ship is limited to how many of a certain power level it may have, all ships must follow these guidelines:\r\n<p>\r\n1 - Level 5 Weapon<br>\r\n\r\n2 &#8211; Level 4 Weapons\r\n<br>\r\n3 &#8211; Level 3 Weapons\r\n<br>\r\n4 &#8211; Level 2 Weapons\r\n<br>\r\nUnlimited Level 1 Weapons \r\n<br>\r\n\r\n<p>\r\nCost: This is how much cash you will need to have on hand to buy the weapon \r\n<p>\r\n\r\n\r\n***Note: Power level is not always the same as the attack rating it gives.\r\n'),
(47, 0, 15, 'Politics and the Ruling Council', 'The politics and ruling council are often the most overlooked aspect of SMR.  Both play a crucial role though, determining which races you can trade with, and which racial weapons you can use.\r\n<p>\r\nEach racial ruling council is made up of the top twenty members of \r\nthat race, ranked by experience.  The President of each race is \r\ndetermined by alignment and experience.  To become president, \r\nyou must have the highest experience out of all the council members\r\n with alignment over 150.  \r\nIn the case of two councilmembers having the same experience, \r\nthe one who has the higher alignment will become president.\r\n<p>\r\nYou can access your race''s ruling council by clicking on the trader link on your current sector screen, then clicking on politics.  You will be able to view who is on your ruling council, and if you are a member, you will be able to cast your vote for peace/war with other races.  The president of the council is the only one who can put up a vote or take a vote down.'),
(48, 0, 17, 'Congratulations', 'Whew!  That was a lot of information to go through, wasn''t it?  Don''t be dismayed or overwhelmed by the amount of information that it takes to play effectively.  It takes time and patience to hone your skills.  \r\n<p>\r\nJust remember:  Everyone was once at the point you are at now.  Don''t judge your own rate of progress against someone else''s progress.  Every player moves, and learns, at their own pace.  Please don''t hesitate to ask someone for help if you have a question.  Most players are willing to help you if you are kind and courteous in your questions.  \r\n<p>\r\nThank you for playing Space Merchant Realms, and we on the admin staff hope you spread the word to your friends!'),
(49, 16, 5, 'Headquarters', 'This is a Racial Headquarters Location.  This is where you start out in the game.  Here, you can deputize and collect bounties/war payments.  Spend a few minutes exploring what is available in your Racial Government.  The next area is the Federal Beacon.  Provided that you have met the requirements for protection, no one else will be able to pod your ship at this location.  Next up is your racial ship shop.  Note that you can only buy racial ships of the race you have chosen.  Last but not least is a hardware shop.  Here you can buy your shields, armor, cargo holds, scanner, and racial technology (if supported by your current ship).\r\n<p>\r\n<center><table border="1" bordercolor="#0b8d35" cellspacing="0"><tr> \r\n<th>Location</th> <th>Option</th></tr><tr><form name="FORM" method="POST" \r\naction=""><input type="hidden" name="sn" \r\nvalue="5722ba9323386263fa1e36a019103c19"><td width="250">\r\n<img src="images/government.gif">&nbsp;WQ Human HQ</td>\r\n<td align="center" valign="middle"><input type="submit" name="action" value="Examine"\r\n id="InputFields"></td></form></tr><tr><form name="FORM" method="POST"\r\n action=""><input type="hidden" name="sn"\r\n value="5ffa13599846e2e4baf045279585a370"><td width="250">\r\n<img src="images/beacon.gif">&nbsp;Beacon of Ultimate Protection</td>\r\n<td align="center" valign="middle">&nbsp;</td></form></tr>\r\n<tr><form name="FORM" method="POST" action="">\r\n<input type="hidden" name="sn" value="8fc7b67bf8d6ff6be441bf6cf2403a8e">\r\n<td width="250"><img src="images/shipdealer.gif">&nbsp;WQ Human Ship Dealer</td>\r\n<td align="center" valign="middle"><input type="submit" name="action" value="Examine" \r\nid="InputFields"></td></form></tr><tr><form name="FORM" method="POST" \r\naction=""><input type="hidden" name="sn" \r\nvalue="0926d4bfa66b0d7bdd4c43adfabba6fd"><td width="250">\r\n<img src="images/hardware.png">&nbsp;West-Quadrant Hardware</td>\r\n<td align="center" valign="middle"><input type="submit" name="action" value="Examine" \r\nid="InputFields"></td></form></tr></table></p><p></center>'),
(50, 0, 16, 'Hunting', 'There are several ways to hunt. You will have to try one or all to see what you like: \r\n<p>\r\n1. Scouts, scouts, and more scouts. Drop scouts everywhere and find out where people trade, get a bearing on what times they trade, then kill them whilst they are trading. \r\n<br>\r\n2. Scouts around major locations and banks. Try and catch someone banking their cash, or arming up a new ship, etc. I would say the bank is a decent place to get kills in newbies. This is especially effective with proper claok useage or IG useage. Who really examines all the 0/1 pods sitting at a bank? \r\n<br>\r\n3. Learn to use mines effectively. Trap people in mines then finish the job. This is not really a specific method per se, you can use this around ports or banks or however you want. \r\n<br>\r\n4. If you are good with your SMC useage, you can tell where people are trading by the experience they get. Then you do not have to drop scouts, you can just go try and kill them. Sometimes dropping scouts tips people off to the fact that you are hunting. I know vets that use both ways quite effectively. I prefer the no scouts, it makes me stealthy personally. \r\n<p>\r\nThen there are the non-hunting ways to get kills... \r\n<p>\r\n1. Planet bust to find logged off players. This requires alliance teamwork, and sometimes everyone but you gets a kill  \r\n<br>\r\n2. PR a level 1 port and uno real fast and see if anyone comes to try and kill you. \r\n<p>\r\nThere are several other ways out there that are either more complex variations of these basic methods, or entirely different (how is that for vague  If you are working with a team, you can try all kinds of funky things. Good luck, see you in vet soon I''m sure.\r\n<p>\r\nAll good strategies. It is recommended that you find an experienced wing man to tell you how to hunt in the beginning. \r\n<p>\r\nThere is one thing though: Lots of people do the IG/bank thing. As far as it is known, the last to arrive in the sector will in top. That is why it is a good idea to check the top 2-3 when you bank. But it has been seen to work so it might for you too.\r\n<p>\r\nJust wanted to add a bit about choice of ship/weapons: \r\n<p>\r\nIf scout hunting: use a jump ship (FW is great - HBC next choice) \r\n<p>\r\nIf you use mines, you should choose an armor-heavy weapons setup since your mines will take out some/most of your targets shields. \r\n<p>\r\nSo you died, a little upset along the way, makes for a better hunter, analyze how these other people trapped you, see what mistakes you made (if any), you can still die without making a mistake, just the luck of the draw (pardon the pun). \r\n<p>\r\nDon''t despair, you''ll learn more from your failures, ask yourself some questions, did I enter the sector to quickly and was seen, didn''t I drop my mines fast enough, should I have fired first before dropping mines.. \r\n<p>\r\nNext pick a ship and try out a strategy (see if you like it), you don''t need anything really expensive, 6 mill gets you 2 of the most effective ships in the game, for early hunting, the Carapace and HBC, with these ships you use a mining stategy like Shesmu just described, they have scouts, so you''ll know where everybody is.... \r\n<p>\r\nLearn to use scanner and local map and look around whilst dropping scouts, you''ll be surprised in a NG how many people will try to hide in a Dead End sector.... (bet you could get 5 kills that way alone) \r\n<p>\r\n1 more thing, perhaps your weapon set up is wrong, sometimes all the biggest weapons do you more harm than good, if you have a low xp, combining that with a low accuracy weapon is almost fatal... try a higher accuracy weapon with a lower damage. \r\n<p>\r\n'),
(51, 0, 18, 'Game Stats', 'Here you can see what stats for ships, weapons, and shops that are used. Just click the sub categories.'),
(52, 51, 1, 'Shiplist', 'The current ship list can be found at: <h1><a href="ship_list.php">http://www.smrealms.de/ship_list.php</a></h1>\r\n<br>Some of its features are:\r\n<li>Sortable\r\n\r\n<p>A currently better, and more up-to-date ship list can be found at: <h1><a href="http://smrtools.b-o-b.org/shipListFull.BOB" target="smrtools">SMR Tools</a></h1>\r\n<br>This site is created and maintained by the Admin B.O.B.\r\n<br>Some of it''s feautures/differences are:\r\n<li>Sortable\r\n<li>Speed based on Game Speed\r\n<li>Trade Power<sup>1</sup>\r\n<li>Base Experience<sup>2</sup>\r\n<li>Rating<sup>3</sup>\r\n<li>Restriction Expanded<sup>4</sup>\r\n<li>Shop List<sup>5</sup>\r\n\r\n<p>1. Trade Power is the ship''s speed multiplied by the cargo holds. The higher the trade power, the better the trade ship. \r\n<br>2. Base experience is the (ship''s cargo holds divided by 15) + 2. IE: (600/15) + 2 = 42. A ship with 600 cargo holds would gain 42 exp per sector for the distance multiplier of the good. So a route that has a distance of 3 sectors would gain you a max of 126 exp.\r\n<br>3. Rating is the defense rating the ship will have if it has full shields, armor, and combat drones.\r\n<br>4. SMRealms Ship List only displays if the ship is restrict to good or evil players.  SMRTools expands this to display if the ship is restricted to fledgling players. Fledgling rank can be obtained by:\r\n<br>10 kills and 40,000 traded experience OR 60,000 traded experience OR 15 kills.\r\n<br>Note: These stats are cumulative over games, which means if you get 30,000 traded experience in your first game, and then another 30,000 traded experience in your second game, you will have 60,000 traded experience and will be Fledgling ranked.\r\n<br>5. This is a drop down list that displays what Shops sell that ship.\r\n'),
(53, 51, 2, 'Weaponlist', 'The current weapon list can be found at: <h1><a href="weapon_list.php">http://www.smrealms.de/weapon_list.php</a></h1>\r\n<br>Some of its features are:\r\n<li>Sortable\r\n\r\n<p>A currently better weapon list can be found at: <h1><a href="http://smrtools.b-o-b.org/wepListFull.BOB" target="smrtools">SMR Tools</a></h1>\r\n<br>This site is created and maintained by the Admin B.O.B.\r\n<br>Some of it''s feautures/differences are:\r\n<li>Sortable\r\n<li>Rating<sup>1</sup>\r\n<li>Shop List<sup>2</sup>\r\n\r\n<p>1. Rating is the attack rating the weapon will add to your ships rating.\r\n<br>2. This is a drop down list that displays what Shops sell that weapon.\r\n'),
(54, 0, 19, 'Glossary', '<!-- ALIGNMENT ----------------------------------------------------------  -->\r\n<b><A name=Alignment>Alignment</A></B> - \r\nAlignment is a positive or negative number that shows a player''s tendency \r\nto be <b><A href="manual.php?54#good">good</A></B> \r\nor <b><A href="manual.php?54#evil">evil</A></B> \r\nor <b><A href="manual.php?54#neutral">neutral</A></B>. \r\nGood or evil players get access to special weapons and other benefits. \r\nAlignment changes up or down depending on your actions in the game. \r\nAttacking players or <b><A href="manual.php?54#port">ports</A></B> \r\nof your own <b><A href="manual.php?54#race">race</A></B> \r\nor ones friendly to your race will lower your alignment. Attacking players \r\nor ports that your race is at war with will raise your alignment.\r\n\r\n<!-- ALLIANCE ----------------------------------------------------------  -->\r\n<P><b><A name=alliance>Alliance</A></B> - Players can join together to \r\nform alliances. The benefits of being in an alliance are many, including \r\nsharing cash and other resources, mutual protection, and the fact that \r\nalliance ships can attack and defend together when they are in the same <b><A \r\nhref="manual.php?54#sector">sector</A></B>.\r\n<P><b><A name=armor>Armor</A></B> - The armor plating that protects a ship \r\nand adds to its <b><A href="manual.php?54#def">defense \r\nrating</A></B>. When a ship''s armor is reduced to zero, it is destroyed. Armor \r\nis purchased at the <b><A href="manual.php?54#uno">UNO</A></B> \r\nshop.\r\n\r\n<!-- ATTACK RATING ----------------------------------------------------------  -->\r\n<P><b><A name=attack>Attack Rating</A></B> - The strength of a ship''s \r\nattack on other ships during battle. Having many strong <b><A \r\nhref="manual.php?54#weapons">weapons</A></B> \r\nmake for a higher and more effective attack rating.\r\n\r\n<!-- BANK ----------------------------------------------------------  -->\r\n<P><b><A name=bank><img src="http://www.smrealms.de/images/bank.png">Bank </A></B> \r\n- Banks hold <b><A href="manual.php?54#credits">credits</A></B> \r\nfor players in one of several accounts. Every player has a personal bank \r\naccount they can use. <b><A href="manual.php?54#alliance">Alliances</A></B> \r\nhave a shared bank account. Anonymous bank accounts are also available and \r\nare password protected. <b><A href="manual.php?54#hunter">Hunters</A></B> \r\noften attack the unwary trader at banks, so be careful.\r\n\r\n<!-- BOUNTIES ----------------------------------------------------------  -->\r\n<P><b><A name=bounties>Bounties</A></B> - When a player kills another \r\nplayer, a bounty can be placed on his head by either the <b><A href="manual.php?54#fed">Federal \r\nGovernment</A></B> or the <b><A href="manual.php?54#UG">Underground</A></B>. \r\nKilling a player who has a bounty entitles the winner to collect the \r\nbounty. Federal bounties can be claimed at any friendly racial <b><A href="manual.php?54#HQ">head \r\nquarters</A></B> and UG bounties can be claimed at the Underground.\r\n\r\n<!-- BUILDER ----------------------------------------------------------  -->\r\n<P><b><A name=builder>Builder</A></B> - The general term for players who \r\nwork to build up a <b><A \r\nhref="manual.php?54#planet">planet''s</A></B> \r\nconstruction level.\r\n\r\n<!-- CARGO HOLDS ----------------------------------------------------------  -->\r\n<P><b><A name=cargo>Cargo Holds</A></B> - Cargo holds on a ship are used \r\nto carry <b><A \r\nhref="manual.php?54#goods">goods</A></B> \r\nfrom place to place. Most ships have cargo holds.\r\n\r\n<!-- CIJSD ----------------------------------------------------------  -->\r\n<P><b><A name=hardware>CIJSD</A></B> - This indicator tells what hardware \r\nis present on a ship. Some hardware can only be installed on certain \r\nships. The letters stand for the type of hardware: C=<b><A \r\nhref="manual.php?54#cloak">Cloaking \r\nDevice</A></B>, I=<b><A \r\nhref="manual.php?54#ig">Illusion \r\nGenerator</A></B>, J=<b><A \r\nhref="manual.php?54#jd">Jump \r\nDrive</A></B>, S=<b><A \r\nhref="manual.php?54#scanner">Scanner</A></B>, \r\nD=<b><A \r\nhref="manual.php?54#ds">Drone \r\nScrambler</A></B>.\r\n\r\n<!-- CLOAKING DEVICE ----------------------------------------------------------  -->\r\n<P><b><A name=cloak>Cloaking Device</A></B> - Hardware installed on a ship \r\nthat allows it to become invisible to players who are the same level or \r\nlower. A cloaked ship will appear as an unidentified or phantom trader if \r\nit is the only ship in the sector. If there is more than one uncloaked \r\nship in the sector it will dispappear completely from view to those of \r\nlower level than the cloaker. Higher level pilots can always see through \r\nthe cloak. The cloak deactivates when the cloaker initiates an attack or \r\nhits a <b><A \r\nhref="manual.php?54#mines">mine</A></B>.\r\n\r\n<!-- COMBAT ACCESSORIES ----------------------------------------------------------  -->\r\n<P><b><A name=ca><img src="http://www.smrealms.de/images/hardware.png">Combat Accessories</A></B> \r\n(CA) - The shop that sells <b><A \r\nhref="manual.php?54#mines">mines</A></B>, \r\n<b><A \r\nhref="manual.php?54#cds">combat \r\ndrones</A></B>, and <b><A \r\nhref="manual.php?54#sd">scout \r\ndrones</A></B>.\r\n\r\n<!-- COMBAT DRONES ----------------------------------------------------------  -->\r\n<P><b><A name=cds><img src="http://www.smrealms.de/images/forces.jpg">Combat Drones</A></B> - \r\nCombat drones can be carried \r\nonboard ships and placed in sectors. They increase a ship''s <b><A \r\nhref="manual.php?54#rating">rating</A></B>, \r\nand can both attack and defend in battle. When placed in a sector with \r\nother forces, they protect the <b><A \r\nhref="manual.php?54#stack">stack</A></B> \r\nby returning fire when the stack is attacked.\r\n\r\n<!-- CREDITS ----------------------------------------------------------  -->\r\n<P><b><A name=credits>Credits</A></B> - The currency used in SMR to buy items. \r\nCash is most often earned by <b><A \r\nhref="manual.php?54#trade">trading</A></B> \r\nat <b><A \r\nhref="manual.php?54#port">ports</A></B> \r\nor destroying other players'' ships and keeping the cash it had onboard at \r\nthe time. Keeping cash in the <b><A \r\nhref="manual.php?54#bank">bank</A></B> \r\nis a wise idea.  Credits, however, are not to be confused with <b><a \r\nhref="manual.php?54#smrcredits">SMR Credits</a></b>\r\n\r\n<!-- CURRENT SECTOR ----------------------------------------------------------  -->\r\n<P><b><A name=cs>Current Sector</A></B> (CS) - The sector a ship is \r\ncurrently in, information accessed by the current sector link. The current \r\nsector can contain other <b><A \r\nhref="manual.php?54#ship">ships</A></B>, \r\n<b><A \r\nhref="manual.php?54#location">locations</A></B>, \r\n<b><A \r\nhref="manual.php?54#port">ports</A></B>, \r\nand <b><A \r\nhref="manual.php?54#planet">planets</A></B>. \r\nWith a <b><A \r\nhref="manual.php?54#scanner">scanner</A></B> \r\ninstalled, it is possible to view the ships and <b><A \r\nhref="manual.php?54#forces">forces</A></B> \r\nin adjacent sectors.\r\n\r\n<!-- CURRENT HOF ----------------------------------------------------------  -->\r\n<P><b><A name=chof>Current HoF</A></B> - Hall of Fame statistics for the \r\ncurrent game on a variety of activities.\r\n<P><b><A name=death>Death</A></B> - When a player''s ship is reduced to \r\nzero shields and armor, it is destroyed and recorded as a death. The \r\n"killed" pilot is transported in an escape pod to his racial head \r\nquarters, where he can buy a new ship and start again.\r\n\r\n<!-- DEFENSE RATING ----------------------------------------------------------  -->\r\n<P><b><A name=def>Defense Rating</A></B> - The strength of a ship''s \r\ndefense against attack in terms of shields, armor, and combat drones. The \r\nmore <b><A \r\nhref="manual.php?54#shields">shields</A></B>, \r\n<b><A \r\nhref="manual.php?54#armor">armor</A></B>, \r\nand <b><A \r\nhref="manual.php?54#cds">combat \r\ndrones</A></B> a ship has, the higher the defense <b><A \r\nhref="manual.php?54#rating">rating</A></B>.\r\n\r\n<!-- DEMAND ----------------------------------------------------------  -->\r\n<P><b><A name=demand>Demand</A></B> - The supply of <b><A \r\nhref="manual.php?54#goods">goods</A></B> \r\navailable at a <b><A \r\nhref="manual.php?54#port">port</A></B> \r\ngoes down as <b><A \r\nhref="manual.php?54#trader">traders</A></B> \r\nbuy and sell it and is restored over time or by the trade of lower classed \r\ngoods. A low supply number means a higher buy/sell price for the trader.\r\n\r\n<!-- DEPUTY ----------------------------------------------------------  -->\r\n<P><b><A name=deputy>Deputy</A></B> - Becoming a deputy at racial <b><A \r\nhref="manual.php?54#hq">head \r\nquarters</A></B> raises a pilot''s <b><A \r\nhref="manual.php?54#alignment">alignment</A></B> \r\nto 150 points. This is the opposite of becoming a gang member at the <b><A \r\nhref="manual.php?54#ug">Underground</A></B>.\r\n\r\n<!-- DRONE SCRAMBLER ----------------------------------------------------------  -->\r\n<P><b><A name=ds>Drone Scrambler</A></B> - Hardware installed on a ship \r\nthat scrambles the targeting system of enemy <b><A \r\nhref="manual.php?54#cds">combat \r\ndrones</A></B> and reduces the amount of damage done to the ship.\r\n\r\n<!-- EXPERIENCE ----------------------------------------------------------  -->\r\n<P><b><A name=exp>Experience</A></B> - Points earned or lost during the \r\ngame. Experience is gained for <b><A \r\nhref="manual.php?54#trade">trading</A></B> \r\nand destroying <b><A \r\nhref="manual.php?54#forces">forces</A></B> \r\nand <b><A \r\nhref="manual.php?54#kill">killing</A></B> \r\nother pilots. Players lose experience points when the ship he is flying is \r\ndestroyed. Players start with zero experience and can reach 500,000 \r\npoints.  A list of all levels can be found at: <b><a \r\nhref="http://www.smrealms.de/manual.php?28">Experience Levels</a></b>\r\n\r\n<!-- EVIL ----------------------------------------------------------  -->\r\n<P><b><A name=evil>Evil</A></B> - An alignment lower than -99 means that a \r\nplayer is Evil. Evil players are allowed to use special <b><A \r\nhref="manual.php?54#weapons">weapons</A></B>, \r\n<b><A \r\nhref="manual.php?54#trade">trade</A></B> \r\nin illegal <b><A \r\nhref="manual.php?54#goods">goods</A></B>, \r\nand collect Underground <b><A \r\nhref="manual.php?54#bounties">bounties</A></B>. \r\nIf a random search by the <b><A \r\nhref="manual.php?54#fed">Federal \r\nGovernment</A></B> finds illegal goods on a ship, the goods are confiscated \r\nand the pilot gets a cash fine and an <b><A \r\nhref="manual.php?54#alignment">alignment</A></B> \r\nincrease of 5 points.\r\n\r\n<!-- FEDERAL GOVERNMENT ----------------------------------------------------------  -->\r\n<P><b><A name=fed><img src="http://www.smrealms.de/images/government.gif">Federal Government</A></B> \r\n- The Federation still has power in some things. It conducts random searches at ports for illegal \r\ngoods. Areas of space are protected by the Federal government and are safe \r\nto park as long as a ship <b><A \r\nhref="manual.php?54#rating">rating</A></B> \r\nis at an acceptable level. Generally a player with an <b><A \r\nhref="manual.php?54#attack">attack \r\nrating</A></B> of 3 or less can park safely under <img \r\nsrc="http://www.smrealms.de/images/beacon.gif">Federal protection. The \r\nTrader screen (accessed by the Trader link) will show whether your ship is \r\nunder protection or not.\r\n\r\n<!-- FORCES ----------------------------------------------------------  -->\r\n<P><b><A name=forces><img src="http://www.smrealms.de/images/forces.jpg">Forces</A></B> - \r\nA general term for the different \r\ntypes of combat accessories used in the game: <b><A \r\nhref="manual.php?54#mines">Mines</A></B>, \r\n<b><A \r\nhref="manual.php?54#cds">Combat \r\nDrones</A></B>, and <b><A \r\nhref="manual.php?54#sd">Scout \r\nDrones</A></B>. Forces can be carried on ships and dropped in a sector for \r\ndifferent purposes. Once dropped in a <b><A \r\nhref="manual.php?54#sector">sector</A></B>, \r\nthey expire after a time unless refreshed.\r\n\r\n<!-- GALACTIC POST ----------------------------------------------------------  -->\r\n<P><b><A name=gp>Galactic Post</A></B> - The in-game journal provides \r\nentertaining and informative reports of events in the game.\r\n\r\n<!-- GANG MEMBER ----------------------------------------------------------  -->\r\n<P><b><A name=gang>Gang member</A></B> - Becoming a gang member at the <b><A \r\nhref="manual.php?54#ug">Underground</A></B> \r\nlowers alignment to -150 points. This is the opposite of becoming a <b><A \r\nhref="manual.php?54#deputy">deputy</A></B> \r\nat a Federal head quarters.\r\n\r\n<!-- GENERATOR ----------------------------------------------------------  -->\r\n<P><b><A name=gen>Generator</A></B> - The building on a planet that \r\nprovides the shields for planet defense. Each generator supports 100 \r\nshields, and a <b><A \r\nhref="manual.php?54#planet">planet</A></B> \r\ncan have a maximum of 100 generators.\r\n\r\n<!-- GLOBAL RELATIONS ----------------------------------------------------------  -->\r\n<P><b><A name=gr>Global Relations</A></B> - The good, bad, or neutral \r\nrelationship that one race has with another based on <b><A \r\nhref="manual.php?54#council">Ruling \r\nCouncil</A></B> actions.\r\n\r\n<!-- GOOD ----------------------------------------------------------  -->\r\n<P><b><A name=good>Good</A></B> - An alignment higher than 99 makes a \r\nplayer Good. Good players are allowed to use special <b><A \r\nhref="manual.php?54#weapons">weapons</A></B> \r\nand <b><A \r\nhref="manual.php?54#ship">ships</A></B>.\r\n\r\n<!-- GOODS ----------------------------------------------------------  -->\r\n<P><b><A name=goods>Goods</A></B> - The items that can be bought and sold \r\nat a port. There are twelve kinds of goods, 8 are available to all traders \r\n(food, wood, ore, textiles, machinery, precious metals, circuits, \r\ncomputers, and luxury items) and four are illegal goods (slaves, weapons, \r\nnarcotics) that can be bought/sold only by <b><A \r\nhref="manual.php?54#evil">evil</A></B> \r\ntraders. Goods are grouped by level. Level one goods are food, wood, ore, \r\nprecious metals, and slaves. Level two goods are textiles, machinery, \r\ncircuits, and weapons. Level three goods are computers, luxury items, and \r\nnarcotics. <img \r\nsrc="http://www.smrealms.de/images/port/1.png"><img \r\nsrc="http://www.smrealms.de/images/port/2.png"><img \r\nsrc="http://www.smrealms.de/images/port/3.png"><img \r\nsrc="http://www.smrealms.de/images/port/4.png"><img \r\nsrc="http://www.smrealms.de/images/port/6.png"><img \r\nsrc="http://www.smrealms.de/images/port/7.png"><img \r\nsrc="http://www.smrealms.de/images/port/8.png"><img \r\nsrc="http://www.smrealms.de/images/port/10.png"><img \r\nsrc="http://www.smrealms.de/images/port/11.png"> (Evil: <img \r\nsrc="http://www.smrealms.de/images/port/5.png"><img \r\nsrc="http://www.smrealms.de/images/port/9.png"><img \r\nsrc="http://www.smrealms.de/images/port/12.png">)\r\n\r\n<!-- HANGAR ----------------------------------------------------------  -->\r\n<P><b><A name=hangar>Hangar</A></B> - The building on a planet that houses \r\nand launches combat drones for planet defense. Each hangar stores 20 \r\ncombat drones, and a <b><A \r\nhref="manual.php?54#planet">planet</A></B> \r\ncan have a maximum of 100 hangars.\r\n\r\n<!-- HALL OF FAME ----------------------------------------------------------  -->\r\n<P><b><A name=hof>Hall of Fame</A></B> - Career totals of statistics on a \r\nvariety of activities. These totals span multiple games.\r\n\r\n<!-- HARD POINTS ----------------------------------------------------------  -->\r\n<P><b><A name=hp>Hard Points</A></B> - The number of <b><A \r\nhref="manual.php?54#weapons">weapons</A></B> \r\nthat can be installed on a ship. Example: the Destroyer has six hard \r\npoints so it can have six weapons.\r\n\r\n<!-- HEAD QUARTERS ----------------------------------------------------------  -->\r\n<P><b><A name=HQ><img src="http://www.smrealms.de/images/government.gif">Head Quarters</A></B> - \r\nEach of the eight <b><A \r\nhref="manual.php?54#race">races</A></B> \r\nhas a racial head quarters where players can get <b><A \r\nhref="manual.php?54#deputy">deputized</A></B>, \r\nclaim and place <b><A \r\nhref="manual.php?54#bounty">bounties</A></B> \r\non other players, and collect <b><A \r\nhref="manual.php?54#mpay">military \r\npayments</A></B> for their kills.\r\n\r\n<!-- HUNTER ----------------------------------------------------------  -->\r\n<P><b><A name=hunter>Hunter</A></B> - The general term for players in the \r\ngame who fly hunter class or capital ships and attack traders as they work \r\nin the trade routes.\r\n<P><b><A name=ig>Illusion Generator</A></B> - Hardware installed on a ship \r\nthat can create the illusion that it is a different kind of ship. The IG \r\nis configurable for most any ship type and <b><A \r\nhref="manual.php?54#rating">rating</A></B>. \r\nFor example, a 23/14 hunter class ship could disguise itself as a 3/6 \r\ntrade ship, or vice versa.\r\n\r\n<!-- IN SECTOR ----------------------------------------------------------  -->\r\n<P><b><A name=is>In Sector</A></B> (IS) - referring to a ship being in a \r\nsector. Example: That Fury was IS with the fleet at 3428.\r\n\r\n<!-- IRC CHAT ----------------------------------------------------------  -->\r\n<P><b><A name=irc>IRC chat</A></B> - Players use the irc chat program \r\n(accessed by the menu link which opens a chat interface) or programs like \r\nit to communicate more quickly while playing. IRC chat helps in \r\ncoordinating alliance actions and it''s a great way to meet and make \r\nfriends in the game.  You can download IRC program, or use the <b><a \r\nhref="http://chat.vjtd3.com/" target="chat">chat applet</a></b>\r\n\r\n<!-- JUMP DRIVE ----------------------------------------------------------  -->\r\n<P><b><A name=jd>Jump Drive</A></B> - Hardware installed on a ship to jump \r\nlong distances in a single move. The cost to jump is 15 <b><A \r\nhref="manual.php?54#turns">turns</A></B>, \r\nonly one warp can be jumped through at a time (you can only jump as far as \r\nthe next galaxy in one jump), and the accuracy of the jump depends on both \r\nthe distance and the pilot''s experience level.\r\n\r\n<!-- KILL ----------------------------------------------------------  -->\r\n<P><b><A name=kill>Kill</A></B> - When one player destroys another \r\nplayer''s ship it is called a kill. Kills are shown in the <b><A \r\nhref="manual.php?54#news">news</A></B> \r\nand are tallied in the <b><A \r\nhref="manual.php?54#rankings">rankings</A></B> \r\nand the <b><A \r\nhref="manual.php?54#hof">Hall of \r\nFame</A></B>. When you kill another player you may be entitled to collect <b><A \r\nhref="manual.php?54#mpay">military \r\npayments</A></B> or <b><A \r\nhref="manual.php?54#bounty">bounties</A></B>.\r\n\r\n<!-- LEVEL ----------------------------------------------------------  -->\r\n<P><b><A name=level>Level</A></B> - <b><A \r\nhref="manual.php?54#experience">Experience</A></B> \r\npoints gained in the game are grouped into tiered levels. Players start at \r\nlevel 1 and can get up to level 50.  A list of all levels can be found at: <b><a \r\nhref="http://www.smrealms.de/manual.php?28">Experience Levels</a></b>\r\n\r\n<!-- LOCAL MAP ----------------------------------------------------------  -->\r\n<P><b><A name=local>Local Map</A></B> - A high-level view of the current \r\nsector and the surrounding 24 sectors. The local map shows <b><A \r\nhref="manual.php?54#port">ports</A></B> \r\nand <b><A \r\nhref="manual.php?54#location">locations</A></B> \r\nand <b><A \r\nhref="manual.php?54#warp">warps</A></B>. \r\nWith a scanner installed on a ship, it is possible to see <b><A \r\nhref="manual.php?54#ship">ships</A></B> \r\nand <b><A \r\nhref="manual.php?54#forces">forces</A></B> \r\nin adjacent sectors.\r\n\r\n<!-- LOCATIONS ----------------------------------------------------------  -->\r\n<P><b><A name=location>Location</A></B> - A shop in a sector that sells \r\nsomething other than trade goods. <b><A \r\nhref="manual.php?54#bank">Banks</A></B>, \r\n<b><A \r\nhref="manual.php?54#ca">Combat \r\nAccessories</A></B>, and <b><A \r\nhref="manual.php?54#uno">UNOs</A></B> \r\nare examples of locations.\r\n\r\n<!-- MESSAGES ----------------------------------------------------------  -->\r\n<P><b><A name=messages>Messages</A></B> - Players can send each other \r\nmessages through the in-game mail system. There are <img \r\nsrc="http://www.smrealms.de/images/personal_msg.png">player-to-player, \r\n<img src="http://www.smrealms.de/images/alliance_msg.png">alliance, \r\n<img src="http://www.smrealms.de/images/council_msg.png">ruling council, and \r\n<img src="http://www.smrealms.de/images/global_msg.png">global messages. All received messages are \r\nsaved in a player''s message center for a week. The message system is \r\nsomewhat slow however, so many players use <b><A \r\nhref="manual.php?54#irc">IRC \r\nChat</A></B> to communicate instantly with other players.\r\n\r\n<!-- MGU ----------------------------------------------------------  -->\r\n<P><b><A name=mgu>MGU (Merchants Guide to the Universe)</A></B> - MGU is a \r\ndownloadable program that can help players find the most profitable trade \r\nroutes and the locations to buy items. It has many of the same features as \r\n<b><A \r\nhref="manual.php?54#smc">SMC</A></B> \r\n(Space Merchant Companion) and some unique features as well.\r\n\r\n<!-- MILITARY PAYMENTS ----------------------------------------------------------  -->\r\n<P><b><A name=mpay>Military payments</A></B> - When a player kills another \r\nplayer and their <b><A \r\nhref="manual.php?54#race">races</A></B> \r\nare at <b><A \r\nhref="manual.php?54#war">war</A></B>, \r\nthe winner of the fight is eligible for military payment. This can be \r\nclaimed at any friendly racial <b><A \r\nhref="manual.php?54#hq">head \r\nquarters</A></B>.\r\n\r\n<!-- MINES ----------------------------------------------------------  -->\r\n<P><b><A name=mines><img src="http://www.smrealms.de/images/forces.jpg">Mines</A></B> - \r\nMines can be carried on some ships. \r\nThey can be dropped in a sector to do damage to enemy ships who pass \r\nthrough that sector. Mines are typically used by traders for protection \r\nfrom <b><A \r\nhref="manual.php?54#hunter">hunters</A></B>, \r\nor by <b><A \r\nhref="manual.php?54#alliance">alliances</A></B> \r\nto defend their <b><A \r\nhref="manual.php?54#planet">planets</A></B> \r\nand other territory.\r\n\r\n<!-- NEGOTIATION ----------------------------------------------------------  -->\r\n<P><b><A name=negotiation>Negotiation</A></B> - Making an offer that is \r\nhigher or lower than what the port is offering for <b><A \r\nhref="manual.php?54#goods">goods</A></B>. \r\nBuy low and sell high! Bartering with the port master will often mean \r\nbetter cash and experience in the trade, so always make a counter offer \r\nunless your <b><A \r\nhref="manual.php?54#relations">relations</A></B> \r\nwith the race are at 1000. \r\n\r\n<!-- NEUTRAL ----------------------------------------------------------  -->\r\n<P><b><A name=neutral>Neutral</A></B> - An alignment inbetween -99 and 99 \r\nmakes a player Neutral. Neutral players can collect both Federal and \r\nUnderground <b><A \r\nhref="manual.php?54#bounties">bounties</A></B> \r\nand they have the option to become a federal <b><A \r\nhref="manual.php?54#deputy">deputy</A></B> \r\nor joining the <b><A \r\nhref="manual.php?54#UG">Underground</A></B>.\r\n\r\n<!-- NEWS ----------------------------------------------------------  -->\r\n<P><b><A name=news>News</A></B> - News reports show activity going on in \r\nthe game. When players are killed, ports and planets are attacked, or one \r\nrace declares <b><A \r\nhref="manual.php?54#war">WAR</A></B> \r\nor <b><A \r\nhref="manual.php?54#peace">PEACE</A></B> \r\nwith another, it is reported in the news. Reading the news is also a good \r\nway for <b><A \r\nhref="manual.php?54#trader">traders</A></B> \r\nto know where the <b><A \r\nhref="manual.php?54#hunter">hunters</A></B> \r\nare working.\r\n\r\n<!-- PEACE ----------------------------------------------------------  -->\r\n<P><b><A name=peace>PEACE</A></B> - When the <b><A \r\nhref="manual.php?54#council">ruling \r\ncouncils</A></B> of two races have both voted for peace with each other, they \r\ncan trade at each other''s ports and use each other''s <b><A \r\nhref="manual.php?54#weapons">weapons</A></B>.\r\n\r\n<!-- PERSONAL RELATIONS ----------------------------------------------------------  -->\r\n<P><b><A name=prelations>Personal Relations</A></B> - The relationship \r\nthat a <b><A \r\nhref="manual.php?54#trader">trader</A></B> \r\nhas with other <b><A \r\nhref="manual.php?54#race">races</A></B> \r\nbased on trading or raiding <b><A \r\nhref="manual.php?54#port">ports</A></B>. \r\nSuccessful trading raises relations with the port''s race. Port raids lower \r\na player''s personal relations with the port owner''s race. There is no maximum \r\npersonal relations, however they will increase very slowly after the first 500. When you reach a combined total of 1000 it means that <b><A \r\nref="#negotiation">negotiation</A></B> at the port is not necessary -- the \r\nprice offered for the <b><A \r\nhref="manual.php?54#goods">goods</A></B> \r\nis always the best for experience, it takes 2000 for profit however.\r\n\r\n<!-- PLANET ----------------------------------------------------------  -->\r\n<P><b><A name=planet><img src="http://www.smrealms.de/images/planet.gif">Planet</A></B> - \r\nPlanets are scattered throughout the \r\nuniverse, usually in non-racial galaxies, and can be used as a landing \r\narea. Players build <b><A \r\nhref="manual.php?54#gen">generators</A></B>, \r\n<b><A \r\nhref="manual.php?54#hangar">hangars</A></B>, \r\nand <b><A \r\nhref="manual.php?54#turret">turrets</A></B> \r\nas defenses against attack. Planets begin at a construction level of 0.00. \r\nAs constuction develops, the level increases to a maximum of level 70.00. \r\nWhen players attack and breach a planet''s defenses, it is called a planet \r\nbust (PB).\r\n\r\n<!-- PLOT ----------------------------------------------------------  -->\r\n<P><b><A name=plot>Plot</A></B> - Each ship can plot a course to a \r\nparticular destination and move there quickly using its onboard course \r\nplotter. The plotter is accessed in the Plot a Course link and will accept \r\nboth a starting and ending sector number. The plotted course will be \r\ninterrupted and need to be re-plotted if a ship hits <b><A \r\nhref="manual.php?54#mines">mines</A></B> \r\nalong the way.\r\n\r\n<!-- PORT ----------------------------------------------------------  -->\r\n<P><b><A name=port>Port</A></B> - The place where traders can buy and sell \r\n<b><A \r\nhref="manual.php?54#goods">goods</A></B> \r\nfor <b><A \r\nhref="manual.php?54#cash">cash</A></B> \r\nand <b><A \r\nhref="manual.php?54#exp">experience</A></B>. \r\nPorts are controlled by one of the eight <b><A \r\nhref="manual.php?54#race">races</A></B> \r\nor they can be neutral. Neutral ports sell goods to all races regardless \r\nof the politics of war or peace. Ports are ranked by level from 1 to 9. \r\nLevel 1 ports sell few goods and have weak defenses against attack. Level \r\n9 ports sell the topmost goods and are protected from attack by heavy \r\nshields, multiple laser turrets, and hundreds of combat drones. When one \r\nor more traders attack a port, it is called a port raid (PR). Successful \r\nports raids can be profitable for the attackers, but most ports are \r\nheavily defended so don''t do this alone.\r\n\r\n<!-- RACE ----------------------------------------------------------  -->\r\n<P><b><A name=race>Race</A></B> - There are eight different races to \r\nchoose from in the game. Each race has it''s strong points and weaknesses \r\nin terms of the ships and weapons it is able to use.\r\n\r\n<!-- RATING ----------------------------------------------------------  -->\r\n<P><b><A name=rating>Rating</A></B> - Two numbers indicating the strength \r\nof a ship''s attack and defense. Example: A Dark Mirage has a rating of \r\n27/18. The first number is the <b><A \r\nhref="manual.php?54#attack">attack \r\nrating</A></B> and the second is the <b><A \r\nhref="manual.php?54#def">defense \r\nrating </A></B>of the ship. \r\n\r\n<!-- RANKINGS ----------------------------------------------------------  -->\r\n<P><b><A name=rankings>Rankings</A></B> - Player activity is ranked in \r\nterms of <b><A \r\nhref="manual.php?54#exp">experience</A></B>, \r\n<b><A \r\nhref="manual.php?54#kill">kills</A></B>, \r\nand <b><A \r\nhref="manual.php?54#death">deaths</A></B>.\r\n\r\n<!-- RELATIONS ----------------------------------------------------------  -->\r\n<P><b><A name=relations>Relations</A></B> - A trader''s status with another \r\nraces can influence his ability to <b><A \r\nhref="manual.php?54#trade">trade</A></B> \r\ngoods and buy weapons with that race. This can be influenced by the <b><A \r\nhref="manual.php?54#gr">Global \r\nRelations</A></B> that his race has and also his <b><A \r\nhref="manual.php?54#prelations">Personal \r\nRelations</A></B>.\r\n\r\n<!-- RULING COUNCIL ----------------------------------------------------------  -->\r\n<P><b><A name=council>Ruling Council</A></B> - Each race has a ruling \r\npolitical council made up of the top 20 players of that <b><A \r\nhref="manual.php?54#race">race</A></B>. \r\nCouncil members vote on <b><A \r\nhref="manual.php?54#war">war</A></B> \r\nand <b><A \r\nhref="manual.php?54#peace">peace</A></B> \r\nwith other races.\r\n\r\n<!-- SCANNER ----------------------------------------------------------  -->\r\n<P><b><A name=scanner>Scanner</A></B> - Hardware installed on a ship that \r\ncan scan adjacent sectors for <b><A \r\nhref="manual.php?54#ship">ships</A></B> \r\nand <b><A \r\nhref="manual.php?54#forces">forces</A></B>. \r\nScanners can be used at both <b><A \r\nhref="manual.php?54#local">local \r\nmap</A></B> and <b><A \r\nhref="manual.php?54#cs">current \r\nsector</A></B>. The current sector scan gives more detailed information on the \r\nnumber and type of ships and forces.\r\n\r\n<!-- SCOUT DRONES ----------------------------------------------------------  -->\r\n<P><b><A name=sd><img src="http://www.smrealms.de/images/forces.jpg">Scout Drones</A></B> - \r\nScouts can be carried on some \r\nships and dropped in a sector. It signals its owner when an enemy ship \r\nenters and exits the sector where the scout is placed.\r\n\r\n<!-- SECTOR ----------------------------------------------------------  -->\r\n<P><b><A name=sector>Sector</A></B> - A single unit of space which can \r\ncontain <b><A \r\nhref="manual.php?54#port">ports</A></B>, \r\n<b><A \r\nhref="manual.php?54#location">locations</A></B>, \r\nand <b><A \r\nhref="manual.php?54#planet">planets</A></B>. \r\nGalaxies are made up of between 25 and 484 sectors.\r\n\r\n<!-- SHIELDS ----------------------------------------------------------  -->\r\n<P><b><A name=shields>Shields</A></B> - Shields surround and protect a \r\nship and add to it''s <b><A \r\nhref="manual.php?54#def">defense \r\nrating</A></B>. When in battle with another ship or when hitting mines, a \r\nship''s shields are reduced first. When all shields are gone, the <b><A \r\nhref="manual.php?54#armor">armor</A></B> \r\nof a ship is exposed to attack. Shields are purchased at the <b><A \r\nhref="manual.php?54#uno">UNO</A></B> \r\nshop.\r\n\r\n<!-- SHIPS ----------------------------------------------------------  -->\r\n<P><b><A name=ship>Ships</A></B> - Ships are used for trading and fighting \r\nand a variety of other purposes. They fall into three main categories: \r\ntrade class, hunter class, and capital. Trade ships have lots of <b><A \r\nhref="manual.php?54#cargo">cargo \r\nholds</A></B> but few weapons. <b><A \r\nhref="manual.php?54#hunter">Hunter</A></B> \r\nclass ship are well armed and fast. Capital ships are heavily armed and \r\ndefended but are usually slow and are used for major attacks on <b><A \r\nhref="manual.php?54#planet">planets</A></B> \r\nand <b><A \r\nhref="manual.php?54#port">ports</A></B>. \r\nHere is a complete <b><A href="http://www.smrealms.de/ship_list.asp">list of \r\nships</A></B>.\r\n\r\n<!-- SMC ----------------------------------------------------------  -->\r\n<P><b><A name=smc>SMC (Space Merchant Companion)</A></B> - SMC is a \r\ndownloadable program that can help players find the most profitable trade \r\nroutes and the locations to buy items. It has many of the same features as \r\n<b><A \r\nhref="manual.php?54#mgu">MGU</A></B> \r\n(Merchants Guide to the Universe) and some unique features as well.\r\n<P><b><a name=smrcredits>SMR Credits</a></b> - 1 SMR Credits are awarded\r\nfor every US Dollar you donate.  SMR Credits can be used to purchase maps,\r\nname your ship, and many more useful feautures.  Also, some special games\r\nmay require credits to play.\r\n\r\n<!-- STACK ----------------------------------------------------------  -->\r\n<P><b><A name=stack><img src="http://www.smrealms.de/images/forces.jpg">Stack</A></B> - \r\nThe common name for <b><A \r\nhref="manual.php?54#forces">forces</A></B> \r\nplaced in a sector.\r\n\r\n<!-- TRADE ----------------------------------------------------------  -->\r\n<P><b><A name=trade>Trade</A></B> - The buying and selling of <b><A \r\nhref="manual.php?54#goods">goods</A></B> \r\nat <b><A \r\nhref="manual.php?54#port">ports</A></B>. \r\n<b><A \r\nhref="manual.php?54#negotiation">Negotiating</A></B> \r\nwith the port master can improve the cash and experience you make on a \r\ntrade. Buy low, sell high!\r\n\r\n<!-- TRADER ----------------------------------------------------------  -->\r\n<P><b><A name=trader><img src="http://www.smrealms.de/images/trader.jpg">Trader</A></B> - \r\nThe general term for players in the \r\ngame who trade.\r\n\r\n<!-- TURNS ----------------------------------------------------------  -->\r\n<P><b><A name=turns>Turns</A></B> - The number of actions or moves you can \r\nmake in the game. Turns are earned each hour based on the speed factor of \r\nthe <b><A \r\nhref="manual.php?54#ship">ship</A></B>. \r\nA ship''s speed is noted in turns-per-hour or TPH. Turns are used to move a \r\nship, make purchases at <b><A \r\nhref="manual.php?54#port">ports</A></B> \r\nand <b><A \r\nhref="manual.php?54#location">locations</A></B>, \r\nand do other things in the game.\r\n\r\n<!-- TURRET ----------------------------------------------------------  -->\r\n<P><b><A name=turret>Turret</A></B> - A heavy laser cannon used for planet \r\nand port defense. <b><A \r\nhref="manual.php?54#planet">Planets</A></B> \r\nhave a maximum of 10 turrets and <b><A \r\nhref="manual.php?54#port">ports</A></B> \r\nhave 15 turrets.\r\n\r\n<!-- UNO ----------------------------------------------------------  -->\r\n<P><b><A name=uno><img src="http://www.smrealms.de/images/hardware.png">UNO</A></B> - \r\nThe shop that sells <b><A \r\nhref="manual.php?54#shields">shields</A></B>, \r\n<b><A \r\nhref="manual.php?54#armor">armor</A></B>, \r\nand <b><A \r\nhref="manual.php?54#cargo">cargo \r\nholds</A></B>.\r\n\r\n<!-- UNDERGROUND ----------------------------------------------------------  -->\r\n<P><b><A name=ug><img src="http://www.smrealms.de/images/underground.gif">Underground</A></B> (UG) - \r\nThis fortress of evil is the \r\nlocation to place and claim UG <b><A \r\nhref="manual.php?54#bounties">bounties</A></B> \r\nand become a <b><A \r\nhref="manual.php?54#gang">gang \r\nmember</A></B> to lower your <b><A \r\nhref="manual.php?54#alignment">alignment</A></B> \r\nquickly. \r\n\r\n<!-- WAR ----------------------------------------------------------  -->\r\n<P><b><A name=war>WAR</A></B> - The <b><A \r\nhref="manual.php?54#council">ruling \r\ncouncil</A></B> of a race may vote to go to war with another race. When two \r\nraces are at war, they are prevented from using each other''s <b><A \r\nhref="manual.php?54#port">ports</A></B> \r\nand <b><A \r\nhref="manual.php?54#weapons">weapons</A></B>. \r\n<b><A \r\nhref="manual.php?54#mpay">Military \r\npayments</A></B> are also paid for <b><A \r\nhref="manual.php?54#kill">killing</A></B> \r\nenemy pilots.\r\n\r\n<!-- WARP ----------------------------------------------------------  -->\r\n<P><b><A name=warp><img src="http://www.smrealms.de/images/warp.gif">Warp</A></B> - \r\nWarps connect galaxies and are used to \r\ntravel across the universe. Warps are viewed and accessed in both the <b><A \r\nhref="manual.php?54#local">local \r\nmap</A></B> and <b><A \r\nhref="manual.php?54#cs">current \r\nsector</A></B>. Traveling through a warp uses 5 <b><A \r\nhref="manual.php?54#turns">turns</A></B>.\r\n\r\n<!-- WEAPONS ----------------------------------------------------------  -->\r\n<P><b><A name=weapons><img src="http://www.smrealms.de/images/weapon_shop.gif">Weapons</A></B> - Weapons are used to battle with \r\nother ships and can be bought at the numerous weapon shops located around \r\nthe universe. Most ships carry at least one weapon. Weapons are ranked by \r\npower level 1-5, and there are limits to how many high power weapons a \r\nship can carry. Here is a complete <b><A \r\nhref="http://www.smrealms.de/weapon_list.php">list of \r\nweapons</A></B>.'),
(57, 22, 3, 'Reading Vessel and Forces Scans', '<br>The numbers shown for vessels and forces can seem cryptic at first, but here is how to decypher the numbers. \r\n\r\n<p>Enemy vessels = combined ship defense rating / 10 \r\n<br>Friendly vessels = combined ship attack rating x 10 \r\n\r\n<p>The ship scans don''t tell the number of ships, only their combined ratings. Thus, it takes some knowledge of ship configurations to accurately determine what ships your scanner is seeing. For example an enemy vessel (EV) scan of 60 indicates that the ships have a defense rating of 6, and is probably a single trading ship. But an EV scan of 200 could be two or three traders or a single warship. Basically, the higher the number you scan, the more ships there are likely to be. \r\n\r\n            <P>\r\n            <TABLE borderColor=#0b8d35 cellSpacing=0 cellPadding=3 border=1>\r\n              <TBODY>\r\n              <TR>\r\n                <TH>&nbsp;</TH>\r\n                <TH align=middle>Scan Results</TH></TR>\r\n              <TR>\r\n                <TD>Friendly vessels</TD>\r\n                <TD align=middle>0</TD></TR>\r\n              <TR>\r\n                <TD>Enemy vessels</TD>\r\n                <TD align=middle>410</TD></TR>\r\n              <TR>\r\n                <TD>Friendly forces</TD>\r\n                <TD align=middle>0</TD></TR>\r\n              <TR>\r\n                <TD>Enemy forces</TD>\r\n                <TD align=middle>0</TD></TR></TBODY></TABLE></P>\r\n            <P>\r\n            <TABLE borderColor="#0b8d35" cellSpacing="0" cellPadding="3" border="1">\r\n              <TBODY>\r\n              <TR>\r\n                <TD>Planet</TD>\r\n                <TD>No</TD></TR>\r\n              <TR>\r\n                <TD>Port</TD>\r\n                <TD>No</TD></TR>\r\n              <TR>\r\n                <TD>Location</TD>\r\n                <TD>Yes</TD></TR></TBODY></TABLE></P>\r\n            <form action="manual.php?57" method="post"><INPUT id="InputFields" type="submit" value="Enter 1105 (1)" name="action"></FORM>\r\n\r\n<p>This scan is of 4 ships, a /15 Planetary Super Freighter, a /12 Advanced Carrier, a /8 Leviathan, and a /6 Vengance.\r\n\r\n<p>Forces follow this formula. It is the same formula for Friendly and Enemy Forces. \r\n\r\n<p>Mine = 3 \r\n<br>Combat Drone = 2 \r\n<br>Scout = 1 \r\n\r\n<p>Again, the scan doesn''t tell how many of each type of force there are, only the combined value. So an enemy force (EF) scan of 1 would be without question a single scout. A scan of 2 could be two scouts or a single CD. A scan of 3 could be a single mine, a CD and a scout, or three scouts. Higher scans are difficult to accurately read what the nature of the forces are, but knowing the basic formula will help. Basically, the higher the scan the more forces there are and the more damage you are likely to suffer by entering the sector. \r\n\r\n            <P>\r\n            <TABLE borderColor="#0b8d35" cellSpacing="0" cellPadding="3" border="1">\r\n              <TBODY>\r\n              <TR>\r\n                <TH>&nbsp;</TH>\r\n                <TH align=middle>Scan Results</TH></TR>\r\n              <TR>\r\n                <TD>Friendly vessels</TD>\r\n                <TD align=middle>0</TD></TR>\r\n              <TR>\r\n                <TD>Enemy vessels</TD>\r\n                <TD align=middle>0</TD></TR>\r\n              <TR>\r\n                <TD>Friendly forces</TD>\r\n                <TD align=middle>0</TD></TR>\r\n              <TR>\r\n                <TD>Enemy forces</TD>\r\n                <TD align=middle>102</TD></TR></TBODY></TABLE></P>\r\n            <P>\r\n            <TABLE borderColor=#0b8d35 cellSpacing=0 cellPadding=3 border=1>\r\n              <TBODY>\r\n              <TR>\r\n                <TD>Planet</TD>\r\n                <TD>No</TD></TR>\r\n              <TR>\r\n                <TD>Port</TD>\r\n                <TD>Yes</TD></TR>\r\n              <TR>\r\n                <TD>Location</TD>\r\n                <TD>No</TD></TR></TBODY></TABLE></P>\r\n            <FORM action="manual.php?57" method="post"><INPUT id="InputFields" type="submit" value="Enter 1106 (1)" name="action"></FORM>\r\n\r\n<p>This scan is of 50 combat drones (50 x 2 = 100) and 2 scout drones (2 x 1 = 2), thus 102 scan.\r\n\r\n<p>Knowing how to use a scanner and using it often will save your ship time and time again.'),
(56, 22, 2, 'Sector Scan', 'With a scanner installed on your ship, you will see the word "scan" below the sector number in the navigation box of the current sector view. When you click that sector scan link, it will give you a basic display with information about that sector. This scanning is also available as you move on a plotted course. The scanner readout shows how many Enemy and Friendly vessels there are and the number of Enemy and Friendly Forces. It also tells if there is a planet, port, or location in that sector. \r\n\r\n            <P>\r\n            <TABLE borderColor=#0b8d35 cellSpacing=0 border=1>\r\n              <TBODY>\r\n              <TR>\r\n                <TH align=middle colSpan=3>Move to</TH></TR>\r\n              <TR>\r\n                <TD>&nbsp;</TD>\r\n                <TD align=middle width=80 height=40><SPAN \r\n                  style="COLOR: green">1091 (1)</SPAN><BR><SPAN \r\n                  style="FONT-SIZE: 75%">Scan<SPAN></SPAN></SPAN></TD>\r\n                <TD>&nbsp;</TD></TR>\r\n              <TR>\r\n                <TD align=middle width=80 height=40><SPAN \r\n                  style="COLOR: green">1104 (1)</SPAN><BR><SPAN \r\n                  style="FONT-SIZE: 75%">Scan<SPAN></SPAN></SPAN></TD>\r\n                <TD align=middle><SPAN \r\n                  style="COLOR: green">1105</SPAN></TD>\r\n                <TD align=middle width=80 height=40><SPAN \r\n                  style="COLOR: lime">1106 (1)</SPAN><BR><SPAN \r\n                  style="FONT-SIZE: 75%">Scan<SPAN></SPAN></SPAN></TD></TR>\r\n              <TR>\r\n                <TD>&nbsp;</TD>\r\n                <TD align=middle width=80 height=40>&nbsp;</TD>\r\n                <TD align=middle width=80 \r\n            height=40>&nbsp;</TD></TR></TBODY></TABLE></P>'),
(55, 22, 1, 'Local Scan', 'A scanner works on your Local Map view by showing you if ships or forces are in surrounding sectors. The scanner is only effective for adjacent sectors that you can move into directly. Ships are indicated by the "Trader Face" icon and forces are indicated by the "Mine" icon. These are the same icons you see on Local Map when ships and forces are in your current sector. The local scan doesn''t tell you whether the ships and forces you see are friendly or enemy ones. \r\n\r\n<p>Local scanning is useful anytime you want advance warning about what is around you. This is especially helpful When trading because a local scan used while moving between ports can show ships around you before you come in sector with them. \r\n\r\n<p>	<DIV align=center>\r\n            <TABLE borderColor=#0b8d35 cellSpacing=0 cellPadding=0 border=1>\r\n              <TBODY>\r\n              <TR>\r\n                <TD vAlign=top align=middle>\r\n                  <TABLE height=120 cellSpacing=0 cellPadding=0 width=120 \r\n                  bgColor=#0b4c1c border=0>\r\n                    <TBODY>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" \r\n                        width=12></TD>\r\n                      <TD></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" \r\n                        width=5></TD>\r\n                      <TD align=right height=21></TD>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver_empty.gif" \r\n                        width=5></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21><IMG \r\n                        src="images/beacon.gif"></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21><SMALL><SPAN \r\n                        style="COLOR: lime">#1075</SPAN></SMALL></TD></TR>\r\n                    <TR>\r\n                      <TD height=42></TD></TR>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" \r\n                        width=12></TD>\r\n                      <TD></TD></TR></TBODY></TABLE></TD>\r\n                <TD vAlign=top align=middle>\r\n                  <TABLE height=120 cellSpacing=0 cellPadding=0 width=120 \r\n                  bgColor=#0b4c1c border=0>\r\n                    <TBODY>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor_empty.gif" \r\n                        width=12></TD>\r\n                      <TD></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver_empty.gif" \r\n                        width=5></TD>\r\n                      <TD align=right height=21></TD>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" \r\n                        width=5></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21><SMALL><SPAN \r\n                        style="COLOR: lime">#1076</SPAN></SMALL></TD></TR>\r\n                    <TR>\r\n                      <TD height=42><IMG \r\n                        src="images/port/buy.gif"><IMG \r\n                        height=16 alt=Wood \r\n                        src="images/port/1.png" \r\n                        width=13 border=0><IMG height=16 alt=Food \r\n                        src="images/port/2.png" \r\n                        width=13 border=0><IMG height=16 alt=Slaves \r\n                        src="images/port/5.png" \r\n                        width=13 border=0><IMG height=16 alt=Textiles \r\n                        src="images/port/6.png" \r\n                        width=13 border=0><IMG height=16 alt=Machinery \r\n                        src="images/port/7.png" \r\n                        width=13 border=0><IMG height=16 alt=Circuitry \r\n                        src="images/port/8.png" \r\n                        width=13 border=0><IMG height=16 alt=Computer \r\n                        src="images/port/10.png" \r\n                        width=13 border=0><BR><IMG \r\n                        src="images/port/sell.gif"><IMG \r\n                        height=16 alt=Ore \r\n                        src="images/port/3.png" \r\n                        width=13 border=0><IMG height=16 alt="Precious Metals" \r\n                        src="images/port/4.png" \r\n                        width=13 border=0><IMG height=16 alt=Weapons \r\n                        src="images/port/9.png" \r\n                        width=13 border=0></TD></TR>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" \r\n                        width=12></TD>\r\n                      <TD></TD></TR></TBODY></TABLE></TD>\r\n                <TD vAlign=top align=middle>\r\n                  <TABLE height=120 cellSpacing=0 cellPadding=0 width=120 \r\n                  bgColor=#0b4c1c border=0>\r\n                    <TBODY>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor_empty.gif" \r\n                        width=12></TD>\r\n                      <TD></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" \r\n                        width=5></TD>\r\n                      <TD align=right height=21></TD>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver_empty.gif" \r\n                        width=5></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21><SMALL><SPAN \r\n                        style="COLOR: lime">#1077</SPAN></SMALL></TD></TR>\r\n                    <TR>\r\n                      <TD height=42><IMG \r\n                        src="images/port/buy.gif"><IMG \r\n                        height=16 alt=Wood \r\n                        src="images/port/1.png" \r\n                        width=13 border=0><IMG height=16 alt=Food \r\n                        src="images/port/2.png" \r\n                        width=13 border=0><IMG height=16 alt=Ore \r\n                        src="images/port/3.png" \r\n                        width=13 border=0><IMG height=16 alt="Precious Metals" \r\n                        src="images/port/4.png" \r\n                        width=13 border=0><IMG height=16 alt=Textiles \r\n                        src="images/port/6.png" \r\n                        width=13 border=0><IMG height=16 alt=Machinery \r\n                        src="images/port/7.png" \r\n                        width=13 border=0><IMG height=16 alt=Weapons \r\n                        src="images/port/9.png" \r\n                        width=13 border=0><BR><IMG \r\n                        src="images/port/sell.gif"><IMG \r\n                        height=16 alt=Slaves \r\n                        src="images/port/5.png" \r\n                        width=13 border=0></TD></TR>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" \r\n                        width=12></TD>\r\n                      <TD></TD></TR></TBODY></TABLE></TD>\r\n                <TD vAlign=top align=middle>\r\n                  <TABLE height=120 cellSpacing=0 cellPadding=0 width=120 \r\n                  bgColor=#0b4c1c border=0>\r\n                    <TBODY>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" \r\n                        width=12></TD>\r\n                      <TD></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver_empty.gif" \r\n                        width=5></TD>\r\n                      <TD align=right height=21></TD>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" \r\n                        width=5></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21><SMALL><SPAN \r\n                        style="COLOR: lime">#1078</SPAN></SMALL></TD></TR>\r\n                    <TR>\r\n                      <TD height=42></TD></TR>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" \r\n                        width=12></TD>\r\n                      <TD></TD></TR></TBODY></TABLE></TD>\r\n                <TD vAlign=top align=middle>\r\n                  <TABLE height=120 cellSpacing=0 cellPadding=0 width=120 \r\n                  bgColor=#0b4c1c border=0>\r\n                    <TBODY>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" \r\n                        width=12></TD>\r\n                      <TD></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" \r\n                        width=5></TD>\r\n                      <TD align=right height=21></TD>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" \r\n                        width=5></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21><SMALL><SPAN \r\n                        style="COLOR: lime">#1065</SPAN></SMALL></TD></TR>\r\n                    <TR>\r\n                      <TD height=42></TD></TR>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" \r\n                        width=12></TD>\r\n                      <TD></TD></TR></TBODY></TABLE></TD></TR>\r\n              <TR>\r\n                <TD vAlign=top align=middle>\r\n                  <TABLE height=120 cellSpacing=0 cellPadding=0 width=120 \r\n                  bgColor=#0b4c1c border=0>\r\n                    <TBODY>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" \r\n                        width=12></TD>\r\n                      <TD></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" \r\n                        width=5></TD>\r\n                      <TD align=right height=21></TD>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" \r\n                        width=5></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21><IMG \r\n                        src="images/beacon.gif"></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21><SMALL><SPAN \r\n                        style="COLOR: lime">#1089</SPAN></SMALL></TD></TR>\r\n                    <TR>\r\n                      <TD height=42></TD></TR>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" \r\n                        width=12></TD>\r\n                      <TD></TD></TR></TBODY></TABLE></TD>\r\n                <TD vAlign=top align=middle>\r\n                  <TABLE height=120 cellSpacing=0 cellPadding=0 width=120 \r\n                  bgColor=#0b4c1c border=0>\r\n                    <TBODY>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" \r\n                        width=12></TD>\r\n                      <TD></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" \r\n                        width=5></TD>\r\n                      <TD align=right height=21></TD>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" \r\n                        width=5></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21><IMG \r\n                        src="images/beacon.gif"></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21><SMALL><SPAN \r\n                        style="COLOR: lime">#1090</SPAN></SMALL></TD></TR>\r\n                    <TR>\r\n                      <TD height=42></TD></TR>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor_empty.gif" \r\n                        width=12></TD>\r\n                      <TD></TD></TR></TBODY></TABLE></TD>\r\n                <TD vAlign=top align=middle>\r\n                  <TABLE height=120 cellSpacing=0 cellPadding=0 width=120 \r\n                  bgColor=#14642f border=0>\r\n                    <TBODY>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" \r\n                        width=12></TD>\r\n                      <TD></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" \r\n                        width=5></TD>\r\n                      <TD align=right height=21><IMG height=16 alt=Forces \r\n                        src="images/forces.jpg" \r\n                        width=13></TD>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" \r\n                        width=5></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21><SMALL><SPAN \r\n                        style="COLOR: lime">#1091</SPAN></SMALL></TD></TR>\r\n                    <TR>\r\n                      <TD height=42><IMG \r\n                        src="images/port/buy.gif"><IMG \r\n                        height=16 alt=Wood \r\n                        src="images/port/1.png" \r\n                        width=13 border=0><IMG height=16 alt=Food \r\n                        src="images/port/2.png" \r\n                        width=13 border=0><IMG height=16 alt=Ore \r\n                        src="images/port/3.png" \r\n                        width=13 border=0><IMG height=16 alt=Weapons \r\n                        src="images/port/9.png" \r\n                        width=13 border=0><BR><IMG \r\n                        src="images/port/sell.gif"><IMG \r\n                        height=16 alt="Precious Metals" \r\n                        src="images/port/4.png" \r\n                        width=13 border=0><IMG height=16 alt=Slaves \r\n                        src="images/port/5.png" \r\n                        width=13 border=0><IMG height=16 alt=Textiles \r\n                        src="images/port/6.png" \r\n                        width=13 border=0><IMG height=16 alt=Circuitry \r\n                        src="images/port/8.png" \r\n                        width=13 border=0></TD></TR>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" \r\n                        width=12></TD>\r\n                      <TD></TD></TR></TBODY></TABLE></TD>\r\n                <TD vAlign=top align=middle>\r\n                  <TABLE height=120 cellSpacing=0 cellPadding=0 width=120 \r\n                  bgColor=#0b4c1c border=0>\r\n                    <TBODY>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" \r\n                        width=12></TD>\r\n                      <TD></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" \r\n                        width=5></TD>\r\n                      <TD align=right height=21></TD>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver_empty.gif" \r\n                        width=5></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21><SMALL><SPAN \r\n                        style="COLOR: lime">#1092</SPAN></SMALL></TD></TR>\r\n                    <TR>\r\n                      <TD height=42></TD></TR>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" \r\n                        width=12></TD>\r\n                      <TD></TD></TR></TBODY></TABLE></TD>\r\n                <TD vAlign=top align=middle>\r\n                  <TABLE height=120 cellSpacing=0 cellPadding=0 width=120 \r\n                  bgColor=#0b4c1c border=0>\r\n                    <TBODY>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" \r\n                        width=12></TD>\r\n                      <TD></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver_empty.gif" \r\n                        width=5></TD>\r\n                      <TD align=right height=21></TD>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" \r\n                        width=5></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21><SMALL><SPAN \r\n                        style="COLOR: lime">#1079</SPAN></SMALL></TD></TR>\r\n                    <TR>\r\n                      <TD height=42><IMG \r\n                        src="images/port/buy.gif"><IMG \r\n                        height=16 alt=Ore \r\n                        src="images/port/3.png" \r\n                        width=13 border=0><IMG height=16 alt=Slaves \r\n                        src="images/port/5.png" \r\n                        width=13 border=0><IMG height=16 alt=Textiles \r\n                        src="images/port/6.png" \r\n                        width=13 border=0><IMG height=16 alt=Machinery \r\n                        src="images/port/7.png" \r\n                        width=13 border=0><IMG height=16 alt=Circuitry \r\n                        src="images/port/8.png" \r\n                        width=13 border=0><IMG height=16 alt="Luxury Items" \r\n                        src="images/port/11.png" \r\n                        width=13 border=0><BR><IMG \r\n                        src="images/port/sell.gif"><IMG \r\n                        height=16 alt=Wood \r\n                        src="images/port/1.png" \r\n                        width=13 border=0><IMG height=16 alt=Food \r\n                        src="images/port/2.png" \r\n                        width=13 border=0><IMG height=16 alt="Precious Metals" \r\n                        src="images/port/4.png" \r\n                        width=13 border=0><IMG height=16 alt=Weapons \r\n                        src="images/port/9.png" \r\n                        width=13 border=0></TD></TR>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" \r\n                        width=12></TD>\r\n                      <TD></TD></TR></TBODY></TABLE></TD></TR>\r\n              <TR>\r\n                <TD vAlign=top align=middle>\r\n                  <TABLE height=120 cellSpacing=0 cellPadding=0 width=120 \r\n                  bgColor=#0b4c1c border=0>\r\n                    <TBODY>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" \r\n                        width=12></TD>\r\n                      <TD></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver_empty.gif" \r\n                        width=5></TD>\r\n                      <TD align=right height=21></TD>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" \r\n                        width=5></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21><IMG \r\n                        src="images/shipdealer.gif"><IMG \r\n                        src="images/hardware.png"><IMG \r\n                        src="images/beacon.gif"><IMG \r\n                        alt=HQ \r\n                        src="images/government.gif"></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21><SMALL><SPAN \r\n                        style="COLOR: lime">#1103</SPAN></SMALL></TD></TR>\r\n                    <TR>\r\n                      <TD height=42></TD></TR>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor_empty.gif" \r\n                        width=12></TD>\r\n                      <TD></TD></TR></TBODY></TABLE></TD>\r\n                <TD vAlign=top align=middle>\r\n                  <TABLE height=120 cellSpacing=0 cellPadding=0 width=120 \r\n                  bgColor=#14642f border=0>\r\n                    <TBODY>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor_empty.gif" \r\n                        width=12></TD>\r\n                      <TD></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" \r\n                        width=5></TD>\r\n                      <TD align=right height=21></TD>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" \r\n                        width=5></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21><IMG \r\n                        src="images/beacon.gif"></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21><SMALL><SPAN \r\n                        style="COLOR: lime">#1104</SPAN></SMALL></TD></TR>\r\n                    <TR>\r\n                      <TD height=42></TD></TR>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" \r\n                        width=12></TD>\r\n                      <TD></TD></TR></TBODY></TABLE></TD>\r\n                <TD vAlign=top align=middle>\r\n                  <TABLE height=120 cellSpacing=0 cellPadding=0 width=120 \r\n                  background="images/currentsector.png" \r\n                  border=0>\r\n                    <TBODY>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" \r\n                        width=12></TD>\r\n                      <TD></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" \r\n                        width=5></TD>\r\n                      <TD align=right height=21><IMG height=16 alt=Trader \r\n                        src="images/trader.jpg" \r\n                        width=13></TD>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" \r\n                        width=5></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21><IMG \r\n                        src="images/beacon.gif"></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21><SMALL><SPAN \r\n                        style="COLOR: lime">#1105</SPAN></SMALL></TD></TR>\r\n                    <TR>\r\n                      <TD height=42></TD></TR>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor_empty.gif" \r\n                        width=12></TD>\r\n                      <TD></TD></TR></TBODY></TABLE></TD>\r\n                <TD vAlign=top align=middle>\r\n                  <TABLE height=120 cellSpacing=0 cellPadding=0 width=120 \r\n                  bgColor=#14642f border=0>\r\n                    <TBODY>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" \r\n                        width=12></TD>\r\n                      <TD></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" \r\n                        width=5></TD>\r\n                      <TD align=right height=21><IMG height=16 alt=Forces \r\n                        src="images/forces.jpg" \r\n                        width=13></TD>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" \r\n                        width=5></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21><SMALL><SPAN \r\n                        style="COLOR: green">#1106</SPAN></SMALL></TD></TR>\r\n                    <TR>\r\n                      <TD height=42><IMG \r\n                        src="images/port/buy.gif"><IMG \r\n                        height=16 alt=Food \r\n                        src="images/port/2.png" \r\n                        width=13 border=0><IMG height=16 alt=Slaves \r\n                        src="images/port/5.png" \r\n                        width=13 border=0><IMG height=16 alt=Machinery \r\n                        src="images/port/7.png" \r\n                        width=13 border=0><IMG height=16 alt=Circuitry \r\n                        src="images/port/8.png" \r\n                        width=13 border=0><BR><IMG \r\n                        src="images/port/sell.gif"><IMG \r\n                        height=16 alt=Wood \r\n                        src="images/port/1.png" \r\n                        width=13 border=0><IMG height=16 alt=Ore \r\n                        src="images/port/3.png" \r\n                        width=13 border=0><IMG height=16 alt="Precious Metals" \r\n                        src="images/port/4.png" \r\n                        width=13 border=0><IMG height=16 alt=Weapons \r\n                        src="images/port/9.png" \r\n                        width=13 border=0></TD></TR>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" \r\n                        width=12></TD>\r\n                      <TD></TD></TR></TBODY></TABLE></TD>\r\n                <TD vAlign=top align=middle>\r\n                  <TABLE height=120 cellSpacing=0 cellPadding=0 width=120 \r\n                  bgColor=#0b4c1c border=0>\r\n                    <TBODY>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" \r\n                        width=12></TD>\r\n                      <TD></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" \r\n                        width=5></TD>\r\n                      <TD align=right height=21></TD>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" \r\n                        width=5></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21><SMALL><SPAN \r\n                        style="COLOR: lime">#1093</SPAN></SMALL></TD></TR>\r\n                    <TR>\r\n                      <TD height=42><IMG \r\n                        src="images/port/buy.gif"><IMG \r\n                        height=16 alt=Food \r\n                        src="images/port/2.png" \r\n                        width=13 border=0><IMG height=16 alt=Ore \r\n                        src="images/port/3.png" \r\n                        width=13 border=0><IMG height=16 alt="Precious Metals" \r\n                        src="images/port/4.png" \r\n                        width=13 border=0><IMG height=16 alt=Slaves \r\n                        src="images/port/5.png" \r\n                        width=13 border=0><IMG height=16 alt=Textiles \r\n                        src="images/port/6.png" \r\n                        width=13 border=0><IMG height=16 alt=Weapons \r\n                        src="images/port/9.png" \r\n                        width=13 border=0><BR><IMG \r\n                        src="images/port/sell.gif"><IMG \r\n                        height=16 alt=Wood \r\n                        src="images/port/1.png" \r\n                        width=13 border=0><IMG height=16 alt=Machinery \r\n                        src="images/port/7.png" \r\n                        width=13 border=0><IMG height=16 alt=Circuitry \r\n                        src="images/port/8.png" \r\n                        width=13 border=0></TD></TR>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" \r\n                        width=12></TD>\r\n                      <TD></TD></TR></TBODY></TABLE></TD></TR>\r\n              <TR>\r\n                <TD vAlign=top align=middle>\r\n                  <TABLE height=120 cellSpacing=0 cellPadding=0 width=120 \r\n                  bgColor=#0b4c1c border=0>\r\n                    <TBODY>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor_empty.gif" \r\n                        width=12></TD>\r\n                      <TD></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" \r\n                        width=5></TD>\r\n                      <TD align=right height=21></TD>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" \r\n                        width=5></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21><SMALL><SPAN \r\n                        style="COLOR: lime">#1117</SPAN></SMALL></TD></TR>\r\n                    <TR>\r\n                      <TD height=42><IMG \r\n                        src="images/port/buy.gif"><IMG \r\n                        height=16 alt=Wood \r\n                        src="images/port/1.png" \r\n                        width=13 border=0><IMG height=16 alt=Food \r\n                        src="images/port/2.png" \r\n                        width=13 border=0><IMG height=16 alt=Ore \r\n                        src="images/port/3.png" \r\n                        width=13 border=0><IMG height=16 alt="Precious Metals" \r\n                        src="images/port/4.png" \r\n                        width=13 border=0><IMG height=16 alt=Textiles \r\n                        src="images/port/6.png" \r\n                        width=13 border=0><IMG height=16 alt=Circuitry \r\n                        src="images/port/8.png" \r\n                        width=13 border=0><IMG height=16 alt=Computer \r\n                        src="images/port/10.png" \r\n                        width=13 border=0><IMG height=16 alt=Narcotics \r\n                        src="images/port/12.png" \r\n                        width=13 border=0><BR><IMG \r\n                        src="images/port/sell.gif"><IMG \r\n                        height=16 alt=Slaves \r\n                        src="images/port/5.png" \r\n                        width=13 border=0><IMG height=16 alt=Machinery \r\n                        src="images/port/7.png" \r\n                        width=13 border=0><IMG height=16 alt=Weapons \r\n                        src="images/port/9.png" \r\n                        width=13 border=0></TD></TR>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" \r\n                        width=12></TD>\r\n                      <TD></TD></TR></TBODY></TABLE></TD>\r\n                <TD vAlign=top align=middle>\r\n                  <TABLE height=120 cellSpacing=0 cellPadding=0 width=120 \r\n                  bgColor=#0b4c1c border=0>\r\n                    <TBODY>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" \r\n                        width=12></TD>\r\n                      <TD></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" \r\n                        width=5></TD>\r\n                      <TD align=right height=21></TD>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver_empty.gif" \r\n                        width=5></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21><IMG \r\n                        src="images/beacon.gif"></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21><SMALL><SPAN \r\n                        style="COLOR: lime">#1118</SPAN></SMALL></TD></TR>\r\n                    <TR>\r\n                      <TD height=42></TD></TR>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor_empty.gif" \r\n                        width=12></TD>\r\n                      <TD></TD></TR></TBODY></TABLE></TD>\r\n                <TD vAlign=top align=middle>\r\n                  <TABLE height=120 cellSpacing=0 cellPadding=0 width=120 \r\n                  bgColor=#0b4c1c border=0>\r\n                    <TBODY>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor_empty.gif" \r\n                        width=12></TD>\r\n                      <TD></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver_empty.gif" \r\n                        width=5></TD>\r\n                      <TD align=right height=21></TD>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" \r\n                        width=5></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21><SMALL><SPAN \r\n                        style="COLOR: lime">#1119</SPAN></SMALL></TD></TR>\r\n                    <TR>\r\n                      <TD height=42><IMG \r\n                        src="images/port/buy.gif"><IMG \r\n                        height=16 alt="Precious Metals" \r\n                        src="images/port/4.png" \r\n                        width=13 border=0><IMG height=16 alt=Slaves \r\n                        src="images/port/5.png" \r\n                        width=13 border=0><IMG height=16 alt=Textiles \r\n                        src="images/port/6.png" \r\n                        width=13 border=0><IMG height=16 alt=Circuitry \r\n                        src="images/port/8.png" \r\n                        width=13 border=0><IMG height=16 alt=Weapons \r\n                        src="images/port/9.png" \r\n                        width=13 border=0><IMG height=16 alt=Computer \r\n                        src="images/port/10.png" \r\n                        width=13 border=0><BR><IMG \r\n                        src="images/port/sell.gif"><IMG \r\n                        height=16 alt=Wood \r\n                        src="images/port/1.png" \r\n                        width=13 border=0><IMG height=16 alt=Food \r\n                        src="images/port/2.png" \r\n                        width=13 border=0><IMG height=16 alt=Ore \r\n                        src="images/port/3.png" \r\n                        width=13 border=0><IMG height=16 alt=Machinery \r\n                        src="images/port/7.png" \r\n                        width=13 border=0><IMG height=16 alt="Luxury Items" \r\n                        src="images/port/11.png" \r\n                        width=13 border=0><IMG height=16 alt=Narcotics \r\n                        src="images/port/12.png" \r\n                        width=13 border=0></TD></TR>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" \r\n                        width=12></TD>\r\n                      <TD></TD></TR></TBODY></TABLE></TD>\r\n                <TD vAlign=top align=middle>\r\n                  <TABLE height=120 cellSpacing=0 cellPadding=0 width=120 \r\n                  bgColor=#0b4c1c border=0>\r\n                    <TBODY>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" \r\n                        width=12></TD>\r\n                      <TD></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" \r\n                        width=5></TD>\r\n                      <TD align=right height=21></TD>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" \r\n                        width=5></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21><SMALL><SPAN \r\n                        style="COLOR: lime">#1120</SPAN></SMALL></TD></TR>\r\n                    <TR>\r\n                      <TD height=42></TD></TR>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" \r\n                        width=12></TD>\r\n                      <TD></TD></TR></TBODY></TABLE></TD>\r\n                <TD vAlign=top align=middle>\r\n                  <TABLE height=120 cellSpacing=0 cellPadding=0 width=120 \r\n                  bgColor=#0b4c1c border=0>\r\n                    <TBODY>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" \r\n                        width=12></TD>\r\n                      <TD></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" \r\n                        width=5></TD>\r\n                      <TD align=right height=21></TD>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver_empty.gif" \r\n                        width=5></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21><SMALL><SPAN \r\n                        style="COLOR: lime">#1107</SPAN></SMALL></TD></TR>\r\n                    <TR>\r\n                      <TD height=42><IMG \r\n                        src="images/port/buy.gif"><IMG \r\n                        height=16 alt=Wood \r\n                        src="images/port/1.png" \r\n                        width=13 border=0><IMG height=16 alt=Food \r\n                        src="images/port/2.png" \r\n                        width=13 border=0><IMG height=16 alt=Ore \r\n                        src="images/port/3.png" \r\n                        width=13 border=0><IMG height=16 alt="Precious Metals" \r\n                        src="images/port/4.png" \r\n                        width=13 border=0><IMG height=16 alt=Textiles \r\n                        src="images/port/6.png" \r\n                        width=13 border=0><IMG height=16 alt=Machinery \r\n                        src="images/port/7.png" \r\n                        width=13 border=0><IMG height=16 alt=Weapons \r\n                        src="images/port/9.png" \r\n                        width=13 border=0><BR><IMG \r\n                        src="images/port/sell.gif"><IMG \r\n                        height=16 alt=Slaves \r\n                        src="images/port/5.png" \r\n                        width=13 border=0></TD></TR>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" \r\n                        width=12></TD>\r\n                      <TD></TD></TR></TBODY></TABLE></TD></TR>\r\n              <TR>\r\n                <TD vAlign=top align=middle>\r\n                  <TABLE height=120 cellSpacing=0 cellPadding=0 width=120 \r\n                  bgColor=#0b4c1c border=0>\r\n                    <TBODY>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" \r\n                        width=12></TD>\r\n                      <TD></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" \r\n                        width=5></TD>\r\n                      <TD align=right height=21></TD>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" \r\n                        width=5></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21><SMALL><SPAN \r\n                        style="COLOR: lime">#1131</SPAN></SMALL></TD></TR>\r\n                    <TR>\r\n                      <TD height=42></TD></TR>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" \r\n                        width=12></TD>\r\n                      <TD></TD></TR></TBODY></TABLE></TD>\r\n                <TD vAlign=top align=middle>\r\n                  <TABLE height=120 cellSpacing=0 cellPadding=0 width=120 \r\n                  bgColor=#0b4c1c border=0>\r\n                    <TBODY>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor_empty.gif" \r\n                        width=12></TD>\r\n                      <TD></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" \r\n                        width=5></TD>\r\n                      <TD align=right height=21></TD>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" \r\n                        width=5></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21><SMALL><SPAN \r\n                        style="COLOR: lime">#1132</SPAN></SMALL></TD></TR>\r\n                    <TR>\r\n                      <TD height=42><IMG \r\n                        src="images/port/buy.gif"><IMG \r\n                        height=16 alt=Food \r\n                        src="images/port/2.png" \r\n                        width=13 border=0><IMG height=16 alt=Ore \r\n                        src="images/port/3.png" \r\n                        width=13 border=0><IMG height=16 alt=Slaves \r\n                        src="images/port/5.png" \r\n                        width=13 border=0><IMG height=16 alt=Machinery \r\n                        src="images/port/7.png" \r\n                        width=13 border=0><IMG height=16 alt=Circuitry \r\n                        src="images/port/8.png" \r\n                        width=13 border=0><IMG height=16 alt=Weapons \r\n                        src="images/port/9.png" \r\n                        width=13 border=0><BR><IMG \r\n                        src="images/port/sell.gif"><IMG \r\n                        height=16 alt=Wood \r\n                        src="images/port/1.png" \r\n                        width=13 border=0><IMG height=16 alt="Precious Metals" \r\n                        src="images/port/4.png" \r\n                        width=13 border=0></TD></TR>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor_empty.gif" \r\n                        width=12></TD>\r\n                      <TD></TD></TR></TBODY></TABLE></TD>\r\n                <TD vAlign=top align=middle>\r\n                  <TABLE height=120 cellSpacing=0 cellPadding=0 width=120 \r\n                  bgColor=#0b4c1c border=0>\r\n                    <TBODY>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" \r\n                        width=12></TD>\r\n                      <TD></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" \r\n                        width=5></TD>\r\n                      <TD align=right height=21></TD>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" \r\n                        width=5></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21><SMALL><SPAN \r\n                        style="COLOR: lime">#1133</SPAN></SMALL></TD></TR>\r\n                    <TR>\r\n                      <TD height=42><IMG \r\n                        src="images/port/buy.gif"><IMG \r\n                        height=16 alt=Ore \r\n                        src="images/port/3.png" \r\n                        width=13 border=0><IMG height=16 alt=Slaves \r\n                        src="images/port/5.png" \r\n                        width=13 border=0><BR><IMG \r\n                        src="images/port/sell.gif"><IMG \r\n                        height=16 alt=Wood \r\n                        src="images/port/1.png" \r\n                        width=13 border=0><IMG height=16 alt=Food \r\n                        src="images/port/2.png" \r\n                        width=13 border=0><IMG height=16 alt="Precious Metals" \r\n                        src="images/port/4.png" \r\n                        width=13 border=0></TD></TR>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" \r\n                        width=12></TD>\r\n                      <TD></TD></TR></TBODY></TABLE></TD>\r\n                <TD vAlign=top align=middle>\r\n                  <TABLE height=120 cellSpacing=0 cellPadding=0 width=120 \r\n                  bgColor=#0b4c1c border=0>\r\n                    <TBODY>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" \r\n                        width=12></TD>\r\n                      <TD></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" \r\n                        width=5></TD>\r\n                      <TD align=right height=21></TD>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" \r\n                        width=5></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21><SMALL><SPAN \r\n                        style="COLOR: lime">#1134</SPAN></SMALL></TD></TR>\r\n                    <TR>\r\n                      <TD height=42></TD></TR>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" \r\n                        width=12></TD>\r\n                      <TD></TD></TR></TBODY></TABLE></TD>\r\n                <TD vAlign=top align=middle>\r\n                  <TABLE height=120 cellSpacing=0 cellPadding=0 width=120 \r\n                  bgColor=#0b4c1c border=0>\r\n                    <TBODY>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" \r\n                        width=12></TD>\r\n                      <TD></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" \r\n                        width=5></TD>\r\n                      <TD align=right height=21></TD>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" \r\n                        width=5></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21><SMALL><SPAN \r\n                        style="COLOR: lime">#1121</SPAN></SMALL></TD></TR>\r\n                    <TR>\r\n                      <TD height=42><IMG \r\n                        src="images/port/buy.gif"><IMG \r\n                        height=16 alt=Ore \r\n                        src="images/port/3.png" \r\n                        width=13 border=0><IMG height=16 alt="Precious Metals" \r\n                        src="images/port/4.png" \r\n                        width=13 border=0><BR><IMG \r\n                        src="images/port/sell.gif"><IMG \r\n                        height=16 alt=Wood \r\n                        src="images/port/1.png" \r\n                        width=13 border=0><IMG height=16 alt=Food \r\n                        src="images/port/2.png" \r\n                        width=13 border=0><IMG height=16 alt=Slaves \r\n                        src="images/port/5.png" \r\n                        width=13 border=0></TD></TR>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" \r\n                        width=12></TD>\r\n                      <TD></TD></TR></TBODY></TABLE></TD></TR></TBODY></TABLE></DIV>'),
(60, 0, 5, 'How your ship works', 'There are many different types of ships, but they all work in the same basic way. Each ship is equipped with shields and armor for protection against attack. Most ships have weapons and cargo holds, and some have configurable hardware. Each ship has a "speed" measured in turns per hour. They all can move from sector to sector using the local map or using the current sector view and all ships have a course plotter. This section will give you information on how your ship works for these basic functions. \r\n\r\n<p><!-- CURRENT SECTOR SCREEN SHOT --------------------------------------- -->\r\n<TABLE height="100%" cellSpacing=0 cellPadding=0 width="100%" border=0>\r\n  <TBODY>\r\n  <TR>\r\n    <TD></TD>\r\n    <TD bgColor=#0b8d35 colSpan=3 height=1></TD>\r\n    <TD></TD></TR>\r\n  <TR>\r\n    <TD vAlign=top align=right width=135><!-- menu -->\r\n      <TABLE cellSpacing=5 cellPadding=5 border=0>\r\n        <TBODY>\r\n        <TR>\r\n          <TD align=right><SMALL><SPAN style="COLOR: yellow">10/6/2005 3:12:37 \r\n            PM</SPAN>\r\n            <P><A \r\n            href="manual.php?69"><BIG><B>Current&nbsp;Sector</B></BIG></A><BR><A \r\n            href="manual.php?70"><BIG><B>Local \r\n            Map</B></BIG></A><BR><A \r\n            href="manual.php?71"><BIG><B>Plot \r\n            a Course</B></BIG></A><BR><A \r\n            href="manual.php?72">DL \r\n            MGU Maps</A><BR><A href="manual.php?73">Galaxy Map</A></P>\r\n            <P><A \r\n            href="javascript:alert(''This will take you to a screen with your trader\\\\''s info.'');">Trader</A><BR><A \r\n            href="javascript:alert(''If you are in an Alliance, this will take you to the Message of the Day from your leader. \r\nIf not, then this will take you to a list of all the alliances currently in the game.'');">Alliance</A>\r\n            <P><A \r\n            href="javascript:alert(''If you own your own planet, this will give you some infomation about your planet, without you having to \r\nvisit it.'');">Planet</A><BR><A \r\n            href="manual.php?65">Forces</A>\r\n            <P><A \r\n            href="javascript:alert(''This will take you to a menu of the different types of messages.'');">Messages</A><BR><A \r\n            href="javascript:alert(''Whenever anything important happens, it goes into the news.'');">Read \r\n            News</A><BR><A \r\n            href="javascript:alert(''The Galactic Post (GP) is articles written by players like yourself.  The GP is not only for your reading \r\nenjoyment, but to also add a little of the RPG element to the game.'');">Galactic \r\n            Post</A></P>\r\n            <P><A \r\n            href="javascript:alert(''Want to know a little bit more about another player, but can\\\\''t quite remember their name?  Well you can\r\nsearch for them here.'');">Search \r\n            for Trader</A><BR><A \r\n            href="javascript:alert(''This displays a list of all the players who have been active in the past 10 minutes.  Important Note:  It is\r\npossible to be online, and not be on the current players list.'');">Current \r\n            Players</A></P>\r\n            <P><A \r\n            href="javascript:alert(''Want to see how you stack up against other players?'');">Rankings</A><BR><A \r\n            href="javascript:alert(''This is a cumulation of player\\\\''s stats over all the games.'');">Hall \r\n            of Fame</A><BR><A \r\n            href="javascript:alert(''This covers more stats than that of the Rankings.'');">Current \r\n            HoF</A></P>\r\n            <P><A \r\n            href="javascript:alert(''If there is more than one game active at the time, this will take you back and let you pick the game.'');">Play \r\n            Game</A><BR><A \r\n            href="javascript:alert(''Done playing for now?  You should logoff to make sure that other people who have access to the PC your are on\r\ndon\\\\''t mess with your account.'');">Logoff</A></P>\r\n            <P><A href="javascript:alert(''This is where you are right now.'');">Manual</A><BR><A \r\n            href="javascript:alert(''You can access your account preferences here.'');">Preferences</A><BR><A \r\n            href="javascript:alert(''Want to show off that mug-o-yours?  Well upload your pic here.  Be sure to read the rules before uploading!'');">Edit \r\n            Photo</A><BR><A href="javascript:alert(''Other players wanted to show off their mugs.  You can view \\\\''em here.'');">Album</A></P>\r\n            <P><A \r\n            href="javascript:alert(''Found a bug?  Please report it!'');">Report \r\n            a Bug</A><BR><A \r\n            href="javascript:alert(''Need to contact the admins for something?  Do it here.'');">Contact \r\n            Form</A></P>\r\n            <P><A \r\n            href="javascript:alert(''The wonderful world of chat.  If you have a problem that you need the admins to deal with, you will get a\r\nquicker response time in chat, than you will in email.'');"><BIG><B>IRC \r\n            Chat</B></BIG></A><BR><A \r\n            href="javascript:alert(''The game rules.  Make sure you look over them, so you can avoid getting banned for something you could have \r\navoided.'');"><B>User Policy</B></A><BR><A \r\n            href="javascript:alert(''A place you and the other players can go and read/write messages.  You can also look for help here.'');"><B>WebBoard</B></A><BR><A \r\n            href="javascript:alert(''While this game is free, it does cost to keep it running, so if you enjoy the game, and want to see it \r\naround for sometime to come, it is always helpful to donate. :)'');">Donate</A></A></P></SMALL></TD></TR></TBODY></TABLE><!-- end menu --></TD>\r\n    <TD width=1 bgColor=#0b8d35></TD>\r\n    <TD vAlign=top align=left bgColor=#06240e>\r\n      <TABLE height="100%" cellSpacing=5 cellPadding=5 width="100%" border=0>\r\n        <TBODY>\r\n        <TR>\r\n          <TD vAlign=top>\r\n            <H1>CURRENT SECTOR: 1105</H1>\r\n            <P>\r\n            <TABLE cellSpacing=1 cellPadding=0 width="100%" border=0>\r\n              <TBODY>\r\n              <TR>\r\n                <TD bgColor=#0b8d35>\r\n                  <TABLE cellSpacing=1 cellPadding=3 width="100%" border=0>\r\n                    <TBODY>\r\n                    <TR bgColor=#0b2121>\r\n                      <TD>\r\n                        <TABLE cellSpacing=2 cellPadding=3 width="100%" \r\nborder=0>\r\n                          <TBODY>\r\n                          <TR bgColor=#0b8d35>\r\n                            <TD align=middle><SMALL><A \r\n                              href="manual.php?71">Plot \r\n                              a Course</A> | <A \r\n                              href="manual.php?70">Local \r\n                              Map</A> | <A \r\n                              href="manual.php?72">Galaxy \r\n                          Map</A></SMALL></TD></TR></TBODY></TABLE></TD></TR></TBODY></TABLE></TD></TR></TBODY></TABLE></P>\r\n            <P><SMALL>Creonti</SMALL></P>\r\n            <P>\r\n            <TABLE borderColor=#0b8d35 cellSpacing=0 border=1>\r\n              <TBODY>\r\n              <TR>\r\n                <TH>Location</TH>\r\n                <TH>Option</TH></TR>\r\n              <TR>\r\n                <TD width=250><IMG \r\n                  src="images/beacon.gif">&nbsp;Beacon \r\n                  of Ultimate Protection</TD>\r\n                <TD vAlign=center \r\n            align=middle>&nbsp;</TD></TR></TBODY></TABLE></P>\r\n            <P>\r\n            <TABLE borderColor=#0b8d35 cellSpacing=0 border=1>\r\n              <TBODY>\r\n              <TR>\r\n                <TH align=middle colSpan=3>Move to</TH></TR>\r\n              <TR>\r\n                <TD>&nbsp;</TD>\r\n                <TD align=middle width=80 height=40><A \r\n                  href="javascript:alert(''This would move you up one to sector #1091.  It would cost you one turn to move.'');"><SPAN \r\n                  style="COLOR: green">1091 (1)</SPAN></A><BR><A \r\n                  href="javascript:alert(''This would scan the sector immediately above you.  This would give you an idea of what is in \r\nthat sector, before deciding to move there.'');"><SPAN \r\n                  style="FONT-SIZE: 75%">Scan<SPAN></A></SPAN></SPAN></TD>\r\n                <TD>&nbsp;</TD></TR>\r\n              <TR>\r\n                <TD align=middle width=80 height=40><A \r\n                  href="javascript:alert(''This would move you left one to sector #1104.  It would cost you one turn to move.'');"><SPAN \r\n                  style="COLOR: green">1104 (1)</SPAN></A><BR><A \r\n                  href="javascript:alert(''This would scan the sector immediately left of you.  This would give you an idea of what is in \r\nthat sector, before deciding to move there.'');"><SPAN \r\n                  style="FONT-SIZE: 75%">Scan<SPAN></A></SPAN></SPAN></TD>\r\n                <TD align=middle><A \r\n                  href="javascript:alert(''This would take you to the current sector screen of the screen you already are in.'');"><SPAN \r\n                  style="COLOR: green">1105</SPAN></A></TD>\r\n                <TD align=middle width=80 height=40><A \r\n                  href="javascript:alert(''This would move you right one to sector #1106.  It would cost you one turn to move.'');"><SPAN \r\n                  style="COLOR: green">1106 (1)</SPAN></A><BR><A \r\n                  href="javascript:alert(''This would scan the sector immediately right of you.  This would give you an idea of what is in \r\nthat sector, before deciding to move there.'');"><SPAN \r\n                  style="FONT-SIZE: 75%">Scan<SPAN></A></SPAN></SPAN></TD></TR>\r\n              <TR>\r\n                <TD>&nbsp;</TD>\r\n                <TD align=middle width=80 height=40>&nbsp;</TD>\r\n                <TD align=middle width=80 \r\n            height=40>&nbsp;</TD></TR></TBODY></TABLE></P>\r\n            <P>\r\n            <TABLE borderColor=#0b8d35 cellSpacing=0 border=1>\r\n              <TBODY>\r\n              <TR>\r\n                <TH width=150>Trader</TH>\r\n                <TH width=100>Option</TH></TR>\r\n              <TR>\r\n                <TD><A \r\n                  href="javascript:alert(''This would take you to a menu where you could view more info about this player.'');"><SPAN \r\n                  style="COLOR: yellow">Omega \r\n                  Prime&nbsp;(150)</SPAN></A>&nbsp;<A \r\n                  href="javascript:alert(''If you are in this alliance, it would take you to the Message of the Day from your leader.\r\nIf not, it would take you to the alliance roster, so you could see who is in the alliance.'');">(Willowstrance)</A><BR><FONT \r\n                  color=white>Leviathan (2/8)</FONT></TD>\r\n                <TD align=middle><A \r\n                  href="javascript:alert(''This would attempt to Examine the player\\\\''s ship.  There are certain conditions, such you being under \r\nfederal or newbie protection, that would prevent you from examining the player\\\\''s ship.'');">Examine</A></TD></TR>\r\n              <TR>\r\n                <TD><A \r\n                  href="javascript:alert(''This would take you to a menu where you could view more info about this player.'');"><SPAN \r\n                  style="COLOR: yellow">Bruno&nbsp;(185)</SPAN></A>&nbsp;<A \r\n                  href="javascript:alert(''If you are in this alliance, it would take you to the Message of the Day from your leader.\r\nIf not, it would take you to the alliance roster, so you could see who is in the alliance.'');">(Legitimate \r\n                  Businessmen)</A><BR><FONT color=white>Planetary Super \r\n                  Freighter (2/15)</FONT></TD>\r\n                <TD align=middle><A \r\n                  href="javascript:alert(''This would attempt to Examine the player\\\\''s ship.  There are certain conditions, such you being under \r\nfederal or newbie protection, that would prevent you from examining the player\\\\''s ship.'');">Examine</A></TD></TR></TBODY></TABLE></P></TD></TR>\r\n        <TR>\r\n          <TD vAlign=bottom><!-- copyright -->\r\n            <TABLE width="100%" border=0>\r\n              <TBODY>\r\n              <TR>\r\n                <TD vAlign=center><BR>\r\n                  <CENTER><SPAN style="FONT-SIZE: 75%">Get <B><U>FREE \r\n                  TURNS</U></B> for voting if you see the \r\n                  star.</SPAN></CENTER><A \r\n                  href="javascript:alert(''In game, if you see a star on the link, you will get bonus turns for voting if you click.  You can only get \r\nbonus turns once a day from each voting site.'');"><IMG \r\n                  height=41 \r\n                  src="images/game_sites/mpogd_vote.png" width=98 \r\n                  border=0></A><A \r\n                  href="javascript:alert(''In game, if you see a star on the link, you will get bonus turns for voting if you click.  You can only get \r\nbonus turns once a day from each voting site.'');"><IMG height=41 \r\n                  src="images/game_sites/twg.png" width=98 \r\n                  border=0></A>&nbsp;&nbsp;<A \r\n                  href="javascript:alert(''In game, if you see a star on the link, you will get bonus turns for voting if you click.  You can only get \r\nbonus turns once a day from each voting site.'');"><IMG \r\n                  height=41 src="images/game_sites/omgn_vote.jpg" \r\n                  width=98 border=0></A></TD>\r\n                <TD style="VERTICAL-ALIGN: bottom" align=right \r\n                  width="100%"><SPAN style="FONT-SIZE: 75%">Space Merchant \r\n                  Realms<BR><A \r\n                  href="javascript:alert(''This would take you to the change log, where you could view the changes that have been made to the game.'');">v1.1.1</A>&nbsp;&nbsp;&nbsp;'),
(61, 60, 1, 'Ship Information', 'On the right hand of your screen, you will see information about your ship. Ship information is grouped into sections. The first group shows the ship type and its basic information. \r\n\r\n<p>Dark Mirage \r\n<br>Rating : 29/18 \r\n<br>Shields : 825/825 \r\n<br>Armor : 825/825 \r\n<br>CIJSD : ---*- \r\n\r\n<p>In the above example, the type of ship (Dark Mirage), its attack and defense rating (29/18 ), the shields and armor (825/825), and its configurable hardware is shown. \r\n\r\n<p><!-- CURRENT SECTOR SCREEN SHOT --------------------------------------- -->\r\n<TABLE height="100%" cellSpacing=0 cellPadding=0 width="100%" border=0>\r\n  <TBODY>\r\n  <TR>\r\n    <TD></TD>\r\n    <TD bgColor=#0b8d35 colSpan=3 height=1></TD>\r\n    <TD></TD></TR>\r\n  <TR>\r\n    <TD vAlign=top align=right width=135><!-- menu -->\r\n      <TABLE cellSpacing=5 cellPadding=5 border=0>\r\n        <TBODY>\r\n        <TR>\r\n          <TD align=right><SMALL><SPAN style="COLOR: yellow">10/6/2005 3:12:37 \r\n            PM</SPAN>\r\n            <P><A \r\n            href="manual.php?69"><BIG><B>Current&nbsp;Sector</B></BIG></A><BR><A \r\n            href="manual.php?70"><BIG><B>Local \r\n            Map</B></BIG></A><BR><A \r\n            href="manual.php?71"><BIG><B>Plot \r\n            a Course</B></BIG></A><BR><A \r\n            href="manual.php?72">DL \r\n            MGU Maps</A><BR><A href="manual.php?73">Galaxy Map</A></P>\r\n            <P><A \r\n            href="javascript:alert(''This will take you to a screen with your trader\\\\''s info.'');">Trader</A><BR><A \r\n            href="javascript:alert(''If you are in an Alliance, this will take you to the Message of the Day from your leader. \r\nIf not, then this will take you to a list of all the alliances currently in the game.'');">Alliance</A>\r\n            <P><A \r\n            href="javascript:alert(''If you own your own planet, this will give you some infomation about your planet, without you having to \r\nvisit it.'');">Planet</A><BR><A \r\n            href="manual.php?65">Forces</A>\r\n            <P><A \r\n            href="javascript:alert(''This will take you to a menu of the different types of messages.'');">Messages</A><BR><A \r\n            href="javascript:alert(''Whenever anything important happens, it goes into the news.'');">Read \r\n            News</A><BR><A \r\n            href="javascript:alert(''The Galactic Post (GP) is articles written by players like yourself.  The GP is not only for your reading \r\nenjoyment, but to also add a little of the RPG element to the game.'');">Galactic \r\n            Post</A></P>\r\n            <P><A \r\n            href="javascript:alert(''Want to know a little bit more about another player, but can\\\\''t quite remember their name?  Well you can\r\nsearch for them here.'');">Search \r\n            for Trader</A><BR><A \r\n            href="javascript:alert(''This displays a list of all the players who have been active in the past 10 minutes.  Important Note:  It is\r\npossible to be online, and not be on the current players list.'');">Current \r\n            Players</A></P>\r\n            <P><A \r\n            href="javascript:alert(''Want to see how you stack up against other players?'');">Rankings</A><BR><A \r\n            href="javascript:alert(''This is a cumulation of player\\\\''s stats over all the games.'');">Hall \r\n            of Fame</A><BR><A \r\n            href="javascript:alert(''This covers more stats than that of the Rankings.'');">Current \r\n            HoF</A></P>\r\n            <P><A \r\n            href="javascript:alert(''If there is more than one game active at the time, this will take you back and let you pick the game.'');">Play \r\n            Game</A><BR><A \r\n            href="javascript:alert(''Done playing for now?  You should logoff to make sure that other people who have access to the PC your are on\r\ndon\\\\''t mess with your account.'');">Logoff</A></P>\r\n            <P><A href="javascript:alert(''This is where you are right now.'');">Manual</A><BR><A \r\n            href="javascript:alert(''You can access your account preferences here.'');">Preferences</A><BR><A \r\n            href="javascript:alert(''Want to show off that mug-o-yours?  Well upload your pic here.  Be sure to read the rules before uploading!'');">Edit \r\n            Photo</A><BR><A href="javascript:alert(''Other players wanted to show off their mugs.  You can view \\\\''em here.'');">Album</A></P>\r\n            <P><A \r\n            href="javascript:alert(''Found a bug?  Please report it!'');">Report \r\n            a Bug</A><BR><A \r\n            href="javascript:alert(''Need to contact the admins for something?  Do it here.'');">Contact \r\n            Form</A></P>\r\n            <P><A \r\n            href="javascript:alert(''The wonderful world of chat.  If you have a problem that you need the admins to deal with, you will get a\r\nquicker response time in chat, than you will in email.'');"><BIG><B>IRC \r\n            Chat</B></BIG></A><BR><A \r\n            href="javascript:alert(''The game rules.  Make sure you look over them, so you can avoid getting banned for something you could have \r\navoided.'');"><B>User Policy</B></A><BR><A \r\n            href="javascript:alert(''A place you and the other players can go and read/write messages.  You can also look for help here.'');"><B>WebBoard</B></A><BR><A \r\n            href="javascript:alert(''While this game is free, it does cost to keep it running, so if you enjoy the game, and want to see it \r\naround for sometime to come, it is always helpful to donate. :)'');">Donate</A></A></P></SMALL></TD></TR></TBODY></TABLE><!-- end menu --></TD>\r\n    <TD width=1 bgColor=#0b8d35></TD>\r\n    <TD vAlign=top align=left bgColor=#06240e>\r\n      <TABLE height="100%" cellSpacing=5 cellPadding=5 width="100%" border=0>\r\n        <TBODY>\r\n        <TR>\r\n          <TD vAlign=top>\r\n            <H1>CURRENT SECTOR: 1105</H1>\r\n            <P>\r\n            <TABLE cellSpacing=1 cellPadding=0 width="100%" border=0>\r\n              <TBODY>\r\n              <TR>\r\n                <TD bgColor=#0b8d35>\r\n                  <TABLE cellSpacing=1 cellPadding=3 width="100%" border=0>\r\n                    <TBODY>\r\n                    <TR bgColor=#0b2121>\r\n                      <TD>\r\n                        <TABLE cellSpacing=2 cellPadding=3 width="100%" \r\nborder=0>\r\n                          <TBODY>\r\n                          <TR bgColor=#0b8d35>\r\n                            <TD align=middle><SMALL><A \r\n                              href="manual.php?71">Plot \r\n                              a Course</A> | <A \r\n                              href="manual.php?70">Local \r\n                              Map</A> | <A \r\n                              href="manual.php?72">Galaxy \r\n                          Map</A></SMALL></TD></TR></TBODY></TABLE></TD></TR></TBODY></TABLE></TD></TR></TBODY></TABLE></P>\r\n            <P><SMALL>Creonti</SMALL></P>\r\n            <P>\r\n            <TABLE borderColor=#0b8d35 cellSpacing=0 border=1>\r\n              <TBODY>\r\n              <TR>\r\n                <TH>Location</TH>\r\n                <TH>Option</TH></TR>\r\n              <TR>\r\n                <TD width=250><IMG \r\n                  src="images/beacon.gif">&nbsp;Beacon \r\n                  of Ultimate Protection</TD>\r\n                <TD vAlign=center \r\n            align=middle>&nbsp;</TD></TR></TBODY></TABLE></P>\r\n            <P>\r\n            <TABLE borderColor=#0b8d35 cellSpacing=0 border=1>\r\n              <TBODY>\r\n              <TR>\r\n                <TH align=middle colSpan=3>Move to</TH></TR>\r\n              <TR>\r\n                <TD>&nbsp;</TD>\r\n                <TD align=middle width=80 height=40><A \r\n                  href="javascript:alert(''This would move you up one to sector #1091.  It would cost you one turn to move.'');"><SPAN \r\n                  style="COLOR: green">1091 (1)</SPAN></A><BR><A \r\n                  href="javascript:alert(''This would scan the sector immediately above you.  This would give you an idea of what is in \r\nthat sector, before deciding to move there.'');"><SPAN \r\n                  style="FONT-SIZE: 75%">Scan<SPAN></A></SPAN></SPAN></TD>\r\n                <TD>&nbsp;</TD></TR>\r\n              <TR>\r\n                <TD align=middle width=80 height=40><A \r\n                  href="javascript:alert(''This would move you left one to sector #1104.  It would cost you one turn to move.'');"><SPAN \r\n                  style="COLOR: green">1104 (1)</SPAN></A><BR><A \r\n                  href="javascript:alert(''This would scan the sector immediately left of you.  This would give you an idea of what is in \r\nthat sector, before deciding to move there.'');"><SPAN \r\n                  style="FONT-SIZE: 75%">Scan<SPAN></A></SPAN></SPAN></TD>\r\n                <TD align=middle><A \r\n                  href="javascript:alert(''This would take you to the current sector screen of the screen you already are in.'');"><SPAN \r\n                  style="COLOR: green">1105</SPAN></A></TD>\r\n                <TD align=middle width=80 height=40><A \r\n                  href="javascript:alert(''This would move you right one to sector #1106.  It would cost you one turn to move.'');"><SPAN \r\n                  style="COLOR: green">1106 (1)</SPAN></A><BR><A \r\n                  href="javascript:alert(''This would scan the sector immediately right of you.  This would give you an idea of what is in \r\nthat sector, before deciding to move there.'');"><SPAN \r\n                  style="FONT-SIZE: 75%">Scan<SPAN></A></SPAN></SPAN></TD></TR>\r\n              <TR>\r\n                <TD>&nbsp;</TD>\r\n                <TD align=middle width=80 height=40>&nbsp;</TD>\r\n                <TD align=middle width=80 \r\n            height=40>&nbsp;</TD></TR></TBODY></TABLE></P>\r\n            <P>\r\n            <TABLE borderColor=#0b8d35 cellSpacing=0 border=1>\r\n              <TBODY>\r\n              <TR>\r\n                <TH width=150>Trader</TH>\r\n                <TH width=100>Option</TH></TR>\r\n              <TR>\r\n                <TD><A \r\n                  href="javascript:alert(''This would take you to a menu where you could view more info about this player.'');"><SPAN \r\n                  style="COLOR: yellow">Omega \r\n                  Prime&nbsp;(150)</SPAN></A>&nbsp;<A \r\n                  href="javascript:alert(''If you are in this alliance, it would take you to the Message of the Day from your leader.\r\nIf not, it would take you to the alliance roster, so you could see who is in the alliance.'');">(Willowstrance)</A><BR><FONT \r\n                  color=white>Leviathan (2/8)</FONT></TD>\r\n                <TD align=middle><A \r\n                  href="javascript:alert(''This would attempt to Examine the player\\\\''s ship.  There are certain conditions, such you being under \r\nfederal or newbie protection, that would prevent you from examining the player\\\\''s ship.'');">Examine</A></TD></TR>\r\n              <TR>\r\n                <TD><A \r\n                  href="javascript:alert(''This would take you to a menu where you could view more info about this player.'');"><SPAN \r\n                  style="COLOR: yellow">Bruno&nbsp;(185)</SPAN></A>&nbsp;<A \r\n                  href="javascript:alert(''If you are in this alliance, it would take you to the Message of the Day from your leader.\r\nIf not, it would take you to the alliance roster, so you could see who is in the alliance.'');">(Legitimate \r\n                  Businessmen)</A><BR><FONT color=white>Planetary Super \r\n                  Freighter (2/15)</FONT></TD>\r\n                <TD align=middle><A \r\n                  href="javascript:alert(''This would attempt to Examine the player\\\\''s ship.  There are certain conditions, such you being under \r\nfederal or newbie protection, that would prevent you from examining the player\\\\''s ship.'');">Examine</A></TD></TR></TBODY></TABLE></P></TD></TR>\r\n        <TR>\r\n          <TD vAlign=bottom><!-- copyright -->\r\n            <TABLE width="100%" border=0>\r\n              <TBODY>\r\n              <TR>\r\n                <TD vAlign=center><BR>\r\n                  <CENTER><SPAN style="FONT-SIZE: 75%">Get <B><U>FREE \r\n                  TURNS</U></B> for voting if you see the \r\n                  star.</SPAN></CENTER><A \r\n                  href="javascript:alert(''In game, if you see a star on the link, you will get bonus turns for voting if you click.  You can only get \r\nbonus turns once a day from each voting site.'');"><IMG \r\n                  height=41 \r\n                  src="images/game_sites/mpogd_vote.png" width=98 \r\n                  border=0></A><A \r\n                  href="javascript:alert(''In game, if you see a star on the link, you will get bonus turns for voting if you click.  You can only get \r\nbonus turns once a day from each voting site.'');"><IMG height=41 \r\n                  src="images/game_sites/twg.png" width=98 \r\n                  border=0></A>&nbsp;&nbsp;<A \r\n                  href="javascript:alert(''In game, if you see a star on the link, you will get bonus turns for voting if you click.  You can only get \r\nbonus turns once a day from each voting site.'');"><IMG \r\n                  height=41 src="images/game_sites/omgn_vote.jpg" \r\n                  width=98 border=0></A></TD>\r\n                <TD style="VERTICAL-ALIGN: bottom" align=right \r\n                  width="100%"><SPAN style="FONT-SIZE: 75%">Space Merchant \r\n                  Realms<BR><A \r\n                  href="javascript:alert(''This would take you to the change log, where you could view the changes that have been made to the game.'');">v1.1.1</A>&nbsp;&nbsp;&nbsp;'),
(62, 60, 2, 'Rating', 'The rating of your ship is represented by a pair of numbers. The first number is the attack rating and indicates how much damage your ship does. Basically, the higher the attack rating the better the damage your ship will do against other ships. That attack rating is calculated like this: \r\n\r\n<p>Attack Rating = INT(((armor dmg + shield dmg)/40) + (drones/50)) \r\n\r\n<p>So for example, a Federal Ultimatum with Holy Hand Grenade, Salvene EM Flux Cannon x3, Creonti "Big Daddy" x2, Torpedo Launcher and 120 Combat Drones would be: INT(((750+600)/40)+(120/50)) = 36 \r\n\r\n<p>The second number is the defense rating and shows how much damage your ship can withstand. Again, the higher the number, the more damage you can take. It''s calculated like this: \r\n\r\n<p>(# of Armor + # of Shield + (# of drones * 3)) / 100 \r\n\r\n<p>So in the example above, the Federal Ultimatum with 700 shields and 600 armor and 120 combat drones would be: (700 + 600 + (120 * 3)) / 100 = 17 '),
(63, 60, 3, 'Shields and Armor', 'Your ship is designed with a number of shields and armor to protect it from harm. The first number in the pair of numbers shown next to Shields and Armor is the actual amount you have and the second number is the maximum possible for your type of ship. When hitting mines or under attack by another ship, your ship takes damage. It will lose shields first. When all shields are gone, your ship begins to lose armor. If your ship has combat drones, these will absorb damage as well. When all shields and armor and drones are gone, your ship is destroyed. You are placed in an escape pod and sent to your racial Headquarters. You lose all of the cargo and cash that was onboard your ship, and you lose some amount of experience. You will then have to buy a new ship with what money your have saved in the Bank. \r\n\r\n<p>You can repair your damaged ship by buying shields and armor at UNO shops located around the galaxies. '),
(64, 60, 4, 'CIJSD', 'These letter abbreviations and the row of dashes ''-'' or stars ''*'' that follow indicate the configurable hardware that can be installed on a ship. The use of these different types of hardware is detailed elsewhere, but here are what the abbreviations stand for: \r\n\r\n<p><b><a href="manual.php?23">C= Cloak</a>\r\n<br><a href="manual.php?24">I= Illusion Generator</a>\r\n<br><a href="manual.php?25">J= Jump Drive</a>\r\n<br><a href="manual.php?22">S= Scanner</a>\r\n<br><a href="manual.php?26">D= Drone Scrambler</a></b>\r\n\r\n<p>Not all ships have configurable hardware, and most can only use one or two different types. If you have one of these installed on your ship, it will by shown by a * in the row of dashes that correspond to the letter. In the example above, the star is in the 4th place meaning that the ship is equipped with a scanner. '),
(65, 60, 5, 'Forces', 'The next group of ship information on the right hand side shows the forces that your ship can carry. Forces are the term for Mines, Combat Drones, and Scouts. Not all ships carry forces. \r\n\r\n<p>Forces \r\n<br>[x] Mines : 11/30 \r\n<br>[x] Combat : 50/50 \r\n<br>Scout : 0/0 \r\n\r\n<p>In the above example, the ship has 11 mines out of a maximum of 30 and all 50 combat drones that it can carry. No scouts are allowed on this ship. The [x] is a clickable link that lets you drop one of that type of force into the current sector where you ship is. \r\n\r\n<p><!-- CURRENT SECTOR SCREEN SHOT --------------------------------------- -->\r\n<TABLE height="100%" cellSpacing=0 cellPadding=0 width="100%" border=0>\r\n  <TBODY>\r\n  <TR>\r\n    <TD></TD>\r\n    <TD bgColor=#0b8d35 colSpan=3 height=1></TD>\r\n    <TD></TD></TR>\r\n  <TR>\r\n    <TD vAlign=top align=right width=135><!-- menu -->\r\n      <TABLE cellSpacing=5 cellPadding=5 border=0>\r\n        <TBODY>\r\n        <TR>\r\n          <TD align=right><SMALL><SPAN style="COLOR: yellow">10/6/2005 3:12:37 \r\n            PM</SPAN>\r\n            <P><A \r\n            href="manual.php?69"><BIG><B>Current&nbsp;Sector</B></BIG></A><BR><A \r\n            href="manual.php?70"><BIG><B>Local \r\n            Map</B></BIG></A><BR><A \r\n            href="manual.php?71"><BIG><B>Plot \r\n            a Course</B></BIG></A><BR><A \r\n            href="manual.php?72">DL \r\n            MGU Maps</A><BR><A href="manual.php?73">Galaxy Map</A></P>\r\n            <P><A \r\n            href="javascript:alert(''This will take you to a screen with your trader\\\\''s info.'');">Trader</A><BR><A \r\n            href="javascript:alert(''If you are in an Alliance, this will take you to the Message of the Day from your leader. \r\nIf not, then this will take you to a list of all the alliances currently in the game.'');">Alliance</A>\r\n            <P><A \r\n            href="javascript:alert(''If you own your own planet, this will give you some infomation about your planet, without you having to \r\nvisit it.'');">Planet</A><BR><A \r\n            href="manual.php?65">Forces</A>\r\n            <P><A \r\n            href="javascript:alert(''This will take you to a menu of the different types of messages.'');">Messages</A><BR><A \r\n            href="javascript:alert(''Whenever anything important happens, it goes into the news.'');">Read \r\n            News</A><BR><A \r\n            href="javascript:alert(''The Galactic Post (GP) is articles written by players like yourself.  The GP is not only for your reading \r\nenjoyment, but to also add a little of the RPG element to the game.'');">Galactic \r\n            Post</A></P>\r\n            <P><A \r\n            href="javascript:alert(''Want to know a little bit more about another player, but can\\\\''t quite remember their name?  Well you can\r\nsearch for them here.'');">Search \r\n            for Trader</A><BR><A \r\n            href="javascript:alert(''This displays a list of all the players who have been active in the past 10 minutes.  Important Note:  It is\r\npossible to be online, and not be on the current players list.'');">Current \r\n            Players</A></P>\r\n            <P><A \r\n            href="javascript:alert(''Want to see how you stack up against other players?'');">Rankings</A><BR><A \r\n            href="javascript:alert(''This is a cumulation of player\\\\''s stats over all the games.'');">Hall \r\n            of Fame</A><BR><A \r\n            href="javascript:alert(''This covers more stats than that of the Rankings.'');">Current \r\n            HoF</A></P>\r\n            <P><A \r\n            href="javascript:alert(''If there is more than one game active at the time, this will take you back and let you pick the game.'');">Play \r\n            Game</A><BR><A \r\n            href="javascript:alert(''Done playing for now?  You should logoff to make sure that other people who have access to the PC your are on\r\ndon\\\\''t mess with your account.'');">Logoff</A></P>\r\n            <P><A href="javascript:alert(''This is where you are right now.'');">Manual</A><BR><A \r\n            href="javascript:alert(''You can access your account preferences here.'');">Preferences</A><BR><A \r\n            href="javascript:alert(''Want to show off that mug-o-yours?  Well upload your pic here.  Be sure to read the rules before uploading!'');">Edit \r\n            Photo</A><BR><A href="javascript:alert(''Other players wanted to show off their mugs.  You can view \\\\''em here.'');">Album</A></P>\r\n            <P><A \r\n            href="javascript:alert(''Found a bug?  Please report it!'');">Report \r\n            a Bug</A><BR><A \r\n            href="javascript:alert(''Need to contact the admins for something?  Do it here.'');">Contact \r\n            Form</A></P>\r\n            <P><A \r\n            href="javascript:alert(''The wonderful world of chat.  If you have a problem that you need the admins to deal with, you will get a\r\nquicker response time in chat, than you will in email.'');"><BIG><B>IRC \r\n            Chat</B></BIG></A><BR><A \r\n            href="javascript:alert(''The game rules.  Make sure you look over them, so you can avoid getting banned for something you could have \r\navoided.'');"><B>User Policy</B></A><BR><A \r\n            href="javascript:alert(''A place you and the other players can go and read/write messages.  You can also look for help here.'');"><B>WebBoard</B></A><BR><A \r\n            href="javascript:alert(''While this game is free, it does cost to keep it running, so if you enjoy the game, and want to see it \r\naround for sometime to come, it is always helpful to donate. :)'');">Donate</A></A></P></SMALL></TD></TR></TBODY></TABLE><!-- end menu --></TD>\r\n    <TD width=1 bgColor=#0b8d35></TD>\r\n    <TD vAlign=top align=left bgColor=#06240e>\r\n      <TABLE height="100%" cellSpacing=5 cellPadding=5 width="100%" border=0>\r\n        <TBODY>\r\n        <TR>\r\n          <TD vAlign=top>\r\n            <H1>CURRENT SECTOR: 1105</H1>\r\n            <P>\r\n            <TABLE cellSpacing=1 cellPadding=0 width="100%" border=0>\r\n              <TBODY>\r\n              <TR>\r\n                <TD bgColor=#0b8d35>\r\n                  <TABLE cellSpacing=1 cellPadding=3 width="100%" border=0>\r\n                    <TBODY>\r\n                    <TR bgColor=#0b2121>\r\n                      <TD>\r\n                        <TABLE cellSpacing=2 cellPadding=3 width="100%" \r\nborder=0>\r\n                          <TBODY>\r\n                          <TR bgColor=#0b8d35>\r\n                            <TD align=middle><SMALL><A \r\n                              href="manual.php?71">Plot \r\n                              a Course</A> | <A \r\n                              href="manual.php?70">Local \r\n                              Map</A> | <A \r\n                              href="manual.php?72">Galaxy \r\n                          Map</A></SMALL></TD></TR></TBODY></TABLE></TD></TR></TBODY></TABLE></TD></TR></TBODY></TABLE></P>\r\n            <P><SMALL>Creonti</SMALL></P>\r\n            <P>\r\n            <TABLE borderColor=#0b8d35 cellSpacing=0 border=1>\r\n              <TBODY>\r\n              <TR>\r\n                <TH>Location</TH>\r\n                <TH>Option</TH></TR>\r\n              <TR>\r\n                <TD width=250><IMG \r\n                  src="images/beacon.gif">&nbsp;Beacon \r\n                  of Ultimate Protection</TD>\r\n                <TD vAlign=center \r\n            align=middle>&nbsp;</TD></TR></TBODY></TABLE></P>\r\n            <P>\r\n            <TABLE borderColor=#0b8d35 cellSpacing=0 border=1>\r\n              <TBODY>\r\n              <TR>\r\n                <TH align=middle colSpan=3>Move to</TH></TR>\r\n              <TR>\r\n                <TD>&nbsp;</TD>\r\n                <TD align=middle width=80 height=40><A \r\n                  href="javascript:alert(''This would move you up one to sector #1091.  It would cost you one turn to move.'');"><SPAN \r\n                  style="COLOR: green">1091 (1)</SPAN></A><BR><A \r\n                  href="javascript:alert(''This would scan the sector immediately above you.  This would give you an idea of what is in \r\nthat sector, before deciding to move there.'');"><SPAN \r\n                  style="FONT-SIZE: 75%">Scan<SPAN></A></SPAN></SPAN></TD>\r\n                <TD>&nbsp;</TD></TR>\r\n              <TR>\r\n                <TD align=middle width=80 height=40><A \r\n                  href="javascript:alert(''This would move you left one to sector #1104.  It would cost you one turn to move.'');"><SPAN \r\n                  style="COLOR: green">1104 (1)</SPAN></A><BR><A \r\n                  href="javascript:alert(''This would scan the sector immediately left of you.  This would give you an idea of what is in \r\nthat sector, before deciding to move there.'');"><SPAN \r\n                  style="FONT-SIZE: 75%">Scan<SPAN></A></SPAN></SPAN></TD>\r\n                <TD align=middle><A \r\n                  href="javascript:alert(''This would take you to the current sector screen of the screen you already are in.'');"><SPAN \r\n                  style="COLOR: green">1105</SPAN></A></TD>\r\n                <TD align=middle width=80 height=40><A \r\n                  href="javascript:alert(''This would move you right one to sector #1106.  It would cost you one turn to move.'');"><SPAN \r\n                  style="COLOR: green">1106 (1)</SPAN></A><BR><A \r\n                  href="javascript:alert(''This would scan the sector immediately right of you.  This would give you an idea of what is in \r\nthat sector, before deciding to move there.'');"><SPAN \r\n                  style="FONT-SIZE: 75%">Scan<SPAN></A></SPAN></SPAN></TD></TR>\r\n              <TR>\r\n                <TD>&nbsp;</TD>\r\n                <TD align=middle width=80 height=40>&nbsp;</TD>\r\n                <TD align=middle width=80 \r\n            height=40>&nbsp;</TD></TR></TBODY></TABLE></P>\r\n            <P>\r\n            <TABLE borderColor=#0b8d35 cellSpacing=0 border=1>\r\n              <TBODY>\r\n              <TR>\r\n                <TH width=150>Trader</TH>\r\n                <TH width=100>Option</TH></TR>\r\n              <TR>\r\n                <TD><A \r\n                  href="javascript:alert(''This would take you to a menu where you could view more info about this player.'');"><SPAN \r\n                  style="COLOR: yellow">Omega \r\n                  Prime&nbsp;(150)</SPAN></A>&nbsp;<A \r\n                  href="javascript:alert(''If you are in this alliance, it would take you to the Message of the Day from your leader.\r\nIf not, it would take you to the alliance roster, so you could see who is in the alliance.'');">(Willowstrance)</A><BR><FONT \r\n                  color=white>Leviathan (2/8)</FONT></TD>\r\n                <TD align=middle><A \r\n                  href="javascript:alert(''This would attempt to Examine the player\\\\''s ship.  There are certain conditions, such you being under \r\nfederal or newbie protection, that would prevent you from examining the player\\\\''s ship.'');">Examine</A></TD></TR>\r\n              <TR>\r\n                <TD><A \r\n                  href="javascript:alert(''This would take you to a menu where you could view more info about this player.'');"><SPAN \r\n                  style="COLOR: yellow">Bruno&nbsp;(185)</SPAN></A>&nbsp;<A \r\n                  href="javascript:alert(''If you are in this alliance, it would take you to the Message of the Day from your leader.\r\nIf not, it would take you to the alliance roster, so you could see who is in the alliance.'');">(Legitimate \r\n                  Businessmen)</A><BR><FONT color=white>Planetary Super \r\n                  Freighter (2/15)</FONT></TD>\r\n                <TD align=middle><A \r\n                  href="javascript:alert(''This would attempt to Examine the player\\\\''s ship.  There are certain conditions, such you being under \r\nfederal or newbie protection, that would prevent you from examining the player\\\\''s ship.'');">Examine</A></TD></TR></TBODY></TABLE></P></TD></TR>\r\n        <TR>\r\n          <TD vAlign=bottom><!-- copyright -->\r\n            <TABLE width="100%" border=0>\r\n              <TBODY>\r\n              <TR>\r\n                <TD vAlign=center><BR>\r\n                  <CENTER><SPAN style="FONT-SIZE: 75%">Get <B><U>FREE \r\n                  TURNS</U></B> for voting if you see the \r\n                  star.</SPAN></CENTER><A \r\n                  href="javascript:alert(''In game, if you see a star on the link, you will get bonus turns for voting if you click.  You can only get \r\nbonus turns once a day from each voting site.'');"><IMG \r\n                  height=41 \r\n                  src="images/game_sites/mpogd_vote.png" width=98 \r\n                  border=0></A><A \r\n                  href="javascript:alert(''In game, if you see a star on the link, you will get bonus turns for voting if you click.  You can only get \r\nbonus turns once a day from each voting site.'');"><IMG height=41 \r\n                  src="images/game_sites/twg.png" width=98 \r\n                  border=0></A>&nbsp;&nbsp;<A \r\n                  href="javascript:alert(''In game, if you see a star on the link, you will get bonus turns for voting if you click.  You can only get \r\nbonus turns once a day from each voting site.'');"><IMG \r\n                  height=41 src="images/game_sites/omgn_vote.jpg" \r\n                  width=98 border=0></A></TD>\r\n                <TD style="VERTICAL-ALIGN: bottom" align=right \r\n                  width="100%"><SPAN style="FONT-SIZE: 75%">Space Merchant \r\n                  Realms<BR><A \r\n                  href="javascript:alert(''This would take you to the change log, where you could view the changes that have been made to the game.'');">v1.1.1</A>&nbsp;&nbsp;&nbsp;'),
(66, 60, 6, 'Cargo Holds', 'Below Forces is Cargo Holds. This shows how many goods your ship can carry. Some ships carry no cargo at all, while others are designed to carry alot of cargo. Cargo Holds is also a clickable link. You can jettison your cargo into space if necessary, but you lose the experience gained from buying it. \r\n\r\n<p>Cargo Holds (60/60) \r\n<br>Empty : 60 \r\n\r\n<p>In this example the ship has a maximum of 60 cargo holds and can carry 60 units of port goods at a time. \r\n\r\n<p><!-- CURRENT SECTOR SCREEN SHOT --------------------------------------- -->\r\n<TABLE height="100%" cellSpacing=0 cellPadding=0 width="100%" border=0>\r\n  <TBODY>\r\n  <TR>\r\n    <TD></TD>\r\n    <TD bgColor=#0b8d35 colSpan=3 height=1></TD>\r\n    <TD></TD></TR>\r\n  <TR>\r\n    <TD vAlign=top align=right width=135><!-- menu -->\r\n      <TABLE cellSpacing=5 cellPadding=5 border=0>\r\n        <TBODY>\r\n        <TR>\r\n          <TD align=right><SMALL><SPAN style="COLOR: yellow">10/6/2005 3:12:37 \r\n            PM</SPAN>\r\n            <P><A \r\n            href="manual.php?69"><BIG><B>Current&nbsp;Sector</B></BIG></A><BR><A \r\n            href="manual.php?70"><BIG><B>Local \r\n            Map</B></BIG></A><BR><A \r\n            href="manual.php?71"><BIG><B>Plot \r\n            a Course</B></BIG></A><BR><A \r\n            href="manual.php?72">DL \r\n            MGU Maps</A><BR><A href="manual.php?73">Galaxy Map</A></P>\r\n            <P><A \r\n            href="javascript:alert(''This will take you to a screen with your trader\\\\''s info.'');">Trader</A><BR><A \r\n            href="javascript:alert(''If you are in an Alliance, this will take you to the Message of the Day from your leader. \r\nIf not, then this will take you to a list of all the alliances currently in the game.'');">Alliance</A>\r\n            <P><A \r\n            href="javascript:alert(''If you own your own planet, this will give you some infomation about your planet, without you having to \r\nvisit it.'');">Planet</A><BR><A \r\n            href="manual.php?65">Forces</A>\r\n            <P><A \r\n            href="javascript:alert(''This will take you to a menu of the different types of messages.'');">Messages</A><BR><A \r\n            href="javascript:alert(''Whenever anything important happens, it goes into the news.'');">Read \r\n            News</A><BR><A \r\n            href="javascript:alert(''The Galactic Post (GP) is articles written by players like yourself.  The GP is not only for your reading \r\nenjoyment, but to also add a little of the RPG element to the game.'');">Galactic \r\n            Post</A></P>\r\n            <P><A \r\n            href="javascript:alert(''Want to know a little bit more about another player, but can\\\\''t quite remember their name?  Well you can\r\nsearch for them here.'');">Search \r\n            for Trader</A><BR><A \r\n            href="javascript:alert(''This displays a list of all the players who have been active in the past 10 minutes.  Important Note:  It is\r\npossible to be online, and not be on the current players list.'');">Current \r\n            Players</A></P>\r\n            <P><A \r\n            href="javascript:alert(''Want to see how you stack up against other players?'');">Rankings</A><BR><A \r\n            href="javascript:alert(''This is a cumulation of player\\\\''s stats over all the games.'');">Hall \r\n            of Fame</A><BR><A \r\n            href="javascript:alert(''This covers more stats than that of the Rankings.'');">Current \r\n            HoF</A></P>\r\n            <P><A \r\n            href="javascript:alert(''If there is more than one game active at the time, this will take you back and let you pick the game.'');">Play \r\n            Game</A><BR><A \r\n            href="javascript:alert(''Done playing for now?  You should logoff to make sure that other people who have access to the PC your are on\r\ndon\\\\''t mess with your account.'');">Logoff</A></P>\r\n            <P><A href="javascript:alert(''This is where you are right now.'');">Manual</A><BR><A \r\n            href="javascript:alert(''You can access your account preferences here.'');">Preferences</A><BR><A \r\n            href="javascript:alert(''Want to show off that mug-o-yours?  Well upload your pic here.  Be sure to read the rules before uploading!'');">Edit \r\n            Photo</A><BR><A href="javascript:alert(''Other players wanted to show off their mugs.  You can view \\\\''em here.'');">Album</A></P>\r\n            <P><A \r\n            href="javascript:alert(''Found a bug?  Please report it!'');">Report \r\n            a Bug</A><BR><A \r\n            href="javascript:alert(''Need to contact the admins for something?  Do it here.'');">Contact \r\n            Form</A></P>\r\n            <P><A \r\n            href="javascript:alert(''The wonderful world of chat.  If you have a problem that you need the admins to deal with, you will get a\r\nquicker response time in chat, than you will in email.'');"><BIG><B>IRC \r\n            Chat</B></BIG></A><BR><A \r\n            href="javascript:alert(''The game rules.  Make sure you look over them, so you can avoid getting banned for something you could have \r\navoided.'');"><B>User Policy</B></A><BR><A \r\n            href="javascript:alert(''A place you and the other players can go and read/write messages.  You can also look for help here.'');"><B>WebBoard</B></A><BR><A \r\n            href="javascript:alert(''While this game is free, it does cost to keep it running, so if you enjoy the game, and want to see it \r\naround for sometime to come, it is always helpful to donate. :)'');">Donate</A></A></P></SMALL></TD></TR></TBODY></TABLE><!-- end menu --></TD>\r\n    <TD width=1 bgColor=#0b8d35></TD>\r\n    <TD vAlign=top align=left bgColor=#06240e>\r\n      <TABLE height="100%" cellSpacing=5 cellPadding=5 width="100%" border=0>\r\n        <TBODY>\r\n        <TR>\r\n          <TD vAlign=top>\r\n            <H1>CURRENT SECTOR: 1105</H1>\r\n            <P>\r\n            <TABLE cellSpacing=1 cellPadding=0 width="100%" border=0>\r\n              <TBODY>\r\n              <TR>\r\n                <TD bgColor=#0b8d35>\r\n                  <TABLE cellSpacing=1 cellPadding=3 width="100%" border=0>\r\n                    <TBODY>\r\n                    <TR bgColor=#0b2121>\r\n                      <TD>\r\n                        <TABLE cellSpacing=2 cellPadding=3 width="100%" \r\nborder=0>\r\n                          <TBODY>\r\n                          <TR bgColor=#0b8d35>\r\n                            <TD align=middle><SMALL><A \r\n                              href="manual.php?71">Plot \r\n                              a Course</A> | <A \r\n                              href="manual.php?70">Local \r\n                              Map</A> | <A \r\n                              href="manual.php?72">Galaxy \r\n                          Map</A></SMALL></TD></TR></TBODY></TABLE></TD></TR></TBODY></TABLE></TD></TR></TBODY></TABLE></P>\r\n            <P><SMALL>Creonti</SMALL></P>\r\n            <P>\r\n            <TABLE borderColor=#0b8d35 cellSpacing=0 border=1>\r\n              <TBODY>\r\n              <TR>\r\n                <TH>Location</TH>\r\n                <TH>Option</TH></TR>\r\n              <TR>\r\n                <TD width=250><IMG \r\n                  src="images/beacon.gif">&nbsp;Beacon \r\n                  of Ultimate Protection</TD>\r\n                <TD vAlign=center \r\n            align=middle>&nbsp;</TD></TR></TBODY></TABLE></P>\r\n            <P>\r\n            <TABLE borderColor=#0b8d35 cellSpacing=0 border=1>\r\n              <TBODY>\r\n              <TR>\r\n                <TH align=middle colSpan=3>Move to</TH></TR>\r\n              <TR>\r\n                <TD>&nbsp;</TD>\r\n                <TD align=middle width=80 height=40><A \r\n                  href="javascript:alert(''This would move you up one to sector #1091.  It would cost you one turn to move.'');"><SPAN \r\n                  style="COLOR: green">1091 (1)</SPAN></A><BR><A \r\n                  href="javascript:alert(''This would scan the sector immediately above you.  This would give you an idea of what is in \r\nthat sector, before deciding to move there.'');"><SPAN \r\n                  style="FONT-SIZE: 75%">Scan<SPAN></A></SPAN></SPAN></TD>\r\n                <TD>&nbsp;</TD></TR>\r\n              <TR>\r\n                <TD align=middle width=80 height=40><A \r\n                  href="javascript:alert(''This would move you left one to sector #1104.  It would cost you one turn to move.'');"><SPAN \r\n                  style="COLOR: green">1104 (1)</SPAN></A><BR><A \r\n                  href="javascript:alert(''This would scan the sector immediately left of you.  This would give you an idea of what is in \r\nthat sector, before deciding to move there.'');"><SPAN \r\n                  style="FONT-SIZE: 75%">Scan<SPAN></A></SPAN></SPAN></TD>\r\n                <TD align=middle><A \r\n                  href="javascript:alert(''This would take you to the current sector screen of the screen you already are in.'');"><SPAN \r\n                  style="COLOR: green">1105</SPAN></A></TD>\r\n                <TD align=middle width=80 height=40><A \r\n                  href="javascript:alert(''This would move you right one to sector #1106.  It would cost you one turn to move.'');"><SPAN \r\n                  style="COLOR: green">1106 (1)</SPAN></A><BR><A \r\n                  href="javascript:alert(''This would scan the sector immediately right of you.  This would give you an idea of what is in \r\nthat sector, before deciding to move there.'');"><SPAN \r\n                  style="FONT-SIZE: 75%">Scan<SPAN></A></SPAN></SPAN></TD></TR>\r\n              <TR>\r\n                <TD>&nbsp;</TD>\r\n                <TD align=middle width=80 height=40>&nbsp;</TD>\r\n                <TD align=middle width=80 \r\n            height=40>&nbsp;</TD></TR></TBODY></TABLE></P>\r\n            <P>\r\n            <TABLE borderColor=#0b8d35 cellSpacing=0 border=1>\r\n              <TBODY>\r\n              <TR>\r\n                <TH width=150>Trader</TH>\r\n                <TH width=100>Option</TH></TR>\r\n              <TR>\r\n                <TD><A \r\n                  href="javascript:alert(''This would take you to a menu where you could view more info about this player.'');"><SPAN \r\n                  style="COLOR: yellow">Omega \r\n                  Prime&nbsp;(150)</SPAN></A>&nbsp;<A \r\n                  href="javascript:alert(''If you are in this alliance, it would take you to the Message of the Day from your leader.\r\nIf not, it would take you to the alliance roster, so you could see who is in the alliance.'');">(Willowstrance)</A><BR><FONT \r\n                  color=white>Leviathan (2/8)</FONT></TD>\r\n                <TD align=middle><A \r\n                  href="javascript:alert(''This would attempt to Examine the player\\\\''s ship.  There are certain conditions, such you being under \r\nfederal or newbie protection, that would prevent you from examining the player\\\\''s ship.'');">Examine</A></TD></TR>\r\n              <TR>\r\n                <TD><A \r\n                  href="javascript:alert(''This would take you to a menu where you could view more info about this player.'');"><SPAN \r\n                  style="COLOR: yellow">Bruno&nbsp;(185)</SPAN></A>&nbsp;<A \r\n                  href="javascript:alert(''If you are in this alliance, it would take you to the Message of the Day from your leader.\r\nIf not, it would take you to the alliance roster, so you could see who is in the alliance.'');">(Legitimate \r\n                  Businessmen)</A><BR><FONT color=white>Planetary Super \r\n                  Freighter (2/15)</FONT></TD>\r\n                <TD align=middle><A \r\n                  href="javascript:alert(''This would attempt to Examine the player\\\\''s ship.  There are certain conditions, such you being under \r\nfederal or newbie protection, that would prevent you from examining the player\\\\''s ship.'');">Examine</A></TD></TR></TBODY></TABLE></P></TD></TR>\r\n        <TR>\r\n          <TD vAlign=bottom><!-- copyright -->\r\n            <TABLE width="100%" border=0>\r\n              <TBODY>\r\n              <TR>\r\n                <TD vAlign=center><BR>\r\n                  <CENTER><SPAN style="FONT-SIZE: 75%">Get <B><U>FREE \r\n                  TURNS</U></B> for voting if you see the \r\n                  star.</SPAN></CENTER><A \r\n                  href="javascript:alert(''In game, if you see a star on the link, you will get bonus turns for voting if you click.  You can only get \r\nbonus turns once a day from each voting site.'');"><IMG \r\n                  height=41 \r\n                  src="images/game_sites/mpogd_vote.png" width=98 \r\n                  border=0></A><A \r\n                  href="javascript:alert(''In game, if you see a star on the link, you will get bonus turns for voting if you click.  You can only get \r\nbonus turns once a day from each voting site.'');"><IMG height=41 \r\n                  src="images/game_sites/twg.png" width=98 \r\n                  border=0></A>&nbsp;&nbsp;<A \r\n                  href="javascript:alert(''In game, if you see a star on the link, you will get bonus turns for voting if you click.  You can only get \r\nbonus turns once a day from each voting site.'');"><IMG \r\n                  height=41 src="images/game_sites/omgn_vote.jpg" \r\n                  width=98 border=0></A></TD>\r\n                <TD style="VERTICAL-ALIGN: bottom" align=right \r\n                  width="100%"><SPAN style="FONT-SIZE: 75%">Space Merchant \r\n                  Realms<BR><A \r\n                  href="javascript:alert(''This would take you to the change log, where you could view the changes that have been made to the game.'');">v1.1.1</A>&nbsp;&nbsp;&nbsp;'),
(67, 60, 7, 'Weapons', 'At the bottom of the right hand screen is a list of your ship''s Weapons. \r\n\r\nWeapons \r\nOpen : 6 \r\n\r\nThis ship currently has no weapons, but can arm itself with a total of 6. \r\n\r\n<p><!-- CURRENT SECTOR SCREEN SHOT --------------------------------------- -->\r\n<TABLE height="100%" cellSpacing=0 cellPadding=0 width="100%" border=0>\r\n  <TBODY>\r\n  <TR>\r\n    <TD></TD>\r\n    <TD bgColor=#0b8d35 colSpan=3 height=1></TD>\r\n    <TD></TD></TR>\r\n  <TR>\r\n    <TD vAlign=top align=right width=135><!-- menu -->\r\n      <TABLE cellSpacing=5 cellPadding=5 border=0>\r\n        <TBODY>\r\n        <TR>\r\n          <TD align=right><SMALL><SPAN style="COLOR: yellow">10/6/2005 3:12:37 \r\n            PM</SPAN>\r\n            <P><A \r\n            href="manual.php?69"><BIG><B>Current&nbsp;Sector</B></BIG></A><BR><A \r\n            href="manual.php?70"><BIG><B>Local \r\n            Map</B></BIG></A><BR><A \r\n            href="manual.php?71"><BIG><B>Plot \r\n            a Course</B></BIG></A><BR><A \r\n            href="manual.php?72">DL \r\n            MGU Maps</A><BR><A href="manual.php?73">Galaxy Map</A></P>\r\n            <P><A \r\n            href="javascript:alert(''This will take you to a screen with your trader\\\\''s info.'');">Trader</A><BR><A \r\n            href="javascript:alert(''If you are in an Alliance, this will take you to the Message of the Day from your leader. \r\nIf not, then this will take you to a list of all the alliances currently in the game.'');">Alliance</A>\r\n            <P><A \r\n            href="javascript:alert(''If you own your own planet, this will give you some infomation about your planet, without you having to \r\nvisit it.'');">Planet</A><BR><A \r\n            href="manual.php?65">Forces</A>\r\n            <P><A \r\n            href="javascript:alert(''This will take you to a menu of the different types of messages.'');">Messages</A><BR><A \r\n            href="javascript:alert(''Whenever anything important happens, it goes into the news.'');">Read \r\n            News</A><BR><A \r\n            href="javascript:alert(''The Galactic Post (GP) is articles written by players like yourself.  The GP is not only for your reading \r\nenjoyment, but to also add a little of the RPG element to the game.'');">Galactic \r\n            Post</A></P>\r\n            <P><A \r\n            href="javascript:alert(''Want to know a little bit more about another player, but can\\\\''t quite remember their name?  Well you can\r\nsearch for them here.'');">Search \r\n            for Trader</A><BR><A \r\n            href="javascript:alert(''This displays a list of all the players who have been active in the past 10 minutes.  Important Note:  It is\r\npossible to be online, and not be on the current players list.'');">Current \r\n            Players</A></P>\r\n            <P><A \r\n            href="javascript:alert(''Want to see how you stack up against other players?'');">Rankings</A><BR><A \r\n            href="javascript:alert(''This is a cumulation of player\\\\''s stats over all the games.'');">Hall \r\n            of Fame</A><BR><A \r\n            href="javascript:alert(''This covers more stats than that of the Rankings.'');">Current \r\n            HoF</A></P>\r\n            <P><A \r\n            href="javascript:alert(''If there is more than one game active at the time, this will take you back and let you pick the game.'');">Play \r\n            Game</A><BR><A \r\n            href="javascript:alert(''Done playing for now?  You should logoff to make sure that other people who have access to the PC your are on\r\ndon\\\\''t mess with your account.'');">Logoff</A></P>\r\n            <P><A href="javascript:alert(''This is where you are right now.'');">Manual</A><BR><A \r\n            href="javascript:alert(''You can access your account preferences here.'');">Preferences</A><BR><A \r\n            href="javascript:alert(''Want to show off that mug-o-yours?  Well upload your pic here.  Be sure to read the rules before uploading!'');">Edit \r\n            Photo</A><BR><A href="javascript:alert(''Other players wanted to show off their mugs.  You can view \\\\''em here.'');">Album</A></P>\r\n            <P><A \r\n            href="javascript:alert(''Found a bug?  Please report it!'');">Report \r\n            a Bug</A><BR><A \r\n            href="javascript:alert(''Need to contact the admins for something?  Do it here.'');">Contact \r\n            Form</A></P>\r\n            <P><A \r\n            href="javascript:alert(''The wonderful world of chat.  If you have a problem that you need the admins to deal with, you will get a\r\nquicker response time in chat, than you will in email.'');"><BIG><B>IRC \r\n            Chat</B></BIG></A><BR><A \r\n            href="javascript:alert(''The game rules.  Make sure you look over them, so you can avoid getting banned for something you could have \r\navoided.'');"><B>User Policy</B></A><BR><A \r\n            href="javascript:alert(''A place you and the other players can go and read/write messages.  You can also look for help here.'');"><B>WebBoard</B></A><BR><A \r\n            href="javascript:alert(''While this game is free, it does cost to keep it running, so if you enjoy the game, and want to see it \r\naround for sometime to come, it is always helpful to donate. :)'');">Donate</A></A></P></SMALL></TD></TR></TBODY></TABLE><!-- end menu --></TD>\r\n    <TD width=1 bgColor=#0b8d35></TD>\r\n    <TD vAlign=top align=left bgColor=#06240e>\r\n      <TABLE height="100%" cellSpacing=5 cellPadding=5 width="100%" border=0>\r\n        <TBODY>\r\n        <TR>\r\n          <TD vAlign=top>\r\n            <H1>CURRENT SECTOR: 1105</H1>\r\n            <P>\r\n            <TABLE cellSpacing=1 cellPadding=0 width="100%" border=0>\r\n              <TBODY>\r\n              <TR>\r\n                <TD bgColor=#0b8d35>\r\n                  <TABLE cellSpacing=1 cellPadding=3 width="100%" border=0>\r\n                    <TBODY>\r\n                    <TR bgColor=#0b2121>\r\n                      <TD>\r\n                        <TABLE cellSpacing=2 cellPadding=3 width="100%" \r\nborder=0>\r\n                          <TBODY>\r\n                          <TR bgColor=#0b8d35>\r\n                            <TD align=middle><SMALL><A \r\n                              href="manual.php?71">Plot \r\n                              a Course</A> | <A \r\n                              href="manual.php?70">Local \r\n                              Map</A> | <A \r\n                              href="manual.php?72">Galaxy \r\n                          Map</A></SMALL></TD></TR></TBODY></TABLE></TD></TR></TBODY></TABLE></TD></TR></TBODY></TABLE></P>\r\n            <P><SMALL>Creonti</SMALL></P>\r\n            <P>\r\n            <TABLE borderColor=#0b8d35 cellSpacing=0 border=1>\r\n              <TBODY>\r\n              <TR>\r\n                <TH>Location</TH>\r\n                <TH>Option</TH></TR>\r\n              <TR>\r\n                <TD width=250><IMG \r\n                  src="images/beacon.gif">&nbsp;Beacon \r\n                  of Ultimate Protection</TD>\r\n                <TD vAlign=center \r\n            align=middle>&nbsp;</TD></TR></TBODY></TABLE></P>\r\n            <P>\r\n            <TABLE borderColor=#0b8d35 cellSpacing=0 border=1>\r\n              <TBODY>\r\n              <TR>\r\n                <TH align=middle colSpan=3>Move to</TH></TR>\r\n              <TR>\r\n                <TD>&nbsp;</TD>\r\n                <TD align=middle width=80 height=40><A \r\n                  href="javascript:alert(''This would move you up one to sector #1091.  It would cost you one turn to move.'');"><SPAN \r\n                  style="COLOR: green">1091 (1)</SPAN></A><BR><A \r\n                  href="javascript:alert(''This would scan the sector immediately above you.  This would give you an idea of what is in \r\nthat sector, before deciding to move there.'');"><SPAN \r\n                  style="FONT-SIZE: 75%">Scan<SPAN></A></SPAN></SPAN></TD>\r\n                <TD>&nbsp;</TD></TR>\r\n              <TR>\r\n                <TD align=middle width=80 height=40><A \r\n                  href="javascript:alert(''This would move you left one to sector #1104.  It would cost you one turn to move.'');"><SPAN \r\n                  style="COLOR: green">1104 (1)</SPAN></A><BR><A \r\n                  href="javascript:alert(''This would scan the sector immediately left of you.  This would give you an idea of what is in \r\nthat sector, before deciding to move there.'');"><SPAN \r\n                  style="FONT-SIZE: 75%">Scan<SPAN></A></SPAN></SPAN></TD>\r\n                <TD align=middle><A \r\n                  href="javascript:alert(''This would take you to the current sector screen of the screen you already are in.'');"><SPAN \r\n                  style="COLOR: green">1105</SPAN></A></TD>\r\n                <TD align=middle width=80 height=40><A \r\n                  href="javascript:alert(''This would move you right one to sector #1106.  It would cost you one turn to move.'');"><SPAN \r\n                  style="COLOR: green">1106 (1)</SPAN></A><BR><A \r\n                  href="javascript:alert(''This would scan the sector immediately right of you.  This would give you an idea of what is in \r\nthat sector, before deciding to move there.'');"><SPAN \r\n                  style="FONT-SIZE: 75%">Scan<SPAN></A></SPAN></SPAN></TD></TR>\r\n              <TR>\r\n                <TD>&nbsp;</TD>\r\n                <TD align=middle width=80 height=40>&nbsp;</TD>\r\n                <TD align=middle width=80 \r\n            height=40>&nbsp;</TD></TR></TBODY></TABLE></P>\r\n            <P>\r\n            <TABLE borderColor=#0b8d35 cellSpacing=0 border=1>\r\n              <TBODY>\r\n              <TR>\r\n                <TH width=150>Trader</TH>\r\n                <TH width=100>Option</TH></TR>\r\n              <TR>\r\n                <TD><A \r\n                  href="javascript:alert(''This would take you to a menu where you could view more info about this player.'');"><SPAN \r\n                  style="COLOR: yellow">Omega \r\n                  Prime&nbsp;(150)</SPAN></A>&nbsp;<A \r\n                  href="javascript:alert(''If you are in this alliance, it would take you to the Message of the Day from your leader.\r\nIf not, it would take you to the alliance roster, so you could see who is in the alliance.'');">(Willowstrance)</A><BR><FONT \r\n                  color=white>Leviathan (2/8)</FONT></TD>\r\n                <TD align=middle><A \r\n                  href="javascript:alert(''This would attempt to Examine the player\\\\''s ship.  There are certain conditions, such you being under \r\nfederal or newbie protection, that would prevent you from examining the player\\\\''s ship.'');">Examine</A></TD></TR>\r\n              <TR>\r\n                <TD><A \r\n                  href="javascript:alert(''This would take you to a menu where you could view more info about this player.'');"><SPAN \r\n                  style="COLOR: yellow">Bruno&nbsp;(185)</SPAN></A>&nbsp;<A \r\n                  href="javascript:alert(''If you are in this alliance, it would take you to the Message of the Day from your leader.\r\nIf not, it would take you to the alliance roster, so you could see who is in the alliance.'');">(Legitimate \r\n                  Businessmen)</A><BR><FONT color=white>Planetary Super \r\n                  Freighter (2/15)</FONT></TD>\r\n                <TD align=middle><A \r\n                  href="javascript:alert(''This would attempt to Examine the player\\\\''s ship.  There are certain conditions, such you being under \r\nfederal or newbie protection, that would prevent you from examining the player\\\\''s ship.'');">Examine</A></TD></TR></TBODY></TABLE></P></TD></TR>\r\n        <TR>\r\n          <TD vAlign=bottom><!-- copyright -->\r\n            <TABLE width="100%" border=0>\r\n              <TBODY>\r\n              <TR>\r\n                <TD vAlign=center><BR>\r\n                  <CENTER><SPAN style="FONT-SIZE: 75%">Get <B><U>FREE \r\n                  TURNS</U></B> for voting if you see the \r\n                  star.</SPAN></CENTER><A \r\n                  href="javascript:alert(''In game, if you see a star on the link, you will get bonus turns for voting if you click.  You can only get \r\nbonus turns once a day from each voting site.'');"><IMG \r\n                  height=41 \r\n                  src="images/game_sites/mpogd_vote.png" width=98 \r\n                  border=0></A><A \r\n                  href="javascript:alert(''In game, if you see a star on the link, you will get bonus turns for voting if you click.  You can only get \r\nbonus turns once a day from each voting site.'');"><IMG height=41 \r\n                  src="images/game_sites/twg.png" width=98 \r\n                  border=0></A>&nbsp;&nbsp;<A \r\n                  href="javascript:alert(''In game, if you see a star on the link, you will get bonus turns for voting if you click.  You can only get \r\nbonus turns once a day from each voting site.'');"><IMG \r\n                  height=41 src="images/game_sites/omgn_vote.jpg" \r\n                  width=98 border=0></A></TD>\r\n                <TD style="VERTICAL-ALIGN: bottom" align=right \r\n                  width="100%"><SPAN style="FONT-SIZE: 75%">Space Merchant \r\n                  Realms<BR><A \r\n                  href="javascript:alert(''This would take you to the change log, where you could view the changes that have been made to the game.'');">v1.1.1</A>&nbsp;&nbsp;&nbsp;'),
(68, 60, 8, 'Maneuvering your ship', 'Your ship is able to move from sector to sector in the galaxy you are in. You can view the sectors in two ways. In the upper left of the screen, you will see these links: \r\n\r\n<p><b><a href="manual.php?69">Current Sector</a>\r\n<br><a href="manual.php?70">Local Map</a>\r\n<br><a href="manual.php?71">Plot a Course</a>\r\n<br><a href="manual.php?72">DL MGU Maps</a>\r\n<br><a href="manual.php?73">Galaxy Map</a></b>'),
(69, 68, 1, 'Current Sector', 'Clicking Current Sector, or CS, will show you and let you interact with what is in the sector your ship is sitting in, whether it is a port a planet or another ship. You will see a navigation box in the center of the screen. It shows you the connections to adjacent sectors and by clicking them you will move your ship to that sector and remain in the Current Sector view. If your ship is equipped with a scanner, you will be able to scan adjacent sectors before entering them. This is useful for avoiding mines and other ships. \r\n\r\n<p><!-- CURRENT SECTOR SCREEN SHOT --------------------------------------- -->\r\n<TABLE height="100%" cellSpacing=0 cellPadding=0 width="100%" border=0>\r\n  <TBODY>\r\n  <TR>\r\n    <TD></TD>\r\n    <TD bgColor=#0b8d35 colSpan=3 height=1></TD>\r\n    <TD></TD></TR>\r\n  <TR>\r\n    <TD vAlign=top align=right width=135><!-- menu -->\r\n      <TABLE cellSpacing=5 cellPadding=5 border=0>\r\n        <TBODY>\r\n        <TR>\r\n          <TD align=right><SMALL><SPAN style="COLOR: yellow">10/6/2005 3:12:37 \r\n            PM</SPAN>\r\n            <P><A \r\n            href="manual.php?69"><BIG><B>Current&nbsp;Sector</B></BIG></A><BR><A \r\n            href="manual.php?70"><BIG><B>Local \r\n            Map</B></BIG></A><BR><A \r\n            href="manual.php?71"><BIG><B>Plot \r\n            a Course</B></BIG></A><BR><A \r\n            href="manual.php?72">DL \r\n            MGU Maps</A><BR><A href="manual.php?73">Galaxy Map</A></P>\r\n            <P><A \r\n            href="javascript:alert(''This will take you to a screen with your trader\\\\''s info.'');">Trader</A><BR><A \r\n            href="javascript:alert(''If you are in an Alliance, this will take you to the Message of the Day from your leader. \r\nIf not, then this will take you to a list of all the alliances currently in the game.'');">Alliance</A>\r\n            <P><A \r\n            href="javascript:alert(''If you own your own planet, this will give you some infomation about your planet, without you having to \r\nvisit it.'');">Planet</A><BR><A \r\n            href="manual.php?65">Forces</A>\r\n            <P><A \r\n            href="javascript:alert(''This will take you to a menu of the different types of messages.'');">Messages</A><BR><A \r\n            href="javascript:alert(''Whenever anything important happens, it goes into the news.'');">Read \r\n            News</A><BR><A \r\n            href="javascript:alert(''The Galactic Post (GP) is articles written by players like yourself.  The GP is not only for your reading \r\nenjoyment, but to also add a little of the RPG element to the game.'');">Galactic \r\n            Post</A></P>\r\n            <P><A \r\n            href="javascript:alert(''Want to know a little bit more about another player, but can\\\\''t quite remember their name?  Well you can\r\nsearch for them here.'');">Search \r\n            for Trader</A><BR><A \r\n            href="javascript:alert(''This displays a list of all the players who have been active in the past 10 minutes.  Important Note:  It is\r\npossible to be online, and not be on the current players list.'');">Current \r\n            Players</A></P>\r\n            <P><A \r\n            href="javascript:alert(''Want to see how you stack up against other players?'');">Rankings</A><BR><A \r\n            href="javascript:alert(''This is a cumulation of player\\\\''s stats over all the games.'');">Hall \r\n            of Fame</A><BR><A \r\n            href="javascript:alert(''This covers more stats than that of the Rankings.'');">Current \r\n            HoF</A></P>\r\n            <P><A \r\n            href="javascript:alert(''If there is more than one game active at the time, this will take you back and let you pick the game.'');">Play \r\n            Game</A><BR><A \r\n            href="javascript:alert(''Done playing for now?  You should logoff to make sure that other people who have access to the PC your are on\r\ndon\\\\''t mess with your account.'');">Logoff</A></P>\r\n            <P><A href="javascript:alert(''This is where you are right now.'');">Manual</A><BR><A \r\n            href="javascript:alert(''You can access your account preferences here.'');">Preferences</A><BR><A \r\n            href="javascript:alert(''Want to show off that mug-o-yours?  Well upload your pic here.  Be sure to read the rules before uploading!'');">Edit \r\n            Photo</A><BR><A href="javascript:alert(''Other players wanted to show off their mugs.  You can view \\\\''em here.'');">Album</A></P>\r\n            <P><A \r\n            href="javascript:alert(''Found a bug?  Please report it!'');">Report \r\n            a Bug</A><BR><A \r\n            href="javascript:alert(''Need to contact the admins for something?  Do it here.'');">Contact \r\n            Form</A></P>\r\n            <P><A \r\n            href="javascript:alert(''The wonderful world of chat.  If you have a problem that you need the admins to deal with, you will get a\r\nquicker response time in chat, than you will in email.'');"><BIG><B>IRC \r\n            Chat</B></BIG></A><BR><A \r\n            href="javascript:alert(''The game rules.  Make sure you look over them, so you can avoid getting banned for something you could have \r\navoided.'');"><B>User Policy</B></A><BR><A \r\n            href="javascript:alert(''A place you and the other players can go and read/write messages.  You can also look for help here.'');"><B>WebBoard</B></A><BR><A \r\n            href="javascript:alert(''While this game is free, it does cost to keep it running, so if you enjoy the game, and want to see it \r\naround for sometime to come, it is always helpful to donate. :)'');">Donate</A></A></P></SMALL></TD></TR></TBODY></TABLE><!-- end menu --></TD>\r\n    <TD width=1 bgColor=#0b8d35></TD>\r\n    <TD vAlign=top align=left bgColor=#06240e>\r\n      <TABLE height="100%" cellSpacing=5 cellPadding=5 width="100%" border=0>\r\n        <TBODY>\r\n        <TR>\r\n          <TD vAlign=top>\r\n            <H1>CURRENT SECTOR: 1105</H1>\r\n            <P>\r\n            <TABLE cellSpacing=1 cellPadding=0 width="100%" border=0>\r\n              <TBODY>\r\n              <TR>\r\n                <TD bgColor=#0b8d35>\r\n                  <TABLE cellSpacing=1 cellPadding=3 width="100%" border=0>\r\n                    <TBODY>\r\n                    <TR bgColor=#0b2121>\r\n                      <TD>\r\n                        <TABLE cellSpacing=2 cellPadding=3 width="100%" \r\nborder=0>\r\n                          <TBODY>\r\n                          <TR bgColor=#0b8d35>\r\n                            <TD align=middle><SMALL><A \r\n                              href="manual.php?71">Plot \r\n                              a Course</A> | <A \r\n                              href="manual.php?70">Local \r\n                              Map</A> | <A \r\n                              href="manual.php?72">Galaxy \r\n                          Map</A></SMALL></TD></TR></TBODY></TABLE></TD></TR></TBODY></TABLE></TD></TR></TBODY></TABLE></P>\r\n            <P><SMALL>Creonti</SMALL></P>\r\n            <P>\r\n            <TABLE borderColor=#0b8d35 cellSpacing=0 border=1>\r\n              <TBODY>\r\n              <TR>\r\n                <TH>Location</TH>\r\n                <TH>Option</TH></TR>\r\n              <TR>\r\n                <TD width=250><IMG \r\n                  src="images/beacon.gif">&nbsp;Beacon \r\n                  of Ultimate Protection</TD>\r\n                <TD vAlign=center \r\n            align=middle>&nbsp;</TD></TR></TBODY></TABLE></P>\r\n            <P>\r\n            <TABLE borderColor=#0b8d35 cellSpacing=0 border=1>\r\n              <TBODY>\r\n              <TR>\r\n                <TH align=middle colSpan=3>Move to</TH></TR>\r\n              <TR>\r\n                <TD>&nbsp;</TD>\r\n                <TD align=middle width=80 height=40><A \r\n                  href="javascript:alert(''This would move you up one to sector #1091.  It would cost you one turn to move.'');"><SPAN \r\n                  style="COLOR: green">1091 (1)</SPAN></A><BR><A \r\n                  href="javascript:alert(''This would scan the sector immediately above you.  This would give you an idea of what is in \r\nthat sector, before deciding to move there.'');"><SPAN \r\n                  style="FONT-SIZE: 75%">Scan<SPAN></A></SPAN></SPAN></TD>\r\n                <TD>&nbsp;</TD></TR>\r\n              <TR>\r\n                <TD align=middle width=80 height=40><A \r\n                  href="javascript:alert(''This would move you left one to sector #1104.  It would cost you one turn to move.'');"><SPAN \r\n                  style="COLOR: green">1104 (1)</SPAN></A><BR><A \r\n                  href="javascript:alert(''This would scan the sector immediately left of you.  This would give you an idea of what is in \r\nthat sector, before deciding to move there.'');"><SPAN \r\n                  style="FONT-SIZE: 75%">Scan<SPAN></A></SPAN></SPAN></TD>\r\n                <TD align=middle><A \r\n                  href="javascript:alert(''This would take you to the current sector screen of the screen you already are in.'');"><SPAN \r\n                  style="COLOR: green">1105</SPAN></A></TD>\r\n                <TD align=middle width=80 height=40><A \r\n                  href="javascript:alert(''This would move you right one to sector #1106.  It would cost you one turn to move.'');"><SPAN \r\n                  style="COLOR: green">1106 (1)</SPAN></A><BR><A \r\n                  href="javascript:alert(''This would scan the sector immediately right of you.  This would give you an idea of what is in \r\nthat sector, before deciding to move there.'');"><SPAN \r\n                  style="FONT-SIZE: 75%">Scan<SPAN></A></SPAN></SPAN></TD></TR>\r\n              <TR>\r\n                <TD>&nbsp;</TD>\r\n                <TD align=middle width=80 height=40>&nbsp;</TD>\r\n                <TD align=middle width=80 \r\n            height=40>&nbsp;</TD></TR></TBODY></TABLE></P>\r\n            <P>\r\n            <TABLE borderColor=#0b8d35 cellSpacing=0 border=1>\r\n              <TBODY>\r\n              <TR>\r\n                <TH width=150>Trader</TH>\r\n                <TH width=100>Option</TH></TR>\r\n              <TR>\r\n                <TD><A \r\n                  href="javascript:alert(''This would take you to a menu where you could view more info about this player.'');"><SPAN \r\n                  style="COLOR: yellow">Omega \r\n                  Prime&nbsp;(150)</SPAN></A>&nbsp;<A \r\n                  href="javascript:alert(''If you are in this alliance, it would take you to the Message of the Day from your leader.\r\nIf not, it would take you to the alliance roster, so you could see who is in the alliance.'');">(Willowstrance)</A><BR><FONT \r\n                  color=white>Leviathan (2/8)</FONT></TD>\r\n                <TD align=middle><A \r\n                  href="javascript:alert(''This would attempt to Examine the player\\\\''s ship.  There are certain conditions, such you being under \r\nfederal or newbie protection, that would prevent you from examining the player\\\\''s ship.'');">Examine</A></TD></TR>\r\n              <TR>\r\n                <TD><A \r\n                  href="javascript:alert(''This would take you to a menu where you could view more info about this player.'');"><SPAN \r\n                  style="COLOR: yellow">Bruno&nbsp;(185)</SPAN></A>&nbsp;<A \r\n                  href="javascript:alert(''If you are in this alliance, it would take you to the Message of the Day from your leader.\r\nIf not, it would take you to the alliance roster, so you could see who is in the alliance.'');">(Legitimate \r\n                  Businessmen)</A><BR><FONT color=white>Planetary Super \r\n                  Freighter (2/15)</FONT></TD>\r\n                <TD align=middle><A \r\n                  href="javascript:alert(''This would attempt to Examine the player\\\\''s ship.  There are certain conditions, such you being under \r\nfederal or newbie protection, that would prevent you from examining the player\\\\''s ship.'');">Examine</A></TD></TR></TBODY></TABLE></P></TD></TR>\r\n        <TR>\r\n          <TD vAlign=bottom><!-- copyright -->\r\n            <TABLE width="100%" border=0>\r\n              <TBODY>\r\n              <TR>\r\n                <TD vAlign=center><BR>\r\n                  <CENTER><SPAN style="FONT-SIZE: 75%">Get <B><U>FREE \r\n                  TURNS</U></B> for voting if you see the \r\n                  star.</SPAN></CENTER><A \r\n                  href="javascript:alert(''In game, if you see a star on the link, you will get bonus turns for voting if you click.  You can only get \r\nbonus turns once a day from each voting site.'');"><IMG \r\n                  height=41 \r\n                  src="images/game_sites/mpogd_vote.png" width=98 \r\n                  border=0></A><A \r\n                  href="javascript:alert(''In game, if you see a star on the link, you will get bonus turns for voting if you click.  You can only get \r\nbonus turns once a day from each voting site.'');"><IMG height=41 \r\n                  src="images/game_sites/twg.png" width=98 \r\n                  border=0></A>&nbsp;&nbsp;<A \r\n                  href="javascript:alert(''In game, if you see a star on the link, you will get bonus turns for voting if you click.  You can only get \r\nbonus turns once a day from each voting site.'');"><IMG \r\n                  height=41 src="images/game_sites/omgn_vote.jpg" \r\n                  width=98 border=0></A></TD>\r\n                <TD style="VERTICAL-ALIGN: bottom" align=right \r\n                  width="100%"><SPAN style="FONT-SIZE: 75%">Space Merchant \r\n                  Realms<BR><A \r\n                  href="javascript:alert(''This would take you to the change log, where you could view the changes that have been made to the game.'');">v1.1.1</A>&nbsp;&nbsp;&nbsp;'),
(70, 68, 2, 'Local Map', 'If you click on Local Map, you will see a higher view of the surrounding 24 sectors. You can move your ship from sector to sector this way too. With a scanner installed on your ship, you will see forces and ships in adjacent sectors. This is very handy when trading when you want to avoid hunter ships. \r\n\r\n<p><!-- CURRENT SECTOR SCREEN SHOT --------------------------------------- -->\r\n<TABLE height="100%" cellSpacing=0 cellPadding=0 width="100%" border=0>\r\n  <TBODY>\r\n  <TR>\r\n    <TD></TD>\r\n    <TD bgColor=#0b8d35 colSpan=3 height=1></TD>\r\n    <TD></TD></TR>\r\n  <TR>\r\n    <TD vAlign=top align=right width=135><!-- menu -->\r\n      <TABLE cellSpacing=5 cellPadding=5 border=0>\r\n        <TBODY>\r\n        <TR>\r\n          <TD align=right><SMALL><SPAN style="COLOR: yellow">10/6/2005 3:12:37 \r\n            PM</SPAN>\r\n            <P><A \r\n            href="manual.php?69"><BIG><B>Current&nbsp;Sector</B></BIG></A><BR><A \r\n            href="manual.php?70"><BIG><B>Local \r\n            Map</B></BIG></A><BR><A \r\n            href="manual.php?71"><BIG><B>Plot \r\n            a Course</B></BIG></A><BR><A \r\n            href="manual.php?72">DL \r\n            MGU Maps</A><BR><A href="manual.php?73">Galaxy Map</A></P>\r\n            <P><A \r\n            href="javascript:alert(''This will take you to a screen with your trader\\\\''s info.'');">Trader</A><BR><A \r\n            href="javascript:alert(''If you are in an Alliance, this will take you to the Message of the Day from your leader. \r\nIf not, then this will take you to a list of all the alliances currently in the game.'');">Alliance</A>\r\n            <P><A \r\n            href="javascript:alert(''If you own your own planet, this will give you some infomation about your planet, without you having to \r\nvisit it.'');">Planet</A><BR><A \r\n            href="manual.php?65">Forces</A>\r\n            <P><A \r\n            href="javascript:alert(''This will take you to a menu of the different types of messages.'');">Messages</A><BR><A \r\n            href="javascript:alert(''Whenever anything important happens, it goes into the news.'');">Read \r\n            News</A><BR><A \r\n            href="javascript:alert(''The Galactic Post (GP) is articles written by players like yourself.  The GP is not only for your reading \r\nenjoyment, but to also add a little of the RPG element to the game.'');">Galactic \r\n            Post</A></P>\r\n            <P><A \r\n            href="javascript:alert(''Want to know a little bit more about another player, but can\\\\''t quite remember their name?  Well you can\r\nsearch for them here.'');">Search \r\n            for Trader</A><BR><A \r\n            href="javascript:alert(''This displays a list of all the players who have been active in the past 10 minutes.  Important Note:  It is\r\npossible to be online, and not be on the current players list.'');">Current \r\n            Players</A></P>\r\n            <P><A \r\n            href="javascript:alert(''Want to see how you stack up against other players?'');">Rankings</A><BR><A \r\n            href="javascript:alert(''This is a cumulation of player\\\\''s stats over all the games.'');">Hall \r\n            of Fame</A><BR><A \r\n            href="javascript:alert(''This covers more stats than that of the Rankings.'');">Current \r\n            HoF</A></P>\r\n            <P><A \r\n            href="javascript:alert(''If there is more than one game active at the time, this will take you back and let you pick the game.'');">Play \r\n            Game</A><BR><A \r\n            href="javascript:alert(''Done playing for now?  You should logoff to make sure that other people who have access to the PC your are on\r\ndon\\\\''t mess with your account.'');">Logoff</A></P>\r\n            <P><A href="javascript:alert(''This is where you are right now.'');">Manual</A><BR><A \r\n            href="javascript:alert(''You can access your account preferences here.'');">Preferences</A><BR><A \r\n            href="javascript:alert(''Want to show off that mug-o-yours?  Well upload your pic here.  Be sure to read the rules before uploading!'');">Edit \r\n            Photo</A><BR><A href="javascript:alert(''Other players wanted to show off their mugs.  You can view \\\\''em here.'');">Album</A></P>\r\n            <P><A \r\n            href="javascript:alert(''Found a bug?  Please report it!'');">Report \r\n            a Bug</A><BR><A \r\n            href="javascript:alert(''Need to contact the admins for something?  Do it here.'');">Contact \r\n            Form</A></P>\r\n            <P><A \r\n            href="javascript:alert(''The wonderful world of chat.  If you have a problem that you need the admins to deal with, you will get a\r\nquicker response time in chat, than you will in email.'');"><BIG><B>IRC \r\n            Chat</B></BIG></A><BR><A \r\n            href="javascript:alert(''The game rules.  Make sure you look over them, so you can avoid getting banned for something you could have \r\navoided.'');"><B>User Policy</B></A><BR><A \r\n            href="javascript:alert(''A place you and the other players can go and read/write messages.  You can also look for help here.'');"><B>WebBoard</B></A><BR><A \r\n            href="javascript:alert(''While this game is free, it does cost to keep it running, so if you enjoy the game, and want to see it \r\naround for sometime to come, it is always helpful to donate. :)'');">Donate</A></A></P></SMALL></TD></TR></TBODY></TABLE><!-- end menu --></TD>\r\n    <TD width=1 bgColor=#0b8d35></TD>\r\n    <TD vAlign=top align=left bgColor=#06240e>\r\n      <TABLE height="100%" cellSpacing=5 cellPadding=5 width="100%" border=0>\r\n        <TBODY>\r\n        <TR>\r\n          <TD vAlign=top>\r\n            <P>Local map of the known <B><BIG>Creonti</BIG></B> galaxy.</P>\r\n            <DIV align=center>\r\n            <TABLE borderColor=#0b8d35 cellSpacing=0 cellPadding=0 border=1>\r\n              <TBODY>\r\n              <TR>\r\n                <TD vAlign=top align=middle>\r\n                  <TABLE height=120 cellSpacing=0 cellPadding=0 width=120 \r\n                  bgColor=#0b4c1c border=0>\r\n                    <TBODY>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" width=12></TD>\r\n                      <TD></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" width=5></TD>\r\n                      <TD align=right height=21></TD>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver_empty.gif" width=5></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21><IMG \r\n                        src="images/beacon.gif"></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21><SMALL><SPAN \r\n                        style="COLOR: lime">#1075</SPAN></SMALL></TD></TR>\r\n                    <TR>\r\n                      <TD height=42></TD></TR>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" width=12></TD>\r\n                      <TD></TD></TR></TBODY></TABLE></TD>\r\n                <TD vAlign=top align=middle>\r\n                  <TABLE height=120 cellSpacing=0 cellPadding=0 width=120 \r\n                  bgColor=#0b4c1c border=0>\r\n                    <TBODY>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor_empty.gif" width=12></TD>\r\n                      <TD></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver_empty.gif" width=5></TD>\r\n                      <TD align=right height=21></TD>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" width=5></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21><SMALL><SPAN \r\n                        style="COLOR: lime">#1076</SPAN></SMALL></TD></TR>\r\n                    <TR>\r\n                      <TD height=42><IMG src="images/port/buy.gif"><IMG \r\n                        height=16 alt=Wood src="images/port/1.png" width=13 \r\n                        border=0><IMG height=16 alt=Food \r\n                        src="images/port/2.png" width=13 border=0><IMG \r\n                        height=16 alt=Slaves src="images/port/5.png" \r\n                        width=13 border=0><IMG height=16 alt=Textiles \r\n                        src="images/port/6.png" width=13 border=0><IMG \r\n                        height=16 alt=Machinery src="images/port/7.png" \r\n                        width=13 border=0><IMG height=16 alt=Circuitry \r\n                        src="images/port/8.png" width=13 border=0><IMG \r\n                        height=16 alt=Computer src="images/port/10.png" \r\n                        width=13 border=0><BR><IMG \r\n                        src="images/port/sell.gif"><IMG height=16 alt=Ore \r\n                        src="images/port/3.png" width=13 border=0><IMG \r\n                        height=16 alt="Precious Metals" \r\n                        src="images/port/4.png" width=13 border=0><IMG \r\n                        height=16 alt=Weapons src="images/port/9.png" \r\n                        width=13 border=0></TD></TR>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" width=12></TD>\r\n                      <TD></TD></TR></TBODY></TABLE></TD>\r\n                <TD vAlign=top align=middle>\r\n                  <TABLE height=120 cellSpacing=0 cellPadding=0 width=120 \r\n                  bgColor=#0b4c1c border=0>\r\n                    <TBODY>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor_empty.gif" width=12></TD>\r\n                      <TD></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" width=5></TD>\r\n                      <TD align=right height=21></TD>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver_empty.gif" width=5></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21><SMALL><SPAN \r\n                        style="COLOR: lime">#1077</SPAN></SMALL></TD></TR>\r\n                    <TR>\r\n                      <TD height=42><IMG src="images/port/buy.gif"><IMG \r\n                        height=16 alt=Wood src="images/port/1.png" width=13 \r\n                        border=0><IMG height=16 alt=Food \r\n                        src="images/port/2.png" width=13 border=0><IMG \r\n                        height=16 alt=Ore src="images/port/3.png" width=13 \r\n                        border=0><IMG height=16 alt="Precious Metals" \r\n                        src="images/port/4.png" width=13 border=0><IMG \r\n                        height=16 alt=Textiles src="images/port/6.png" \r\n                        width=13 border=0><IMG height=16 alt=Machinery \r\n                        src="images/port/7.png" width=13 border=0><IMG \r\n                        height=16 alt=Weapons src="images/port/9.png" \r\n                        width=13 border=0><BR><IMG \r\n                        src="images/port/sell.gif"><IMG height=16 alt=Slaves \r\n                        src="images/port/5.png" width=13 border=0></TD></TR>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" width=12></TD>\r\n                      <TD></TD></TR></TBODY></TABLE></TD>\r\n                <TD vAlign=top align=middle>\r\n                  <TABLE height=120 cellSpacing=0 cellPadding=0 width=120 \r\n                  bgColor=#0b4c1c border=0>\r\n                    <TBODY>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" width=12></TD>\r\n                      <TD></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver_empty.gif" width=5></TD>\r\n                      <TD align=right height=21></TD>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" width=5></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21><SMALL><SPAN \r\n                        style="COLOR: lime">#1078</SPAN></SMALL></TD></TR>\r\n                    <TR>\r\n                      <TD height=42></TD></TR>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" width=12></TD>\r\n                      <TD></TD></TR></TBODY></TABLE></TD>\r\n                <TD vAlign=top align=middle>\r\n                  <TABLE height=120 cellSpacing=0 cellPadding=0 width=120 \r\n                  bgColor=#0b4c1c border=0>\r\n                    <TBODY>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" width=12></TD>\r\n                      <TD></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" width=5></TD>\r\n                      <TD align=right height=21></TD>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" width=5></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21><SMALL><SPAN \r\n                        style="COLOR: lime">#1065</SPAN></SMALL></TD></TR>\r\n                    <TR>\r\n                      <TD height=42></TD></TR>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" width=12></TD>\r\n                      <TD></TD></TR></TBODY></TABLE></TD></TR>\r\n              <TR>\r\n                <TD vAlign=top align=middle>\r\n                  <TABLE height=120 cellSpacing=0 cellPadding=0 width=120 \r\n                  bgColor=#0b4c1c border=0>\r\n                    <TBODY>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" width=12></TD>\r\n                      <TD></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" width=5></TD>\r\n                      <TD align=right height=21></TD>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" width=5></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21><IMG \r\n                        src="images/beacon.gif"></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21><SMALL><SPAN \r\n                        style="COLOR: lime">#1089</SPAN></SMALL></TD></TR>\r\n                    <TR>\r\n                      <TD height=42></TD></TR>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" width=12></TD>\r\n                      <TD></TD></TR></TBODY></TABLE></TD>\r\n                <TD vAlign=top align=middle>\r\n                  <TABLE height=120 cellSpacing=0 cellPadding=0 width=120 \r\n                  bgColor=#0b4c1c border=0>\r\n                    <TBODY>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" width=12></TD>\r\n                      <TD></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" width=5></TD>\r\n                      <TD align=right height=21></TD>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" width=5></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21><IMG \r\n                        src="images/beacon.gif"></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21><SMALL><SPAN \r\n                        style="COLOR: lime">#1090</SPAN></SMALL></TD></TR>\r\n                    <TR>\r\n                      <TD height=42></TD></TR>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor_empty.gif" width=12></TD>\r\n                      <TD></TD></TR></TBODY></TABLE></TD>\r\n                <TD vAlign=top align=middle>\r\n                  <TABLE height=120 cellSpacing=0 cellPadding=0 width=120 \r\n                  bgColor=#14642f border=0>\r\n                    <TBODY>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" width=12></TD>\r\n                      <TD></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" width=5></TD>\r\n                      <TD align=right height=21></TD>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" width=5></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21><SMALL><A \r\n                        href="javascript:alert(''This will move you up one to sector #1091.'');"><SPAN \r\n                        style="COLOR: lime">#1091</SPAN></A></SMALL></TD></TR>\r\n                    <TR>\r\n                      <TD height=42><IMG src="images/port/buy.gif"><IMG \r\n                        height=16 alt=Wood src="images/port/1.png" width=13 \r\n                        border=0><IMG height=16 alt=Food \r\n                        src="images/port/2.png" width=13 border=0><IMG \r\n                        height=16 alt=Ore src="images/port/3.png" width=13 \r\n                        border=0><IMG height=16 alt=Weapons \r\n                        src="images/port/9.png" width=13 border=0><BR><IMG \r\n                        src="images/port/sell.gif"><IMG height=16 \r\n                        alt="Precious Metals" src="images/port/4.png" \r\n                        width=13 border=0><IMG height=16 alt=Slaves \r\n                        src="images/port/5.png" width=13 border=0><IMG \r\n                        height=16 alt=Textiles src="images/port/6.png" \r\n                        width=13 border=0><IMG height=16 alt=Circuitry \r\n                        src="images/port/8.png" width=13 border=0></TD></TR>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" width=12></TD>\r\n                      <TD></TD></TR></TBODY></TABLE></TD>\r\n                <TD vAlign=top align=middle>\r\n                  <TABLE height=120 cellSpacing=0 cellPadding=0 width=120 \r\n                  bgColor=#0b4c1c border=0>\r\n                    <TBODY>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" width=12></TD>\r\n                      <TD></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" width=5></TD>\r\n                      <TD align=right height=21></TD>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver_empty.gif" width=5></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21><SMALL><SPAN \r\n                        style="COLOR: lime">#1092</SPAN></SMALL></TD></TR>\r\n                    <TR>\r\n                      <TD height=42></TD></TR>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" width=12></TD>\r\n                      <TD></TD></TR></TBODY></TABLE></TD>\r\n                <TD vAlign=top align=middle>\r\n                  <TABLE height=120 cellSpacing=0 cellPadding=0 width=120 \r\n                  bgColor=#0b4c1c border=0>\r\n                    <TBODY>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" width=12></TD>\r\n                      <TD></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver_empty.gif" width=5></TD>\r\n                      <TD align=right height=21></TD>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" width=5></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21><SMALL><SPAN \r\n                        style="COLOR: lime">#1079</SPAN></SMALL></TD></TR>\r\n                    <TR>\r\n                      <TD height=42><IMG src="images/port/buy.gif"><IMG \r\n                        height=16 alt=Ore src="images/port/3.png" width=13 \r\n                        border=0><IMG height=16 alt=Slaves \r\n                        src="images/port/5.png" width=13 border=0><IMG \r\n                        height=16 alt=Textiles src="images/port/6.png" \r\n                        width=13 border=0><IMG height=16 alt=Machinery \r\n                        src="images/port/7.png" width=13 border=0><IMG \r\n                        height=16 alt=Circuitry src="images/port/8.png" \r\n                        width=13 border=0><IMG height=16 alt="Luxury Items" \r\n                        src="images/port/11.png" width=13 border=0><BR><IMG \r\n                        src="images/port/sell.gif"><IMG height=16 alt=Wood \r\n                        src="images/port/1.png" width=13 border=0><IMG \r\n                        height=16 alt=Food src="images/port/2.png" width=13 \r\n                        border=0><IMG height=16 alt="Precious Metals" \r\n                        src="images/port/4.png" width=13 border=0><IMG \r\n                        height=16 alt=Weapons src="images/port/9.png" \r\n                        width=13 border=0></TD></TR>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" width=12></TD>\r\n                      <TD></TD></TR></TBODY></TABLE></TD></TR>\r\n              <TR>\r\n                <TD vAlign=top align=middle>\r\n                  <TABLE height=120 cellSpacing=0 cellPadding=0 width=120 \r\n                  bgColor=#0b4c1c border=0>\r\n                    <TBODY>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" width=12></TD>\r\n                      <TD></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver_empty.gif" width=5></TD>\r\n                      <TD align=right height=21></TD>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" width=5></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21><IMG \r\n                        src="images/shipdealer.gif"><IMG \r\n                        src="images/hardware.png"><IMG \r\n                        src="images/beacon.gif"><IMG alt=HQ \r\n                        src="images/government.gif"></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21><SMALL><SPAN \r\n                        style="COLOR: lime">#1103</SPAN></SMALL></TD></TR>\r\n                    <TR>\r\n                      <TD height=42></TD></TR>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor_empty.gif" width=12></TD>\r\n                      <TD></TD></TR></TBODY></TABLE></TD>\r\n                <TD vAlign=top align=middle>\r\n                  <TABLE height=120 cellSpacing=0 cellPadding=0 width=120 \r\n                  bgColor=#14642f border=0>\r\n                    <TBODY>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor_empty.gif" width=12></TD>\r\n                      <TD></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" width=5></TD>\r\n                      <TD align=right height=21></TD>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" width=5></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21><IMG \r\n                        src="images/beacon.gif"></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21><SMALL><A \r\n                        href="javascript:alert(''This will move you left one to sector #1104.'');"><SPAN \r\n                        style="COLOR: lime">#1104</SPAN></A></SMALL></TD></TR>\r\n                    <TR>\r\n                      <TD height=42></TD></TR>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" width=12></TD>\r\n                      <TD></TD></TR></TBODY></TABLE></TD>\r\n                <TD vAlign=top align=middle>\r\n                  <TABLE height=120 cellSpacing=0 cellPadding=0 width=120 \r\n                  background="images/currentsector.png" border=0>\r\n                    <TBODY>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" width=12></TD>\r\n                      <TD></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" width=5></TD>\r\n                      <TD align=right height=21><IMG height=16 alt=Trader \r\n                        src="images/trader.jpg" width=13></TD>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" width=5></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21><IMG \r\n                        src="images/beacon.gif"></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21><SMALL><A \r\n                        href="javascript:alert(''This will take you to the current sector screen.'');"><SPAN \r\n                        style="COLOR: lime">#1105</SPAN></A></SMALL></TD></TR>\r\n                    <TR>\r\n                      <TD height=42></TD></TR>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor_empty.gif" width=12></TD>\r\n                      <TD></TD></TR></TBODY></TABLE></TD>\r\n                <TD vAlign=top align=middle>\r\n                  <TABLE height=120 cellSpacing=0 cellPadding=0 width=120 \r\n                  bgColor=#14642f border=0>\r\n                    <TBODY>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" width=12></TD>\r\n                      <TD></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" width=5></TD>\r\n                      <TD align=right height=21><IMG height=16 alt=Forces \r\n                        src="images/forces.jpg" width=13></TD>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" width=5></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21><SMALL><A \r\n                        href="javascript:alert(''This will move you right one to sector #1106.'');"><SPAN \r\n                        style="COLOR: lime">#1106</SPAN></A></SMALL></TD></TR>\r\n                    <TR>\r\n                      <TD height=42><IMG src="images/port/buy.gif"><IMG \r\n                        height=16 alt=Food src="images/port/2.png" width=13 \r\n                        border=0><IMG height=16 alt=Slaves \r\n                        src="images/port/5.png" width=13 border=0><IMG \r\n                        height=16 alt=Machinery src="images/port/7.png" \r\n                        width=13 border=0><IMG height=16 alt=Circuitry \r\n                        src="images/port/8.png" width=13 border=0><BR><IMG \r\n                        src="images/port/sell.gif"><IMG height=16 alt=Wood \r\n                        src="images/port/1.png" width=13 border=0><IMG \r\n                        height=16 alt=Ore src="images/port/3.png" width=13 \r\n                        border=0><IMG height=16 alt="Precious Metals" \r\n                        src="images/port/4.png" width=13 border=0><IMG \r\n                        height=16 alt=Weapons src="images/port/9.png" \r\n                        width=13 border=0></TD></TR>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" width=12></TD>\r\n                      <TD></TD></TR></TBODY></TABLE></TD>\r\n                <TD vAlign=top align=middle>\r\n                  <TABLE height=120 cellSpacing=0 cellPadding=0 width=120 \r\n                  bgColor=#0b4c1c border=0>\r\n                    <TBODY>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" width=12></TD>\r\n                      <TD></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" width=5></TD>\r\n                      <TD align=right height=21></TD>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" width=5></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21><SMALL><SPAN \r\n                        style="COLOR: lime">#1093</SPAN></SMALL></TD></TR>\r\n                    <TR>\r\n                      <TD height=42><IMG src="images/port/buy.gif"><IMG \r\n                        height=16 alt=Food src="images/port/2.png" width=13 \r\n                        border=0><IMG height=16 alt=Ore \r\n                        src="images/port/3.png" width=13 border=0><IMG \r\n                        height=16 alt="Precious Metals" \r\n                        src="images/port/4.png" width=13 border=0><IMG \r\n                        height=16 alt=Slaves src="images/port/5.png" \r\n                        width=13 border=0><IMG height=16 alt=Textiles \r\n                        src="images/port/6.png" width=13 border=0><IMG \r\n                        height=16 alt=Weapons src="images/port/9.png" \r\n                        width=13 border=0><BR><IMG \r\n                        src="images/port/sell.gif"><IMG height=16 alt=Wood \r\n                        src="images/port/1.png" width=13 border=0><IMG \r\n                        height=16 alt=Machinery src="images/port/7.png" \r\n                        width=13 border=0><IMG height=16 alt=Circuitry \r\n                        src="images/port/8.png" width=13 border=0></TD></TR>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" width=12></TD>\r\n                      <TD></TD></TR></TBODY></TABLE></TD></TR>\r\n              <TR>\r\n                <TD vAlign=top align=middle>\r\n                  <TABLE height=120 cellSpacing=0 cellPadding=0 width=120 \r\n                  bgColor=#0b4c1c border=0>\r\n                    <TBODY>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor_empty.gif" width=12></TD>\r\n                      <TD></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" width=5></TD>\r\n                      <TD align=right height=21></TD>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" width=5></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21><SMALL><SPAN \r\n                        style="COLOR: lime">#1117</SPAN></SMALL></TD></TR>\r\n                    <TR>\r\n                      <TD height=42><IMG src="images/port/buy.gif"><IMG \r\n                        height=16 alt=Wood src="images/port/1.png" width=13 \r\n                        border=0><IMG height=16 alt=Food \r\n                        src="images/port/2.png" width=13 border=0><IMG \r\n                        height=16 alt=Ore src="images/port/3.png" width=13 \r\n                        border=0><IMG height=16 alt="Precious Metals" \r\n                        src="images/port/4.png" width=13 border=0><IMG \r\n                        height=16 alt=Textiles src="images/port/6.png" \r\n                        width=13 border=0><IMG height=16 alt=Circuitry \r\n                        src="images/port/8.png" width=13 border=0><IMG \r\n                        height=16 alt=Computer src="images/port/10.png" \r\n                        width=13 border=0><IMG height=16 alt=Narcotics \r\n                        src="images/port/12.png" width=13 border=0><BR><IMG \r\n                        src="images/port/sell.gif"><IMG height=16 alt=Slaves \r\n                        src="images/port/5.png" width=13 border=0><IMG \r\n                        height=16 alt=Machinery src="images/port/7.png" \r\n                        width=13 border=0><IMG height=16 alt=Weapons \r\n                        src="images/port/9.png" width=13 border=0></TD></TR>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" width=12></TD>\r\n                      <TD></TD></TR></TBODY></TABLE></TD>\r\n                <TD vAlign=top align=middle>\r\n                  <TABLE height=120 cellSpacing=0 cellPadding=0 width=120 \r\n                  bgColor=#0b4c1c border=0>\r\n                    <TBODY>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" width=12></TD>\r\n                      <TD></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" width=5></TD>\r\n                      <TD align=right height=21></TD>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver_empty.gif" width=5></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21><IMG \r\n                        src="images/beacon.gif"></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21><SMALL><SPAN \r\n                        style="COLOR: lime">#1118</SPAN></SMALL></TD></TR>\r\n                    <TR>\r\n                      <TD height=42></TD></TR>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor_empty.gif" width=12></TD>\r\n                      <TD></TD></TR></TBODY></TABLE></TD>\r\n                <TD vAlign=top align=middle>\r\n                  <TABLE height=120 cellSpacing=0 cellPadding=0 width=120 \r\n                  bgColor=#0b4c1c border=0>\r\n                    <TBODY>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor_empty.gif" width=12></TD>\r\n                      <TD></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver_empty.gif" width=5></TD>\r\n                      <TD align=right height=21></TD>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" width=5></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21><SMALL><SPAN \r\n                        style="COLOR: lime">#1119</SPAN></SMALL></TD></TR>\r\n                    <TR>\r\n                      <TD height=42><IMG src="images/port/buy.gif"><IMG \r\n                        height=16 alt="Precious Metals" \r\n                        src="images/port/4.png" width=13 border=0><IMG \r\n                        height=16 alt=Slaves src="images/port/5.png" \r\n                        width=13 border=0><IMG height=16 alt=Textiles \r\n                        src="images/port/6.png" width=13 border=0><IMG \r\n                        height=16 alt=Circuitry src="images/port/8.png" \r\n                        width=13 border=0><IMG height=16 alt=Weapons \r\n                        src="images/port/9.png" width=13 border=0><IMG \r\n                        height=16 alt=Computer src="images/port/10.png" \r\n                        width=13 border=0><BR><IMG \r\n                        src="images/port/sell.gif"><IMG height=16 alt=Wood \r\n                        src="images/port/1.png" width=13 border=0><IMG \r\n                        height=16 alt=Food src="images/port/2.png" width=13 \r\n                        border=0><IMG height=16 alt=Ore \r\n                        src="images/port/3.png" width=13 border=0><IMG \r\n                        height=16 alt=Machinery src="images/port/7.png" \r\n                        width=13 border=0><IMG height=16 alt="Luxury Items" \r\n                        src="images/port/11.png" width=13 border=0><IMG \r\n                        height=16 alt=Narcotics src="images/port/12.png" \r\n                        width=13 border=0></TD></TR>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" width=12></TD>\r\n                      <TD></TD></TR></TBODY></TABLE></TD>\r\n                <TD vAlign=top align=middle>\r\n                  <TABLE height=120 cellSpacing=0 cellPadding=0 width=120 \r\n                  bgColor=#0b4c1c border=0>\r\n                    <TBODY>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" width=12></TD>\r\n                      <TD></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" width=5></TD>\r\n                      <TD align=right height=21></TD>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" width=5></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21><SMALL><SPAN \r\n                        style="COLOR: lime">#1120</SPAN></SMALL></TD></TR>\r\n                    <TR>\r\n                      <TD height=42></TD></TR>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" width=12></TD>\r\n                      <TD></TD></TR></TBODY></TABLE></TD>\r\n                <TD vAlign=top align=middle>\r\n                  <TABLE height=120 cellSpacing=0 cellPadding=0 width=120 \r\n                  bgColor=#0b4c1c border=0>\r\n                    <TBODY>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" width=12></TD>\r\n                      <TD></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" width=5></TD>\r\n                      <TD align=right height=21></TD>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver_empty.gif" width=5></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21><SMALL><SPAN \r\n                        style="COLOR: lime">#1107</SPAN></SMALL></TD></TR>\r\n                    <TR>\r\n                      <TD height=42><IMG src="images/port/buy.gif"><IMG \r\n                        height=16 alt=Wood src="images/port/1.png" width=13 \r\n                        border=0><IMG height=16 alt=Food \r\n                        src="images/port/2.png" width=13 border=0><IMG \r\n                        height=16 alt=Ore src="images/port/3.png" width=13 \r\n                        border=0><IMG height=16 alt="Precious Metals" \r\n                        src="images/port/4.png" width=13 border=0><IMG \r\n                        height=16 alt=Textiles src="images/port/6.png" \r\n                        width=13 border=0><IMG height=16 alt=Machinery \r\n                        src="images/port/7.png" width=13 border=0><IMG \r\n                        height=16 alt=Weapons src="images/port/9.png" \r\n                        width=13 border=0><BR><IMG \r\n                        src="images/port/sell.gif"><IMG height=16 alt=Slaves \r\n                        src="images/port/5.png" width=13 border=0></TD></TR>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" width=12></TD>\r\n                      <TD></TD></TR></TBODY></TABLE></TD></TR>\r\n              <TR>\r\n                <TD vAlign=top align=middle>\r\n                  <TABLE height=120 cellSpacing=0 cellPadding=0 width=120 \r\n                  bgColor=#0b4c1c border=0>\r\n                    <TBODY>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" width=12></TD>\r\n                      <TD></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" width=5></TD>\r\n                      <TD align=right height=21></TD>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" width=5></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21><SMALL><SPAN \r\n                        style="COLOR: lime">#1131</SPAN></SMALL></TD></TR>\r\n                    <TR>\r\n                      <TD height=42></TD></TR>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" width=12></TD>\r\n                      <TD></TD></TR></TBODY></TABLE></TD>\r\n                <TD vAlign=top align=middle>\r\n                  <TABLE height=120 cellSpacing=0 cellPadding=0 width=120 \r\n                  bgColor=#0b4c1c border=0>\r\n                    <TBODY>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor_empty.gif" width=12></TD>\r\n                      <TD></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" width=5></TD>\r\n                      <TD align=right height=21></TD>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" width=5></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21><SMALL><SPAN \r\n                        style="COLOR: lime">#1132</SPAN></SMALL></TD></TR>\r\n                    <TR>\r\n                      <TD height=42><IMG src="images/port/buy.gif"><IMG \r\n                        height=16 alt=Food src="images/port/2.png" width=13 \r\n                        border=0><IMG height=16 alt=Ore \r\n                        src="images/port/3.png" width=13 border=0><IMG \r\n                        height=16 alt=Slaves src="images/port/5.png" \r\n                        width=13 border=0><IMG height=16 alt=Machinery \r\n                        src="images/port/7.png" width=13 border=0><IMG \r\n                        height=16 alt=Circuitry src="images/port/8.png" \r\n                        width=13 border=0><IMG height=16 alt=Weapons \r\n                        src="images/port/9.png" width=13 border=0><BR><IMG \r\n                        src="images/port/sell.gif"><IMG height=16 alt=Wood \r\n                        src="images/port/1.png" width=13 border=0><IMG \r\n                        height=16 alt="Precious Metals" \r\n                        src="images/port/4.png" width=13 border=0></TD></TR>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor_empty.gif" width=12></TD>\r\n                      <TD></TD></TR></TBODY></TABLE></TD>\r\n                <TD vAlign=top align=middle>\r\n                  <TABLE height=120 cellSpacing=0 cellPadding=0 width=120 \r\n                  bgColor=#0b4c1c border=0>\r\n                    <TBODY>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" width=12></TD>\r\n                      <TD></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" width=5></TD>\r\n                      <TD align=right height=21></TD>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" width=5></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21><SMALL><SPAN \r\n                        style="COLOR: lime">#1133</SPAN></SMALL></TD></TR>\r\n                    <TR>\r\n                      <TD height=42><IMG src="images/port/buy.gif"><IMG \r\n                        height=16 alt=Ore src="images/port/3.png" width=13 \r\n                        border=0><IMG height=16 alt=Slaves \r\n                        src="images/port/5.png" width=13 border=0><BR><IMG \r\n                        src="images/port/sell.gif"><IMG height=16 alt=Wood \r\n                        src="images/port/1.png" width=13 border=0><IMG \r\n                        height=16 alt=Food src="images/port/2.png" width=13 \r\n                        border=0><IMG height=16 alt="Precious Metals" \r\n                        src="images/port/4.png" width=13 border=0></TD></TR>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" width=12></TD>\r\n                      <TD></TD></TR></TBODY></TABLE></TD>\r\n                <TD vAlign=top align=middle>\r\n                  <TABLE height=120 cellSpacing=0 cellPadding=0 width=120 \r\n                  bgColor=#0b4c1c border=0>\r\n                    <TBODY>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" width=12></TD>\r\n                      <TD></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" width=5></TD>\r\n                      <TD align=right height=21></TD>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" width=5></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21><SMALL><SPAN \r\n                        style="COLOR: lime">#1134</SPAN></SMALL></TD></TR>\r\n                    <TR>\r\n                      <TD height=42></TD></TR>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" width=12></TD>\r\n                      <TD></TD></TR></TBODY></TABLE></TD>\r\n                <TD vAlign=top align=middle>\r\n                  <TABLE height=120 cellSpacing=0 cellPadding=0 width=120 \r\n                  bgColor=#0b4c1c border=0>\r\n                    <TBODY>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" width=12></TD>\r\n                      <TD></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" width=5></TD>\r\n                      <TD align=right height=21></TD>\r\n                      <TD align=middle width=5 rowSpan=4><IMG height=12 \r\n                        src="images/link_ver.gif" width=5></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21></TD></TR>\r\n                    <TR>\r\n                      <TD align=middle height=21><SMALL><SPAN \r\n                        style="COLOR: lime">#1121</SPAN></SMALL></TD></TR>\r\n                    <TR>\r\n                      <TD height=42><IMG src="images/port/buy.gif"><IMG \r\n                        height=16 alt=Ore src="images/port/3.png" width=13 \r\n                        border=0><IMG height=16 alt="Precious Metals" \r\n                        src="images/port/4.png" width=13 border=0><BR><IMG \r\n                        src="images/port/sell.gif"><IMG height=16 alt=Wood \r\n                        src="images/port/1.png" width=13 border=0><IMG \r\n                        height=16 alt=Food src="images/port/2.png" width=13 \r\n                        border=0><IMG height=16 alt=Slaves \r\n                        src="images/port/5.png" width=13 border=0></TD></TR>\r\n                    <TR>\r\n                      <TD></TD>\r\n                      <TD vAlign=center align=middle><IMG height=5 \r\n                        src="images/link_hor.gif" width=12></TD>\r\n                      <TD></TD></TR></TBODY></TABLE></TD></TR></TBODY></TABLE></DIV></TD></TR>\r\n        <TR>\r\n          <TD vAlign=bottom><!-- copyright -->\r\n            <TABLE width="100%" border=0>\r\n              <TBODY>\r\n              <TR>\r\n                <TD vAlign=center><BR>\r\n                  <CENTER><SPAN style="FONT-SIZE: 75%">Get <B><U>FREE \r\n                  TURNS</U></B> for voting if you see the \r\n                  star.</SPAN></CENTER><A \r\n                  href="javascript:alert(''In game, if you see a star on the link, you will get bonus turns for voting if you click.  You can only get \r\nbonus turns once a day from each voting site.'');"><IMG \r\n                  height=41 \r\n                  src="images/game_sites/mpogd_vote.png" width=98 \r\n                  border=0></A><A \r\n                  href="javascript:alert(''In game, if you see a star on the link, you will get bonus turns for voting if you click.  You can only get \r\nbonus turns once a day from each voting site.'');"><IMG height=41 \r\n                  src="images/game_sites/twg.png" width=98 \r\n                  border=0></A>&nbsp;&nbsp;<A \r\n                  href="javascript:alert(''In game, if you see a star on the link, you will get bonus turns for voting if you click.  You can only get \r\nbonus turns once a day from each voting site.'');"><IMG \r\n                  height=41 src="images/game_sites/omgn_vote.png" \r\n                  width=98 border=0></A></TD>\r\n                <TD style="VERTICAL-ALIGN: bottom" align=right \r\n                  width="100%"><SPAN style="FONT-SIZE: 75%">Space Merchant \r\n                  Realms<BR><A \r\n                  href="javascript:alert(''This would take you to the change log, where you could view the changes that have been made to the game.'');">v1.1.1</A>&nbsp;&nbsp;&nbsp;'),
(71, 68, 3, 'Plot a Course', 'You can plot a course to a distant sector using the Plot a Course link. Fill in your destination sector number and hit Plot Course. You are returned to your current sector view and there is a link now above the navigation box to follow the plotted course. When figuring a plotted course, your current sector is the default starting sector but this can be changed as well, so you can calculate the distance between any two sectors easily. '),
(72, 68, 4, 'DL MGU Maps', 'The DL MGU Maps link is there to help you use the Merchants Guide to the Universe (MGU) program. MGU is a downloadable program that helps SMR players find trade routes and shops faster. Details on MGU are found elsewhere. '),
(73, 68, 5, 'Galaxy Map', 'The Galaxy Map link will show you a list of all the galaxies in the current game. Details of the galaxies are shown only after they have been fully explored, either by yourself actually having explored all the sectors or by your alliance mates sharing their explored sectors with you. The Galaxy Maps are a good tool for finding locations, warps, and trade routes, or for just getting a better idea of what the space around you looks like. '),
(74, 60, 9, 'Turns', 'Space Merchant Realms is a turn-based game. Almost everything you do in-game will cost you turns. The number of turns you have is shown in the upper right part of the screen. You are only able to move or act in the game if you have enough turns to do it. Below is a chart that shows which actions cost you turns and how many. \r\n\r\n<p>Turn Cost Activity \r\n<br>1 turn Moving from one sector to another \r\n<br>1 turn Landing on a Planet \r\n<br>1 turn Buying or Selling goods \r\n<br>1 turn Dumping cargo \r\n<br>3 turns Attacking a trader. \r\n<br>3 turns Attacking a port. \r\n<br>3 turns Attacking a planet. \r\n<br>3 turns Attacking forces. \r\n<br>5 turns Moving through a galaxy warp. \r\n<br>15 turns Using a jump drive. \r\n\r\n<p>You gain turns normally over the course of an hour. The number of turns you get per hour (TPH) depends on the type of ship you have and is referred to as ship speed. Each ship has a base TPH value. The TPH of your current ship is shown on the Trader information screen. If the speed of a particular game is greater than 1.0, then the tph is increased accordingly. '),
(75, 60, 10, 'Newbie Turns', 'At the start of a game, each player is given 500 newbie turns. You also receive 100 newbie turns when your ship is destroyed. Newbie turns are safe turns that prevent your ship from being attacked or damaged by mines. Having newbie turns also prevents you from attacking other ships or dropping forces in a sector. They are supposed to be used to safely get a new ship after you have been podded and trade at ports for a while without worry of attack. Newbie turns are used up at the same rate as normal turns. If you have newbie turns, they are shown below the normal turns you have in the upper right hand corner of the screen. Once your newbie turns are gone, you are vulnerable to attack and your ship can be damaged by mines. If you want to drop your newbie turns, you can do it under the Trader options.'),
(76, 51, 3, 'Shoplist', 'SMRealms currently does not have a shop list that you view, however, one can be found at: <h1><a href="http://smrtools.b-o-b.org/shopListFull.BOB" target="smrtools">SMR Tools</a></h1>\r\n<br>This site is created and maintained by the Admin B.O.B.\r\n<br>Some of it''s feautures:\r\n<li>Sortable by Name or Type\r\n<li>Displays the Items the Shop Sells.\r\n<li>Items are clickable links that will display the stats of the item.');

INSERT INTO `message_box_types` (`box_type_id`, `box_type_name`) VALUES
(1, 'Automatic Bug Reports'),
(2, 'Player Bug Reports'),
(3, 'Global Messages'),
(4, 'Alliance Descriptions'),
(5, 'Beta Applications'),
(6, 'Photo Album Comments');

INSERT INTO `message_type` (`message_type_id`, `message_type_name`) VALUES
(1, 'Global Messages'),
(2, 'Player Messages'),
(3, 'Planet Messages'),
(4, 'Scout Messages'),
(5, 'Political Messages'),
(6, 'Alliance Messages'),
(7, 'Admin Messages'),
(8, 'Casino Messages');

INSERT INTO `open_forms` (`type`, `open`) VALUES
('FEATURE', 'TRUE'),
('BETA', 'FALSE');

INSERT INTO `permission` (`permission_id`, `permission_name`, `link_to`) VALUES
(1, 'Manage Admin Permissions', 'permission_manage.php'),
(2, '1.2.1 Universe Generator', 'universe_create.php'),
(3, 'Game Open/Close', 'game_status.php'),
(4, 'Delete Game', 'game_delete.php'),
(5, 'Create Announcement', 'announcement_create.php'),
(6, 'Send Message', 'admin_message_send.php'),
(7, 'View Reported Messages', 'notify_view.php'),
(8, 'Edit Account', 'account_edit.php'),
(9, 'Multi Tools', 'ip_view.php'),
(10, 'Check Passwords', 'password_check.php'),
(11, 'Check Info', 'info.php'),
(12, 'Cheating Ship Check', 'ship_check.php'),
(13, 'Show Map to User', 'map_show.php'),
(14, 'Create SMC File', 'smc_choose.php'),
(15, 'Search for Keywords', 'keyword_search.php'),
(16, 'Log Console', 'log_console.php'),
(17, 'Send Newsletter', 'newsletter_send.php'),
(18, 'Form Access', 'form_open.php'),
(19, 'Approve Photo Album', 'album_approve.php'),
(20, 'Moderate Photo Album', 'album_moderate.php'),
(21, 'Manage ChangeLog', 'changelog.php'),
(22, 'Anon Account View', 'anon_acc_view.php'),
(23, 'Word Filter', 'word_filter.php'),
(24, 'Combat Simulator', 'combat_simulator.php'),
(25, 'Edit Locations', 'location_edit.php'),
(27, 'Can Moderate Feature Requests', ''),
(26, 'View Message Boxes', 'box_view.php'),
(28, 'Can Edit Alliance Descriptions', ''),
(30, '1.6 Universe Generator', '1.6/universe_create.php'),
(31, 'Create Vote', 'vote_create.php'),
(32, 'Can Edit Started Games', '');

INSERT INTO `planet_construction` (`construction_id`, `construction_name`, `construction_description`, `max_construction`, `exp_gain`) VALUES
(1, 'Generator', 'Increase shield capacity', 100, 45),
(2, 'Hangar', 'Increase drone capacity', 100, 90),
(3, 'Turret', 'Increase weapons capacity', 10, 270);

INSERT INTO `planet_cost_credits` (`construction_id`, `amount`) VALUES
(1, 100000),
(2, 100000),
(3, 1000000);

INSERT INTO `planet_cost_good` (`construction_id`, `good_id`, `amount`) VALUES
(1, 2, 20),
(1, 3, 15),
(1, 7, 35),
(1, 10, 5),
(2, 2, 20),
(2, 3, 10),
(2, 1, 25),
(3, 2, 35),
(3, 3, 35),
(3, 7, 15),
(3, 10, 10);

INSERT INTO `planet_cost_time` (`construction_id`, `amount`) VALUES
(1, 10800),
(2, 21600),
(3, 64800);

INSERT INTO `race` (`race_id`, `race_name`, `race_description`) VALUES
(1, 'Neutral', NULL),
(2, 'Alskant', 'This race of tall, thin humanoids have just recently (in the last 100 years) developed the technology that allows inter-stellar travel. However, in the last 100 years, their friendly nature has allowed them to trade for much of the technology the other races had achieved. They do not focus on combat, but they have been preparing themselves in case it arrives. Their ships tend to be geared more towards commerce than combat, and this matches their enterprising nature. They continue to seek the knowledge of the other races, and to explore to the edges of space. They tend to have relatively good relationships with most of the other races.'),
(3, 'Creonti', 'The Creonti are an introverted race that has little to do with the other races, except for trade which has become vital to all of the races. Their small stature also has led them to feel inferior to some of the other races. However, these feelings are easily overcome with the weapons they usually carry. They have moderately good to neutral relationships with the other races, but most of them live by a "Creonti First" motto. They are very team oriented, and unite quickly to defend their own. While they do not start conflicts often, they have been involved in several and are viewed as proficient pilots.'),
(4, 'Human', 'These humanoids tend to be the first to jump into the different wars that develop. They are often the first to take sides when a conflict ignites, even if it does not affect them directly. They were the original founders of several attempts to unify the races, all of which failed. However, they are the most courageous (and outspoken) of the races. They tend to roam the entire galaxy, and team up with various traders of many races if it suits their goals. They also have the broadest knowledge of the universe as a whole, due to their extensive exploration and the fact that they were the second race to develop the technology for interstellar travel. Their relationships with other races tend to vary a lot. The only relationship with any level of consistency is their irritation with the West-Quadrant Humans, a division of their race that broke away over 300 years ago.'),
(5, 'Ik''Thorne', 'Considering the average Ik''Thorne stands about 11 feet tall, it is no surprise that they are the designers and builders of the large cruisers throughout the galaxy. While there are several other models available, the Ik''Thorne line of battle cruisers and carriers are commonly regarded as the strongest of the ships. However, their weapons are not exceedingly strong, and they usually are forced to go to others to equip the ship with weapons and combat drones. The Ik''Thorne like stability. They don''t care as much if they are at war with someone or at peace, but just like things to be consistent. Races who are constantly changing their views on things annoy them. This is probably due to their extremely long life spans. While they have butted heads with the Thevians and the Salvene over the centuries, they maintain no outstanding "enemies." However, their empire is often attacked and plundered for its wealth and knowledge, so they are drawn into many conflicts.'),
(6, 'Salvene', 'This race of quadripeds has a strong focus on conquest. They are not concerned with honor and justice and other such "trivialities", but rather they focus on the wealth, power, and extent of their own empire. They tend to alter their relations with the other races to whatever suits them best at a given time. While they are very trusting and dependent on those of their own race, relationships with others are slow to form, and they distrust most of the other races. While they are involved in a lot of trading, they put a large focus on combat. It is rare that they are not at war with somebody. While their ships are decent trade ships, they excel in combat situations.'),
(7, 'Thevian', 'The strong focus on reputation is what distinguishes Thevian culture from most of the other races. This race lives its entire life inside of a robotic shell. They use the shell for all movement and interaction. This shell is shaped like a humanoid, but the Thevians themselves are quite indescribable. Their desire for reputation is what causes them to be extremists. The good Thevians will be extremely good, hunting down evil throughout the galaxy, even if no formal bounty is set. If there is a bounty on a person, they view it as an even better chance to make a name for themselves. The evil Thevians take the exact opposite route, becoming the most notorious criminals in the galaxy for their acts of destruction and cruelty. They wander around raiding ports and planets and destroying all they encounter. Thus, their need to distinguish themselves leads to them contributing to most admirable police/bounty hunters and the most notorious criminals.'),
(8, 'WQ Human', 'While they are of the same race as the Humans, the WQ Humans want nothing to do with them. When the Humans were attempting to unite the races, the WQ Humans began colonizing the Western Quadrant of the galaxy. Here they developed their communities and began extensive trading with other races, specifically the Thevians. The unification attempt failed due to a war that broke out between the Thevians and the Ik''Thorne. The main Human forces almost immediately joined forces with the Ik''Thorne, for several reasons that are not clearly understood. The WQ Humans looked upon this as being unjust, and lobbied several times to withdraw Human involvement from the conflict. Finally, they declared themselves a separate entity. Immediately, the Humans withdrew from the Thevian-Ik''Thorne conflict, and attempted to suppress the rebellion. The civil war continued for over 50 years, and ended in an unstable peace treaty. Since then there have been several conflicts between the two human groups, and their relations seem to be worsening.'),
(9, 'Nijarin', 'The Nijarin are a race of six-limbed reptilian creatures. The Nijarin race has existed just as long if not longer than the other races but has only just recently come out of hiding. The Nijarin have become very powerful contenders in the war for resources even though they only recently resurfaced. Their focus is on offensive power which has caused the creation of high-HP ships and very powerful weaponry. This has caused the reduction of shields and armour to allow their ships to support such a heavy payload. To make up for this the Nijarin use a technology called the Drone Communications Scrambler. This device causes enemy drones to be much less effective. The Nijarin fleet cannot be held back from taking back their part of this universe of war.');

INSERT INTO `ship_type` (`ship_type_id`, `ship_name`, `speed`, `race_id`, `cost`, `hardpoint`, `lvl_needed`, `buyer_restriction`) VALUES
(1, 'Galactic Semi', 8, 1, 0, 1, 1, 0),
(2, 'Armoured Semi', 7, 1, 122152, 1, 1, 0),
(3, 'Celestial Trader', 9, 1, 435969, 1, 6, 0),
(4, 'Merchant Vessel', 9, 1, 825408, 2, 5, 0),
(5, 'Planetary Trader', 8, 1, 5283335, 2, 8, 0),
(6, 'Stellar Freighter', 8, 1, 7508455, 1, 9, 0),
(7, 'Light Courier Vessel', 10, 1, 2530676, 2, 10, 0),
(8, 'Advanced Courier Vessel', 10, 1, 4560740, 2, 9, 0),
(9, 'Inter-Stellar Trader', 7, 1, 5314159, 2, 15, 0),
(10, 'Freighter', 10, 1, 4791393, 1, 10, 0),
(11, 'Planetary Freighter', 5, 1, 6215028, 1, 10, 0),
(12, 'Planetary Super Freighter', 4, 1, 7035792, 1, 20, 0),
(13, 'Unarmed Scout', 12, 1, 65251, 0, 1, 0),
(14, 'Small Escort', 10, 1, 520821, 2, 5, 0),
(15, 'Light Cruiser', 7, 1, 939440, 3, 10, 0),
(16, 'Medium Cruiser', 6, 1, 1461380, 4, 10, 0),
(17, 'Battle Cruiser', 8, 1, 6628134, 5, 10, 0),
(18, 'Celestial Mercenary', 9, 1, 2865997, 3, 9, 0),
(19, 'Celestial Combatant', 9, 1, 2856949, 5, 9, 0),
(20, 'Federal Discovery', 11, 1, 3335689, 3, 8, 1),
(21, 'Federal Warrant', 9, 1, 12026598, 5, 14, 1),
(22, 'Federal Ultimatum', 8, 1, 23675738, 7, 22, 1),
(23, 'Thief', 12, 1, 9802157, 3, 15, 2),
(24, 'Assassin', 11, 1, 7483452, 5, 16, 2),
(25, 'Death Cruiser', 9, 1, 19890100, 5, 18, 2),
(26, 'Light Carrier', 9, 1, 783035, 1, 5, 0),
(27, 'Medium Carrier', 8, 1, 2630523, 3, 8, 0),
(28, 'Newbie Merchant Vessel', 9, 1, 0, 2, 1, 0),
(29, 'Small-Timer', 9, 2, 0, 1, 1, 0),
(30, 'Trip-Maker', 12, 2, 4354955, 1, 7, 0),
(31, 'Deal-Maker', 10, 2, 5727059, 2, 9, 0),
(32, 'Deep-Spacer', 8, 2, 5721889, 3, 8, 0),
(33, 'Trade-Master', 6, 2, 7095764, 2, 10, 0),
(34, 'Medium Cargo Hulk', 8, 3, 0, 1, 1, 0),
(35, 'Leviathan', 7, 3, 1122251, 2, 10, 0),
(36, 'Goliath', 10, 3, 3380866, 5, 11, 0),
(37, 'Juggernaut', 8, 3, 5104130, 5, 12, 0),
(38, 'Devastator', 6, 3, 11559082, 8, 16, 0),
(39, 'Light Freighter', 8, 4, 0, 1, 1, 0),
(40, 'Ambassador', 9, 4, 4292187, 1, 7, 0),
(41, 'Renaissance', 8, 4, 306664, 2, 5, 0),
(42, 'Border Cruiser', 8, 4, 6887432, 5, 10, 0),
(43, 'Destroyer', 7, 4, 12719505, 6, 20, 0),
(44, 'Tiny Delight', 8, 5, 0, 1, 1, 0),
(45, 'Rebellious Child', 12, 5, 73707, 1, 5, 0),
(46, 'Favoured Offspring', 8, 5, 1986141, 1, 11, 0),
(47, 'Proto Carrier', 10, 5, 53810, 1, 5, 0),
(48, 'Advanced Carrier', 9, 5, 3110264, 2, 11, 0),
(49, 'Mother Ship', 6, 5, 6266207, 1, 15, 0),
(50, 'A Hatchling''s Due', 7, 6, 0, 1, 1, 0),
(51, 'Drudge', 7, 6, 875683, 2, 7, 0),
(52, 'Watchful Eye', 12, 6, 65366, 1, 4, 0),
(53, 'Predator', 10, 6, 2823300, 5, 9, 0),
(54, 'Ravager', 8, 6, 6307691, 5, 14, 0),
(55, 'Eater of Souls', 7, 6, 13306175, 6, 16, 0),
(56, 'Swift Venture', 9, 7, 0, 1, 1, 0),
(57, 'Expediter', 12, 7, 1272250, 0, 8, 0),
(58, 'Star Ranger', 14, 7, 60923, 1, 4, 0),
(59, 'Bounty Hunter', 13, 7, 2568634, 5, 12, 0),
(60, 'Carapace', 11, 7, 7045094, 5, 15, 0),
(61, 'Assault Craft', 10, 7, 12789862, 6, 20, 0),
(62, 'Slip Freighter', 9, 8, 0, 1, 1, 0),
(63, 'Negotiator', 11, 8, 155234, 1, 6, 0),
(64, 'Resistance', 10, 8, 2156265, 5, 8, 0),
(65, 'Rogue', 10, 8, 6574860, 5, 8, 0),
(66, 'Blockade Runner', 9, 8, 5131071, 3, 13, 0),
(67, 'Dark Mirage', 9, 8, 11088764, 6, 18, 0),
(68, 'Spooky Midnight Special', 10, 1, 0, 10, 50, 0),
(69, 'Escape Pod', 7, 1, 0, 0, 1, 0),
(70, 'Redeemer', 9, 9, 0, 1, 0, 0),
(71, 'Retaliation', 9, 9, 435900, 8, 5, 0),
(72, 'Vengeance', 10, 9, 1137543, 2, 7, 0),
(73, 'Retribution', 10, 9, 3235800, 5, 10, 0),
(74, 'Vindicator', 8, 9, 6800000, 6, 15, 0),
(75, 'Fury', 8, 9, 13724001, 7, 20, 0),
(100, 'Slayer', 10, 1, 0, 1, 0, 1),
(666, 'Demonica', 10, 1, 0, 6, 0, 2);

INSERT INTO `ship_type_support_hardware` (`ship_type_id`, `hardware_type_id`, `max_amount`) VALUES
(1, 1, 150),
(1, 2, 175),
(1, 3, 60),
(1, 4, 0),
(1, 5, 0),
(1, 6, 0),
(1, 7, 0),
(1, 8, 0),
(1, 9, 0),
(1, 10, 0),
(2, 1, 225),
(2, 2, 350),
(2, 3, 60),
(2, 4, 0),
(2, 5, 0),
(2, 6, 0),
(2, 7, 0),
(2, 8, 0),
(2, 9, 0),
(2, 10, 0),
(3, 1, 250),
(3, 2, 275),
(3, 3, 80),
(3, 4, 0),
(3, 5, 0),
(3, 6, 0),
(3, 7, 0),
(3, 8, 0),
(3, 9, 1),
(3, 10, 0),
(4, 1, 250),
(4, 2, 325),
(4, 3, 170),
(4, 4, 15),
(4, 5, 5),
(4, 6, 5),
(4, 7, 1),
(4, 8, 0),
(4, 9, 0),
(4, 10, 0),
(5, 1, 355),
(5, 2, 375),
(5, 3, 300),
(5, 4, 10),
(5, 5, 5),
(5, 6, 15),
(5, 7, 1),
(5, 8, 0),
(5, 9, 1),
(5, 10, 0),
(6, 1, 250),
(6, 2, 400),
(6, 3, 225),
(6, 4, 0),
(6, 5, 10),
(6, 6, 15),
(6, 7, 1),
(6, 8, 1),
(6, 9, 0),
(6, 10, 0),
(7, 1, 250),
(7, 2, 300),
(7, 3, 120),
(7, 4, 0),
(7, 5, 0),
(7, 6, 0),
(7, 7, 1),
(7, 8, 0),
(7, 9, 0),
(7, 10, 1),
(8, 1, 250),
(8, 2, 250),
(8, 3, 235),
(8, 4, 0),
(8, 5, 0),
(8, 6, 0),
(8, 7, 1),
(8, 8, 0),
(8, 9, 0),
(8, 10, 1),
(9, 1, 375),
(9, 2, 250),
(9, 3, 300),
(9, 4, 15),
(9, 5, 5),
(9, 6, 25),
(9, 7, 1),
(9, 8, 0),
(9, 9, 0),
(9, 10, 1),
(10, 1, 300),
(10, 2, 300),
(10, 3, 250),
(10, 4, 0),
(10, 5, 0),
(10, 6, 0),
(10, 7, 1),
(10, 8, 0),
(10, 9, 0),
(10, 10, 0),
(11, 1, 400),
(11, 2, 800),
(11, 3, 400),
(11, 4, 0),
(11, 5, 5),
(11, 6, 15),
(11, 7, 1),
(11, 8, 0),
(11, 9, 0),
(11, 10, 0),
(12, 1, 500),
(12, 2, 1000),
(12, 3, 425),
(12, 4, 0),
(12, 5, 5),
(12, 6, 50),
(12, 7, 1),
(12, 8, 0),
(12, 9, 0),
(12, 10, 0),
(13, 1, 150),
(13, 2, 75),
(13, 3, 30),
(13, 4, 0),
(13, 5, 75),
(13, 6, 0),
(13, 7, 1),
(13, 8, 0),
(13, 9, 0),
(13, 10, 0),
(14, 1, 300),
(14, 2, 250),
(14, 3, 30),
(14, 4, 0),
(14, 5, 0),
(14, 6, 0),
(14, 7, 0),
(14, 8, 0),
(14, 9, 0),
(14, 10, 0),
(15, 1, 350),
(15, 2, 275),
(15, 3, 75),
(15, 4, 20),
(15, 5, 0),
(15, 6, 10),
(15, 7, 0),
(15, 8, 0),
(15, 9, 0),
(15, 10, 0),
(16, 1, 400),
(16, 2, 300),
(16, 3, 115),
(16, 4, 30),
(16, 5, 0),
(16, 6, 20),
(16, 7, 1),
(16, 8, 0),
(16, 9, 0),
(16, 10, 0),
(17, 1, 525),
(17, 2, 500),
(17, 3, 0),
(17, 4, 75),
(17, 5, 25),
(17, 6, 100),
(17, 7, 1),
(17, 8, 0),
(17, 9, 0),
(17, 10, 0),
(18, 1, 300),
(18, 2, 275),
(18, 3, 75),
(18, 4, 15),
(18, 5, 5),
(18, 6, 10),
(18, 7, 1),
(18, 8, 1),
(18, 9, 0),
(18, 10, 0),
(19, 1, 325),
(19, 2, 300),
(19, 3, 95),
(19, 4, 15),
(19, 5, 15),
(19, 6, 20),
(19, 7, 1),
(19, 8, 0),
(19, 9, 1),
(19, 10, 0),
(20, 1, 500),
(20, 2, 400),
(20, 3, 60),
(20, 4, 0),
(20, 5, 30),
(20, 6, 0),
(20, 7, 1),
(20, 8, 0),
(20, 9, 0),
(20, 10, 1),
(21, 1, 400),
(21, 2, 400),
(21, 3, 90),
(21, 4, 60),
(21, 5, 20),
(21, 6, 20),
(21, 7, 1),
(21, 8, 0),
(21, 9, 0),
(21, 10, 1),
(22, 1, 625),
(22, 2, 575),
(22, 3, 120),
(22, 4, 120),
(22, 5, 15),
(22, 6, 0),
(22, 7, 1),
(22, 8, 0),
(22, 9, 0),
(22, 10, 1),
(23, 1, 250),
(23, 2, 300),
(23, 3, 160),
(23, 4, 30),
(23, 5, 0),
(23, 6, 5),
(23, 7, 1),
(23, 8, 1),
(23, 9, 0),
(23, 10, 0),
(24, 1, 300),
(24, 2, 300),
(24, 3, 120),
(24, 4, 30),
(24, 5, 20),
(24, 6, 25),
(24, 7, 1),
(24, 8, 1),
(24, 9, 0),
(24, 10, 0),
(25, 1, 700),
(25, 2, 400),
(25, 3, 100),
(25, 4, 75),
(25, 5, 30),
(25, 6, 75),
(25, 7, 1),
(25, 8, 1),
(25, 9, 0),
(25, 10, 0),
(26, 1, 375),
(26, 2, 50),
(26, 3, 75),
(26, 4, 125),
(26, 5, 10),
(26, 6, 25),
(26, 7, 0),
(26, 8, 0),
(26, 9, 0),
(26, 10, 0),
(27, 1, 450),
(27, 2, 100),
(27, 3, 100),
(27, 4, 200),
(27, 5, 20),
(27, 6, 50),
(27, 7, 1),
(27, 8, 0),
(27, 9, 0),
(27, 10, 0),
(28, 1, 500),
(28, 2, 500),
(28, 3, 170),
(28, 4, 15),
(28, 5, 5),
(28, 6, 5),
(28, 7, 1),
(28, 8, 0),
(28, 9, 0),
(28, 10, 0),
(29, 1, 150),
(29, 2, 150),
(29, 3, 150),
(29, 4, 0),
(29, 5, 0),
(29, 6, 0),
(29, 7, 0),
(29, 8, 0),
(29, 9, 0),
(29, 10, 0),
(30, 1, 400),
(30, 2, 550),
(30, 3, 235),
(30, 4, 0),
(30, 5, 0),
(30, 6, 0),
(30, 7, 1),
(30, 8, 0),
(30, 9, 1),
(30, 10, 0),
(31, 1, 350),
(31, 2, 275),
(31, 3, 200),
(31, 4, 30),
(31, 5, 10),
(31, 6, 15),
(31, 7, 1),
(31, 8, 1),
(31, 9, 0),
(31, 10, 0),
(32, 1, 500),
(32, 2, 400),
(32, 3, 300),
(32, 4, 50),
(32, 5, 20),
(32, 6, 25),
(32, 7, 1),
(32, 8, 0),
(32, 9, 0),
(32, 10, 1),
(33, 1, 675),
(33, 2, 525),
(33, 3, 400),
(33, 4, 100),
(33, 5, 25),
(33, 6, 100),
(33, 7, 1),
(33, 8, 0),
(33, 9, 0),
(33, 10, 0),
(34, 1, 150),
(34, 2, 225),
(34, 3, 60),
(34, 4, 0),
(34, 5, 0),
(34, 6, 0),
(34, 7, 0),
(34, 8, 0),
(34, 9, 0),
(34, 10, 0),
(35, 1, 50),
(35, 2, 750),
(35, 3, 255),
(35, 4, 0),
(35, 5, 25),
(35, 6, 100),
(35, 7, 1),
(35, 8, 0),
(35, 9, 0),
(35, 10, 0),
(36, 1, 100),
(36, 2, 700),
(36, 3, 30),
(36, 4, 0),
(36, 5, 20),
(36, 6, 10),
(36, 7, 1),
(36, 8, 0),
(36, 9, 0),
(36, 10, 0),
(37, 1, 150),
(37, 2, 1150),
(37, 3, 75),
(37, 4, 0),
(37, 5, 25),
(37, 6, 25),
(37, 7, 1),
(37, 8, 0),
(37, 9, 0),
(37, 10, 0),
(38, 1, 300),
(38, 2, 1800),
(38, 3, 45),
(38, 4, 0),
(38, 5, 0),
(38, 6, 0),
(38, 7, 1),
(38, 8, 0),
(38, 9, 0),
(38, 10, 0),
(39, 1, 150),
(39, 2, 175),
(39, 3, 60),
(39, 4, 10),
(39, 5, 0),
(39, 6, 0),
(39, 7, 0),
(39, 8, 0),
(39, 9, 0),
(39, 10, 0),
(40, 1, 500),
(40, 2, 450),
(40, 3, 235),
(40, 4, 0),
(40, 5, 10),
(40, 6, 0),
(40, 7, 1),
(40, 8, 0),
(40, 9, 0),
(40, 10, 1),
(41, 1, 300),
(41, 2, 250),
(41, 3, 190),
(41, 4, 10),
(41, 5, 10),
(41, 6, 5),
(41, 7, 0),
(41, 8, 0),
(41, 9, 0),
(41, 10, 1),
(42, 1, 575),
(42, 2, 600),
(42, 3, 120),
(42, 4, 40),
(42, 5, 50),
(42, 6, 150),
(42, 7, 1),
(42, 8, 0),
(42, 9, 0),
(42, 10, 1),
(43, 1, 750),
(43, 2, 750),
(43, 3, 90),
(43, 4, 100),
(43, 5, 0),
(43, 6, 0),
(43, 7, 1),
(43, 8, 0),
(43, 9, 0),
(43, 10, 1),
(44, 1, 150),
(44, 2, 175),
(44, 3, 60),
(44, 4, 20),
(44, 5, 0),
(44, 6, 0),
(44, 7, 0),
(44, 8, 0),
(44, 9, 0),
(44, 10, 0),
(45, 1, 375),
(45, 2, 75),
(45, 3, 30),
(45, 4, 0),
(45, 5, 50),
(45, 6, 10),
(45, 7, 1),
(45, 8, 0),
(45, 9, 0),
(45, 10, 1),
(46, 1, 575),
(46, 2, 100),
(46, 3, 250),
(46, 4, 100),
(46, 5, 10),
(46, 6, 25),
(46, 7, 0),
(46, 8, 0),
(46, 9, 1),
(46, 10, 0),
(47, 1, 50),
(47, 2, 50),
(47, 3, 175),
(47, 4, 350),
(47, 5, 20),
(47, 6, 10),
(47, 7, 1),
(47, 8, 0),
(47, 9, 0),
(47, 10, 0),
(48, 1, 700),
(48, 2, 100),
(48, 3, 150),
(48, 4, 275),
(48, 5, 20),
(48, 6, 50),
(48, 7, 1),
(48, 8, 0),
(48, 9, 0),
(48, 10, 0),
(49, 1, 1000),
(49, 2, 50),
(49, 3, 120),
(49, 4, 400),
(49, 5, 0),
(49, 6, 50),
(49, 7, 1),
(49, 8, 0),
(49, 9, 0),
(49, 10, 0),
(50, 1, 150),
(50, 2, 200),
(50, 3, 60),
(50, 4, 0),
(50, 5, 0),
(50, 6, 0),
(50, 7, 1),
(50, 8, 0),
(50, 9, 0),
(50, 10, 0),
(51, 1, 400),
(51, 2, 525),
(51, 3, 260),
(51, 4, 10),
(51, 5, 0),
(51, 6, 10),
(51, 7, 1),
(51, 8, 0),
(51, 9, 1),
(51, 10, 0),
(52, 1, 75),
(52, 2, 125),
(52, 3, 40),
(52, 4, 0),
(52, 5, 250),
(52, 6, 0),
(52, 7, 1),
(52, 8, 0),
(52, 9, 1),
(52, 10, 0),
(53, 1, 415),
(53, 2, 375),
(53, 3, 30),
(53, 4, 0),
(53, 5, 25),
(53, 6, 10),
(53, 7, 1),
(53, 8, 0),
(53, 9, 1),
(53, 10, 0),
(54, 1, 675),
(54, 2, 725),
(54, 3, 30),
(54, 4, 0),
(54, 5, 25),
(54, 6, 50),
(54, 7, 1),
(54, 8, 0),
(54, 9, 1),
(54, 10, 0),
(55, 1, 1050),
(55, 2, 950),
(55, 3, 30),
(55, 4, 0),
(55, 5, 0),
(55, 6, 25),
(55, 7, 1),
(55, 8, 0),
(55, 9, 1),
(55, 10, 0),
(56, 1, 150),
(56, 2, 150),
(56, 3, 60),
(56, 4, 0),
(56, 5, 0),
(56, 6, 0),
(56, 7, 0),
(56, 8, 0),
(56, 9, 0),
(56, 10, 0),
(57, 1, 375),
(57, 2, 475),
(57, 3, 160),
(57, 4, 0),
(57, 5, 0),
(57, 6, 0),
(57, 7, 1),
(57, 8, 0),
(57, 9, 0),
(57, 10, 0),
(58, 1, 225),
(58, 2, 200),
(58, 3, 0),
(58, 4, 0),
(58, 5, 100),
(58, 6, 0),
(58, 7, 1),
(58, 8, 0),
(58, 9, 0),
(58, 10, 0),
(59, 1, 250),
(59, 2, 250),
(59, 3, 70),
(59, 4, 0),
(59, 5, 50),
(59, 6, 75),
(59, 7, 1),
(59, 8, 0),
(59, 9, 0),
(59, 10, 0),
(60, 1, 525),
(60, 2, 635),
(60, 3, 30),
(60, 4, 0),
(60, 5, 50),
(60, 6, 100),
(60, 7, 1),
(60, 8, 0),
(60, 9, 0),
(60, 10, 0),
(61, 1, 950),
(61, 2, 950),
(61, 3, 30),
(61, 4, 0),
(61, 5, 0),
(61, 6, 0),
(61, 7, 1),
(61, 8, 0),
(61, 9, 0),
(61, 10, 0),
(62, 1, 150),
(62, 2, 150),
(62, 3, 60),
(62, 4, 0),
(62, 5, 5),
(62, 6, 10),
(62, 7, 0),
(62, 8, 0),
(62, 9, 0),
(62, 10, 0),
(63, 1, 200),
(63, 2, 150),
(63, 3, 130),
(63, 4, 0),
(63, 5, 10),
(63, 6, 0),
(63, 7, 1),
(63, 8, 0),
(63, 9, 0),
(63, 10, 0),
(64, 1, 300),
(64, 2, 250),
(64, 3, 90),
(64, 4, 10),
(64, 5, 20),
(64, 6, 10),
(64, 7, 1),
(64, 8, 1),
(64, 9, 0),
(64, 10, 0),
(65, 1, 425),
(65, 2, 550),
(65, 3, 120),
(65, 4, 50),
(65, 5, 50),
(65, 6, 50),
(65, 7, 1),
(65, 8, 1),
(65, 9, 0),
(65, 10, 0),
(66, 1, 550),
(66, 2, 500),
(66, 3, 200),
(66, 4, 50),
(66, 5, 10),
(66, 6, 20),
(66, 7, 1),
(66, 8, 1),
(66, 9, 0),
(66, 10, 0),
(67, 1, 825),
(67, 2, 825),
(67, 3, 60),
(67, 4, 50),
(67, 5, 0),
(67, 6, 30),
(67, 7, 1),
(67, 8, 1),
(67, 9, 0),
(67, 10, 0),
(68, 1, 50000),
(68, 2, 21000),
(68, 3, 1000),
(68, 4, 21000),
(68, 5, 1000),
(68, 6, 1000),
(68, 7, 1),
(68, 8, 1),
(68, 9, 1),
(68, 10, 1),
(69, 1, 50),
(69, 2, 50),
(69, 3, 5),
(69, 4, 0),
(69, 5, 0),
(69, 6, 0),
(69, 7, 0),
(69, 8, 0),
(69, 9, 0),
(69, 10, 0),
(70, 1, 100),
(70, 2, 100),
(70, 3, 45),
(70, 4, 0),
(70, 5, 0),
(70, 6, 0),
(70, 7, 0),
(70, 8, 0),
(70, 9, 0),
(70, 10, 0),
(71, 1, 125),
(71, 2, 175),
(71, 3, 80),
(71, 4, 0),
(71, 5, 10),
(71, 6, 5),
(71, 7, 1),
(71, 8, 0),
(71, 9, 0),
(71, 10, 0),
(72, 1, 300),
(72, 2, 300),
(72, 3, 200),
(72, 4, 0),
(72, 5, 5),
(72, 6, 0),
(72, 7, 1),
(72, 8, 0),
(72, 9, 0),
(72, 10, 0),
(73, 1, 215),
(73, 2, 215),
(73, 3, 70),
(73, 4, 50),
(73, 5, 25),
(73, 6, 10),
(73, 7, 1),
(73, 8, 0),
(73, 9, 0),
(73, 10, 0),
(74, 1, 425),
(74, 2, 400),
(74, 3, 50),
(74, 4, 25),
(74, 5, 50),
(74, 6, 75),
(74, 7, 1),
(74, 8, 0),
(74, 9, 0),
(74, 10, 0),
(75, 1, 875),
(75, 2, 700),
(75, 3, 90),
(75, 4, 0),
(75, 5, 0),
(75, 6, 0),
(75, 7, 1),
(75, 8, 0),
(75, 9, 0),
(75, 10, 0),
(71, 11, 1),
(72, 11, 1),
(73, 11, 1),
(74, 11, 1),
(75, 11, 1),
(1, 11, 0),
(2, 11, 0),
(3, 11, 0),
(4, 11, 0),
(5, 11, 0),
(6, 11, 0),
(7, 11, 0),
(8, 11, 0),
(9, 11, 0),
(10, 11, 0),
(11, 11, 0),
(12, 11, 0),
(13, 11, 0),
(14, 11, 0),
(15, 11, 0),
(16, 11, 0),
(17, 11, 0),
(18, 11, 0),
(19, 11, 0),
(20, 11, 0),
(21, 11, 0),
(22, 11, 0),
(23, 11, 0),
(24, 11, 0),
(25, 11, 0),
(26, 11, 0),
(27, 11, 0),
(28, 11, 0),
(29, 11, 0),
(30, 11, 0),
(31, 11, 0),
(32, 11, 0),
(33, 11, 1),
(34, 11, 0),
(35, 11, 0),
(36, 11, 0),
(37, 11, 0),
(38, 11, 0),
(39, 11, 0),
(40, 11, 0),
(41, 11, 0),
(42, 11, 0),
(43, 11, 0),
(44, 11, 0),
(45, 11, 0),
(46, 11, 0),
(47, 11, 0),
(48, 11, 0),
(49, 11, 0),
(50, 11, 0),
(51, 11, 0),
(52, 11, 0),
(53, 11, 0),
(54, 11, 0),
(55, 11, 0),
(56, 11, 0),
(57, 11, 0),
(58, 11, 0),
(59, 11, 0),
(60, 11, 0),
(61, 11, 0),
(62, 11, 0),
(63, 11, 0),
(64, 11, 0),
(65, 11, 0),
(66, 11, 0),
(67, 11, 0),
(68, 11, 0),
(69, 11, 0),
(70, 11, 0),
(100, 1, 1000),
(100, 2, 1000),
(100, 3, 60),
(100, 4, 1000),
(100, 5, 0),
(100, 6, 0),
(100, 7, 0),
(100, 8, 0),
(100, 9, 0),
(100, 10, 0),
(100, 11, 0),
(666, 1, 6666),
(666, 2, 6666),
(666, 3, 666),
(666, 4, 666),
(666, 5, 1000),
(666, 6, 1000),
(666, 7, 1),
(666, 8, 1),
(666, 9, 1),
(666, 10, 1),
(666, 11, 1);

INSERT INTO `user_rankings` (`rank`, `rank_name`) VALUES
(1, 'Newbie'),
(2, 'Beginner'),
(3, 'Fledgling'),
(4, 'Average'),
(5, 'Adept'),
(6, 'Expert'),
(7, 'Elite'),
(8, 'Master');

INSERT INTO `weapon_type` (`weapon_type_id`, `weapon_name`, `race_id`, `cost`, `shield_damage`, `armour_damage`, `accuracy`, `power_level`, `buyer_restriction`) VALUES
(1, 'Newbie Pulse Laser', 1, 0, 40, 40, 65, 3, 0),
(2, 'Nuke', 1, 352500, 0, 300, 35, 5, 2),
(3, 'Holy Hand Grenade', 1, 200350, 300, 0, 35, 5, 1),
(4, 'Thevian Rail Gun', 7, 35250, 0, 45, 54, 1, 0),
(5, 'Thevian Assault Laser', 7, 86750, 20, 90, 51, 3, 0),
(6, 'Thevian Flux Missile', 7, 137500, 40, 40, 54, 2, 0),
(7, 'Thevian Shield Disperser', 7, 94000, 175, 0, 48, 4, 0),
(8, 'Salvene Flux Resonator', 6, 135000, 75, 0, 58, 2, 0),
(9, 'Salvene EM Flux Cannon', 6, 187500, 100, 50, 44, 3, 0),
(10, 'Salvene Frag Missile', 6, 187500, 0, 75, 54, 2, 0),
(11, 'Salvene Chain Laser', 6, 91250, 70, 40, 54, 3, 0),
(12, 'Ik-Thorne Burst Laser System', 5, 140000, 50, 50, 54, 3, 0),
(13, 'Ik-Thorne Rapid Fire Cannon', 5, 137500, 0, 50, 58, 1, 0),
(14, 'Ik-Thorne Cluster Missile', 5, 87500, 0, 80, 51, 2, 0),
(15, 'Ik-Thorne Accoustic Jammer', 5, 92000, 50, 0, 62, 2, 0),
(16, 'WQ Human Flechette Cannon', 8, 90000, 0, 50, 78, 2, 0),
(17, 'WQ Human Shield Vaporizer', 8, 185000, 200, 0, 38, 4, 0),
(18, 'Human Harmonic Disruptor', 4, 98000, 100, 0, 80, 4, 0),
(19, 'Human Space Shotgun', 4, 195000, 0, 50, 82, 2, 0),
(20, 'WQ Human Multi-Phase Laser', 8, 47500, 60, 40, 54, 3, 0),
(21, 'Human Multi-Phase Laser', 4, 78000, 40, 60, 54, 3, 0),
(22, 'Photon Torpedo', 1, 78000, 0, 150, 40, 3, 0),
(23, 'Human Photon Torpedo', 4, 142500, 0, 150, 44, 3, 0),
(24, 'Creonti Particle Cannon', 3, 142500, 30, 30, 72, 2, 0),
(25, 'Little Junior Torpedo', 1, 90500, 0, 110, 74, 4, 0),
(26, 'Creonti Mole Missile', 3, 156500, 0, 85, 68, 3, 0),
(27, 'Creonti Shield Sucker', 3, 132750, 85, 0, 68, 3, 0),
(28, 'Alskant Focused Laser', 2, 96000, 50, 50, 80, 4, 0),
(29, 'Alskant Pulse-Fist Missile', 2, 167500, 25, 60, 54, 2, 0),
(30, 'Alskant Space Flechette', 2, 104000, 0, 90, 62, 3, 0),
(31, 'Alskant Anti-Shield System', 2, 113500, 65, 0, 62, 2, 0),
(32, 'Anti-Ship Missile (Guided)', 1, 93000, 0, 50, 72, 2, 0),
(33, 'Anti-Ship Missile (Heat-Seeking)', 1, 87500, 0, 50, 58, 1, 0),
(34, 'Anti-Ship Missile', 1, 87500, 0, 50, 48, 1, 0),
(35, 'Huge Pulse Laser', 1, 189750, 85, 85, 51, 4, 0),
(36, 'Large Pulse Laser', 1, 189750, 60, 60, 58, 3, 0),
(37, 'Pulse Laser', 1, 111000, 40, 40, 65, 3, 0),
(38, 'Projectile Cannon Lvl 4', 1, 94000, 0, 75, 58, 2, 0),
(39, 'Projectile Cannon Lvl 3', 1, 71250, 0, 50, 65, 2, 0),
(40, 'Projectile Cannon Lvl 2', 1, 67500, 0, 35, 68, 1, 0),
(41, 'Projectile Cannon Lvl 1', 1, 55250, 0, 20, 72, 1, 0),
(42, 'Advanced Shield Disruptor', 1, 72000, 60, 0, 64, 2, 0),
(43, 'Shield Disruptor', 1, 46000, 30, 0, 68, 1, 0),
(44, 'Insanely Large Laser', 1, 81000, 60, 60, 54, 3, 0),
(45, 'Large Laser', 1, 54000, 40, 40, 62, 2, 0),
(46, 'Laser', 1, 88750, 25, 25, 68, 2, 0),
(47, 'Small Laser', 1, 43500, 10, 10, 72, 1, 0),
(48, 'Creonti "Big Daddy"', 3, 251000, 0, 250, 34, 4, 0),
(49, 'Big Momma Torpedo Launcher', 1, 153000, 0, 200, 40, 4, 0),
(50, 'Torpedo Launcher', 1, 55000, 0, 100, 40, 2, 0),
(51, 'Nijarin Ion Pulse Phaser', 9, 85000, 35, 5, 58, 1, 0),
(52, 'Nijarin Ion Disrupter', 9, 100000, 80, 20, 50, 2, 0),
(53, 'Nijarin Ion Phaser Beam', 9, 180000, 130, 20, 44, 3, 0),
(54, 'Nijarin Claymore Missile', 9, 200000, 0, 170, 51, 4, 0),
(55, 'Planetary Pulse Laser', 1, 325500, 150, 150, 34, 5, 0),
(10001, 'Planet Turret', 1, 0, 250, 250, 25, 0, 0),
(10000, 'Port Turret', 1, 0, 250, 250, 10, 0, 0),
(666, 'Hell Blaster', 1, 666, 666, 666, 66, 0, 2);
