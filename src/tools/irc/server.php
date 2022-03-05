<?php declare(strict_types=1);

/**
 * Very important!
 * If we do not answer the ping from server we will be disconnected
 *
 * @param resource $fp
 */
function server_ping($fp, string $rdata) : bool
{
	global $last_ping;

	if (preg_match('/^PING\s:(.*)\s/i', $rdata, $msg)) {

		$server = $msg[1];

		// remember the last time we got a ping from the server
		$last_ping = time();

		// This message is very spammy
		if (defined('IRC_BOT_VERBOSE_PING') && IRC_BOT_VERBOSE_PING) {
			echo_r('[PING] from ' . $server);
		}

		fputs($fp, 'PONG ' . $server . EOL);
		return true;
	}

	return false;
}

/**
 * Part of a whois msg
 *
 * @param resource $fp
 */
function server_msg_307($fp, string $rdata) : bool
{

	// :alpha.theairlock.net 307 Caretaker MrSpock :is identified for this nick
	if (preg_match('/^:(.*) 307 ' . IRC_BOT_NICK . ' (.*) :is identified for this nick\s/i', $rdata, $msg)) {

		$server = $msg[1];
		$nick = $msg[2];

		echo_r('[SERVER_307] ' . $server . ' said that ' . $nick . ' is registered');

		$db = Smr\Database::getInstance();
		$dbResult = $db->read('SELECT * FROM irc_seen WHERE nick = ' . $db->escapeString($nick));
		foreach ($dbResult->records() as $dbRecord) {
			$seen_id = $dbRecord->getInt('seen_id');

			$db->write('UPDATE irc_seen SET ' .
						'registered = 1 ' .
						'WHERE seen_id = ' . $seen_id);
		}

		return true;
	}

	return false;
}

/**
 * End of whois list
 *
 * @param resource $fp
 */
function server_msg_318($fp, string $rdata) : bool
{

	// :ice.coldfront.net 318 Caretaker MrSpock :End of /WHOIS list.
	if (preg_match('/^:(.*) 318 ' . IRC_BOT_NICK . ' (.*) :End of \/WHOIS list\.\s/i', $rdata, $msg)) {

		$server = $msg[1];
		$nick = $msg[2];

		echo_r('[SERVER_318] ' . $server . ' end of /WHOIS for ' . $nick);

		$db = Smr\Database::getInstance();

		$dbResult = $db->read('SELECT * FROM irc_seen WHERE nick = ' . $db->escapeString($nick) . ' AND registered IS NULL');
		foreach ($dbResult->records() as $dbRecord) {
			$seen_id = $dbRecord->getInt('seen_id');

			$db->write('UPDATE irc_seen SET ' .
						'registered = 0 ' .
						'WHERE seen_id = ' . $seen_id);
		}


		global $actions;
		foreach ($actions as $key => $action) {

			// is that a callback for our nick?
			if ($action[0] == 'MSG_318' && $nick == $action[2]) {

				unset($actions[$key]);

				// so we should do a callback but need to check first if the guy has registered
				$dbResult = $db->read('SELECT 1 FROM irc_seen WHERE nick = ' . $db->escapeString($nick) . ' AND registered = 1 AND channel = ' . $db->escapeString($action[1]));
				if ($dbResult->hasRecord()) {
					//Forward to a NICKSERV INFO call.
					$action[0] = 'NICKSERV_INFO';
					$action[4] = time();
					array_push($actions, $action);
					fputs($fp, 'NICKSERV INFO ' . $nick . EOL);
				} elseif ($action[5] === true) {
					fputs($fp, 'PRIVMSG ' . $action[1] . ' :' . $nick . ', you are not using a registered nick. Please identify with NICKSERV and try the last command again.' . EOL);
				}

			}

		}

		return true;
	}

	return false;
}

/**
 * Response to WHO
 *
 * @param resource $fp
 */
function server_msg_352($fp, string $rdata) : bool
{

	// :ice.coldfront.net 352 Caretaker #KMFDM caretaker coldfront-425DB813.dip.t-dialin.net ice.coldfront.net Caretaker Hr :0 Official SMR bot
	if (preg_match('/^:(.*?) 352 ' . IRC_BOT_NICK . ' (.*?) (.*?) (.*?) (.*?) (.*?) (.*?) (.*?) (.*?)$/i', $rdata, $msg)) {

		$server = $msg[1];
		$channel = $msg[2];
		$user = $msg[3];
		$host = $msg[4];
		$nick = $msg[6];

		echo_r('[WHO] ' . $channel . ': ' . $nick);

		$db = Smr\Database::getInstance();

		// check if we have seen this user before
		$dbResult = $db->read('SELECT * FROM irc_seen WHERE nick = ' . $db->escapeString($nick) . ' AND channel = ' . $db->escapeString($channel));

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
				'nick' => $db->escapeString($nick),
				'user' => $db->escapeString($user),
				'host' => $db->escapeString($host),
				'channel' => $db->escapeString($channel),
				'signed_on' => $db->escapeNumber(time()),
			]);
		}

		return true;
	}

	return false;
}

/**
 * Unknown user
 *
 * @param resource $fp
 */
function server_msg_401($fp, string $rdata) : bool
{

	// :ice.coldfront.net 401 Caretaker MrSpock :No such nick/channel
	if (preg_match('/^:(.*) 401 ' . IRC_BOT_NICK . ' (.*) :No such nick\/channel\s/i', $rdata, $msg)) {

		$server = $msg[1];
		$nick = $msg[2];

		echo_r('[SERVER_401] ' . $server . ' said: "No such nick/channel" for ' . $nick);

		$db = Smr\Database::getInstance();

		// get the user in question
		$dbResult = $db->read('SELECT * FROM irc_seen WHERE nick = ' . $db->escapeString($nick) . ' AND signed_off = 0');
		if ($dbResult->hasRecord()) {
			$seen_id = $dbResult->record()->getInt('seen_id');

			// maybe he left without us noticing, so we fix this now
			$db->write('UPDATE irc_seen SET ' .
					   'signed_off = ' . time() . ', ' .
					   'WHERE seen_id = ' . $seen_id);

		}

		return true;
	}

	return false;
}
