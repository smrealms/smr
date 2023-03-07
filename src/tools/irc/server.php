<?php declare(strict_types=1);

use Smr\Database;
use Smr\Irc\CallbackEvent;

/**
 * Very important!
 * If we do not answer the ping from server we will be disconnected
 *
 * @param resource $fp
 */
function server_ping($fp, string $rdata): bool {
	global $last_ping;

	if (preg_match('/^PING\s:(.*)\s/i', $rdata, $msg)) {

		$server = $msg[1];

		// remember the last time we got a ping from the server
		$last_ping = time();

		// This message is very spammy
		if (defined('IRC_BOT_VERBOSE_PING') && IRC_BOT_VERBOSE_PING) {
			echo_r('[PING] from ' . $server);
		}

		fwrite($fp, 'PONG ' . $server . EOL);
		return true;
	}

	return false;
}

/**
 * Part of a whois msg
 *
 * @param resource $fp
 */
function server_msg_307($fp, string $rdata): bool {

	// :alpha.theairlock.net 307 Caretaker MrSpock :is identified for this nick
	if (preg_match('/^:(.*) 307 ' . IRC_BOT_NICK . ' (.*) :is identified for this nick\s/i', $rdata, $msg)) {

		$server = $msg[1];
		$nick = $msg[2];

		echo_r('[SERVER_307] ' . $server . ' said that ' . $nick . ' is registered');

		$db = Database::getInstance();
		$dbResult = $db->read('SELECT * FROM irc_seen WHERE nick = :nick', [
			'nick' => $db->escapeString($nick),
		]);
		foreach ($dbResult->records() as $dbRecord) {
			$seen_id = $dbRecord->getInt('seen_id');

			$db->update(
				'irc_seen',
				['registered' => 1],
				['seen_id' => $seen_id],
			);
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
function server_msg_318($fp, string $rdata): bool {

	// :ice.coldfront.net 318 Caretaker MrSpock :End of /WHOIS list.
	if (preg_match('/^:(.*) 318 ' . IRC_BOT_NICK . ' (.*) :End of \/WHOIS list\.\s/i', $rdata, $msg)) {

		$server = $msg[1];
		$nick = $msg[2];

		echo_r('[SERVER_318] ' . $server . ' end of /WHOIS for ' . $nick);

		$db = Database::getInstance();

		$dbResult = $db->read('SELECT * FROM irc_seen WHERE nick = :nick AND registered IS NULL', [
			'nick' => $db->escapeString($nick),
		]);
		foreach ($dbResult->records() as $dbRecord) {
			$seen_id = $dbRecord->getInt('seen_id');

			$db->update(
				'irc_seen',
				['registered' => 0],
				['seen_id' => $seen_id],
			);
		}

		foreach (CallbackEvent::getAll() as $event) {

			// is that a callback for our nick?
			if ($event->type == 'MSG_318' && $event->nick == $nick) {

				CallbackEvent::remove($event);

				// so we should do a callback but need to check first if the guy has registered
				$dbResult = $db->read('SELECT 1 FROM irc_seen WHERE nick = :nick AND registered = 1 AND channel = :channel', [
					'nick' => $db->escapeString($nick),
					'channel' => $db->escapeString($event->channel),
				]);
				if ($dbResult->hasRecord()) {
					//Forward to a NICKSERV INFO call.
					fwrite($fp, 'NICKSERV INFO ' . $nick . EOL);
					CallbackEvent::add(new CallbackEvent(
						type: 'NICKSERV_INFO',
						channel: $event->channel,
						nick: $event->nick,
						callback: $event->callback,
						time: time(),
						validate: $event->validate,
					));
				} elseif ($event->validate) {
					fwrite($fp, 'PRIVMSG ' . $event->channel . ' :' . $nick . ', you are not using a registered nick. Please identify with NICKSERV and try the last command again.' . EOL);
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
function server_msg_352($fp, string $rdata): bool {

	// :ice.coldfront.net 352 Caretaker #KMFDM caretaker coldfront-425DB813.dip.t-dialin.net ice.coldfront.net Caretaker Hr :0 Official SMR bot
	if (preg_match('/^:(.*?) 352 ' . IRC_BOT_NICK . ' (.*?) (.*?) (.*?) (.*?) (.*?) (.*?) (.*?) (.*?)$/i', $rdata, $msg)) {

		$channel = $msg[2];
		$user = $msg[3];
		$host = $msg[4];
		$nick = $msg[6];

		echo_r('[WHO] ' . $channel . ': ' . $nick);

		$db = Database::getInstance();

		// check if we have seen this user before
		$dbResult = $db->read('SELECT * FROM irc_seen WHERE nick = :nick AND channel = :channel', [
			'nick' => $db->escapeString($nick),
			'channel' => $db->escapeString($channel),
		]);

		if ($dbResult->hasRecord()) {
			// exiting nick?
			$seen_id = $dbResult->record()->getInt('seen_id');

			$db->update(
				'irc_seen',
				[
					'signed_on' => time(),
					'signed_off' => 0,
					'user' => $db->escapeString($user),
					'host' => $db->escapeString($host),
					'registered' => null,
				],
				['seen_id' => $db->escapeNumber($seen_id)],
			);

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
function server_msg_401($fp, string $rdata): bool {

	// :ice.coldfront.net 401 Caretaker MrSpock :No such nick/channel
	if (preg_match('/^:(.*) 401 ' . IRC_BOT_NICK . ' (.*) :No such nick\/channel\s/i', $rdata, $msg)) {

		$server = $msg[1];
		$nick = $msg[2];

		echo_r('[SERVER_401] ' . $server . ' said: "No such nick/channel" for ' . $nick);

		$db = Database::getInstance();

		// get the user in question
		$dbResult = $db->read('SELECT * FROM irc_seen WHERE nick = :nick AND signed_off = 0', [
			'nick' => $db->escapeString($nick),
		]);
		if ($dbResult->hasRecord()) {
			$seen_id = $dbResult->record()->getInt('seen_id');

			// maybe he left without us noticing, so we fix this now
			$db->update(
				'irc_seen',
				['signed_off' => time()],
				['seen_id' => $seen_id],
			);
		}

		return true;
	}

	return false;
}
