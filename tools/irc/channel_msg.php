<?php

function error_not_registered($fp, $channel, $nick)
{
	fputs($fp, 'PRIVMSG #' . $channel . ' :' . $nick . ', to me it looks like that you do not use a registered nick. Please identify with NICKSERV and try the last command again.' . EOL);
	fputs($fp, 'WHOIS ' . $nick . EOL);
}

function error_public_channel($fp, $channel, $nick)
{
	fputs($fp, 'PRIVMSG #' . $channel . ' :' . $nick . ', that command does only work in an alliance channel.' . EOL);
}

function error_unknown_account($fp, $channel, $nick)
{
	fputs($fp, 'PRIVMSG #' . $channel . ' :' . $nick . ', you need to set your \'Hall-of-Fame\' name in SMR to your registered nick so i can recognize you.' . EOL);
}

function error_unknown_player($fp, $channel, $nick)
{
	fputs($fp, 'PRIVMSG #' . $channel . ' :' . $nick . ', you have not joined the game that this channel belongs to.' . EOL);
}

function error_unknown_alliance($fp, $channel, $nick)
{
	fputs($fp, 'KICK #' . $channel . ' ' . $nick . ' :You are not a member of this alliance!' . EOL);
}

function error_unknown_channel($fp, $channel, $nick)
{
	fputs($fp, 'PRIVMSG #' . $channel . ' :' . $nick . ', the channel #' . $channel . ' has not been registered with me.' . EOL);
}

function channel_msg_seen($fp, $rdata)
{

	// <Caretaker> MrSpock, Azool (Azool@smrealms.rulez) was last seen quitting #smr
	// 2 days 10 hours 43 minutes ago (05.10. 05:04) stating 'Some people follow their dreams,
	// others hunt them down and mercessly beat them into submission' after spending 1 hour 54 minutes there.

	// MrSpock, do I look like a mirror? ^_^

	// MrSpock, please look a bit closer at the memberlist of this channel.

	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s#(.*)\s:!seen\s(.*)\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];
		$seennick = $msg[5];

		echo_r('[SEEN] by ' . $nick . ' in #' . $channel . ' for ' . $seennick);

		// if the user asks for himself
		if ($nick == $seennick) {
			fputs($fp, 'PRIVMSG #' . $channel . ' :' . $nick . ', do I look like a mirror?' . EOL);
			return true;
		}

		$db = new SmrMySqlDatabase();

		// if user provided more than 3 letters we do a wildcard search
		if (strlen($seennick) > 3) {
			$db->query('SELECT * FROM irc_seen WHERE nick LIKE ' . $db->escapeString('%' . $seennick . '%') . ' AND channel = ' . $db->escapeString($channel) . ' ORDER BY signed_on DESC');
		} else {
			$db->query('SELECT * FROM irc_seen WHERE nick = ' . $db->escapeString($seennick) . ' AND channel = ' . $db->escapeString($channel));
		}

		// get only one result. shouldn't match more than one
		if ($db->nextRecord()) {

			$seennick = $db->getField('nick');
			$seenuser = $db->getField('user');
			$seenhost = $db->getField('host');
			$signed_on = $db->getField('signed_on');
			$signed_off = $db->getField('signed_off');

			if ($signed_off > 0) {

				$seen_id = $db->getField('seen_id');

				// remember who did the !seen command
				$db->query('UPDATE irc_seen SET ' .
				           'seen_count = seen_count + 1, ' .
				           'seen_by = ' . $db->escapeString($nick) . ' ' .
				           'WHERE seen_id = ' . $seen_id);

				fputs($fp, 'PRIVMSG #' . $channel . ' :' . $nick . ', ' . $seennick . ' (' . $seenuser . '@' . $seenhost . ') was last seen quitting ' . $channel . ' ' . format_time(time() - $signed_off) . ' ago after spending ' . format_time($signed_off - $signed_on) . ' there.' . EOL);
			} else {
				fputs($fp, 'PRIVMSG #' . $channel . ' :' . $nick . ', please look a bit closer at the memberlist of this channel.' . EOL);
			}

			return true;

		}

		fputs($fp, 'PRIVMSG #' . $channel . ' :' . $nick . ', I don\'t remember seeing ' . $seennick . '.' . EOL);
		return true;

	}

	return false;

}

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
			error_not_registered($fp, $channel, $nick);
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
		$account =& SmrAccount::getAccountByHofName($nick, true);

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
			error_not_registered($fp, $channel, $nick);
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
		$account =& SmrAccount::getAccountByHofName($nick, true);

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
			error_not_registered($fp, $channel, $nick);
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
		$account =& SmrAccount::getAccountByHofName($nick, true);

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

