<?php

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
		fputs($fp, 'PRIVMSG #' . $channel . ' :  !op info         Displays the time left until next op' . EOL);
		fputs($fp, 'PRIVMSG #' . $channel . ' :  !op list         Displays a list of players who have signed up' . EOL);
		fputs($fp, 'PRIVMSG #' . $channel . ' :  !op yes/no/maybe Sign you up for the upcoming OP' . EOL);
		fputs($fp, 'PRIVMSG #' . $channel . ' :  !op set <time>   The leader can set up an OP. <time> has to be a unix timestamp. Use http://www.epochconverter.com' . EOL);
		fputs($fp, 'PRIVMSG #' . $channel . ' :  !op cancel       The leader can cancel the OP' . EOL);
		fputs($fp, 'PRIVMSG #' . $channel . ' :  !op turns        The leader can get a turn count of all attendees during OP' . EOL);

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

		// check if the query is in public channel
		if ($channel == 'smr' || $channel == 'smr-bar') {
			error_public_channel($fp, $channel, $nick);
			return true;
		}

		$db = new SmrMySqlDatabase();

		// only registered users are allowed to use this command
		$db->query('SELECT * FROM irc_seen WHERE nick = ' . $db->escapeString($nick) . ' AND registered = 1 AND channel = ' . $db->escapeString($channel));
		if (!$db->nextRecord()) {
			error_not_registered($fp, $channel, $nick, 'channel_msg_op_info($fp, \'' . $rdata . '\');');
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

		// get the op from db
		$db->query('SELECT time, yes, no, maybe ' .
		           'FROM alliance_has_op ' .
		           'WHERE alliance_id = ' . $alliance_id . ' AND ' .
		           '      game_id = ' . $game_id);
		if (!$db->nextRecord()) {
			fputs($fp, 'PRIVMSG #' . $channel . ' :' . $nick . ', your leader has not scheduled an OP.' . EOL);
			return true;
		}

		// retrieve values
		$op_time = $db->getField('time');
		$yes = unserialize($db->getField('yes'));
		if (!is_array($yes))
			$yes = array();
		$no = unserialize($db->getField('no'));
		if (!is_array($no))
			$no = array();
		$maybe = unserialize($db->getField('maybe'));
		if (!is_array($maybe))
			$maybe = array();

		// check the that is in the future
		if ($op_time < time()) {
			fputs($fp, 'PRIVMSG #' . $channel . ' :' . $nick . ', sorry. You missed the OP.' . EOL);
			return true;
		}

		// announce time left
		fputs($fp, 'PRIVMSG #' . $channel . ' :' . $nick . ', the next scheduled op will be in ' . format_time($op_time - time()) . EOL);

		// have we signed up?
		if (array_search($nick, $yes) !== false) {

			// get uncached ship
			$ship =& SmrShip::getShip($player, true);

			$op_turns = ($player->getTurns() + floor(($op_time - $player->getLastTurnUpdate()) * $ship->getRealSpeed() / 3600));
			if ($op_turns > $player->getMaxTurns())
				$op_turns = $player->getMaxTurns();
			fputs($fp, 'PRIVMSG #' . $channel . ' :You are on the YES list and you will have ' . ($op_turns) . ' turns by then.' . EOL);

		}
		elseif (array_search($nick, $no) !== false) {
			fputs($fp, 'PRIVMSG #' . $channel . ' :You are on the NO list.' . EOL);
		}
		elseif (array_search($nick, $maybe) !== false) {
			fputs($fp, 'PRIVMSG #' . $channel . ' :You are on the MAYBE list.' . EOL);
		} else {
			fputs($fp, 'PRIVMSG #' . $channel . ' :You have not signed up for this one.' . EOL);
		}

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

		// check if the query is in public channel
		if ($channel == 'smr' || $channel == 'smr-bar') {
			error_public_channel($fp, $channel, $nick);
			return true;
		}

		$db = new SmrMySqlDatabase();

		// only registered users are allowed to use this command
		$db->query('SELECT * FROM irc_seen WHERE nick = ' . $db->escapeString($nick) . ' AND registered = 1 AND channel = ' . $db->escapeString($channel));
		if (!$db->nextRecord()) {
			error_not_registered($fp, $channel, $nick, 'channel_msg_op_cancel($fp, \'' . $rdata . '\');');
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
		if ($player->getAlliance(true)->getLeaderID() != $player->getAccountID()) {
			fputs($fp, 'PRIVMSG #' . $channel . ' :' . $nick . ', only the leader of the alliance can cancel an OP.' . EOL);
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

		// check if the query is in public channel
		if ($channel == 'smr' || $channel == 'smr-bar') {
			error_public_channel($fp, $channel, $nick);
			return true;
		}

		$db = new SmrMySqlDatabase();

		// only registered users are allowed to use this command
		$db->query('SELECT * FROM irc_seen WHERE nick = ' . $db->escapeString($nick) . ' AND registered = 1 AND channel = ' . $db->escapeString($channel));
		if (!$db->nextRecord()) {
			error_not_registered($fp, $channel, $nick, 'channel_msg_op_set($fp, \'' . $rdata . '\');');
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
		if ($player->getAlliance(true)->getLeaderID() != $player->getAccountID()) {
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

function channel_msg_op_turns($fp, $rdata)
{

	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s#(.*)\s:!op turns\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];

		echo_r('[OP_TURNS] by ' . $nick . ' in #' . $channel);

		// check if the query is in public channel
		if ($channel == 'smr' || $channel == 'smr-bar') {
			error_public_channel($fp, $channel, $nick);
			return true;
		}

		$db = new SmrMySqlDatabase();

		// only registered users are allowed to use this command
		$db->query('SELECT * FROM irc_seen WHERE nick = ' . $db->escapeString($nick) . ' AND registered = 1 AND channel = ' . $db->escapeString($channel));
		if (!$db->nextRecord()) {
			error_not_registered($fp, $channel, $nick, 'channel_msg_op_turns($fp, \'' . $rdata . '\');');
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
		if ($player->getAlliance(true)->getLeaderID() != $player->getAccountID()) {
			fputs($fp, 'PRIVMSG #' . $channel . ' :' . $nick . ', only the leader of the alliance can use this command.' . EOL);
			return true;
		}

		// get the op from db
		$db->query('SELECT time, yes ' .
		           'FROM   alliance_has_op ' .
		           'WHERE  alliance_id = ' . $alliance_id . ' ' .
		           '  AND  game_id = ' . $game_id);
		if (!$db->nextRecord()) {
			fputs($fp, 'PRIVMSG #' . $channel . ' :' . $nick . ', there is no op scheduled.' . EOL);
			return true;
		}

		$op_time = $db->getField('time');
		$yes = unserialize($db->getField('yes'));
		if (!is_array($yes))
			$yes = array();

		// the op needs to be running
		if ($op_time > time()) {
			fputs($fp, 'PRIVMSG #' . $channel . ' :' . $nick . ', the OP has not started yet.' . EOL);
			return true;
		}

		foreach ($yes as $attendee) {

			$attendeeAccount =& SmrAccount::getAccountByIrcNick($attendee, true);
			if ($attendeeAccount == null)
				continue;
			
			$attendeePlayer =& SmrPlayer::getPlayer($attendeeAccount->getAccountID(), $game_id, true);
			if ($attendeePlayer == null)
				continue;

			fputs($fp, 'PRIVMSG #' . $channel . ' :' . $attendee . ': ' . $attendeePlayer->getTurns() . EOL);
		}





		return true;

	}

	return false;

}

function channel_msg_op_yes($fp, $rdata)
{

	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s#(.*)\s:!op yes\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];

		echo_r('[OP_YES] by ' . $nick . ' in #' . $channel);

		// check if the query is in public channel
		if ($channel == 'smr' || $channel == 'smr-bar') {
			error_public_channel($fp, $channel, $nick);
			return true;
		}

		$db = new SmrMySqlDatabase();

		// only registered users are allowed to use this command
		$db->query('SELECT * FROM irc_seen WHERE nick = ' . $db->escapeString($nick) . ' AND registered = 1 AND channel = ' . $db->escapeString($channel));
		if (!$db->nextRecord()) {
			error_not_registered($fp, $channel, $nick, 'channel_msg_op_yes($fp, \'' . $rdata . '\');');
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

		// get the op info from db
		$db->query('SELECT time, yes, no, maybe ' .
		           'FROM alliance_has_op ' .
		           'WHERE alliance_id = ' . $alliance_id . ' AND ' .
		           '      game_id = ' . $game_id);
		if (!$db->nextRecord()) {
			fputs($fp, 'PRIVMSG #' . $channel . ' :' . $nick . ', your leader has not scheduled an OP.' . EOL);
			return true;
		}

		$op_time = $db->getField('time');
		$yes = unserialize($db->getField('yes'));
		if (!is_array($yes))
			$yes = array();
		$no = unserialize($db->getField('no'));
		if (!is_array($no))
			$no = array();
		$maybe = unserialize($db->getField('maybe'));
		if (!is_array($maybe))
			$maybe = array();

		// check if the OP is in the past
		if ($op_time < time()) {
			fputs($fp, 'PRIVMSG #' . $channel . ' :' . $nick . ', sorry. You missed the OP.' . EOL);
			return true;
		}

		// remove us from the no list
		if (($key = array_search($nick, $no)) !== false) {
			unset($no[$key]);
		}
		// remove us from the maybe list
		if (($key = array_search($nick, $maybe)) !== false) {
			unset($maybe[$key]);
		}

		// add nick to the list of the attendees
		if (array_search($nick, $yes) === false) {
			array_push($yes, $nick);
		}

		// save it back in the database
		$db->query('UPDATE alliance_has_op ' .
		           'SET yes   = ' . $db->escapeString(serialize($yes)) . ', ' .
		           '    no    = ' . $db->escapeString(serialize($no)) . ', ' .
		           '    maybe = ' . $db->escapeString(serialize($maybe)) . ' ' .
		           'WHERE alliance_id = ' . $alliance_id . ' AND ' .
		           '      game_id = ' . $game_id);

		fputs($fp, 'PRIVMSG #' . $channel . ' :' . $nick . ', you have been added to the YES list. Yes: ' . count($yes) . ', No: ' . count($no) . ', Maybe: ' . count($maybe) . EOL);

		return true;

	}

	return false;

}

function channel_msg_op_no($fp, $rdata)
{

	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s#(.*)\s:!op no\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];

		echo_r('[OP_NO] by ' . $nick . ' in #' . $channel);

		// check if the query is in public channel
		if ($channel == 'smr' || $channel == 'smr-bar') {
			error_public_channel($fp, $channel, $nick);
			return true;
		}

		$db = new SmrMySqlDatabase();

		// only registered users are allowed to use this command
		$db->query('SELECT * FROM irc_seen WHERE nick = ' . $db->escapeString($nick) . ' AND registered = 1 AND channel = ' . $db->escapeString($channel));
		if (!$db->nextRecord()) {
			error_not_registered($fp, $channel, $nick, 'channel_msg_op_no($fp, \'' . $rdata . '\');');
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

		// get the op info from db
		$db->query('SELECT time, yes, no, maybe ' .
		           'FROM alliance_has_op ' .
		           'WHERE alliance_id = ' . $alliance_id . ' AND ' .
		           '      game_id = ' . $game_id);
		if (!$db->nextRecord()) {
			fputs($fp, 'PRIVMSG #' . $channel . ' :' . $nick . ', your leader has not scheduled an OP.' . EOL);
			return true;
		}

		$op_time = $db->getField('time');
		$yes = unserialize($db->getField('yes'));
		if (!is_array($yes))
			$yes = array();
		$no = unserialize($db->getField('no'));
		if (!is_array($no))
			$no = array();
		$maybe = unserialize($db->getField('maybe'));
		if (!is_array($maybe))
			$maybe = array();

		// check if the OP is in the past
		if ($op_time < time()) {
			fputs($fp, 'PRIVMSG #' . $channel . ' :' . $nick . ', sorry. You missed the OP.' . EOL);
			return true;
		}

		// remove us from the yes list
		if (($key = array_search($nick, $yes)) !== false) {
			unset($yes[$key]);
		}
		// remove us from the maybe list
		if (($key = array_search($nick, $maybe)) !== false) {
			unset($maybe[$key]);
		}

		// add nick to the list of the attendees
		if (array_search($nick, $no) === false) {
			array_push($no, $nick);
		}

		// save it back in the database
		$db->query('UPDATE alliance_has_op ' .
		           'SET yes   = ' . $db->escapeString(serialize($yes)) . ', ' .
		           '    no    = ' . $db->escapeString(serialize($no)) . ', ' .
		           '    maybe = ' . $db->escapeString(serialize($maybe)) . ' ' .
		           'WHERE alliance_id = ' . $alliance_id . ' AND ' .
		           '      game_id = ' . $game_id);

		fputs($fp, 'PRIVMSG #' . $channel . ' :' . $nick . ', you have been added to the NO list. Yes: ' . count($yes) . ', No: ' . count($no) . ', Maybe: ' . count($maybe) . EOL);

		return true;

	}

	return false;

}

function channel_msg_op_maybe($fp, $rdata)
{

	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s#(.*)\s:!op maybe\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];

		echo_r('[OP_YES] by ' . $nick . ' in #' . $channel);

		// check if the query is in public channel
		if ($channel == 'smr' || $channel == 'smr-bar') {
			error_public_channel($fp, $channel, $nick);
			return true;
		}

		$db = new SmrMySqlDatabase();

		// only registered users are allowed to use this command
		$db->query('SELECT * FROM irc_seen WHERE nick = ' . $db->escapeString($nick) . ' AND registered = 1 AND channel = ' . $db->escapeString($channel));
		if (!$db->nextRecord()) {
			error_not_registered($fp, $channel, $nick, 'channel_msg_op_maybe($fp, \'' . $rdata . '\');');
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

		// get the op info from db
		$db->query('SELECT time, yes, no, maybe ' .
		           'FROM alliance_has_op ' .
		           'WHERE alliance_id = ' . $alliance_id . ' AND ' .
		           '      game_id = ' . $game_id);
		if (!$db->nextRecord()) {
			fputs($fp, 'PRIVMSG #' . $channel . ' :' . $nick . ', your leader has not scheduled an OP.' . EOL);
			return true;
		}

		$op_time = $db->getField('time');
		$yes = unserialize($db->getField('yes'));
		if (!is_array($yes))
			$yes = array();
		$no = unserialize($db->getField('no'));
		if (!is_array($no))
			$no = array();
		$maybe = unserialize($db->getField('maybe'));
		if (!is_array($maybe))
			$maybe = array();

		// check if the OP is in the past
		if ($op_time < time()) {
			fputs($fp, 'PRIVMSG #' . $channel . ' :' . $nick . ', sorry. You missed the OP.' . EOL);
			return true;
		}

		// remove us from the no list
		if (($key = array_search($nick, $no)) !== false) {
			unset($no[$key]);
		}
		// remove us from the maybe list
		if (($key = array_search($nick, $yes)) !== false) {
			unset($yes[$key]);
		}

		// add nick to the list of the attendees
		if (array_search($nick, $maybe) === false) {
			array_push($maybe, $nick);
		}

		// save it back in the database
		$db->query('UPDATE alliance_has_op ' .
		           'SET yes   = ' . $db->escapeString(serialize($yes)) . ', ' .
		           '    no    = ' . $db->escapeString(serialize($no)) . ', ' .
		           '    maybe = ' . $db->escapeString(serialize($maybe)) . ' ' .
		           'WHERE alliance_id = ' . $alliance_id . ' AND ' .
		           '      game_id = ' . $game_id);

		fputs($fp, 'PRIVMSG #' . $channel . ' :' . $nick . ', you have been added to the MAYBE list. Yes: ' . count($yes) . ', No: ' . count($no) . ', Maybe: ' . count($maybe) . EOL);

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

		// check if the query is in public channel
		if ($channel == 'smr' || $channel == 'smr-bar') {
			error_public_channel($fp, $channel, $nick);
			return true;
		}

		$db = new SmrMySqlDatabase();

		// only registered users are allowed to use this command
		$db->query('SELECT * FROM irc_seen WHERE nick = ' . $db->escapeString($nick) . ' AND registered = 1 AND channel = ' . $db->escapeString($channel));
		if (!$db->nextRecord()) {
			error_not_registered($fp, $channel, $nick, 'channel_msg_op_list($fp, \'' . $rdata . '\');');
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

		// get the op info from db
		$db->query('SELECT time, yes, no, maybe ' .
		           'FROM alliance_has_op ' .
		           'WHERE alliance_id = ' . $alliance_id . ' AND ' .
		           '      game_id = ' . $game_id);
		if (!$db->nextRecord()) {
			fputs($fp, 'PRIVMSG #' . $channel . ' :' . $nick . ', your leader has not scheduled an OP.' . EOL);
			return true;
		}

		$yes = unserialize($db->getField('yes'));
		if (!is_array($yes))
			$yes = array();
		$no = unserialize($db->getField('no'));
		if (!is_array($no))
			$no = array();
		$maybe = unserialize($db->getField('maybe'));
		if (!is_array($maybe))
			$maybe = array();

		if ((count($yes) + count($no) + count($maybe)) == 0) {
			fputs($fp, 'PRIVMSG #' . $channel . ' :Noone has signed up for the upcoming OP.' . EOL);
			return true;
		}

		if (count($yes) > 0) {
			fputs($fp, 'PRIVMSG #' . $channel . ' :YES (' . count($yes) . '):' . EOL);
			foreach ($yes as $attendee) {
				fputs($fp, 'PRIVMSG #' . $channel . ' :  * ' . $attendee . EOL);
			}
			unset($attendee);
		}

		if (count($no) > 0) {
			fputs($fp, 'PRIVMSG #' . $channel . ' :NO (' . count($no) . '):' . EOL);
			foreach ($no as $attendee) {
				fputs($fp, 'PRIVMSG #' . $channel . ' :  * ' . $attendee . EOL);
			}
			unset($attendee);
		}

		if (count($maybe) > 0) {
			fputs($fp, 'PRIVMSG #' . $channel . ' :MAYBE (' . count($maybe) . '):' . EOL);
			foreach ($maybe as $attendee) {
				fputs($fp, 'PRIVMSG #' . $channel . ' :  * ' . $attendee . EOL);
			}
			unset($attendee);
		}

		return true;

	}

	return false;

}

?>