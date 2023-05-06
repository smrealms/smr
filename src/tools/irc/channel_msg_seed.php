<?php declare(strict_types=1);

use Smr\AbstractPlayer;
use Smr\Irc\Message;

/**
 * @param resource $fp
 */
function channel_msg_seed($fp, Message $msg, AbstractPlayer $player): bool {
	if ($msg->text === '!seed') {

		$nick = $msg->nick;
		$channel = $msg->channel;

		echo_r('[SEED] by ' . $nick . ' in ' . $channel);

		$result = shared_channel_msg_seed($player);
		foreach ($result as $line) {
			fwrite($fp, 'PRIVMSG ' . $channel . ' :' . $line . EOL);
		}

		return true;
	}

	return false;
}

/**
 * @param resource $fp
 */
function channel_msg_seedlist($fp, Message $msg): bool {
	if (preg_match('/^!seedlist(\s*help)?$/i', $msg->text)) {

		$nick = $msg->nick;
		$channel = $msg->channel;

		echo_r('[SEEDLIST] by ' . $nick . ' in ' . $channel);

		fwrite($fp, 'PRIVMSG ' . $channel . ' :The !seedlist command enables alliance leader to add or remove sectors to the seedlist' . EOL);
		fwrite($fp, 'PRIVMSG ' . $channel . ' :The following sub commands are available:' . EOL);
		fwrite($fp, 'PRIVMSG ' . $channel . ' :  !seedlist add <sector1> <sector2> ...       Adds <sector> to the seedlist' . EOL);
		fwrite($fp, 'PRIVMSG ' . $channel . ' :  !seedlist del <sector1> <sector2> ...       Removes <sector> from seedlist' . EOL);

		return true;
	}

	return false;
}

/**
 * @param resource $fp
 */
function channel_msg_seedlist_add($fp, Message $msg, AbstractPlayer $player): bool {
	if (preg_match('/^!seedlist add (.*)$/i', $msg->text, $args)) {

		$nick = $msg->nick;
		$channel = $msg->channel;
		$sectors = explode(' ', $args[1]);

		echo_r('[SEEDLIST_ADD] by ' . $nick . ' in ' . $channel);

		$result = shared_channel_msg_seedlist_add($player, $sectors);
		foreach ($result as $line) {
			fwrite($fp, 'PRIVMSG ' . $channel . ' :' . $line . EOL);
		}

		return true;
	}

	return false;
}

/**
 * @param resource $fp
 */
function channel_msg_seedlist_del($fp, Message $msg, AbstractPlayer $player): bool {
	if (preg_match('/^!seedlist del (.*)$/i', $msg->text, $args)) {

		$nick = $msg->nick;
		$channel = $msg->channel;
		$sectors = explode(' ', $args[1]);

		echo_r('[SEEDLIST_DEL] by ' . $nick . ' in ' . $channel);

		$result = shared_channel_msg_seedlist_del($player, $sectors);
		foreach ($result as $line) {
			fwrite($fp, 'PRIVMSG ' . $channel . ' :' . $line . EOL);
		}

		return true;
	}

	return false;
}
