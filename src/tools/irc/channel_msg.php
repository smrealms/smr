<?php declare(strict_types=1);

/**
 * @param resource $fp
 */
function check_for_registration($fp, string $nick, string $channel, callable $callback, bool $validationMessages = true) : AbstractSmrPlayer|false {
	//Force $validationMessages to always be boolean.
	$validationMessages = $validationMessages === true;

	$db = Smr\Database::getInstance();

	// only registered users are allowed to use this command
	$dbResult = $db->read('SELECT * FROM irc_seen WHERE nick = ' . $db->escapeString($nick) . ' AND registered = 1 AND channel = ' . $db->escapeString($channel));
	if (!$dbResult->hasRecord()) {

		global $actions;

		// execute a whois and continue here on whois
		fputs($fp, 'WHOIS ' . $nick . EOL);
		array_push($actions, array('MSG_318', $channel, $nick, $callback, time(), $validationMessages));

		return false;
	}

	$registeredNick = $dbResult->record()->getString('registered_nick');

	// get alliance_id and game_id for this channel
	try {
		$alliance = SmrAlliance::getAllianceByIrcChannel($channel, true);
	} catch (Smr\Exceptions\AllianceNotFound) {
		if ($validationMessages === true) {
			fputs($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', the channel ' . $channel . ' has not been registered with me.' . EOL);
		}
		return false;
	}

	// get smr account
	try {
		$account = SmrAccount::getAccountByIrcNick($nick, true);
	} catch (Smr\Exceptions\AccountNotFound) {
		try {
			$account = SmrAccount::getAccountByIrcNick($registeredNick, true);
		} catch (Smr\Exceptions\AccountNotFound) {
			if ($validationMessages === true) {
				fputs($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', please set your \'irc nick\' in SMR preferences to your registered nick so i can recognize you.' . EOL);
			}
			return false;
		}
	}

	// get smr player
	try {
		$player = SmrPlayer::getPlayer($account->getAccountID(), $alliance->getGameID(), true);
	} catch (Smr\Exceptions\PlayerNotFound) {
		if ($validationMessages === true) {
			fputs($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', you have not joined the game that this channel belongs to.' . EOL);
		}
		return false;
	}

	// is the user part of this alliance? (no need to check for 0, cannot happen at this point in code)
	if ($player->getAllianceID() != $alliance->getAllianceID()) {
		if ($validationMessages === true) {
			fputs($fp, 'KICK ' . $channel . ' ' . $nick . ' :You are not a member of this alliance!' . EOL);
		}
		return false;
	}

	return $player;
}

/**
 * @param resource $fp
 */
function channel_msg_with_registration($fp, string $rdata) : bool
{
	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s(.*)\s:!(money|forces|seed|seedlist|op|sd)\s/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];

		// check if the query is in public channel
		if ($channel == '#smr' || $channel == '#smr-bar') {
			fputs($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', that command can only be used in an alliance controlled channel.' . EOL);
			return true;
		}

		$callback = function() use($fp, $rdata) : bool {
			return channel_msg_with_registration($fp, $rdata);
		};
		if (($player = check_for_registration($fp, $nick, $channel, $callback)) === false) {
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
function channel_msg_seen($fp, string $rdata) : bool
{

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
			fputs($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', do I look like a mirror?' . EOL);
			return true;
		}

		$db = Smr\Database::getInstance();

		// if user provided more than 3 letters we do a wildcard search
		if (strlen($seennick) > 3) {
			$dbResult = $db->read('SELECT * FROM irc_seen WHERE nick LIKE ' . $db->escapeString('%' . $seennick . '%') . ' AND channel = ' . $db->escapeString($channel) . ' ORDER BY signed_on DESC');
		} else {
			$dbResult = $db->read('SELECT * FROM irc_seen WHERE nick = ' . $db->escapeString($seennick) . ' AND channel = ' . $db->escapeString($channel));
		}

		// get only one result. shouldn't match more than one
		if ($dbResult->hasRecord()) {
			$dbRecord = $dbResult->record();

			$seennick = $dbRecord->getField('nick');
			$seenuser = $dbRecord->getField('user');
			$seenhost = $dbRecord->getField('host');
			$signed_on = $dbRecord->getInt('signed_on');
			$signed_off = $dbRecord->getInt('signed_off');

			if ($signed_off > 0) {

				$seen_id = $dbRecord->getInt('seen_id');

				// remember who did the !seen command
				$db->write('UPDATE irc_seen
							SET seen_count = seen_count + 1,
								seen_by = ' . $db->escapeString($nick) . '
							WHERE seen_id = ' . $seen_id);

				fputs($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', ' . $seennick . ' (' . $seenuser . '@' . $seenhost . ') was last seen quitting ' . $channel . ' ' . format_time(time() - $signed_off) . ' ago after spending ' . format_time($signed_off - $signed_on) . ' there.' . EOL);
			} else {
				fputs($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', please look a bit closer at the memberlist of this channel.' . EOL);
			}

			return true;
		}

		fputs($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', I don\'t remember seeing ' . $seennick . '.' . EOL);
		return true;
	}

	return false;
}

/**
 * @param resource $fp
 */
function channel_msg_money($fp, string $rdata, AbstractSmrPlayer $player) : bool
{

	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s(.*)\s:!money\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];

		echo_r('[MONEY] by ' . $nick . ' in ' . $channel);

		$result = shared_channel_msg_money($player);

		foreach ($result as $line) {
			fputs($fp, 'PRIVMSG ' . $channel . ' :' . $line . EOL);
		}

		return true;
	}

	return false;
}

