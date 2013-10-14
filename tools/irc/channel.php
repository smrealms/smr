<?php

function channel_join($fp, $rdata)
{

	if (preg_match('/^:(.*)!(.*)@(.*)\sJOIN\s:(.*)\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];

		echo_r('[JOIN] ' . $nick . '!' . $user . '@' . $host . ' joined ' . $channel);

//		if ($nick == 'MrSpock' && $user == 'mrspock')
//			fputs($fp, 'PRIVMSG ' . $channel . ' :The creator! The God! He\'s among us! Praise him!' . EOL);
		if ($nick == 'Holti' && $user == 'Holti')
			fputs($fp, 'PRIVMSG ' . $channel . ' :' . chr(1) . 'ACTION hands ' . $nick . ' a ' . chr(3) . '4@' . chr(3) . '3' . chr(2) . '}' . chr(2) . '-,`--' . chr(1) . EOL);
		if ($nick == 'kiNky' && $user == 'cicika')
			fputs($fp, 'PRIVMSG ' . $channel . ' :' . chr(1) . 'ACTION hands ' . $nick . ' a ' . chr(3) . '4@' . chr(3) . '3' . chr(2) . '}' . chr(2) . '-,`--' . chr(1) . EOL);
		if ($nick == 'River' && $user == 'Serenity')
			fputs($fp, 'PRIVMSG ' . $channel . ' :' . chr(1) . 'ACTION hands ' . $nick . ' a ' . chr(3) . '8@' . chr(3) . '3' . chr(2) . '}' . chr(2) . '-,`--' . chr(1) . EOL);

		$db = new SmrMySqlDatabase();

		// check if we have seen this user before
		$db->query('SELECT * FROM irc_seen WHERE nick = ' . $db->escapeString($nick) . ' AND channel = ' . $db->escapeString($channel));

		if ($db->nextRecord()) {
			// exiting nick?
			$seen_id = $db->getField('seen_id');

			$seen_count = $db->getField('seen_count');
			$seen_by = $db->getField('seen_by');

			if ($seen_count > 1) {
				fputs($fp, 'PRIVMSG ' . $channel . ' :Welcome back ' . $nick . '. While being away ' . $seen_count . ' players were looking for you, the last one being ' . $seen_by . EOL);
			} elseif ($seen_count > 0) {
				fputs($fp, 'PRIVMSG ' . $channel . ' :Welcome back ' . $nick . '. While being away ' . $seen_by . ' was looking for you.' . EOL);
			}

			$db->query('UPDATE irc_seen
						SET signed_on = ' . $db->escapeNumber(time()) . ',
							signed_off = 0,
							user = ' . $db->escapeString($user) . ',
							host = ' . $db->escapeString($host) . ',
							seen_count = 0,
							seen_by = NULL,
							registered = NULL
						WHERE seen_id = ' . $db->escapeNumber($seen_id));

		} else {
			// new nick?
			$db->query('INSERT INTO irc_seen (nick, user, host, channel, signed_on) VALUES(' . $db->escapeString($nick) . ', ' . $db->escapeString($user) . ', ' . $db->escapeString($host) . ', ' . $db->escapeString($channel) . ', ' . time() . ')');
		}

		// check if player joined alliance chat
		channel_op_notification($fp, $rdata, $nick, $channel);


		return true;

	}

	return false;

}

function channel_part($fp, $rdata)
{

	// :Azool!Azool@coldfront-F706F7E1.co.hfc.comcastbusiness.net PART #smr-irc :
	// :SomeGuy!mrspock@coldfront-DD847655.dip.t-dialin.net PART #smr-irc
	if (preg_match('/^:(.*)!(.*)@(.*)\sPART\s(.*?)\s/i', $rdata, $msg)) {

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