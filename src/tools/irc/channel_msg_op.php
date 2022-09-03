<?php declare(strict_types=1);

use Smr\Database;

/**
 * @param resource $fp
 */
function channel_msg_op($fp, string $rdata): bool {

	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s(.*)\s:!op(\s*help)?\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];

		echo_r('[OP] by ' . $nick . ' in ' . $channel);

		fwrite($fp, 'PRIVMSG ' . $channel . ' :The !op command can be used to manage an upcoming op.' . EOL);
		fwrite($fp, 'PRIVMSG ' . $channel . ' :The following sub commands are available:' . EOL);
		fwrite($fp, 'PRIVMSG ' . $channel . ' :  !op info         Displays the time left until next op' . EOL);
		fwrite($fp, 'PRIVMSG ' . $channel . ' :  !op list         Displays a list of players who have signed up' . EOL);
		fwrite($fp, 'PRIVMSG ' . $channel . ' :  !op yes/no/maybe Sign you up for the upcoming OP' . EOL);
		fwrite($fp, 'PRIVMSG ' . $channel . ' :  !op set <time>   The leader can set up an OP. <time> has to be a unix timestamp. Use http://www.epochconverter.com' . EOL);
		fwrite($fp, 'PRIVMSG ' . $channel . ' :  !op cancel       The leader can cancel the OP' . EOL);
		fwrite($fp, 'PRIVMSG ' . $channel . ' :  !op turns        The leader can get a turn count of all attendees during OP' . EOL);

		return true;
	}

	return false;
}

/**
 * @param resource $fp
 */
function channel_msg_op_info($fp, string $rdata, AbstractSmrPlayer $player): bool {
	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s(.*)\s:!op info\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];

		echo_r('[OP_INFO] by ' . $nick . ' in ' . $channel);

		// announce signup status
		$result = shared_channel_msg_op_info($player);
		foreach ($result as $line) {
			fwrite($fp, 'PRIVMSG ' . $channel . ' :' . $line . EOL);
		}

		return true;
	}
	return false;
}

/**
 * @param resource $fp
 */
function channel_msg_op_cancel($fp, string $rdata, AbstractSmrPlayer $player): bool {

	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s(.*)\s:!op cancel\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];

		echo_r('[OP_CANCEL] by ' . $nick . ' in ' . $channel);

		// check if $nick is leader
		if (!$player->isAllianceLeader(true)) {
			fwrite($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', only the leader of the alliance can cancel an OP.' . EOL);
			return true;
		}

		// get the op from db
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT 1
					FROM alliance_has_op
					WHERE alliance_id = ' . $player->getAllianceID() . '
						AND game_id = ' . $player->getGameID());
		if (!$dbResult->hasRecord()) {
			fwrite($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', your leader has not scheduled an OP.' . EOL);
			return true;
		}

		// just get rid of op
		$db->write('DELETE FROM alliance_has_op
					WHERE alliance_id = ' . $player->getAllianceID() . '
						AND game_id = ' . $player->getGameID());
		$db->write('DELETE FROM alliance_has_op_response
					WHERE alliance_id = ' . $player->getAllianceID() . '
						AND game_id = ' . $player->getGameID());

		fwrite($fp, 'PRIVMSG ' . $channel . ' :The OP has been canceled.' . EOL);
		return true;
	}

	return false;
}

/**
 * @param resource $fp
 */
function channel_msg_op_set($fp, string $rdata, AbstractSmrPlayer $player): bool {

	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s(.*)\s:!op set (.*)\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];
		$op_time = $msg[5];

		echo_r('[OP_SET] by ' . $nick . ' in ' . $channel);

		// check if $nick is leader
		if (!$player->isAllianceLeader(true)) {
			fwrite($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', only the leader of the alliance can setup an OP.' . EOL);
			return true;
		}

		// get the op from db
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT 1
					FROM alliance_has_op
					WHERE alliance_id = ' . $player->getAllianceID() . '
						AND  game_id = ' . $player->getGameID());
		if ($dbResult->hasRecord()) {
			fwrite($fp, 'PRIVMSG ' . $channel . ' :There is already an OP scheduled. Cancel it first!' . EOL);
			return true;
		}

		if (!is_numeric($op_time)) {
			fwrite($fp, 'PRIVMSG ' . $channel . ' :The <time> needs to be a unix timestamp. See http://www.epochconverter.com for a converter.' . EOL);
			return true;
		}

		// add op to db
		$db->insert('alliance_has_op', [
			'alliance_id' => $db->escapeNumber($player->getAllianceID()),
			'game_id' => $db->escapeNumber($player->getGameID()),
			'time' => $db->escapeNumber($op_time),
		]);

		fwrite($fp, 'PRIVMSG ' . $channel . ' :The OP has been scheduled.' . EOL);
		return true;
	}

	return false;
}

