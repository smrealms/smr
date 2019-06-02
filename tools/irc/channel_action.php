<?php

function channel_action_slap($fp, $rdata)
{

	// :MrSpock!mrspock@coldfront-25B201B9.dip.t-dialin.net PRIVMSG #rod : ACTION slaps Caretaker around a bit with a large trout
	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s(.*)\s:.ACTION slaps ' . IRC_BOT_NICK . '/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];

		echo_r('[SLAP] by ' . $nick . ' in ' . $channel);

		$slap_responses = array(
			'blocks ' . $nick . '\'s attack and beats six shades of shit out of their pets',
			'drops dead on the ground',
			'ducks and takes aim with an M16',
			'throws rocks at ' . $nick,
			'beats ' . $nick . ' like a red-headed step child',
			'gets up off the ground and roundhouse kicks ' . $nick . ' in the face',
			'does an evasive backflip and throws ninja stars at ' . $nick,
			'slaps ' . $nick . ' around a bit with a large trout',
			'deflects the slap and deals ' . rand(1, 999999) . ' damage to ' . $nick,
			'steals the trout and throws it back in the river'
		);
		fputs($fp, 'PRIVMSG ' . $channel . ' :' . chr(1) . 'ACTION ' . $slap_responses[rand(0, count($slap_responses) - 1)] . chr(1) . EOL);

		return true;

	}

}
