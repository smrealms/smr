<?php declare(strict_types=1);

use Smr\Database;
use Smr\Exceptions\AccountNotFound;
use Smr\Exceptions\AllianceNotFound;
use Smr\Exceptions\PlayerNotFound;
use Smr\Irc\CallbackEvent;

/**
 * @param resource $fp
 */
function check_for_registration($fp, string $nick, string $channel, Closure $callback, bool $validationMessages = true): AbstractSmrPlayer|false {
	$db = Database::getInstance();

	// only registered users are allowed to use this command
	$dbResult = $db->read('SELECT * FROM irc_seen WHERE nick = ' . $db->escapeString($nick) . ' AND registered = 1 AND channel = ' . $db->escapeString($channel));
	if (!$dbResult->hasRecord()) {

		// execute a whois and continue here on whois
		fwrite($fp, 'WHOIS ' . $nick . EOL);
		CallbackEvent::add(new CallbackEvent(
			type: 'MSG_318',
			channel: $channel,
			nick: $nick,
			callback: $callback,
			time: time(),
			validate: $validationMessages
		));

		return false;
	}

	$registeredNick = $dbResult->record()->getString('registered_nick');

	// get alliance_id and game_id for this channel
	try {
		$alliance = SmrAlliance::getAllianceByIrcChannel($channel, true);
	} catch (AllianceNotFound) {
		if ($validationMessages === true) {
			fwrite($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', the channel ' . $channel . ' has not been registered with me.' . EOL);
		}
		return false;
	}

	// get smr account
	try {
		$account = SmrAccount::getAccountByIrcNick($nick, true);
	} catch (AccountNotFound) {
		try {
			$account = SmrAccount::getAccountByIrcNick($registeredNick, true);
		} catch (AccountNotFound) {
			if ($validationMessages === true) {
				fwrite($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', please set your \'irc nick\' in SMR preferences to your registered nick so i can recognize you.' . EOL);
			}
			return false;
		}
	}

	// get smr player
	try {
		$player = SmrPlayer::getPlayer($account->getAccountID(), $alliance->getGameID(), true);
	} catch (PlayerNotFound) {
		if ($validationMessages === true) {
			fwrite($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', you have not joined the game that this channel belongs to.' . EOL);
		}
		return false;
	}

	// is the user part of this alliance? (no need to check for 0, cannot happen at this point in code)
	if ($player->getAllianceID() != $alliance->getAllianceID()) {
		if ($validationMessages === true) {
			fwrite($fp, 'KICK ' . $channel . ' ' . $nick . ' :You are not a member of this alliance!' . EOL);
		}
		return false;
	}

	return $player;
}

/**
 * @param resource $fp
 */
function channel_msg_with_registration($fp, string $rdata): bool {
	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s(.*)\s:!(money|forces|seed|seedlist|op|sd)\s/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];

		// check if the query is in public channel
		if ($channel == '#smr' || $channel == '#smr-bar') {
			fwrite($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', that command can only be used in an alliance controlled channel.' . EOL);
			return true;
		}

		$callback = function() use($fp, $rdata): bool {
			return channel_msg_with_registration($fp, $rdata);
		};
		$player = check_for_registration($fp, $nick, $channel, $callback);
		if ($player === false) {
			return true;
		}

		if (channel_msg_money($fp, $rdata, $player)) {
			return true;
		}
		if (channel_msg_forces($fp, $rdata, $player)) {
			return true;
		}

		if (channel_msg_seed($fp, $rdata, $player)) {
			return true;
		}
		if (channel_msg_seedlist_add($fp, $rdata, $player)) {
			return true;
		}
		if (channel_msg_seedlist_del($fp, $rdata, $player)) {
			return true;
		}

		if (channel_msg_op_info($fp, $rdata, $player)) {
			return true;
		}
		if (channel_msg_op_cancel($fp, $rdata, $player)) {
			return true;
		}
		if (channel_msg_op_set($fp, $rdata, $player)) {
			return true;
		}
		if (channel_msg_op_turns($fp, $rdata, $player)) {
			return true;
		}
		if (channel_msg_op_response($fp, $rdata, $player)) {
			return true;
		}
		if (channel_msg_op_list($fp, $rdata, $player)) {
			return true;
		}
		if (channel_msg_sd_set($fp, $rdata)) {
			return true;
		}
		if (channel_msg_sd_del($fp, $rdata)) {
			return true;
		}
		if (channel_msg_sd_list($fp, $rdata, $player)) {
			return true;
		}

	}

	return false;
}


