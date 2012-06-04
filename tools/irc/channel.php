<?php

function channel_join($fp, $rdata)
{

	if (preg_match('/^:(.*)!(.*)@(.*)\sJOIN\s:#(.*)\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];

		echo_r('[JOIN] ' . $nick . '!' . $user . '@' . $host . ' joined #' . $channel);

//		if ($nick == 'MrSpock' && $user == 'mrspock')
//			fputs($fp, 'PRIVMSG #' . $channel . ' :The creator! The God! He\'s among us! Praise him!' . EOL);
		if ($nick == 'Holti' && $user == 'Holti')
			fputs($fp, 'PRIVMSG #' . $channel . ' :' . chr(1) . 'ACTION hands Holti a ' . chr(3) . '4@' . chr(3) . '3' . chr(2) . '}' . chr(2) . '-,`--' . EOL);
		if ($nick == 'kiNky' && $user == 'cicika')
			fputs($fp, 'PRIVMSG #' . $channel . ' :' . chr(1) . 'ACTION hands kiNky a ' . chr(3) . '4@' . chr(3) . '3' . chr(2) . '}' . chr(2) . '-,`--' . EOL);

		$db = new SmrMySqlDatabase();

		// check if we have seen this user before
		$db->query('SELECT * FROM irc_seen WHERE nick = ' . $db->escapeString($nick) . ' AND channel = ' . $db->escapeString($channel));

		if ($db->nextRecord()) {
			// exiting nick?
			$seen_id = $db->getField('seen_id');

			$seen_count = $db->getField('seen_count');
			$seen_by = $db->getField('seen_by');

			if ($seen_count > 1) {
				fputs($fp, 'PRIVMSG #' . $channel . ' :Welcome back ' . $nick . '. While being away ' . $seen_count . ' players were looking for you, the last one being ' . $seen_by . EOL);
			} elseif ($seen_count > 0) {
				fputs($fp, 'PRIVMSG #' . $channel . ' :Welcome back ' . $nick . '. While being away ' . $seen_by . ' was looking for you.' . EOL);
			}

			$db->query('UPDATE irc_seen SET ' .
			           'signed_on = ' . time() . ', ' .
			           'signed_off = 0, ' .
			           'user = ' . $db->escapeString($user) . ', ' .
			           'host = ' . $db->escapeString($host) . ', ' .
			           'seen_count = 0, ' .
			           'seen_by = NULL, ' .
			           'registered = NULL ' .
			           'WHERE seen_id = ' . $seen_id);

		} else {
			// new nick?
			$db->query('INSERT INTO irc_seen (nick, user, host, channel, signed_on) VALUES(' . $db->escapeString($nick) . ', ' . $db->escapeString($user) . ', ' . $db->escapeString($host) . ', ' . $db->escapeString($channel) . ', ' . time() . ')');
		}

		fputs($fp, 'WHOIS ' . $nick . EOL);

		// check if player joined alliance chat
		$db->query('SELECT * FROM irc_alliance_has_channel WHERE channel = ' . $db->escapeString($channel));
		if ($db->nextRecord()) {
			$game_id = $db->getField('game_id');
			$alliance_id = $db->getField('alliance_id');

			// check if there is an upcoming op
			$db->query('SELECT time, attendees ' .
					   'FROM alliance_has_op ' .
					   'WHERE alliance_id = ' . $alliance_id . ' AND ' .
					   '      game_id = ' . $game_id . ' AND ' .
			           '      time > ' . time());
			if ($db->nextRecord()) {
				$attendees = unserialize($db->getField('attendees'));
				if (!is_array($attendees))
					$attendees = array();

				// if we are not in the attendees list we give the player a hint
				if (array_search($nick, $attendees) === false && $nick !== 'Caretaker') {
					fputs($fp, 'PRIVMSG #' . $channel . ' :' . $nick . ', your alliance leader has scheduled an OP, which you have not signed up yet. Please use the !op command to do so.' . EOL);
				}
			}

		}


		return true;

	}

	return false;

}

function channel_part($fp, $rdata)
{

	// :Azool!Azool@coldfront-F706F7E1.co.hfc.comcastbusiness.net PART #smr-irc :
	// :SomeGuy!mrspock@coldfront-DD847655.dip.t-dialin.net PART #smr-irc
	if (preg_match('/^:(.*)!(.*)@(.*)\sPART\s#(.*?)\s/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];

		echo_r('[PART] ' . $nick . '!' . $user . '@' . $host . ' ' . $channel);

		// database object
		$db = new SmrMySqlDatabase();

		$db->query('SELECT * FROM irc_seen WHERE nick = ' . $db->escapeString($nick) . ' AND channel = ' . $db->escapeString($channel));

		// exiting nick?
		if ($db->nextRecord()) {

			$seen_id = $db->getField('seen_id');

			$db->query('UPDATE irc_seen SET signed_off = ' . time() . ' WHERE seen_id = ' . $seen_id);

		} else {

			// we don't know this one, but who cares? he just left anyway...

		}

		return true;

	}

	return false;

}

?>