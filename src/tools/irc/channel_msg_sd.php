<?php declare(strict_types=1);

use Smr\Irc\Message;

/**
 * @param resource $fp
 */
function channel_msg_sd($fp, Message $msg): bool {

	if (preg_match('/^!sd(\s*help)?$/i', $msg->text)) {

		$nick = $msg->nick;
		$channel = $msg->channel;

		echo_r('[SD] by ' . $nick . ' in ' . $channel);

		fwrite($fp, 'PRIVMSG ' . $channel . ' :The !sd command can be used to manage supply/demand for ports.' . EOL);
		fwrite($fp, 'PRIVMSG ' . $channel . ' :The following sub commands are available:' . EOL);
		fwrite($fp, 'PRIVMSG ' . $channel . ' :  !sd list                Displays a list of of all sectors with current supply/demand' . EOL);
		fwrite($fp, 'PRIVMSG ' . $channel . ' :  !sd set <sector> <sd>   Sets the supply/demand for given sector' . EOL);
		fwrite($fp, 'PRIVMSG ' . $channel . ' :  !sd del <sector>        Removes the given sector from the supply/demand list' . EOL);

		return true;
	}

	return false;
}

/**
 * @param resource $fp
 */
function channel_msg_sd_set($fp, Message $msg): bool {

	if (preg_match('/^!sd set (\d+) (\d+)$/i', $msg->text, $args)) {

		global $sds;

		$nick = $msg->nick;
		$channel = $msg->channel;
		$sector = $args[1];
		$sd = $args[2];

		echo_r('[SD_SET] by ' . $nick . ' in ' . $channel);

		// delete any old entries in the list
		foreach ($sds as $key => $value) {

			if ($value[3] != $channel) {
				continue;
			}

			if ($value[0] == $sector) {
				unset($sds[$key]);
			}

		}

		// add new entry
		$sds[] = [$sector, $sd, time(), $channel];

		fwrite($fp, 'PRIVMSG ' . $channel . ' :The supply/demand of ' . $sd . ' for sector ' . $sector . ' has been recorded' . EOL);

		return true;
	}

	return false;
}

/**
 * @param resource $fp
 */
function channel_msg_sd_del($fp, Message $msg): bool {

	if (preg_match('/^!sd del (\d+)$/i', $msg->text, $args)) {

		global $sds;

		$nick = $msg->nick;
		$channel = $msg->channel;
		$sector = $args[1];

		echo_r('[SD_DEL] by ' . $nick . ' in ' . $channel);

		foreach ($sds as $key => $sd) {

			if ($sd[3] != $channel) {
				continue;
			}

			if ($sd[0] == $sector) {
				fwrite($fp, 'PRIVMSG ' . $channel . ' :The supply/demand for sector ' . $sector . ' has been deleted.' . EOL);
				unset($sds[$key]);
			}

		}

		return true;
	}

	return false;
}

/**
 * @param resource $fp
 */
function channel_msg_sd_list($fp, Message $msg, AbstractSmrPlayer $player): bool {

	if ($msg->text == '!sd list') {

		global $sds;

		$nick = $msg->nick;
		$channel = $msg->channel;

		echo_r('[SD_LIST] by ' . $nick . ' in ' . $channel);

		$refresh_per_hour = 250 * $player->getGame()->getGameSpeed();
		$refresh_per_sec = $refresh_per_hour / 3600;

		fwrite($fp, 'PRIVMSG ' . $channel . ' :The following supply/demand list has been recorded:' . EOL);
		fwrite($fp, 'PRIVMSG ' . $channel . ' :Sector   Amount' . EOL);
		foreach ($sds as $sd) {
			if ($sd[3] == $channel) {

				$seconds_since_refresh = time() - $sd[2];
				if ($seconds_since_refresh < 0) {
					$seconds_since_refresh = 0;
				}
				$amt_to_add = floor($seconds_since_refresh * $refresh_per_sec);

				if ($sd[1] + $amt_to_add > 4000) {
					fwrite($fp, 'PRIVMSG ' . $channel . ' : ' . sprintf('%4s', $sd[0]) . '     ' . sprintf('%4s', 'full') . EOL);
				} else {
					fwrite($fp, 'PRIVMSG ' . $channel . ' : ' . sprintf('%4s', $sd[0]) . '     ' . sprintf('%4s', $sd[1] + $amt_to_add) . EOL);
				}

			}
		}

		return true;
	}

	return false;
}