/**
 * @param resource $fp
 */
function channel_msg_seen($fp, string $rdata): bool {

	// <Caretaker> MrSpock, Azool (Azool@smrealms.rulez) was last seen quitting #smr
	// 2 days 10 hours 43 minutes ago (05.10. 05:04) stating 'Some people follow their dreams,
	// others hunt them down and mercessly beat them into submission' after spending 1 hour 54 minutes there.

	// MrSpock, do I look like a mirror? ^_^

	// MrSpock, please look a bit closer at the memberlist of this channel.

	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s(.*)\s:!seen\s(.*)\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];
		$seennick = $msg[5];

		echo_r('[SEEN] by ' . $nick . ' in ' . $channel . ' for ' . $seennick);

		// if the user asks for himself
		if ($nick == $seennick) {
			fwrite($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', do I look like a mirror?' . EOL);
			return true;
		}

		$db = Database::getInstance();

		// if user provided more than 3 letters we do a wildcard search
		if (strlen($seennick) > 3) {
			$dbResult = $db->read('SELECT * FROM irc_seen WHERE nick LIKE ' . $db->escapeString('%' . $seennick . '%') . ' AND channel = ' . $db->escapeString($channel) . ' ORDER BY signed_on DESC');
		} else {
			$dbResult = $db->read('SELECT * FROM irc_seen WHERE nick = ' . $db->escapeString($seennick) . ' AND channel = ' . $db->escapeString($channel));
		}

		// get only one result. shouldn't match more than one
		if ($dbResult->hasRecord()) {
			$dbRecord = $dbResult->record();

			$seennick = $dbRecord->getString('nick');
			$seenuser = $dbRecord->getString('user');
			$seenhost = $dbRecord->getString('host');
			$signed_on = $dbRecord->getInt('signed_on');
			$signed_off = $dbRecord->getInt('signed_off');

			if ($signed_off > 0) {

				$seen_id = $dbRecord->getInt('seen_id');

				// remember who did the !seen command
				$db->write('UPDATE irc_seen
							SET seen_count = seen_count + 1,
								seen_by = ' . $db->escapeString($nick) . '
							WHERE seen_id = ' . $seen_id);

				fwrite($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', ' . $seennick . ' (' . $seenuser . '@' . $seenhost . ') was last seen quitting ' . $channel . ' ' . format_time(time() - $signed_off) . ' ago after spending ' . format_time($signed_off - $signed_on) . ' there.' . EOL);
			} else {
				fwrite($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', please look a bit closer at the memberlist of this channel.' . EOL);
			}

			return true;
		}

		fwrite($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', I don\'t remember seeing ' . $seennick . '.' . EOL);
		return true;
	}

	return false;
}

/**
 * @param resource $fp
 */
function channel_msg_money($fp, string $rdata, AbstractSmrPlayer $player): bool {

	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s(.*)\s:!money\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];

		echo_r('[MONEY] by ' . $nick . ' in ' . $channel);

		$result = shared_channel_msg_money($player);

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
function channel_msg_timer($fp, string $rdata): bool {

	if (preg_match('/^:(.*)!(.*)@(.*) PRIVMSG (.*) :!timer(\s\d+)?(\s.+)?\s$/i', $rdata, $msg)) {

		global $events;

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];

		// no countdown means we give a list of active timers
		if (!isset($msg[5])) {

			fwrite($fp, 'PRIVMSG ' . $channel . ' :The following timers have been defined for this channel:' . EOL);
			foreach ($events as $event) {
				if ($event[2] == $channel) {
					fwrite($fp, 'PRIVMSG ' . $channel . ' :' . $event[1] . ' in ' . format_time($event[0] - time()) . EOL);
				}
			}

			return true;
		}

		$countdown = $msg[5];
		if (!is_numeric($countdown)) {
			fwrite($fp, 'PRIVMSG ' . $channel . ' :I need to know in how many minutes the timer needs to go off. Example: !timer 25 message to channel' . EOL);
		}

		$message = 'ALERT! ALERT! ALERT!';
		if (isset($msg[6])) {
			$message .= ' ' . $msg[6];
		}

		echo_r('[TIMER] ' . $nick . ' started a timer with ' . $countdown . ' minute(s) (' . $message . ') in ' . $channel);

		$events[] = [time() + $countdown * 60, $message, $channel];

		fwrite($fp, 'PRIVMSG ' . $channel . ' :The timer has been started and will go off in ' . $countdown . ' minute(s).' . EOL);

		return true;
	}

	return false;
}

