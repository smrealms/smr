<?php

function channel_msg_op($fp, $rdata)
{

	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s(.*)\s:!op(\s*help)?\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];

		echo_r('[OP] by ' . $nick . ' in ' . $channel);

		fputs($fp, 'PRIVMSG ' . $channel . ' :The !op command can be used to manage an upcoming op.' . EOL);
		fputs($fp, 'PRIVMSG ' . $channel . ' :The following sub commands are available:' . EOL);
		fputs($fp, 'PRIVMSG ' . $channel . ' :  !op info         Displays the time left until next op' . EOL);
		fputs($fp, 'PRIVMSG ' . $channel . ' :  !op list         Displays a list of players who have signed up' . EOL);
		fputs($fp, 'PRIVMSG ' . $channel . ' :  !op yes/no/maybe Sign you up for the upcoming OP' . EOL);
		fputs($fp, 'PRIVMSG ' . $channel . ' :  !op set <time>   The leader can set up an OP. <time> has to be a unix timestamp. Use http://www.epochconverter.com' . EOL);
		fputs($fp, 'PRIVMSG ' . $channel . ' :  !op cancel       The leader can cancel the OP' . EOL);
		fputs($fp, 'PRIVMSG ' . $channel . ' :  !op turns        The leader can get a turn count of all attendees during OP' . EOL);

		return true;

	}

	return false;

}

function channel_msg_op_info($fp, $rdata, $account, $player)
{

	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s(.*)\s:!op info\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];

		echo_r('[OP_INFO] by ' . $nick . ' in ' . $channel);

		// get the op from db
		$db = new SmrMySqlDatabase();
		$db->query('SELECT time
					FROM alliance_has_op
					WHERE alliance_id = ' . $player->getAllianceID() . '
						AND game_id = ' . $player->getGameID());
		if (!$db->nextRecord()) {
			fputs($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', your leader has not scheduled an OP.' . EOL);
			return true;
		}

		// retrieve values
		$op_time = $db->getField('time');

		// check that the op is in the future
		if ($op_time < time()) {
			fputs($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', sorry. You missed the OP.' . EOL);
			return true;
		}

		// announce time left
		fputs($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', the next scheduled op will be in ' . format_time($op_time - time()) . EOL);

		// have we signed up?
		$db->query('SELECT response
					FROM alliance_has_op_response
					WHERE alliance_id = ' . $player->getAllianceID() . '
						AND game_id = ' . $player->getGameID() . '
						AND account_id = ' . $player->getAccountID());
		if ($db->nextRecord()) {
			switch($db->getField('response')) {
				case 'YES':
					// get uncached ship
					$ship =& SmrShip::getShip($player, true);
					$op_turns = ($player->getTurns() + floor(($op_time - $player->getLastTurnUpdate()) * $ship->getRealSpeed() / 3600));

					$msg = 'You are on the YES list and you will have ';

					if ($op_turns > $player->getMaxTurns())
						$msg .= 'max turns by then. If you do not move you\'ll waste ' . ($op_turns - $player->getMaxTurns()) . ' turns.';
					else
						$msg .= $op_turns . ' turns by then.';

					fputs($fp, 'PRIVMSG ' . $channel . ' :' . $msg . EOL);
				break;
				case 'NO':
					fputs($fp, 'PRIVMSG ' . $channel . ' :You are on the NO list.' . EOL);
				break;
				case 'MAYBE':
					fputs($fp, 'PRIVMSG ' . $channel . ' :You are on the MAYBE list.' . EOL);
				break;
			}
		}
		else {
			fputs($fp, 'PRIVMSG ' . $channel . ' :You have not signed up for this one.' . EOL);
		}

		return true;

	}

	return false;

}

