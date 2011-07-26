<?php

function channel_msg_seed($fp, $rdata)
{

	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s#(.*)\s:!seed\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];

		echo_r('[SEED] by ' . $nick . ' in #' . $channel);

		$db = new SmrMySqlDatabase();

		// only registered users are allowed to use this command
		$db->query('SELECT * FROM irc_seen WHERE nick = ' . $db->escapeString($nick) . ' AND registered = 1 AND channel = ' . $db->escapeString($channel));
		if (!$db->nextRecord()) {
			error_not_registered($fp, $channel, $nick, 'channel_msg_seed($fp, \'' . $rdata . '\');');
			return true;
		}

		// check if the query is in public channel
		if ($channel == 'smr') {
			error_public_channel($fp, $channel, $nick);
			return true;
		}

		// get alliance_id and game_id for this channel
		$db->query('SELECT * FROM irc_alliance_has_channel WHERE channel = ' . $db->escapeString($channel));
		if ($db->nextRecord()) {
			$game_id = $db->getField('game_id');
			$alliance_id = $db->getField('alliance_id');
		} else {
			error_unknown_channel($fp, $channel, $nick);
			return true;
		}

		// get smr account
		$account =& SmrAccount::getAccountByIrcNick($nick, true);

		// do we have such an account?
		if ($account == null) {
			error_unknown_account($fp, $channel, $nick);
			return true;
		}

		// get smr player
		$player =& SmrPlayer::getPlayer($account->getAccountID(), $game_id, true);

		// do we have such an account?
		if ($player == null) {
			error_unknown_player($fp, $channel, $nick);
			return true;
		}

		// is the user part of this alliance?
		if ($player->getAllianceID() != $alliance_id) {
			error_unknown_alliance($fp, $channel, $nick);
			return true;
		}

		// get the seedlist from db
		$db->query('SELECT sector_id ' .
		           'FROM alliance_has_seedlist ' .
		           'WHERE alliance_id = ' . $alliance_id . ' AND ' .
		           '      game_id = ' . $game_id . ' AND ' .
		           '      sector_id NOT IN (SELECT sector_id ' .
		           '                        FROM sector_has_forces ' .
		           '                        WHERE game_id = ' . $game_id . ' AND ' .
		           '                              owner_id = ' . $account->getAccountID() .
		           '                       )');
		$missing_seeds = array();
		while ($db->nextRecord()) {
			array_push($missing_seeds, $db->getField('sector_id'));
		}

		if (count($missing_seeds) == 0) {
			fputs($fp, 'PRIVMSG #' . $channel . ' :' . $nick . ', you seeded all mandatory sectors.' . EOL);
		} else {
			$seed_list = '';
			foreach ($missing_seeds as $sector) {
				$seed_list .= $sector . ', ';
			}
			fputs($fp, 'PRIVMSG #' . $channel . ' :' . $nick . ', you are missing the following seeds:' . EOL);
			fputs($fp, 'PRIVMSG #' . $channel . ' :' . substr_replace($seed_list, ' [' . count($missing_seeds) . ' missing seed(s)]', -2, 1) . EOL);
		}

		return true;

	}

	return false;

}

function channel_msg_seedlist($fp, $rdata)
{

	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s#(.*)\s:!seedlist\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];

		echo_r('[SEEDLIST] by ' . $nick . ' in #' . $channel);

		fputs($fp, 'PRIVMSG #' . $channel . ' :The !seedlist command enables alliance leader to add or remove sectors to the seedlist' . EOL);
		fputs($fp, 'PRIVMSG #' . $channel . ' :The following sub commands are available:' . EOL);
		fputs($fp, 'PRIVMSG #' . $channel . ' :  !seedlist add <sector>        Adds <sector> to the seedlist' . EOL);
		fputs($fp, 'PRIVMSG #' . $channel . ' :  !seedlist del <sector>        Removes <sector> from seedlist' . EOL);

		return true;

	}

	return false;

}

