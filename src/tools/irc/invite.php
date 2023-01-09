<?php declare(strict_types=1);

/**
 * @param resource $fp
 */
function invite($fp, string $rdata): bool {

	// :MrSpock!mrspock@coldfront-425DB813.dip.t-dialin.net INVITE Caretaker :#fe
	if (preg_match('/^:(.*)!(.*)@(.*) INVITE ' . IRC_BOT_NICK . ' :(.*)\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$channel = $msg[4];

		echo_r('[INVITE] by ' . $nick . ' for ' . $channel);

		// join channel where they want us
		fwrite($fp, 'JOIN ' . $channel . EOL);
		sleep(1);
		fwrite($fp, 'WHO ' . $channel . EOL);

		return true;
	}

	return false;
}