function channel_msg_op_cancel($fp, $rdata, $account, $player)
{

	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s(.*)\s:!op cancel\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];

		echo_r('[OP_CANCEL] by ' . $nick . ' in ' . $channel);

		// check if $nick is leader
		if (!$player->isAllianceLeader(true)) {
			fputs($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', only the leader of the alliance can cancel an OP.' . EOL);
			return true;
		}

		// get the op from db
		$db = new SmrMySqlDatabase();
		$db->query('SELECT 1
					FROM alliance_has_op
					WHERE alliance_id = ' . $player->getAllianceID() . '
						AND game_id = ' . $player->getGameID());
		if (!$db->nextRecord()) {
			fputs($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', your leader has not scheduled an OP.' . EOL);
			return true;
		}

		// just get rid of op
		$db->query('DELETE FROM alliance_has_op
					WHERE alliance_id = ' . $player->getAllianceID() . '
						AND game_id = ' . $player->getGameID());
		$db->query('DELETE FROM alliance_has_op_response
					WHERE alliance_id = ' . $player->getAllianceID() . '
						AND game_id = ' . $player->getGameID());

		fputs($fp, 'PRIVMSG ' . $channel . ' :The OP has been canceled.' . EOL);
		return true;

	}

	return false;

}

function channel_msg_op_set($fp, $rdata, $account, $player)
{

	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s(.*)\s:!op set (.*)\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];
		$op_time = $msg[5];

		echo_r('[OP_SET] by ' . $nick . ' in ' . $channel);

		// check if $nick is leader
		if (!$player->isAllianceLeader(true)) {
			fputs($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', only the leader of the alliance can setup an OP.' . EOL);
			return true;
		}

		// get the op from db
		$db = new SmrMySqlDatabase();
		$db->query('SELECT time
					FROM alliance_has_op
					WHERE alliance_id = ' . $player->getAllianceID() . '
						AND  game_id = ' . $player->getGameID());
		if ($db->nextRecord()) {
			fputs($fp, 'PRIVMSG ' . $channel . ' :There is already an OP scheduled. Cancel it first!' . EOL);
			return true;
		}

		if (!is_numeric($op_time)) {
			fputs($fp, 'PRIVMSG ' . $channel . ' :The <time> needs to be a unix timestamp. See http://www.epochconverter.com for a converter.' . EOL);
			return true;
		}

		// add op to db
		$db->query('INSERT INTO alliance_has_op (alliance_id, game_id, time)
					VALUES (' . $player->getAllianceID() . ', ' . $player->getGameID() . ', ' . $db->escapeNumber($op_time) . ')');

		fputs($fp, 'PRIVMSG ' . $channel . ' :The OP has been scheduled.' . EOL);
		return true;

	}

	return false;

}

function channel_msg_op_turns($fp, $rdata, $account, $player)
{

	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s(.*)\s:!op turns\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];

		echo_r('[OP_TURNS] by ' . $nick . ' in ' . $channel);

		// check if $nick is leader
		if (!$player->isAllianceLeader(true)) {
			fputs($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', only the leader of the alliance can use this command.' . EOL);
			return true;
		}

		// get the op from db
		$db = new SmrMySqlDatabase();
		$db->query('SELECT time
					FROM alliance_has_op
					WHERE alliance_id = ' . $player->getAllianceID() . '
						AND game_id = ' . $player->getGameID());
		if (!$db->nextRecord()) {
			fputs($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', there is no op scheduled.' . EOL);
			return true;
		}

		$op_time = $db->getField('time');

		// the op needs to be running
		if ($op_time > time()) {
			fputs($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', the OP has not started yet.' . EOL);
			return true;
		}

		$oppers = array();
		$db->query('SELECT account_id
					FROM alliance_has_op_response
					WHERE alliance_id = ' . $player->getAllianceID() . '
						AND game_id = ' . $player->getGameID() . '
						AND response = \'YES\'');
		while($db->nextRecord()) {
			
			$attendeePlayer =& SmrPlayer::getPlayer($db->getInt('account_id'), $player->getGameID(), true);
			if ($attendeePlayer == null || $attendeePlayer->getAllianceID() != $player->getAllianceID())
				continue;

			$oppers[$attendeePlayer->getPlayerName()] = $attendeePlayer->getTurns();
		}

		// sort by turns
		arsort($oppers);

		// return result to channel
		foreach ($oppers as $opper => $turn) {
			fputs($fp, 'PRIVMSG ' . $channel . ' :' . $opper . ': ' . $turn . EOL);
		}

		return true;

	}

	return false;

}