function channel_msg_op($fp, $rdata)
{

	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s#(.*)\s:!op\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];

		echo_r('[OP] by ' . $nick . ' in #' . $channel);

		fputs($fp, 'PRIVMSG #' . $channel . ' :The !op command can be used to manage an upcoming op.' . EOL);
		fputs($fp, 'PRIVMSG #' . $channel . ' :The following sub commands are available:' . EOL);
		fputs($fp, 'PRIVMSG #' . $channel . ' :  !op info       Displays the time left until next op' . EOL);
		fputs($fp, 'PRIVMSG #' . $channel . ' :  !op list       Displays a list of players who have signed up' . EOL);
		fputs($fp, 'PRIVMSG #' . $channel . ' :  !op signup     Sign you up for the upcoming OP' . EOL);
		fputs($fp, 'PRIVMSG #' . $channel . ' :  !op set <time> The leader can set up an OP. <time> has to be a unix timestamp. Use http://www.epochconverter.com' . EOL);
		fputs($fp, 'PRIVMSG #' . $channel . ' :  !op cancel     The leader can cancel the OP' . EOL);

		return true;

	}

	return false;

}

function channel_msg_op_info($fp, $rdata)
{

	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s#(.*)\s:!op info\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];

		echo_r('[OP_INFO] by ' . $nick . ' in #' . $channel);

		$db = new SmrMySqlDatabase();

		// only registered users are allowed to use this command
		$db->query('SELECT * FROM irc_seen WHERE nick = ' . $db->escapeString($nick) . ' AND registered = 1 AND channel = ' . $db->escapeString($channel));
		if (!$db->nextRecord()) {
			error_not_registered($fp, $channel, $nick);
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
		$account =& SmrAccount::getAccountByHofName($nick, true);

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

		// get the op from db
		$db->query('SELECT time, attendees ' .
		           'FROM alliance_has_op ' .
		           'WHERE alliance_id = ' . $alliance_id . ' AND ' .
		           '      game_id = ' . $game_id);
		if (!$db->nextRecord()) {
			fputs($fp, 'PRIVMSG #' . $channel . ' :' . $nick . ', your leader has not scheduled an OP.' . EOL);
			return true;
		}

		// retrieve values
		$op_time = $db->getField('time');
		$attendees = unserialize($db->getField('attendees'));
		if (!is_array($attendees))
			$attendees = array();

		// check the that is in the future
		if ($op_time < time()) {
			fputs($fp, 'PRIVMSG #' . $channel . ' :' . $nick . ', sorry. You missed the OP.' . EOL);
			return true;
		}

		// announce time left
		fputs($fp, 'PRIVMSG #' . $channel . ' :' . $nick . ', the next scheduled op will be in ' . format_time($op_time - time()) . EOL);

		// have we signed up?
		if (array_search($nick, $attendees) === false) {
			fputs($fp, 'PRIVMSG #' . $channel . ' :You have NOT signed up for this one.' . EOL);
		} else {
			fputs($fp, 'PRIVMSG #' . $channel . ' :You already signed up for this one.' . EOL);
		}

		// announce players turns
		$op_turns = ($player->getTurns() + floor(($op_time - $player->getLastTurnUpdate()) * $player->getShip()->getRealSpeed() / 3600));
		if ($op_turns > $player->getMaxTurns())
			$op_turns = $player->getMaxTurns();
		fputs($fp, 'PRIVMSG #' . $channel . ' :You will have ' . ($op_turns) . ' turns by then.' . EOL);

		return true;

	}

	return false;

}

