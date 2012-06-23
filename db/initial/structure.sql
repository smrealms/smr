SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;


DELIMITER $$
CREATE DEFINER=`smr`@`localhost` PROCEDURE `remove_from_mailing`(IN p_email VARCHAR(255))
BEGIN

  DECLARE l_acc_id INT DEFAULT 0;
  DECLARE l_curr_time INT DEFAULT 0;

  SET l_curr_time = UNIX_TIMESTAMP();

  SET l_acc_id = 0;
  SELECT account_id
  FROM smr_new.account
  WHERE email = p_email
  INTO l_acc_id;

  IF l_acc_id > 0 THEN
    INSERT INTO smr_new.account_is_closed VALUES (l_acc_id, 1, 'Requested by account owner', 0);
    INSERT INTO smr_new.account_has_closing_history VALUES (l_acc_id, l_curr_time, 2, 'Closed');
  END IF;

  SET l_acc_id = 0;
  SELECT account_id
  FROM smr_classic.account
  WHERE email = p_email
  INTO l_acc_id;

  IF l_acc_id > 0 THEN
    INSERT INTO smr_classic.account_is_closed VALUES (l_acc_id, 3, 'Requested by account owner', 0);
    INSERT INTO smr_classic.account_has_closing_history VALUES (l_acc_id, l_curr_time, 2, 'Closed');
  END IF;


  SET l_acc_id = 0;
  SELECT account_id
  FROM smr_12.account
  WHERE email = p_email
  INTO l_acc_id;

  IF l_acc_id > 0 THEN
    INSERT INTO smr_12.account_is_closed VALUES (l_acc_id, 11, 'Requested by account owner', 0);
    INSERT INTO smr_12.account_has_closing_history VALUES (l_acc_id, l_curr_time, 763, 'Closed');
  END IF;

END$$

DELIMITER ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7479 ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=13 ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=129 ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=17 ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=341 ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=28 ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=384954 ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=549 ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=108 ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 PACK_KEYS=1 AUTO_INCREMENT=50 ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

CREATE TABLE IF NOT EXISTS `good` (
  `good_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `good_name` varchar(32) DEFAULT NULL,
  `base_price` int(10) unsigned DEFAULT NULL,
  `max_amount` int(10) unsigned NOT NULL DEFAULT '5000',
  `good_class` int(10) unsigned NOT NULL DEFAULT '1',
  `align_restriction` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`good_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=13 ;

CREATE TABLE IF NOT EXISTS `hardware_type` (
  `hardware_type_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `hardware_name` varchar(32) DEFAULT NULL,
  `cost` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`hardware_type_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12 ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5418 ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=51 ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2001 ;

CREATE TABLE IF NOT EXISTS `locks_queue` (
  `lock_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `game_id` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `account_id` smallint(5) unsigned NOT NULL,
  `sector_id` smallint(6) unsigned NOT NULL DEFAULT '0',
  `timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`lock_id`,`game_id`,`sector_id`),
  KEY `timestamp` (`timestamp`),
  KEY `account_id` (`account_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=61713270 ;

CREATE TABLE IF NOT EXISTS `log_has_notes` (
  `account_id` smallint(6) unsigned NOT NULL DEFAULT '0',
  `notes` text NOT NULL,
  PRIMARY KEY (`account_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `log_type` (
  `log_type_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `log_type_entry` varchar(20) NOT NULL,
  PRIMARY KEY (`log_type_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=14 ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=77 ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4433239 ;

CREATE TABLE IF NOT EXISTS `message_blacklist` (
  `entry_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `game_id` smallint(5) unsigned NOT NULL,
  `account_id` smallint(5) unsigned NOT NULL,
  `blacklisted_id` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`entry_id`),
  KEY `game_id` (`game_id`,`account_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=217 ;

CREATE TABLE IF NOT EXISTS `message_boxes` (
  `message_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `game_id` int(10) unsigned NOT NULL,
  `sender_id` int(10) unsigned NOT NULL,
  `send_time` int(10) unsigned NOT NULL,
  `box_type_id` tinyint(3) unsigned NOT NULL,
  `message_text` text NOT NULL,
  PRIMARY KEY (`message_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=28451 ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=24648 ;

CREATE TABLE IF NOT EXISTS `newsletter` (
  `newsletter_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `newsletter_html` longtext NOT NULL,
  `newsletter_text` longtext NOT NULL,
  PRIMARY KEY (`newsletter_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=29 ;
CREATE TABLE IF NOT EXISTS `newsletter_accounts` (
`account_id` bigint(20) unsigned
,`email` varchar(128)
,`first_name` varchar(50)
,`last_name` varchar(50)
);
CREATE TABLE IF NOT EXISTS `notification` (
  `notification_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `notification_type` enum('validation_code','inactive') DEFAULT NULL,
  `account_id` int(10) unsigned DEFAULT NULL,
  `time` int(10) DEFAULT NULL,
  PRIMARY KEY (`notification_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='1 - validation code.' AUTO_INCREMENT=7031 ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1285 ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=33 ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2145 ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=113 ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1003 ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;

CREATE TABLE IF NOT EXISTS `version` (
  `version_id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `major_version` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `minor_version` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `patch_level` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `went_live` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`version_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=43 ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=28 ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10006 ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=39 ;
DROP TABLE IF EXISTS `newsletter_accounts`;

CREATE ALGORITHM=UNDEFINED DEFINER=`smr`@`localhost` SQL SECURITY DEFINER VIEW `newsletter_accounts` AS select `a`.`account_id` AS `account_id`,`a`.`email` AS `email`,`a`.`first_name` AS `first_name`,`a`.`last_name` AS `last_name` from `account` `a` where ((`a`.`validated` = 'TRUE') and (`a`.`email` not in ('noone@smrealms.de','NPC@smrealms.de')) and (not(exists(select `account_is_closed`.`account_id` from `account_is_closed` where (`account_is_closed`.`account_id` = `a`.`account_id`))))) union select (100000 + `b`.`account_id`) AS `account_id`,`b`.`email` AS `email`,`b`.`first_name` AS `first_name`,`b`.`last_name` AS `last_name` from `smr_classic`.`account` `b` where ((`b`.`validated` = 'TRUE') and (`b`.`email` not in ('noone@smrealms.de','NPC@smrealms.de')) and (not(exists(select `smr_classic`.`account_is_closed`.`account_id` from `smr_classic`.`account_is_closed` where (`smr_classic`.`account_is_closed`.`account_id` = `b`.`account_id`)))) and (not(exists(select `account`.`email` from `account` where (`account`.`email` = `b`.`email`))))) union select (200000 + `c`.`account_id`) AS `account_id`,`c`.`email` AS `email`,`c`.`first_name` AS `first_name`,`c`.`last_name` AS `last_name` from `smr_12`.`account` `c` where ((`c`.`validated` = 'TRUE') and (`c`.`email` not in ('noone@smrealms.de','NPC@smrealms.de')) and (not(exists(select `smr_12`.`account_is_closed`.`account_id` from `smr_12`.`account_is_closed` where (`smr_12`.`account_is_closed`.`account_id` = `c`.`account_id`)))) and (not(exists(select `account`.`email` from `account` where (`account`.`email` = `c`.`email`)))) and (not(exists(select `smr_classic`.`account`.`email` from `smr_classic`.`account` where (`smr_classic`.`account`.`email` = `c`.`email`)))));
