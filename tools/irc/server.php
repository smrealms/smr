<?php

// very important
// if we do not answer the ping from server we will be disconnected
function server_ping($fp, $rdata)
{

	if (preg_match('/^PING\s:(.*)\s/i', $rdata, $msg)) {

		$server = $msg[1];

		// This message is very spammy
		if (defined('IRC_BOT_VERBOSE_PING') && IRC_BOT_VERBOSE_PING) {
			echo_r('[PING] from ' . $server);
		}

		fputs($fp, 'PONG ' . $server . EOL);
		return true;
	}

	return false;

}

// part of a whois msg
function server_msg_307($fp, $rdata)
{

	// :alpha.theairlock.net 307 Caretaker MrSpock :is identified for this nick
	if (preg_match('/^:(.*) 307 ' . IRC_BOT_NICK . ' (.*) :is identified for this nick\s/i', $rdata, $msg)) {

		$server = $msg[1];
		$nick = $msg[2];

		echo_r('[SERVER_307] ' . $server . ' said that ' . $nick . ' is registered');

		$db = new SmrMySqlDatabase();
		$db2 = new SmrMySqlDatabase();

		$db->query('SELECT * FROM irc_seen WHERE nick = ' . $db->escapeString($nick));
		while ($db->nextRecord()) {
			$seen_id = $db->getField('seen_id');

			$db2->query('UPDATE irc_seen SET ' .
						'registered = 1 ' .
						'WHERE seen_id = ' . $seen_id);
		}

		return true;
	}

	return false;

}

// end of whois list
function server_msg_318($fp, $rdata)
{

	// :ice.coldfront.net 318 Caretaker MrSpock :End of /WHOIS list.
	if (preg_match('/^:(.*) 318 ' . IRC_BOT_NICK . ' (.*) :End of \/WHOIS list\.\s/i', $rdata, $msg)) {

		$server = $msg[1];
		$nick = $msg[2];

		echo_r('[SERVER_318] ' . $server . ' end of /WHOIS for ' . $nick);

		$db = new SmrMySqlDatabase();
		$db2 = new SmrMySqlDatabase();

		$db->query('SELECT * FROM irc_seen WHERE nick = ' . $db->escapeString($nick) . ' AND registered IS NULL');
		while ($db->nextRecord()) {
			$seen_id = $db->getField('seen_id');

			$db2->query('UPDATE irc_seen SET ' .
						'registered = 0 ' .
						'WHERE seen_id = ' . $seen_id);
		}


		global $actions;
		foreach($actions as $key => $action) {

			// is that a callback for our nick?
			if ($action[0] == 'MSG_318' && $nick == $action[2]) {

				echo_r('Callback found: ' . $action[3]);
				
				unset($actions[$key]);

				// so we should do a callback but need to check first if the guy has registered
				$db->query('SELECT * FROM irc_seen WHERE nick = ' . $db->escapeString($nick) . ' AND registered = 1 AND channel = ' . $db->escapeString($action[1]));
				if ($db->nextRecord()) {
					//Forward to a NICKSERV INFO call.
					$action[0] = 'NICKSERV_INFO';
					$action[4] = time();
					array_push($actions, $action);
					fputs($fp, 'NICKSERV INFO ' . $nick . EOL);
				} else if($action[5] === true) {
					fputs($fp, 'PRIVMSG ' . $action[1] . ' :' . $nick . ', you are not using a registered nick. Please identify with NICKSERV and try the last command again.' . EOL);
				}

			}

		}

		return true;
	}

	return false;

}

// response to WHO
function server_msg_352($fp, $rdata)
{

	// :ice.coldfront.net 352 Caretaker #KMFDM caretaker coldfront-425DB813.dip.t-dialin.net ice.coldfront.net Caretaker Hr :0 Official SMR bot
	if (preg_match('/^:(.*?) 352 ' . IRC_BOT_NICK . ' (.*?) (.*?) (.*?) (.*?) (.*?) (.*?) (.*?) (.*?)$/i', $rdata, $msg)) {

		$server = $msg[1];
		$channel = $msg[2];
		$user = $msg[3];
		$host = $msg[4];
		$nick = $msg[6];

		echo_r('[WHO] ' . $channel . ': ' . $nick);

		$db = new SmrMySqlDatabase();

		// check if we have seen this user before
		$db->query('SELECT * FROM irc_seen WHERE nick = ' . $db->escapeString($nick) . ' AND channel = ' . $db->escapeString($channel));

		if ($db->nextRecord()) {
			// exiting nick?
			$seen_id = $db->getField('seen_id');

			$db->query('UPDATE irc_seen SET ' .
					   'signed_on = ' . time() . ', ' .
					   'signed_off = 0, ' .
					   'user = ' . $db->escapeString($user) . ', ' .
					   'host = ' . $db->escapeString($host) . ', ' .
					   'registered = NULL ' .
					   'WHERE seen_id = ' . $seen_id);

		} else {
			// new nick?
			$db->query('INSERT INTO irc_seen (nick, user, host, channel, signed_on) ' .
					   'VALUES(' . $db->escapeString($nick) . ', ' . $db->escapeString($user) . ', ' . $db->escapeString($host) . ', ' . $db->escapeString($channel) . ', ' . time() . ')');
		}

		return true;

	}

	return false;

}

// unknown user
function server_msg_401($fp, $rdata)
{

	// :ice.coldfront.net 401 Caretaker MrSpock :No such nick/channel
	if (preg_match('/^:(.*) 401 ' . IRC_BOT_NICK . ' (.*) :No such nick\/channel\s/i', $rdata, $msg)) {

		$server = $msg[1];
		$nick = $msg[2];

		echo_r('[SERVER_401] ' . $server . ' said: "No such nick/channel" for ' . $nick);

		$db = new SmrMySqlDatabase();
		$db2 = new SmrMySqlDatabase();

		// get the user in question
		$db->query('SELECT * FROM irc_seen WHERE nick = ' . $db->escapeString($nick) . ' AND signed_off = 0');
		if ($db->nextRecord()) {
			$seen_id = $db->getField('seen_id');

			// maybe he left without us noticing, so we fix this now
			$db->query('UPDATE irc_seen SET ' .
					   'signed_off = ' . time() . ', ' .
					   'WHERE seen_id = ' . $seen_id);

		}

		return true;
	}

	return false;

}
