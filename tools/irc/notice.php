<?php declare(strict_types=1);

function notice_nickserv_registered_user($fp, $rdata)
{

	// :NickServ!services@coldfront.net NOTICE Caretaker
	if (preg_match('/^:NickServ!services@theairlock.net NOTICE ' . IRC_BOT_NICK . ' :([^ ]+) is ([^.]+)\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$registeredNick = $msg[2];

		echo_r('[NOTICE_NICKSERV_REGISTERED_NICK] ' . $nick . ' is ' . $registeredNick);

		$db = MySqlDatabase::getInstance();
		$db2 = MySqlDatabase::getInstance(true);

		$db->query('SELECT * FROM irc_seen WHERE nick = ' . $db->escapeString($nick));
		while ($db->nextRecord()) {
			$seen_id = $db->getInt('seen_id');

			$db2->query('UPDATE irc_seen SET
						registered_nick = ' . $db->escapeString($registeredNick) . '
						WHERE seen_id = ' . $seen_id);
		}

		global $actions;
		foreach ($actions as $key => $action) {

			// is that a callback for our nick?
			if ($action[0] == 'NICKSERV_INFO' && $nick == $action[2]) {

				echo_r('Callback found: ' . $action[3]);

				unset($actions[$key]);

				eval($action[3]);

			}

		}


		return true;

	}

	return false;

}

function notice_nickserv_unknown_user($fp, $rdata)
{

	// :NickServ!services@coldfront.net NOTICE Caretaker :Nickname Slevin isn't registered.
	if (preg_match('/^:NickServ!services@theairlock.net NOTICE ' . IRC_BOT_NICK . ' :Nickname .(.*). isn\'t registered\.\s$/i', $rdata, $msg)) {

		$nick = $msg[1];

		echo_r('[NOTICE_NICKSERV_UNKNOWN_NICK] ' . $nick);

		global $actions;
		foreach ($actions as $key => $action) {

			// is that a callback for our nick?
			if ($action[0] == 'NICKSERV_INFO' && $nick == $action[2]) {

				echo_r('Callback found: ' . $action[3]);

				unset($actions[$key]);

				if ($action[5] === true) {
					fputs($fp, 'PRIVMSG ' . $action[1] . ' :' . $nick . ', you are not using a registered nick. Please identify with NICKSERV and try the last command again.' . EOL);
				}

			}

		}

		return true;

	}

	return false;

}