/**
 * @param resource $fp
 */
function channel_msg_op_turns($fp, string $rdata, AbstractSmrPlayer $player): bool {
	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s(.*)\s:!op turns\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];

		echo_r('[OP_TURNS] by ' . $nick . ' in ' . $channel);

		if (!$player->isAllianceLeader(true)) {
			fwrite($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', only the leader of the alliance can use this command.' . EOL);
			return true;
		}

		$result = shared_channel_msg_op_turns($player);
		foreach ($result as $line) {
			fwrite($fp, 'PRIVMSG ' . $channel . ' :' . $line . EOL);
		}

		return true;
	}

	return false;
}

/**
 * @param resource $fp
 */
function channel_msg_op_response($fp, string $rdata, AbstractSmrPlayer $player): bool {

	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s(.*)\s:!op (yes|no|maybe)\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];
		$response = strtoupper($msg[5]);

		echo_r('[OP_' . $response . '] by ' . $nick . ' in ' . $channel);

		// get the op info from db
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT 1
					FROM alliance_has_op
					WHERE alliance_id = ' . $player->getAllianceID() . '
						AND game_id = ' . $player->getGameID());
		if (!$dbResult->hasRecord()) {
			fwrite($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', your leader has not scheduled an OP.' . EOL);
			return true;
		}

		$db->replace('alliance_has_op_response', [
			'alliance_id' => $db->escapeNumber($player->getAllianceID()),
			'game_id' => $db->escapeNumber($player->getGameID()),
			'account_id' => $db->escapeNumber($player->getAccountID()),
			'response' => $db->escapeString($response),
		]);

		fwrite($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', you have been added to the ' . $response . ' list.' . EOL);

		return true;
	}

	return false;
}

/**
 * @param resource $fp
 */
function channel_msg_op_list($fp, string $rdata, AbstractSmrPlayer $player): bool {
	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s(.*)\s:!op list\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];

		echo_r('[OP_LIST] by ' . $nick . ' in ' . $channel);

		$result = shared_channel_msg_op_list($player);
		foreach ($result as $line) {
			fwrite($fp, 'PRIVMSG ' . $channel . ' :' . $line . EOL);
		}

		return true;
	}

	return false;
}

/**
 * @param resource $fp
 */
function channel_op_notification($fp, string $rdata, string $nick, string $channel): bool {
	echo_r('[OP_ATTENDANCE_CHECK] ' . $nick);

	$callback = function() use($fp, $rdata, $nick, $channel): bool {
		return channel_op_notification($fp, $rdata, $nick, $channel);
	};
	$player = check_for_registration($fp, $nick, $channel, $callback, false);
	if ($player === false) {
		return true;
	}

	$db = Database::getInstance();
	// check if there is an upcoming op
	$dbResult = $db->read('SELECT 1
				FROM alliance_has_op
				WHERE alliance_id = ' . $player->getAllianceID() . '
					AND game_id = ' . $player->getGameID() . '
					AND time > ' . time());
	if (!$dbResult->hasRecord()) {
		return true;
	}

	$dbResult = $db->read('SELECT 1
				FROM alliance_has_op_response
				WHERE alliance_id = ' . $player->getAllianceID() . '
					AND game_id = ' . $player->getGameID() . '
					AND account_id = ' . $player->getAccountID());
	if (!$dbResult->hasRecord()) {
		fwrite($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', your alliance leader has scheduled an OP, which you have not signed up yet. Please use the !op yes/no/maybe command to do so.' . EOL);
	}

	return true;
}
