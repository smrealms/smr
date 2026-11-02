<?php declare(strict_types=1);

use Smr\Irc\Message;
use Smr\Irc\SupplyDemandEntry;
use Smr\Irc\SupplyDemandState;
use Smr\Player;

/**
 * @param resource $fp
 */
function channel_msg_sd($fp, Message $msg): bool {
	if (preg_match('/^!sd(\s*help)?$/i', $msg->text) === 1) {

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
function channel_msg_sd_set($fp, Message $msg, SupplyDemandState $state): bool {
	if (preg_match('/^!sd set (\d+) (\d+)$/i', $msg->text, $args) === 1) {
		$nick = $msg->nick;
		$channel = $msg->channel;
		$sector = filter_var($args[1], FILTER_VALIDATE_INT);
		$sd = filter_var($args[2], FILTER_VALIDATE_INT);
		if ($sector === false || $sd === false) {
			fwrite($fp, 'PRIVMSG ' . $channel . ' :Please provide valid numeric sector and supply/demand values.' . EOL);
			return true;
		}

		echo_r('[SD_SET] by ' . $nick . ' in ' . $channel);

		$state->replace(new SupplyDemandEntry(
			sector: $sector,
			supplyDemand: $sd,
			recordedAt: time(),
			channel: $channel,
		));

		fwrite($fp, 'PRIVMSG ' . $channel . ' :The supply/demand of ' . $sd . ' for sector ' . $sector . ' has been recorded' . EOL);
		return true;
	}

	return false;
}

/**
 * @param resource $fp
 */
function channel_msg_sd_del($fp, Message $msg, SupplyDemandState $state): bool {
	if (preg_match('/^!sd del (\d+)$/i', $msg->text, $args) === 1) {
		$nick = $msg->nick;
		$channel = $msg->channel;
		$sector = filter_var($args[1], FILTER_VALIDATE_INT);
		if ($sector === false) {
			fwrite($fp, 'PRIVMSG ' . $channel . ' :Please provide a valid numeric sector.' . EOL);
			return true;
		}

		echo_r('[SD_DEL] by ' . $nick . ' in ' . $channel);

		if ($state->delete($channel, $sector)) {
			fwrite($fp, 'PRIVMSG ' . $channel . ' :The supply/demand for sector ' . $sector . ' has been deleted.' . EOL);
		}

		return true;
	}

	return false;
}

/**
 * @param resource $fp
 */
function channel_msg_sd_list($fp, Message $msg, Player $player, SupplyDemandState $state): bool {
	if ($msg->text === '!sd list') {
		$nick = $msg->nick;
		$channel = $msg->channel;

		echo_r('[SD_LIST] by ' . $nick . ' in ' . $channel);

		$refreshPerHour = 250 * $player->getGame()->getGameSpeed();
		$refreshPerSecond = $refreshPerHour / 3600;

		fwrite($fp, 'PRIVMSG ' . $channel . ' :The following supply/demand list has been recorded:' . EOL);
		fwrite($fp, 'PRIVMSG ' . $channel . ' :Sector   Amount' . EOL);
		foreach ($state->entriesForChannel($channel) as $entry) {
			$secondsSinceRefresh = max(0, time() - $entry->recordedAt);
			$amountToAdd = floor($secondsSinceRefresh * $refreshPerSecond);
			$amount = $entry->supplyDemand + $amountToAdd;
			if ($amount > 4000) {
				fwrite($fp, 'PRIVMSG ' . $channel . ' : ' . sprintf('%4s', $entry->sector) . '     ' . sprintf('%4s', 'full') . EOL);
			} else {
				fwrite($fp, 'PRIVMSG ' . $channel . ' : ' . sprintf('%4s', $entry->sector) . '     ' . sprintf('%4s', $amount) . EOL);
			}
		}

		return true;
	}

	return false;
}