function channel_msg_op_response($fp, $rdata, $account, $player) {

	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s(.*)\s:!op (yes|no|maybe)\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];
		$response = strtoupper($msg[5]);

		echo_r('[OP_' . $response . '] by ' . $nick . ' in ' . $channel);

		// get the op info from db
		$db = new SmrMySqlDatabase();
		$db->query('SELECT 1
					FROM alliance_has_op
					WHERE alliance_id = ' . $player->getAllianceID() . '
						AND game_id = ' . $player->getGameID());
		if (!$db->nextRecord()) {
			fputs($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', your leader has not scheduled an OP.' . EOL);
			return true;
		}

		$db->query('REPLACE INTO alliance_has_op_response (alliance_id, game_id, account_id, response)
					VALUES (' . $player->getAllianceID() . ', ' . $player->getGameID() . ', ' . $player->getAccountID() . ', ' . $db->escapeString($response) . ')');

		fputs($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', you have been added to the ' . $response . ' list.' . EOL);

		return true;

	}

	return false;

}

function channel_msg_op_list($fp, $rdata, $account, $player)
{

	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s(.*)\s:!op list\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];

		echo_r('[OP_LIST] by ' . $nick . ' in ' . $channel);

		// get the op info from db
		$db = new SmrMySqlDatabase();
		$db->query('SELECT 1
					FROM alliance_has_op
					WHERE alliance_id = ' . $player->getAllianceID() . '
						AND game_id = ' . $player->getGameID());
		if (!$db->nextRecord()) {
			fputs($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', your leader has not scheduled an OP.' . EOL);
			return true;
		}

		$yes = array();
		$no = array();
		$maybe = array();
		$db->query('SELECT account_id, response
					FROM alliance_has_op_response
					WHERE alliance_id = ' . $player->getAllianceID() . '
						AND game_id = ' . $player->getGameID());
		while($db->nextRecord()) {
			$respondingPlayer = SmrPlayer::getPlayer($db->getInt('account_id'), $player->getGameID());
			if(!$player->sameAlliance($respondingPlayer)) {
				continue;
			}
			switch($db->getField('response')) {
				case 'YES':
					$yes[] = $respondingPlayer;
				break;
				case 'NO':
					$no[] = $respondingPlayer;
				break;
				case 'MAYBE':
					$maybe[] = $respondingPlayer;
				break;
			}
		}

		if ((count($yes) + count($no) + count($maybe)) == 0) {
			fputs($fp, 'PRIVMSG ' . $channel . ' :Noone has signed up for the upcoming OP.' . EOL);
			return true;
		}

		if (count($yes) > 0) {
			fputs($fp, 'PRIVMSG ' . $channel . ' :YES (' . count($yes) . '):' . EOL);
			foreach ($yes as $attendee) {
				fputs($fp, 'PRIVMSG ' . $channel . ' :  * ' . $attendee->getPlayerName() . EOL);
			}
		}

		if (count($no) > 0) {
			fputs($fp, 'PRIVMSG ' . $channel . ' :NO (' . count($no) . '):' . EOL);
			foreach ($no as $attendee) {
				fputs($fp, 'PRIVMSG ' . $channel . ' :  * ' . $attendee->getPlayerName() . EOL);
			}
		}

		if (count($maybe) > 0) {
			fputs($fp, 'PRIVMSG ' . $channel . ' :MAYBE (' . count($maybe) . '):' . EOL);
			foreach ($maybe as $attendee) {
				fputs($fp, 'PRIVMSG ' . $channel . ' :  * ' . $attendee->getPlayerName() . EOL);
			}
		}

		return true;

	}

	return false;

}

function channel_op_notification($fp, $rdata, $nick, $channel) {
	echo_r('[OP_ATTENDANCE_CHECK] ' . $nick);
	if(check_for_registration($account, $player, $fp, $nick, $channel, 'channel_op_notification($fp, \'' . $rdata . '\', \'' . $nick . '\', \'' . $channel . '\');', false)) {
		return true;
	}

	$db = new SmrMySqlDatabase();
	// check if there is an upcoming op
	$db->query('SELECT 1
				FROM alliance_has_op
				WHERE alliance_id = ' . $player->getAllianceID() . '
					AND game_id = ' . $player->getGameID() . '
					AND time > ' . time());
	if (!$db->nextRecord()) {
		return true;
	}

	$db->query('SELECT 1
				FROM alliance_has_op_response
				WHERE alliance_id = ' . $player->getAllianceID() . '
					AND game_id = ' . $player->getGameID() . '
					AND account_id = ' . $player->getAccountID());
	if (!$db->nextRecord()) {
		fputs($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', your alliance leader has scheduled an OP, which you have not signed up yet. Please use the !op yes/no/maybe command to do so.' . EOL);
	}

	return true;
}
?>