function channel_msg_seedlist_add($fp, $rdata)
{

	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s#(.*)\s:!seedlist add (.*)\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];
		$sector = $msg[5];

		echo_r('[SEEDLIST_ADD] by ' . $nick . ' in #' . $channel);

		$db = new SmrMySqlDatabase();

		// only registered users are allowed to use this command
		$db->query('SELECT * FROM irc_seen WHERE nick = ' . $db->escapeString($nick) . ' AND registered = 1 AND channel = ' . $db->escapeString($channel));
		if (!$db->nextRecord()) {
			error_not_registered($fp, $channel, $nick, 'channel_msg_seedlist_add($fp, \'' . $rdata . '\');');
			return true;
		}

		// check if the query is in public channel
		if ($channel == 'smr') {
			error_public_channel($fp, $channel, $nick);
			return true;
		}

		// get alliance_id and game_id for this channel
		$db->query('SELECT * FROM irc_alliance_has_channel WHERE channel = ' . $db->escapeString($channel));
		if ($db->nextRecord()) {
			$game_id = $db->getField('game_id');
			$alliance_id = $db->getField('alliance_id');
		} else {
			error_unknown_channel($fp, $channel, $nick);
			return true;
		}

		// get smr account
		$account =& SmrAccount::getAccountByIrcNick($nick, true);

		// do we have such an account?
		if ($account == null) {
			error_unknown_account($fp, $channel, $nick);
			return true;
		}

		// get smr player
		$player =& SmrPlayer::getPlayer($account->getAccountID(), $game_id, true);

		// do we have such an account?
		if ($player == null) {
			error_unknown_player($fp, $channel, $nick);
			return true;
		}

		// is the user part of this alliance?
		if ($player->getAllianceID() != $alliance_id) {
			error_unknown_alliance($fp, $channel, $nick);
			return true;
		}

		// check if $nick is leader
		if ($player->getAlliance()->getLeaderID() != $player->getAccountID()) {
			fputs($fp, 'PRIVMSG #' . $channel . ' :' . $nick . ', only the leader of the alliance manages the seedlist.' . EOL);
			return true;
		}

		// see if the sector is numeric
		if (!is_numeric($sector)) {
			fputs($fp, 'PRIVMSG #' . $channel . ' :The sector needs to be numeric. Example: !seedlist add 1537' . EOL);
			return true;
		}

		// check if the sector is a part of the game
		$db->query('SELECT sector_id ' .
		           'FROM   sector ' .
		           'WHERE  game_id = ' . $game_id . ' ' .
		           '  AND  sector_id = ' . $db->escapeNumber($sector)
		);

		if (!$db->nextRecord()) {
			fputs($fp, 'PRIVMSG #' . $channel . ' :The sector ' . $sector . ' does not exist in current game.' . EOL);
			return true;
		}

		// check if the given sector is already part of the seed list
		$db->query('SELECT sector_id ' .
		           'FROM   alliance_has_seedlist ' .
		           'WHERE  alliance_id = ' . $alliance_id . ' ' .
		           '  AND  game_id = ' . $game_id . ' ' .
				   '  AND  sector_id = ' . $db->escapeNumber($sector)
		);

		if ($db->nextRecord()) {
			fputs($fp, 'PRIVMSG #' . $channel . ' :The sector ' . $sector . ' is already in the seedlist.' . EOL);
			return true;
		}

		// add sector to db
		$db->query('INSERT INTO alliance_has_seedlist ' .
		           '(alliance_id, game_id, sector_id) ' .
		           'VALUES (' . $alliance_id . ', ' . $game_id . ', ' . $db->escapeNumber($sector) . ')');

		fputs($fp, 'PRIVMSG #' . $channel . ' :The sector has been added.' . EOL);
		return true;

	}

	return false;

}

function channel_msg_seedlist_del($fp, $rdata)
{

	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s#(.*)\s:!seedlist del (.*)\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];
		$sector = $msg[5];

		echo_r('[SEEDLIST_DEL] by ' . $nick . ' in #' . $channel);

		$db = new SmrMySqlDatabase();

		// only registered users are allowed to use this command
		$db->query('SELECT * FROM irc_seen WHERE nick = ' . $db->escapeString($nick) . ' AND registered = 1 AND channel = ' . $db->escapeString($channel));
		if (!$db->nextRecord()) {
			error_not_registered($fp, $channel, $nick, 'channel_msg_seedlist_del($fp, \'' . $rdata . '\');');
			return true;
		}

		// check if the query is in public channel
		if ($channel == 'smr') {
			error_public_channel($fp, $channel, $nick);
			return true;
		}

		// get alliance_id and game_id for this channel
		$db->query('SELECT * FROM irc_alliance_has_channel WHERE channel = ' . $db->escapeString($channel));
		if ($db->nextRecord()) {
			$game_id = $db->getField('game_id');
			$alliance_id = $db->getField('alliance_id');
		} else {
			error_unknown_channel($fp, $channel, $nick);
			return true;
		}

		// get smr account
		$account =& SmrAccount::getAccountByIrcNick($nick, true);

		// do we have such an account?
		if ($account == null) {
			error_unknown_account($fp, $channel, $nick);
			return true;
		}

		// get smr player
		$player =& SmrPlayer::getPlayer($account->getAccountID(), $game_id, true);

		// do we have such an account?
		if ($player == null) {
			error_unknown_player($fp, $channel, $nick);
			return true;
		}

		// is the user part of this alliance?
		if ($player->getAllianceID() != $alliance_id) {
			error_unknown_alliance($fp, $channel, $nick);
			return true;
		}

		// check if $nick is leader
		if ($player->getAlliance()->getLeaderID() != $player->getAccountID()) {
			fputs($fp, 'PRIVMSG #' . $channel . ' :' . $nick . ', only the leader of the alliance manages the seedlist.' . EOL);
			return true;
		}

		// see if the sector is numeric
		if (!is_numeric($sector)) {
			fputs($fp, 'PRIVMSG #' . $channel . ' :The sector needs to be numeric. Example: !seedlist del 1537' . EOL);
			return true;
		}

		// check if the given sector is already part of the seed list
		$db->query('SELECT sector_id ' .
		           'FROM   alliance_has_seedlist ' .
		           'WHERE  alliance_id = ' . $alliance_id . ' ' .
		           '  AND  game_id = ' . $game_id . ' ' .
				   '  AND  sector_id = ' . $db->escapeNumber($sector)
		);

		if (!$db->nextRecord()) {
			fputs($fp, 'PRIVMSG #' . $channel . ' :The sector ' . $sector . ' is not part of the seedlist.' . EOL);
			return true;
		}

		// add sector to db
		$db->query('DELETE FROM alliance_has_seedlist ' .
		           'WHERE  alliance_id = ' . $alliance_id . ' ' .
		           '  AND  game_id = ' . $game_id . ' ' .
				   '  AND  sector_id = ' . $db->escapeNumber($sector)
		);

		fputs($fp, 'PRIVMSG #' . $channel . ' :The sector has been deleted.' . EOL);
		return true;

	}

	return false;

}

?>