function channel_msg_op_cancel($fp, $rdata)
{

	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s#(.*)\s:!op cancel\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];

		echo_r('[OP_CANCEL] by ' . $nick . ' in #' . $channel);

		$db = new SmrMySqlDatabase();

		// only registered users are allowed to use this command
		$db->query('SELECT * FROM irc_seen WHERE nick = ' . $db->escapeString($nick) . ' AND registered = 1 AND channel = ' . $db->escapeString($channel));
		if (!$db->nextRecord()) {
			error_not_registered($fp, $channel, $nick);
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
		$account =& SmrAccount::getAccountByHofName($nick, true);

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

		// get the op from db
		$db->query('SELECT time ' .
		           'FROM alliance_has_op ' .
		           'WHERE alliance_id = ' . $alliance_id . ' AND ' .
		           '      game_id = ' . $game_id);
		if (!$db->nextRecord()) {
			fputs($fp, 'PRIVMSG #' . $channel . ' :' . $nick . ', your leader has not scheduled an OP.' . EOL);
			return true;
		}

		// check if $nick is leader
		if ($player->getAlliance()->getLeaderID() != $player->getAccountID()) {
			fputs($fp, 'PRIVMSG #' . $channel . ' :' . $nick . ', only the leader of the alliance can cancel an OP.' . EOL);
			return true;
		}

		// just get rid of op
		$db->query('DELETE FROM alliance_has_op ' .
		           'WHERE alliance_id = ' . $alliance_id . ' AND ' .
		           '      game_id = ' . $game_id);

		fputs($fp, 'PRIVMSG #' . $channel . ' :The OP has been canceled.' . EOL);
		return true;

	}

	return false;

}

function channel_msg_op_set($fp, $rdata)
{

	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s#(.*)\s:!op set (.*)\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];
		$op_time = $msg[5];

		echo_r('[OP_SET] by ' . $nick . ' in #' . $channel);

		$db = new SmrMySqlDatabase();

		// only registered users are allowed to use this command
		$db->query('SELECT * FROM irc_seen WHERE nick = ' . $db->escapeString($nick) . ' AND registered = 1 AND channel = ' . $db->escapeString($channel));
		if (!$db->nextRecord()) {
			error_not_registered($fp, $channel, $nick);
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
		$account =& SmrAccount::getAccountByHofName($nick, true);

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
			fputs($fp, 'PRIVMSG #' . $channel . ' :' . $nick . ', only the leader of the alliance can setup an OP.' . EOL);
			return true;
		}

		// get the op from db
		$db->query('SELECT time ' .
		           'FROM   alliance_has_op ' .
		           'WHERE  alliance_id = ' . $alliance_id . ' ' .
		           '  AND  game_id = ' . $game_id);
		if ($db->nextRecord()) {
			fputs($fp, 'PRIVMSG #' . $channel . ' :There is already an OP scheduled. Cancel it first!' . EOL);
			return true;
		}

		if (!is_numeric($op_time)) {
			fputs($fp, 'PRIVMSG #' . $channel . ' :The <time> needs to be a unix timestamp. See http://www.epochconverter.com for a converter.' . EOL);
			return true;
		}

		// add op to db
		$db->query('INSERT INTO alliance_has_op (alliance_id, game_id, time) ' .
		           'VALUES (' . $alliance_id . ', ' . $game_id . ', ' . $db->escapeNumber($op_time) . ')');

		fputs($fp, 'PRIVMSG #' . $channel . ' :The OP has been scheduled.' . EOL);
		return true;

	}

	return false;

}

