<?php declare(strict_types=1);

function query_command($fp, $rdata)
{

	// :MrSpock!mrspock@coldfront-120CBD34.dip.t-dialin.net PRIVMSG Caretaker :Test
	if (preg_match('/^:(MrSpock!mrspock|Page!Page)@.*\sPRIVMSG\s' . IRC_BOT_NICK . '\s:(.*)\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$text = $msg[2];

		echo_r('[QUERY] by ' . $nick . ': ' . $text);

		// relay msg as our own
		fputs($fp, $text . EOL);

		return true;

	}

	return false;
}
