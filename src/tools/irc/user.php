<?php declare(strict_types=1);

function user_quit(string $rdata) : bool
{

	// :Fubar!Mibbit@coldfront-77C78B7B.dyn.optonline.net QUIT :Quit: http://www.mibbit.com ajax IRC Client
	if (preg_match('/^:(.*)!(.*)@(.*)\sQUIT\s:(.*)\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$quit_msg = $msg[4];

		echo_r('[QUIT] ' . $nick . '!' . $user . '@' . $host . ' stated ' . $quit_msg);

		// database object
		$db = Smr\Database::getInstance();

		$dbResult = $db->read('SELECT * FROM irc_seen WHERE nick = ' . $db->escapeString($nick));

		// sign off all nicks
		foreach ($dbResult->records() as $dbRecord) {

			$seen_id = $dbRecord->getInt('seen_id');

			$db->write('UPDATE irc_seen SET signed_off = ' . time() . ' WHERE seen_id = ' . $seen_id);

		}

		return true;

	}

	return false;

}

/**
 * Someone changed his nick
 */
function user_nick(string $rdata) : bool
{

	if (preg_match('/^:(.*)!(.*)@(.*)\sNICK\s:(.*)\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$new_nick = $msg[4];

		echo_r('[NICK] ' . $nick . ' -> ' . $new_nick);

		// database object
		$db = Smr\Database::getInstance();

		$channel_list = array();

		// 'sign off' all active old_nicks (multiple channels)
		$dbResult = $db->read('SELECT * FROM irc_seen WHERE nick = ' . $db->escapeString($nick) . ' AND signed_off = 0');
		foreach ($dbResult->records() as $dbRecord) {

			$seen_id = $dbRecord->getInt('seen_id');

			// remember channels where this nick was active
			array_push($channel_list, $dbRecord->getField('channel'));

			$db->write('UPDATE irc_seen SET signed_off = ' . time() . ' WHERE seen_id = ' . $seen_id);

		}

		// now sign in the new_nick in every channel
		foreach ($channel_list as $channel) {

			// 'sign in' the new nick
			$dbResult = $db->read('SELECT * FROM irc_seen WHERE nick = ' . $db->escapeString($new_nick) . ' AND channel = ' . $db->escapeString($channel));

			if ($dbResult->hasRecord()) {
				// exiting nick?
				$seen_id = $dbResult->record()->getInt('seen_id');

				$db->write('UPDATE irc_seen SET ' .
						   'signed_on = ' . time() . ', ' .
						   'signed_off = 0, ' .
						   'user = ' . $db->escapeString($user) . ', ' .
						   'host = ' . $db->escapeString($host) . ', ' .
						   'registered = NULL ' .
						   'WHERE seen_id = ' . $seen_id);

			} else {
				// new nick?
				$db->insert('irc_seen', [
					'nick' => $db->escapeString($new_nick),
					'user' => $db->escapeString($user),
					'host' => $db->escapeString($host),
					'channel' => $db->escapeString($channel),
					'signed_on' => $db->escapeNumber(time()),
				]);
			}

		}

		return true;

	}

	return false;

}
