<?php declare(strict_types=1);

use Smr\AbstractPlayer;
use Smr\Alliance;
use Smr\Database;
use Smr\Irc\Message;

/**
 * @param resource $fp
 */
function channel_msg_op($fp, Message $msg): bool {

	if (preg_match('/^!op(\s*help)?$/i', $msg->text)) {

		$nick = $msg->nick;
		$channel = $msg->channel;

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
function channel_msg_op_info($fp, Message $msg, AbstractPlayer $player): bool {
	if ($msg->text == '!op info') {

		$nick = $msg->nick;
		$channel = $msg->channel;

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
function channel_msg_op_cancel($fp, Message $msg, AbstractPlayer $player): bool {

	if ($msg->text == '!op cancel') {

		$nick = $msg->nick;
		$channel = $msg->channel;

		echo_r('[OP_CANCEL] by ' . $nick . ' in ' . $channel);

		// check if $nick is leader
		if (!$player->isAllianceLeader(true)) {
			fwrite($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', only the leader of the alliance can cancel an OP.' . EOL);
			return true;
		}

		$alliance = $player->getAlliance();

		// get the op from db
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT 1
					FROM alliance_has_op
					WHERE ' . Alliance::SQL, $alliance->SQLID);
		if (!$dbResult->hasRecord()) {
			fwrite($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', your leader has not scheduled an OP.' . EOL);
			return true;
		}

		// just get rid of op
		$db->write('DELETE FROM alliance_has_op
					WHERE ' . Alliance::SQL, $alliance->SQLID);
		$db->write('DELETE FROM alliance_has_op_response
					WHERE ' . Alliance::SQL, $alliance->SQLID);

		fwrite($fp, 'PRIVMSG ' . $channel . ' :The OP has been canceled.' . EOL);
		return true;
	}

	return false;
}

/**
 * @param resource $fp
 */
function channel_msg_op_set($fp, Message $msg, AbstractPlayer $player): bool {

	if (preg_match('/^!op set (.*)$/i', $msg->text, $args)) {

		$nick = $msg->nick;
		$channel = $msg->channel;

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
					WHERE alliance_id = :alliance_id
						AND  game_id = :game_id', [
			'alliance_id' => $player->getAllianceID(),
			'game_id' => $player->getGameID(),
		]);
		if ($dbResult->hasRecord()) {
			fwrite($fp, 'PRIVMSG ' . $channel . ' :There is already an OP scheduled. Cancel it first!' . EOL);
			return true;
		}

		$op_time = filter_var($args[1], FILTER_VALIDATE_INT);
		if ($op_time === false) {
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
function channel_msg_op_turns($fp, Message $msg, AbstractPlayer $player): bool {
	if ($msg->text == '!op turns') {

		$nick = $msg->nick;
		$channel = $msg->channel;

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
function channel_msg_op_response($fp, Message $msg, AbstractPlayer $player): bool {

	if (preg_match('/^!op (yes|no|maybe)$/i', $msg->text, $args)) {

		$nick = $msg->nick;
		$channel = $msg->channel;
		$response = strtoupper($args[1]);

		echo_r('[OP_' . $response . '] by ' . $nick . ' in ' . $channel);

		// get the op info from db
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT 1
					FROM alliance_has_op
					WHERE alliance_id = :alliance_id
						AND game_id = :game_id', [
			'alliance_id' => $player->getAllianceID(),
			'game_id' => $player->getGameID(),
		]);
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
function channel_msg_op_list($fp, Message $msg, AbstractPlayer $player): bool {
	if ($msg->text == '!op list') {

		$nick = $msg->nick;
		$channel = $msg->channel;

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
				WHERE alliance_id = :alliance_id
					AND game_id = :game_id
					AND time > :now', [
		'alliance_id' => $player->getAllianceID(),
		'game_id' => $player->getGameID(),
		'now' => time(),
	]);
	if (!$dbResult->hasRecord()) {
		return true;
	}

	$dbResult = $db->read('SELECT 1
				FROM alliance_has_op_response
				WHERE alliance_id = :alliance_id
					AND game_id = :game_id
					AND account_id = :account_id', [
		'alliance_id' => $player->getAllianceID(),
		'game_id' => $player->getGameID(),
		'account_id' => $player->getAccountID(),
	]);
	if (!$dbResult->hasRecord()) {
		fwrite($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', your alliance leader has scheduled an OP, which you have not signed up yet. Please use the !op yes/no/maybe command to do so.' . EOL);
	}

	return true;
}
