<?php declare(strict_types=1);

use Smr\Irc\Exceptions\Timeout;

require_once(TOOLS . 'irc/server.php');
require_once(TOOLS . 'irc/ctcp.php');
require_once(TOOLS . 'irc/invite.php');
require_once(TOOLS . 'irc/user.php');
require_once(TOOLS . 'irc/query.php');
require_once(TOOLS . 'irc/notice.php');
require_once(TOOLS . 'irc/channel.php');
require_once(TOOLS . 'irc/channel_action.php');
require_once(TOOLS . 'irc/channel_msg.php');
require_once(TOOLS . 'irc/channel_msg_op.php');
require_once(TOOLS . 'irc/channel_msg_sd.php');
require_once(TOOLS . 'irc/channel_msg_seed.php');
require_once(TOOLS . 'irc/maintenance.php');
require_once(TOOLS . 'chat_helpers/channel_msg_money.php');
require_once(TOOLS . 'chat_helpers/channel_msg_op_info.php');
require_once(TOOLS . 'chat_helpers/channel_msg_op_list.php');
require_once(TOOLS . 'chat_helpers/channel_msg_op_turns.php');
require_once(TOOLS . 'chat_helpers/channel_msg_seed.php');
require_once(TOOLS . 'chat_helpers/channel_msg_seedlist.php');
require_once(TOOLS . 'chat_helpers/channel_msg_forces.php');
require_once(TOOLS . 'chat_helpers/channel_msg_8ball.php');

/**
 * @param resource $fp
 */
function safefputs($fp, string $text): void {
	stream_set_blocking($fp, false);
	while (readFromStream($fp));
	fwrite($fp, $text);
	stream_set_blocking($fp, true);
}

/**
 * @param resource $fp
 */
function readFromStream($fp): bool {
	global $last_ping;

	// timeout detection!
	if ($last_ping < time() - 300) {
		echo_r('TIMEOUT detected!');
		fclose($fp); // close socket
		throw new Timeout();
	}

	// we simply do some poll stuff here
	check_events($fp);

	// try to get message from the server
	$rdata = fgets($fp, 4096);
	if ($rdata === false) { // no message or error
		return false;
	}
	$rdata = preg_replace('/\s+/', ' ', $rdata);

	// required!!! otherwise timeout!
	if (server_ping($fp, $rdata)) {
		return true;
	}

	// server msg
	if (server_msg_307($fp, $rdata)) {
		return true;
	}
	if (server_msg_318($fp, $rdata)) {
		return true;
	}
	if (server_msg_352($fp, $rdata)) {
		return true;
	}
	if (server_msg_401($fp, $rdata)) {
		return true;
	}

	//Are they using a linked nick instead
	if (notice_nickserv_registered_user($fp, $rdata)) {
		return true;
	}
	if (notice_nickserv_unknown_user($fp, $rdata)) {
		return true;
	}

	// some nice things
	if (ctcp_version($fp, $rdata)) {
		return true;
	}
	if (ctcp_finger($fp, $rdata)) {
		return true;
	}
	if (ctcp_time($fp, $rdata)) {
		return true;
	}
	if (ctcp_ping($fp, $rdata)) {
		return true;
	}

	if (invite($fp, $rdata)) {
		return true;
	}

	// join and part
	if (channel_join($fp, $rdata)) {
		return true;
	}
	if (channel_part($fp, $rdata)) {
		return true;
	}

	// nick change and quit
	if (user_nick($rdata)) {
		return true;
	}
	if (user_quit($rdata)) {
		return true;
	}

	if (channel_action_slap($fp, $rdata)) {
		return true;
	}

	// channel msg (!xyz) without registration
	if (channel_msg_help($fp, $rdata)) {
		return true;
	}
	if (channel_msg_seedlist($fp, $rdata)) {
		return true;
	}
	if (channel_msg_op($fp, $rdata)) {
		return true;
	}
	if (channel_msg_timer($fp, $rdata)) {
		return true;
	}
	if (channel_msg_8ball($fp, $rdata)) {
		return true;
	}
	if (channel_msg_seen($fp, $rdata)) {
		return true;
	}
	if (channel_msg_sd($fp, $rdata)) {
		return true;
	}

	// channel msg (!xyz) with registration
	if (channel_msg_with_registration($fp, $rdata)) {
		return true;
	}

	// MrSpock can use this to send commands as caretaker
	if (query_command($fp, $rdata)) {
		return true;
	}

	// If here, we have some unhandled response (print and move on)
	echo_r('[UNKNOWN] ' . $rdata);
	return true;
}