/**
 * @param resource $fp
 */
function channel_msg_8ball($fp, string $rdata): bool {
	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s(.*)\s:!8ball (.*)\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];
		$question = $msg[4];

		echo_r('[8BALL] by ' . $nick . ' in ' . $channel . '. Question: ' . $question);

		fwrite($fp, 'PRIVMSG ' . $channel . ' :' . shared_channel_msg_8ball() . EOL);

		return true;
	}

	return false;
}

/**
 * @param resource $fp
 */
function channel_msg_forces($fp, string $rdata, AbstractSmrPlayer $player): bool {
	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s(.*)\s:!forces(.*)\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];
		$galaxy = trim($msg[5]);

		echo_r('[FORCE_EXPIRE] by ' . $nick . ' in ' . $channel . ' Galaxy: ' . $galaxy);

		$result = shared_channel_msg_forces($player, $galaxy);
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
function channel_msg_help($fp, string $rdata): bool {

	// global help?
	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s(.*)\s:!help\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];

		echo_r('[HELP] ' . $nick . '!' . $user . '@' . $host . ' ' . $channel);

		fwrite($fp, 'NOTICE ' . $nick . ' :--- HELP ---' . EOL);
		fwrite($fp, 'NOTICE ' . $nick . ' :' . IRC_BOT_NICK . ' is the official SMR bot' . EOL);
		fwrite($fp, 'NOTICE ' . $nick . ' :If you want his services in your channel please invite him using \'/invite ' . IRC_BOT_NICK . ' #channel\'' . EOL);
		fwrite($fp, 'NOTICE ' . $nick . ' : ' . EOL);
		fwrite($fp, 'NOTICE ' . $nick . ' :Available public commands commands:' . EOL);
		fwrite($fp, 'NOTICE ' . $nick . ' :  !seen <nickname>         Displays the last time <nickname> was seen' . EOL);
		fwrite($fp, 'NOTICE ' . $nick . ' :  !timer <mins> <msg>      Starts a countdown which will send a notice to the channel with the <msg> in <mins> minutes' . EOL);
		fwrite($fp, 'NOTICE ' . $nick . ' :  !8ball <question>        Display one of the famous 8ball answers to your <question>' . EOL);
		fwrite($fp, 'NOTICE ' . $nick . ' :Available alliance commands commands:' . EOL);
		fwrite($fp, 'NOTICE ' . $nick . ' :  !seedlist                Manages the seedlist' . EOL);
		fwrite($fp, 'NOTICE ' . $nick . ' :  !seed                    Displays a list of sectors you have not yet seeded' . EOL);
		fwrite($fp, 'NOTICE ' . $nick . ' :  !op                      Command to manage OPs' . EOL);
		fwrite($fp, 'NOTICE ' . $nick . ' :  !sd                      Command to manage supply/demands for ports' . EOL);
		fwrite($fp, 'NOTICE ' . $nick . ' :  !money                   Displays the funds the alliance owns' . EOL);
		fwrite($fp, 'NOTICE ' . $nick . ' :  !forces [Galaxy]         Will tell you when forces will expire. Can be used without parameters.' . EOL);

		return true;

		// help on a spec command?
	} elseif (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s(.*)\s:!help\s(.*)\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];
		$topic = $msg[5];

		echo_r('[HELP' . $topic . '] ' . $nick . '!' . $user . '@' . $host . ' ' . $channel);

		if ($topic == 'seen') {
			fwrite($fp, 'NOTICE ' . $nick . ' :Syntax !seen <nickname>' . EOL);
			fwrite($fp, 'NOTICE ' . $nick . ' :   Displays the last time <nickname> was seen' . EOL);
		} else {
			fwrite($fp, 'NOTICE ' . $nick . ' :There is no help available for this command! Try !help' . EOL);
		}

		return true;
	}

	return false;
}
