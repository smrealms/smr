<?php declare(strict_types=1);

function check_for_registration(&$account, &$player, $fp, $nick, $channel, $callback, $validationMessages = true) {
	//Force $validationMessages to always be boolean.
	$validationMessages = $validationMessages === true;
	
	$db = new SmrMySqlDatabase();

	// only registered users are allowed to use this command
	$db->query('SELECT * FROM irc_seen WHERE nick = ' . $db->escapeString($nick) . ' AND registered = 1 AND channel = ' . $db->escapeString($channel));
	if (!$db->nextRecord()) {

		global $actions;

		// execute a whois and continue here on whois
		fputs($fp, 'WHOIS ' . $nick . EOL);
		array_push($actions, array('MSG_318', $channel, $nick, $callback, time(), $validationMessages));

		return true;
	}
	
	$registeredNick = $db->getField('registered_nick');

	// get alliance_id and game_id for this channel
	$alliance = SmrAlliance::getAllianceByIrcChannel($channel, true);
	if ($alliance == null) {
		if ($validationMessages === true) {
			fputs($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', the channel ' . $channel . ' has not been registered with me.' . EOL);
		}
		return true;
	}

	// get smr account
	$account = SmrAccount::getAccountByIrcNick($nick, true);
	if ($account == null) {
		if ($registeredNick != '') {
			$account = SmrAccount::getAccountByIrcNick($registeredNick, true);
		}
		if ($account == null) {
			if ($validationMessages === true) {
				fputs($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', please set your \'irc nick\' in SMR preferences to your registered nick so i can recognize you.' . EOL);
			}
			return true;
		}
	}

	// get smr player
	try {
		$player = SmrPlayer::getPlayer($account->getAccountID(), $alliance->getGameId(), true);
	}
	catch (PlayerNotFoundException $e) {
		if ($validationMessages === true) {
			fputs($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', you have not joined the game that this channel belongs to.' . EOL);
		}
		return true;
	}

	// is the user part of this alliance? (no need to check for 0, cannot happen at this point in code)
	if ($player->getAllianceID() != $alliance->getAllianceID()) {
		if ($validationMessages === true) {
			fputs($fp, 'KICK ' . $channel . ' ' . $nick . ' :You are not a member of this alliance!' . EOL);
		}
		return true;
	}
	
	return false;
}

function channel_msg_with_registration($fp, $rdata)
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
		
		if (check_for_registration($account, $player, $fp, $nick, $channel, 'channel_msg_with_registration($fp, \'' . $rdata . '\');')) {
			return true;
		}

		if (channel_msg_money($fp, $rdata, $account, $player))
			return true;
		if (channel_msg_forces($fp, $rdata, $account, $player))
			return true;

		if (channel_msg_seed($fp, $rdata, $account, $player))
			return true;
		if (channel_msg_seedlist_add($fp, $rdata, $account, $player))
			return true;
		if (channel_msg_seedlist_del($fp, $rdata, $account, $player))
			return true;

		if (channel_msg_op_info($fp, $rdata, $account, $player))
			return true;
		if (channel_msg_op_cancel($fp, $rdata, $account, $player))
			return true;
		if (channel_msg_op_set($fp, $rdata, $account, $player))
			return true;
		if (channel_msg_op_turns($fp, $rdata, $account, $player))
			return true;
		if (channel_msg_op_response($fp, $rdata, $account, $player))
			return true;
		if (channel_msg_op_list($fp, $rdata, $account, $player))
			return true;
		if (channel_msg_sd_set($fp, $rdata, $account, $player))
			return true;
		if (channel_msg_sd_del($fp, $rdata, $account, $player))
			return true;
		if (channel_msg_sd_list($fp, $rdata, $account, $player))
			return true;

	}

	return false;

}


function channel_msg_seen($fp, $rdata)
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
			$signed_on = $db->getInt('signed_on');
			$signed_off = $db->getInt('signed_off');

			if ($signed_off > 0) {

				$seen_id = $db->getInt('seen_id');

				// remember who did the !seen command
				$db->query('UPDATE irc_seen
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

function channel_msg_money($fp, $rdata, $account, $player)
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

function channel_msg_timer($fp, $rdata)
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

		if (isset($msg[6]))
			$message .= ' ' . $msg[6];

		echo_r('[TIMER] ' . $nick . ' started a timer with ' . $countdown . ' minute(s) (' . $message . ') in ' . $channel);

		array_push($events, array(time() + $countdown * 60, $message, $channel));

		fputs($fp, 'PRIVMSG ' . $channel . ' :The timer has been started and will go off in ' . $countdown . ' minute(s).' . EOL);

		return true;

	}

	return false;

}

function channel_msg_8ball($fp, $rdata)
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

function channel_msg_forces($fp, $rdata, $account, $player)
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

function channel_msg_help($fp, $rdata)
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