function channel_msg_op_signup($fp, $rdata)
{

	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s#(.*)\s:!op signup\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];

		echo_r('[OP_SIGNUP] by ' . $nick . ' in #' . $channel);

		$db = new SmrMySqlDatabase();

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

		// get the op info from db
		$db->query('SELECT time, attendees ' .
		           'FROM alliance_has_op ' .
		           'WHERE alliance_id = ' . $alliance_id . ' AND ' .
		           '      game_id = ' . $game_id);
		if (!$db->nextRecord()) {
			fputs($fp, 'PRIVMSG #' . $channel . ' :' . $nick . ', your leader has not scheduled an OP.' . EOL);
			return true;
		}

		$op_time = $db->getField('time');
		$attendees = unserialize($db->getField('attendees'));
		if (!is_array($attendees))
			$attendees = array();

		// check the that is in the future
		if ($op_time < time()) {
			fputs($fp, 'PRIVMSG #' . $channel . ' :' . $nick . ', sorry. You missed the OP.' . EOL);
			return true;
		}

		// check if we are on the list
		if (!array_search($nick, $attendees)) {

			// add nick to the list of the attendees
			array_push($attendees, $nick);

			// save it back in the database
			$db->query('UPDATE alliance_has_op ' .
					   'SET attendees = ' . $db->escapeString(serialize($attendees)) . ' ' .
					   'WHERE alliance_id = ' . $alliance_id . ' AND ' .
					   '      game_id = ' . $game_id);

			fputs($fp, 'PRIVMSG #' . $channel . ' :' . $nick . ', you have been added to the list of attendees. You are number ' . count($attendees) . EOL);

		} else {
			fputs($fp, 'PRIVMSG #' . $channel . ' :' . $nick . ', you have already signed up for the OP.' . EOL);
		}

		return true;

	}

	return false;

}

function channel_msg_op_list($fp, $rdata)
{

	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s#(.*)\s:!op list\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];

		echo_r('[OP_LIST] by ' . $nick . ' in #' . $channel);

		$db = new SmrMySqlDatabase();

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

		// get the op info from db
		$db->query('SELECT time, attendees ' .
		           'FROM alliance_has_op ' .
		           'WHERE alliance_id = ' . $alliance_id . ' AND ' .
		           '      game_id = ' . $game_id);
		if (!$db->nextRecord()) {
			fputs($fp, 'PRIVMSG #' . $channel . ' :' . $nick . ', your leader has not scheduled an OP.' . EOL);
			return true;
		}

		$attendees = unserialize($db->getField('attendees'));
		if (!is_array($attendees))
			$attendees = array();

		if (count($attendees) == 0) {
			fputs($fp, 'PRIVMSG #' . $channel . ' :Noone has signed up for the upcoming OP.' . EOL);
			return true;
		}

		fputs($fp, 'PRIVMSG #' . $channel . ' :The following people have signed up:' . EOL);
		foreach($attendees as $attendee) {
			fputs($fp, 'PRIVMSG #' . $channel . ' :  * ' . $attendee . EOL);
		}
		unset($attendee);

		return true;

	}

	return false;

}

function channel_msg_money($fp, $rdata)
{

	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s#(.*)\s:!money\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];

		echo_r('[MONEY] by ' . $nick . ' in #' . $channel);

		$db = new SmrMySqlDatabase();

		// only registered users are allowed to use this command
		$db->query('SELECT * FROM irc_seen WHERE nick = ' . $db->escapeString($nick) . ' AND registered = 1 AND channel = ' . $db->escapeString($channel));
		if (!$db->nextRecord()) {
			error_not_registered($fp, $channel, $nick);
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
		$account =& SmrAccount::getAccountByHofName($nick, true);

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

		$db->query('SELECT alliance_account ' .
		           'FROM alliance ' .
		           'WHERE alliance_id = ' . $alliance_id . ' AND ' .
		           '      game_id = ' . $game_id);

		if ($db->nextRecord())
			fputs($fp, 'PRIVMSG #' . $channel . ' :The alliance has ' . number_format($db->getField('alliance_account')) . ' credits in the bank account.' . EOL);

		$db->query('SELECT sum(credits) as total_onship, sum(bank) as total_onbank ' .
		           'FROM player ' .
		           'WHERE alliance_id = ' . $alliance_id . ' AND ' .
		           '      game_id = ' . $game_id);

		if ($db->nextRecord()) {
			fputs($fp, 'PRIVMSG #' . $channel . ' :Alliance members carry a total of ' . number_format($db->getField('total_onship')) . ' credits with them' . EOL);
			fputs($fp, 'PRIVMSG #' . $channel . ' :and keep a total of ' . number_format($db->getField('total_onbank')) . ' credits in their personal bank accounts.' . EOL);
		}

		$db->query('SELECT SUM(credits) AS total_credits, SUM(bonds) AS total_bonds ' .
		           'FROM planet ' .
		           'WHERE game_id = ' . $game_id . ' AND ' .
		           '      owner_id IN (SELECT account_id ' .
		           '                   FROM player ' .
		           '                   WHERE alliance_id = ' . $alliance_id . ' AND ' .
		           '                         game_id = ' . $game_id .
		           '                   )');
		if ($db->nextRecord()) {
			fputs($fp, 'PRIVMSG #' . $channel . ' :There is a total of ' . number_format($db->getField('total_credits')) . ' credits on the planets' . EOL);
			fputs($fp, 'PRIVMSG #' . $channel . ' :and ' . number_format($db->getField('total_bonds')) . ' credits in bonds.' . EOL);
		}

		return true;

	}

	return false;

}

