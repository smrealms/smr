<?php declare(strict_types=1);

/**
 * @param resource $fp
 */
function ctcp_version($fp, string $rdata): bool {

	// :(nick)!(user)@(host) PRIVMSG (botnick)
	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s(.*)\s:' . chr(1) . 'VERSION' . chr(1) . '\s$/i', $rdata, $msg)) {

		$nick = $msg[1];

		echo_r('[CTCP_VERSION] by ' . $nick);

		fwrite($fp, 'NOTICE ' . $nick . ' :' . chr(1) . 'VERSION SMR BOT Version 1.0!' . chr(1) . EOL);
		return true;
	}

	return false;
}

/**
 * @param resource $fp
 */
function ctcp_finger($fp, string $rdata): bool {

	// :(nick)!(user)@(host) PRIVMSG (botnick)
	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s(.*)\s:' . chr(1) . 'FINGER' . chr(1) . '\s$/i', $rdata, $msg)) {

		$nick = $msg[1];

		echo_r('[CTCP_FINGER] by ' . $nick);

		fwrite($fp, 'NOTICE ' . $nick . ' :' . chr(1) . 'FINGER Go finger yourself!' . chr(1) . EOL);
		return true;
	}

	return false;
}

/**
 * @param resource $fp
 */
function ctcp_time($fp, string $rdata): bool {

	// :(nick)!(user)@(host) PRIVMSG (botnick)
	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s(.*)\s:' . chr(1) . 'TIME' . chr(1) . '\s$/i', $rdata, $msg)) {

		$nick = $msg[1];

		echo_r('[CTCP_TIME] by ' . $nick);

		fwrite($fp, 'NOTICE ' . $nick . ' :' . chr(1) . 'TIME You don\'t know what time it is? Me neither!' . chr(1) . EOL);
		return true;
	}

	return false;
}

/**
 * @param resource $fp
 */
function ctcp_ping($fp, string $rdata): bool {

	// :(nick)!(user)@(host) PRIVMSG (botnick)
	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s(.*)\s:' . chr(1) . 'PING\s(.*)' . chr(1) . '\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$their_time = $msg[5];

		echo_r('[CTCP_PING] by ' . $nick . ' at ' . $their_time);

		fwrite($fp, 'NOTICE ' . $nick . ' :' . chr(1) . 'PING ' . time() . chr(1) . EOL);
		return true;
	}

	return false;
}
