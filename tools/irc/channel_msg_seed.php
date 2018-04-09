<?php

function channel_msg_seed($fp, $rdata, $account, $player)
{
	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s(.*)\s:!seed\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];

		echo_r('[SEED] by ' . $nick . ' in ' . $channel);

		$result = shared_channel_msg_seed($player);
		foreach ($result as $line) {
			fputs($fp, 'PRIVMSG ' . $channel . ' :' . $line . EOL);
		}

		return true;
	}

	return false;
}

function channel_msg_seedlist($fp, $rdata)
{
	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s(.*)\s:!seedlist(\s*help)?\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];

		echo_r('[SEEDLIST] by ' . $nick . ' in ' . $channel);

		fputs($fp, 'PRIVMSG ' . $channel . ' :The !seedlist command enables alliance leader to add or remove sectors to the seedlist' . EOL);
		fputs($fp, 'PRIVMSG ' . $channel . ' :The following sub commands are available:' . EOL);
		fputs($fp, 'PRIVMSG ' . $channel . ' :  !seedlist add <sector1> <sector2> ...       Adds <sector> to the seedlist' . EOL);
		fputs($fp, 'PRIVMSG ' . $channel . ' :  !seedlist del <sector1> <sector2> ...       Removes <sector> from seedlist' . EOL);

		return true;
	}

	return false;
}

function channel_msg_seedlist_add($fp, $rdata, $account, $player)
{
	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s(.*)\s:!seedlist add (.*)\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];
		$sectors = explode(' ', $msg[5]);

		echo_r('[SEEDLIST_ADD] by ' . $nick . ' in ' . $channel);

		$result = shared_channel_msg_seedlist_add($player, $sectors);
		foreach ($result as $line) {
			fputs($fp, 'PRIVMSG ' . $channel . ' :' . $line . EOL);
		}

		return true;
	}

	return false;
}

function channel_msg_seedlist_del($fp, $rdata, $account, $player)
{
	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s(.*)\s:!seedlist del (.*)\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];
		$sectors = explode(' ', $msg[5]);

		echo_r('[SEEDLIST_DEL] by ' . $nick . ' in ' . $channel);

		$result = shared_channel_msg_seedlist_del($player, $sectors);
		foreach ($result as $line) {
			fputs($fp, 'PRIVMSG ' . $channel . ' :' . $line . EOL);
		}

		return true;
	}

	return false;
}
