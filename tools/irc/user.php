<?php declare(strict_types=1);

function user_quit($fp, $rdata)
{

	// :Fubar!Mibbit@coldfront-77C78B7B.dyn.optonline.net QUIT :Quit: http://www.mibbit.com ajax IRC Client
	if (preg_match('/^:(.*)!(.*)@(.*)\sQUIT\s:(.*)\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$quit_msg = $msg[4];

		echo_r('[QUIT] ' . $nick . '!' . $user . '@' . $host . ' stated ' . $quit_msg);

		// database object
		$db = MySqlDatabase::getInstance();
		$db2 = MySqlDatabase::getInstance(true);

		$db->query('SELECT * FROM irc_seen WHERE nick = ' . $db->escapeString($nick));

		// sign off all nicks
		while ($db->nextRecord()) {

			$seen_id = $db->getInt('seen_id');

			$db2->query('UPDATE irc_seen SET signed_off = ' . time() . ' WHERE seen_id = ' . $seen_id);

		}

		return true;

	}

	return false;

}

/**
 * Someone changed his nick
 */
function user_nick($fp, $rdata)
{

	if (preg_match('/^:(.*)!(.*)@(.*)\sNICK\s:(.*)\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$new_nick = $msg[4];

		echo_r('[NICK] ' . $nick . ' -> ' . $new_nick);

		// database object
		$db = MySqlDatabase::getInstance();
		$db2 = MySqlDatabase::getInstance(true);

		$channel_list = array();

		// 'sign off' all active old_nicks (multiple channels)
		$db->query('SELECT * FROM irc_seen WHERE nick = ' . $db->escapeString($nick) . ' AND signed_off = 0');
		while ($db->nextRecord()) {

			$seen_id = $db->getInt('seen_id');

			// remember channels where this nick was active
			array_push($channel_list, $db->getField('channel'));

			$db2->query('UPDATE irc_seen SET signed_off = ' . time() . ' WHERE seen_id = ' . $seen_id);

		}

		// now sign in the new_nick in every channel
		foreach ($channel_list as $channel) {

			// 'sign in' the new nick
			$db->query('SELECT * FROM irc_seen WHERE nick = ' . $db->escapeString($new_nick) . ' AND channel = ' . $db->escapeString($channel));

			if ($db->nextRecord()) {
				// exiting nick?
				$seen_id = $db->getInt('seen_id');

				$db->query('UPDATE irc_seen SET ' .
						   'signed_on = ' . time() . ', ' .
						   'signed_off = 0, ' .
						   'user = ' . $db->escapeString($user) . ', ' .
						   'host = ' . $db->escapeString($host) . ', ' .
						   'registered = NULL ' .
						   'WHERE seen_id = ' . $seen_id);

			} else {
				// new nick?
				$db->query('INSERT INTO irc_seen (nick, user, host, channel, signed_on) VALUES(' . $db->escapeString($new_nick) . ', ' . $db->escapeString($user) . ', ' . $db->escapeString($host) . ', ' . $db->escapeString($channel) . ', ' . time() . ')');
			}

		}

		unset($channel_list);

		return true;

	}

	return false;

}
