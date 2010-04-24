<?php

function ctcp_version($fp, $rdata) {

	global $nick;

	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s'.$nick.'\s:.VERSION.\s$/i', $rdata, $msg)) {

		echo_r($msg);
		fputs($fp, 'NOTICE '.$msg[1].' :SMR BOT Version 1.0!'.EOL);
		return true;

	}

	return false;

}

function ctcp_time($fp, $rdata) {

	global $nick;

	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s'.$nick.'\s:.TIME.\s$/i', $rdata, $msg)) {

		echo_r($msg);
		fputs($fp, 'NOTICE '.$msg[1].' :You don\'t know what time it is? Me neither!'.EOL);
		return true;

	}

	return false;

}

function server_ping($fp, $rdata) {

	if (preg_match('/^PING\s:(.*)/i', $rdata)) {

		fputs($fp, 'PONG ' . substr($rdata, 6));
		return true;
	}

	return false;

}

?>