function channel_msg_timer($fp, $rdata)
{

	if (preg_match('/^:(.*)!(.*)@(.*) PRIVMSG #(.*) :!timer ([^ ]+) (.*)$/i', $rdata, $msg)) {

		global $events;

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];
		$countdown = $msg[5];
		$message = trim($msg[6]);

		echo_r('[TIMER] ' . $nick . ' started a timer with ' . $countdown . ' minute(s) (' . $message . ') in #' . $channel);

		array_push($events, array(time() + $countdown * 60, $message, $channel));

		fputs($fp, 'PRIVMSG #' . $channel . ' :The timer has been started and will go off in ' . $countdown . ' minute(s).' . EOL);

		return true;

	}

	return false;

}

function channel_msg_8ball($fp, $rdata, $answers)
{

	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s#(.*)\s:!8ball (.*)\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];
		$question = $msg[4];

		echo_r('[8BALL] by ' . $nick . ' in #' . $channel . '. Question: ' . $question);

		fputs($fp, 'PRIVMSG #' . $channel . ' :' . $answers[rand(0, count($answers) - 1)] . EOL);

		return true;

	}

	return false;

}

function channel_msg_help($fp, $rdata)
{

	// global help?
	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s#(.*)\s:!help\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];

		echo_r('[HELP] ' . $nick . '!' . $user . '@' . $host . ' ' . $channel);

		fputs($fp, 'NOTICE ' . $nick . ' :--- HELP ---' . EOL);
		fputs($fp, 'NOTICE ' . $nick . ' :Caretaker is the official SMR bot' . EOL);
		fputs($fp, 'NOTICE ' . $nick . ' :If you want his services in your channel please invite him using \'/invite caretaker #channel\'' . EOL);
		fputs($fp, 'NOTICE ' . $nick . ' : ' . EOL);
		fputs($fp, 'NOTICE ' . $nick . ' :Available public commands commands:' . EOL);
		//		fputs($fp, 'NOTICE '.$nick.' :!rank <nickname>         Displays the rank of the specified trader'.EOL);
		//		fputs($fp, 'NOTICE '.$nick.' :!level <rank>            Displays the experience requirement for the specified level'.EOL);
		//		fputs($fp, 'NOTICE '.$nick.' :!weapon level <level> <order>  Displays all weapons that have power level equal to <level> in the order specified (See !help weapon level)'.EOL);
		//		fputs($fp, 'NOTICE '.$nick.' :!weapon name <name>           Displays the weapon closest matching <name>'.EOL);
		//		fputs($fp, 'NOTICE '.$nick.' :!weapon range <object> <lower_limit> <upper_limit> <order>'.EOL);
		//		fputs($fp, 'NOTICE '.$nick.' :                         Displays all weapons that have <object> great than <lower_limit> and <object> less than <upper_limit> in order (see !help weapon range)'.EOL);
		fputs($fp, 'NOTICE ' . $nick . ' :  !seen <nickname>         Displays the last time <nickname> was seen' . EOL);
		fputs($fp, 'NOTICE ' . $nick . ' :  !timer <mins> <msg>      Starts a countdown which will send a notice to the channel with the <msg> in <mins> minutes' . EOL);
		fputs($fp, 'NOTICE ' . $nick . ' :  !8ball <question>        Display one of the famous 8ball answers to your <question>' . EOL);
		fputs($fp, 'NOTICE ' . $nick . ' :Available alliance commands commands:' . EOL);
		fputs($fp, 'NOTICE ' . $nick . ' :  !seedlist                Manages the seedlist' . EOL);
		fputs($fp, 'NOTICE ' . $nick . ' :  !seed                    Displays a list of sectors you have not yet seeded' . EOL);
		fputs($fp, 'NOTICE ' . $nick . ' :  !op                      Command to manage OPs' . EOL);
		fputs($fp, 'NOTICE ' . $nick . ' :  !money                   Displays the funds the alliance owns' . EOL);

		return true;

		// help on a spec command?
	} elseif (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s#(.*)\s:!help\s(.*)\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];
		$topic = $msg[5];

		echo_r('[HELP' . $topic . '] ' . $nick . '!' . $user . '@' . $host . ' ' . $channel);

		if ($topic == 'seen') {
			fputs($fp, 'NOTICE ' . $nick . ' :Syntax !seen <nickname>' . EOL);
			fputs($fp, 'NOTICE ' . $nick . ' :   Displays the last time <nickname> was seen' . EOL);
		} else
			fputs($fp, 'NOTICE ' . $nick . ' :There is no help available for this command! Try !help' . EOL);

		//		if ($topic == 'login')
		//			fputs($fp, 'NOTICE '.$nick.' :No help available yet! Ask MrSpock!'.EOL);
		//		elseif ($topic == '!rank')
		//			fputs($fp, 'NOTICE '.$nick.' :No help available yet! Ask MrSpock!'.EOL);
		//		elseif ($topic == '!level')
		//			fputs($fp, 'NOTICE '.$nick.' :No help available yet! Ask MrSpock!'.EOL);
		//		elseif ($topic == 'weapon level') {
		//
		//			fputs($fp, 'NOTICE '.$nick.' :Syntax !weapon level <level> <order>'.EOL);
		//			fputs($fp, 'NOTICE '.$nick.' :Returns all weapons that are level <level> in order <order>'.EOL);
		//			fputs($fp, 'NOTICE '.$nick.' :Example !weapon level 4 shield_damage would return the level 4 power weapons ordered by the amount of shield damage they do.'.EOL);
		//			fputs($fp, 'NOTICE '.$nick.' :<order> options are cost, shield_damage, armour_damage, buyer_restriction, race_id, accuracy, and weapon_name'.EOL);
		//			fputs($fp, 'NOTICE '.$nick.' :All "order" commands must be spelt correctly'.EOL);
		//			fputs($fp, 'NOTICE '.$nick.' :See Azool for additional help on this topic'.EOL);
		//
		//		} elseif ($topic == 'weapon range') {
		//
		//			fputs($fp, 'NOTICE '.$nick.' :Syntax !weapon range <object> <cost1> <cost2> <order>'.EOL);
		//			fputs($fp, 'NOTICE '.$nick.' :Returns all weapons that have <object> greater than <lower_limit> and less than <upper_limit> in the order <order>'.EOL);
		//			fputs($fp, 'NOTICE '.$nick.' :Example !weapon range cost_range 100000 200000 shield_damage would return all weapons whose costs are between 100000 and 200000 ordered by the amount of shield damage they do.'.EOL);
		//			fputs($fp, 'NOTICE '.$nick.' :<object> and <order> options are cost, shield_damage, armour_damage, buyer_restriction, race_id, accuracy, power_level, and weapon_name'.EOL);
		//			fputs($fp, 'NOTICE '.$nick.' :All "order" and "object" commands must be spelt correctly'.EOL);
		//			fputs($fp, 'NOTICE '.$nick.' :See Azool for additional help on this topic'.EOL);
		//
		//		} else
		//			fputs($fp, 'NOTICE '.$nick.' :There is no help available for this command! Try !help'.EOL);

		return true;

	}

	return false;

}

?>