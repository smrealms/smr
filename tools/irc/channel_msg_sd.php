<?php

function channel_msg_sd($fp, $rdata)
{

	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s#(.*)\s:!sd\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];

		echo_r('[SD] by ' . $nick . ' in #' . $channel);

		fputs($fp, 'PRIVMSG #' . $channel . ' :The !sd command can be used to manage supply/demand for ports.' . EOL);
		fputs($fp, 'PRIVMSG #' . $channel . ' :The following sub commands are available:' . EOL);
		fputs($fp, 'PRIVMSG #' . $channel . ' :  !sd list                Displays a list of of all sectors with current supply/demand' . EOL);
		fputs($fp, 'PRIVMSG #' . $channel . ' :  !sd set <sector> <sd>   Sets the supply/demand for given sector' . EOL);
		fputs($fp, 'PRIVMSG #' . $channel . ' :  !sd del <sector>        Removes the given sector from the supply/demand list' . EOL);

		return true;

	}

	return false;

}

function channel_msg_sd_set($fp, $rdata, $account, $player)
{

	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s#(.*)\s:!sd set (\d+) (\d+)\s$/i', $rdata, $msg)) {

		global $sds;

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];
		$sector = $msg[5];
		$sd = $msg[6];

		echo_r('[SD_SET] by ' . $nick . ' in #' . $channel);

		// delete any old entries in the list
		foreach($sds as $key => $value) {

			if ($value[3] != $channel)
				continue;

			if ($value[0] == $sector) {
				unset($sds[$key]);
			}

		}

		// add new entry
		array_push($sds, array($sector, $sd, time(), $channel));

		fputs($fp, 'PRIVMSG #' . $channel . ' :The supply/demand of ' . $sd . ' for sector ' . $sector . ' has been recorded' . EOL);

		return true;

	}

}

function channel_msg_sd_del($fp, $rdata, $account, $player)
{

	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s#(.*)\s:!sd del (\d+)\s$/i', $rdata, $msg)) {

		global $sds;

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];
		$sector = $msg[5];

		echo_r('[SD_DEL] by ' . $nick . ' in #' . $channel);

		foreach($sds as $key => $sd) {

			if ($sd[3] != $channel)
				continue;

			if ($sd[0] == $sector) {
				fputs($fp, 'PRIVMSG #' . $channel . ' :The supply/demand for sector ' . $sector . ' has been deleted.' . EOL);
				unset($sds[$key]);
			}

		}

		return true;

	}

}

function channel_msg_sd_list($fp, $rdata, $account, $player)
{

	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s#(.*)\s:!sd list\s$/i', $rdata, $msg)) {

		global $sds;

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];

		echo_r('[SD_LIST] by ' . $nick . ' in #' . $channel);

		$refresh_per_hour = 250 * Globals::getGameSpeed($player->getGameID());
		$refresh_per_sec = $refresh_per_hour / 3600;

		fputs($fp, 'PRIVMSG #' . $channel . ' :The floowing supply/demand list has been recorded:' . EOL);
		fputs($fp, 'PRIVMSG #' . $channel . ' :Sector   Amount' . EOL);
		foreach ($sds as $sd) {
			if ($sd[3] == $channel) {

				$seconds_since_refresh = time() - $sd[2];
				if ($seconds_since_refresh < 0) $seconds_since_refresh = 0;
				$amt_to_add = floor($seconds_since_refresh * $refresh_per_sec);

				if ($sd[1] + $amt_to_add > 4000) {
					fputs($fp, 'PRIVMSG #' . $channel . ' : ' . sprintf('%4s', $sd[0]) . '     ' . sprintf('%4s', 'full') . EOL);
				} else {
					fputs($fp, 'PRIVMSG #' . $channel . ' : ' . sprintf('%4s', $sd[0]) . '     ' . sprintf('%4s', $sd[1] + $amt_to_add) . EOL);
				}

			}
		}

		return true;

	}

}


?>