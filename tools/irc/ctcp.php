<?php declare(strict_types=1);

function ctcp_version($fp, $rdata)
{

	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s(.*)\s:' . chr(1) . 'VERSION' . chr(1) . '\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$botnick = $msg[4];

		echo_r('[CTCP_VERSION] by ' . $nick);

		fputs($fp, 'NOTICE ' . $nick . ' :' . chr(1) . 'VERSION SMR BOT Version 1.0!' . chr(1) . EOL);
		return true;

	}

	return false;

}

function ctcp_finger($fp, $rdata)
{

	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s(.*)\s:' . chr(1) . 'FINGER' . chr(1) . '\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$botnick = $msg[4];

		echo_r('[CTCP_FINGER] by ' . $nick);

		fputs($fp, 'NOTICE ' . $nick . ' :' . chr(1) . 'FINGER Go finger yourself!' . chr(1) . EOL);
		return true;

	}

	return false;

}

function ctcp_time($fp, $rdata)
{

	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s(.*)\s:' . chr(1) . 'TIME' . chr(1) . '\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$botnick = $msg[4];

		echo_r('[CTCP_TIME] by ' . $nick);

		fputs($fp, 'NOTICE ' . $nick . ' :' . chr(1) . 'TIME You don\'t know what time it is? Me neither!' . chr(1) . EOL);
		return true;

	}

	return false;

}

function ctcp_ping($fp, $rdata)
{

	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s(.*)\s:' . chr(1) . 'PING\s(.*)' . chr(1) . '\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$botnick = $msg[4];
		$their_time = $msg[5];

		echo_r('[CTCP_PING] by ' . $nick . ' at ' . $their_time);

		fputs($fp, 'NOTICE ' . $nick . ' :' . chr(1) . 'PING ' . time() . chr(1) . EOL);
		return true;

	}

	return false;

}
