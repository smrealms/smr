<?php

function invite($fp, $rdata)
{

	// :MrSpock!mrspock@coldfront-425DB813.dip.t-dialin.net INVITE Caretaker :#fe
	if (preg_match('/^:(.*)!(.*)@(.*) INVITE Caretaker :#(.*)\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];

		echo_r('[INVITE] by ' . $nick . ' for #' . $channel);

		// join channel where they want us
		fputs($fp, 'JOIN #' . $channel . EOL);
		sleep(1);
		fputs($fp, 'WHO #' . $channel . EOL);

	}

}