/**
 * @param resource $fp
 */
function channel_msg_timer($fp, string $rdata) : bool
{

	if (preg_match('/^:(.*)!(.*)@(.*) PRIVMSG (.*) :!timer(\s\d+)?(\s.+)?\s$/i', $rdata, $msg)) {

		global $events;

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];

		// no countdown means we give a list of active timers
		if (!isset($msg[5])) {

			fputs($fp, 'PRIVMSG ' . $channel . ' :The following timers have been defined for this channel:' . EOL);
			foreach ($events as $event) {
				if ($event[2] == $channel) {
					fputs($fp, 'PRIVMSG ' . $channel . ' :' . $event[1] . ' in ' . format_time($event[0] - time()) . EOL);
				}
			}

			return true;
		}

		if (!is_numeric($msg[5])) {
			fputs($fp, 'PRIVMSG ' . $channel . ' :I need to know in how many minutes the timer needs to go off. Example: !timer 25 message to channel' . EOL);
		}

		$countdown = intval($msg[5]);
		$message = 'ALERT! ALERT! ALERT!';

		if (isset($msg[6])) {
			$message .= ' ' . $msg[6];
		}

		echo_r('[TIMER] ' . $nick . ' started a timer with ' . $countdown . ' minute(s) (' . $message . ') in ' . $channel);

		array_push($events, array(time() + $countdown * 60, $message, $channel));

		fputs($fp, 'PRIVMSG ' . $channel . ' :The timer has been started and will go off in ' . $countdown . ' minute(s).' . EOL);

		return true;
	}

	return false;
}

/**
 * @param resource $fp
 */
function channel_msg_8ball($fp, string $rdata) : bool
{
	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s(.*)\s:!8ball (.*)\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];
		$question = $msg[4];

		echo_r('[8BALL] by ' . $nick . ' in ' . $channel . '. Question: ' . $question);

		fputs($fp, 'PRIVMSG ' . $channel . ' :' . shared_channel_msg_8ball() . EOL);

		return true;
	}

	return false;
}

/**
 * @param resource $fp
 */
function channel_msg_forces($fp, string $rdata, AbstractSmrPlayer $player) : bool
{
	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s(.*)\s:!forces(.*)\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];
		$galaxy = trim($msg[5]);

		echo_r('[FORCE_EXPIRE] by ' . $nick . ' in ' . $channel . ' Galaxy: ' . $galaxy);

		$result = shared_channel_msg_forces($player, $galaxy);
		foreach ($result as $line) {
			fputs($fp, 'PRIVMSG ' . $channel . ' :' . $line . EOL);
		}

		return true;
	}

	return false;
}

/**
 * @param resource $fp
 */
function channel_msg_help($fp, string $rdata) : bool
{

	// global help?
	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s(.*)\s:!help\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];

		echo_r('[HELP] ' . $nick . '!' . $user . '@' . $host . ' ' . $channel);

		fputs($fp, 'NOTICE ' . $nick . ' :--- HELP ---' . EOL);
		fputs($fp, 'NOTICE ' . $nick . ' :' . IRC_BOT_NICK . ' is the official SMR bot' . EOL);
		fputs($fp, 'NOTICE ' . $nick . ' :If you want his services in your channel please invite him using \'/invite ' . IRC_BOT_NICK . ' #channel\'' . EOL);
		fputs($fp, 'NOTICE ' . $nick . ' : ' . EOL);
		fputs($fp, 'NOTICE ' . $nick . ' :Available public commands commands:' . EOL);
		fputs($fp, 'NOTICE ' . $nick . ' :  !seen <nickname>         Displays the last time <nickname> was seen' . EOL);
		fputs($fp, 'NOTICE ' . $nick . ' :  !timer <mins> <msg>      Starts a countdown which will send a notice to the channel with the <msg> in <mins> minutes' . EOL);
		fputs($fp, 'NOTICE ' . $nick . ' :  !8ball <question>        Display one of the famous 8ball answers to your <question>' . EOL);
		fputs($fp, 'NOTICE ' . $nick . ' :Available alliance commands commands:' . EOL);
		fputs($fp, 'NOTICE ' . $nick . ' :  !seedlist                Manages the seedlist' . EOL);
		fputs($fp, 'NOTICE ' . $nick . ' :  !seed                    Displays a list of sectors you have not yet seeded' . EOL);
		fputs($fp, 'NOTICE ' . $nick . ' :  !op                      Command to manage OPs' . EOL);
		fputs($fp, 'NOTICE ' . $nick . ' :  !sd                      Command to manage supply/demands for ports' . EOL);
		fputs($fp, 'NOTICE ' . $nick . ' :  !money                   Displays the funds the alliance owns' . EOL);
		fputs($fp, 'NOTICE ' . $nick . ' :  !forces [Galaxy]         Will tell you when forces will expire. Can be used without parameters.' . EOL);

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
			fputs($fp, 'NOTICE ' . $nick . ' :Syntax !seen <nickname>' . EOL);
			fputs($fp, 'NOTICE ' . $nick . ' :   Displays the last time <nickname> was seen' . EOL);
		} else {
			fputs($fp, 'NOTICE ' . $nick . ' :There is no help available for this command! Try !help' . EOL);
		}

		return true;
	}

	return false;
}
