<?php declare(strict_types=1);

use Smr\Database;
use Smr\Irc\CallbackEvent;

/**
 * @param resource $fp
 */
function notice_nickserv_registered_user($fp, string $rdata): bool {

	// :NickServ!services@coldfront.net NOTICE Caretaker
	if (preg_match('/^:NickServ!services@theairlock.net NOTICE ' . IRC_BOT_NICK . ' :([^ ]+) is ([^.]+)\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$registeredNick = $msg[2];

		echo_r('[NOTICE_NICKSERV_REGISTERED_NICK] ' . $nick . ' is ' . $registeredNick);

		$db = Database::getInstance();

		$dbResult = $db->read('SELECT * FROM irc_seen WHERE nick = ' . $db->escapeString($nick));
		foreach ($dbResult->records() as $dbRecord) {
			$seen_id = $dbRecord->getInt('seen_id');

			$db->write('UPDATE irc_seen SET
						registered_nick = ' . $db->escapeString($registeredNick) . '
						WHERE seen_id = ' . $seen_id);
		}

		foreach (CallbackEvent::getAll() as $event) {

			// is that a callback for our nick?
			if ($event->type == 'NICKSERV_INFO' && $event->nick == $nick) {
				CallbackEvent::remove($event);
				($event->callback)();
			}

		}

		return true;
	}

	return false;
}

/**
 * @param resource $fp
 */
function notice_nickserv_unknown_user($fp, string $rdata): bool {

	// :NickServ!services@coldfront.net NOTICE Caretaker :Nickname Slevin isn't registered.
	if (preg_match('/^:NickServ!services@theairlock.net NOTICE ' . IRC_BOT_NICK . ' :Nickname .(.*). isn\'t registered\.\s$/i', $rdata, $msg)) {

		$nick = $msg[1];

		echo_r('[NOTICE_NICKSERV_UNKNOWN_NICK] ' . $nick);

		foreach (CallbackEvent::getAll() as $event) {

			// is that a callback for our nick?
			if ($event->type == 'NICKSERV_INFO' && $event->nick == $nick) {

				CallbackEvent::remove($event);

				if ($event->validate) {
					fwrite($fp, 'PRIVMSG ' . $event->channel . ' :' . $nick . ', you are not using a registered nick. Please identify with NICKSERV and try the last command again.' . EOL);
				}

			}

		}
		return true;

	}

	return false;
}
