<?php declare(strict_types=1);

function notice_nickserv_registered_user($fp, string $rdata): bool {

	// :NickServ!services@coldfront.net NOTICE Caretaker
	if (preg_match('/^:NickServ!services@theairlock.net NOTICE ' . IRC_BOT_NICK . ' :([^ ]+) is ([^.]+)\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$registeredNick = $msg[2];

		echo_r('[NOTICE_NICKSERV_REGISTERED_NICK] ' . $nick . ' is ' . $registeredNick);

		$db = Smr\Database::getInstance();

		$dbResult = $db->read('SELECT * FROM irc_seen WHERE nick = ' . $db->escapeString($nick));
		foreach ($dbResult->records() as $dbRecord) {
			$seen_id = $dbRecord->getInt('seen_id');

			$db->write('UPDATE irc_seen SET
						registered_nick = ' . $db->escapeString($registeredNick) . '
						WHERE seen_id = ' . $seen_id);
		}

		global $actions;
		foreach ($actions as $key => $action) {

			// is that a callback for our nick?
			if ($action[0] == 'NICKSERV_INFO' && $nick == $action[2]) {
				unset($actions[$key]);

				$action[3]();
			}

		}

		return true;
	}

	return false;
}

function notice_nickserv_unknown_user($fp, string $rdata): bool {

	// :NickServ!services@coldfront.net NOTICE Caretaker :Nickname Slevin isn't registered.
	if (preg_match('/^:NickServ!services@theairlock.net NOTICE ' . IRC_BOT_NICK . ' :Nickname .(.*). isn\'t registered\.\s$/i', $rdata, $msg)) {

		$nick = $msg[1];

		echo_r('[NOTICE_NICKSERV_UNKNOWN_NICK] ' . $nick);

		global $actions;
		foreach ($actions as $key => $action) {

			// is that a callback for our nick?
			if ($action[0] == 'NICKSERV_INFO' && $nick == $action[2]) {

				unset($actions[$key]);

				if ($action[5] === true) {
					fwrite($fp, 'PRIVMSG ' . $action[1] . ' :' . $nick . ', you are not using a registered nick. Please identify with NICKSERV and try the last command again.' . EOL);
				}

			}

		}
		return true;

	}

	return false;
}
