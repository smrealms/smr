<?php declare(strict_types=1);

use Smr\Irc\Message;

/**
 * @param resource $fp
 */
function channel_action_slap($fp, Message $msg): bool {

	// :MrSpock!mrspock@coldfront-25B201B9.dip.t-dialin.net PRIVMSG #rod : ACTION slaps Caretaker around a bit with a large trout
	if (preg_match('/^ACTION slaps ' . IRC_BOT_NICK . '/i', $msg->text) === 1) {

		$nick = $msg->nick;
		$channel = $msg->channel;

		echo_r('[SLAP] by ' . $nick . ' in ' . $channel);

		$slap_responses = [
			'blocks ' . $nick . '\'s attack and beats six shades of shit out of their pets',
			'drops dead on the ground',
			'ducks and takes aim with an M16',
			'throws rocks at ' . $nick,
			'beats ' . $nick . ' like a red-headed step child',
			'gets up off the ground and roundhouse kicks ' . $nick . ' in the face',
			'does an evasive backflip and throws ninja stars at ' . $nick,
			'slaps ' . $nick . ' around a bit with a large trout',
			'deflects the slap and deals ' . rand(1, 999999) . ' damage to ' . $nick,
			'steals the trout and throws it back in the river',
		];
		fwrite($fp, 'PRIVMSG ' . $channel . ' :' . chr(1) . 'ACTION ' . array_rand_value($slap_responses) . chr(1) . EOL);

		return true;

	}

	return false;

}
