<?php declare(strict_types=1);

class TimeoutException extends Exception {}

function echo_r(string $message): void
{
	echo date("Y-m-d H:i:s => ") . $message . EOL;
}

// not keeping the filehandle might not be the wisest idea.
function write_log_message(string $msg): void
{
	$logFile = fopen("/var/log/irc/" . date("Ymd") . ".log", "a+");
	fwrite($logFile, round(microtime(true) * 1000) . ' ' . $msg . EOL);
	fclose($logFile);
}

// config file
require_once(realpath(dirname(__FILE__)) . '/../../bootstrap.php');
// bot config
require_once(CONFIG . 'irc/config.specific.php');

// timer events
$events = [];

// supply/demand list
$sds = [];

// on<something> actions
$actions = [];

$logging = false;
$debugging = false;

if ($argc > 1) {
	foreach ($argv as $arg) {
		if ($arg == '-log') {
			$logging = true;
		}
		if ($arg == '-debug') {
			$debugging = true;
		}
	}
}
define('IRC_LOGGING', $logging);
define('IRC_DEBUGGING', $debugging);

// include all sub files
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

// just in case we need to exit for good
$running = true;

// after a timeout we start over
while ($running) {

	// delete all seen stats that appear to be on (we do not want to take
	// something for granted that happend while we were away)
	$db = Smr\Database::getInstance();
	$db->write('DELETE from irc_seen WHERE signed_off = 0');

	// Reset last ping each time we try connecting.
	$last_ping = time();

	echo_r('Connecting to ' . IRC_BOT_SERVER_ADDRESS);
	$fp = fsockopen(IRC_BOT_SERVER_ADDRESS, IRC_BOT_SERVER_PORT);

	if (!$fp) {
		// network troubles
		echo_r('There was an error connecting to ' . IRC_BOT_SERVER_ADDRESS . '/' . IRC_BOT_SERVER_PORT);

		// sleep and try again!
		sleep(60);
		continue;
	}

	echo_r('Socket ' . $fp . ' is connected...');

	try {
		echo_r('Identifying...');
		safefputs($fp, 'NICK ' . IRC_BOT_NICK . EOL);
		safefputs($fp, 'USER ' . IRC_BOT_USER . EOL);

		sleep(3);

		safefputs($fp, 'NICKSERV IDENTIFY ' . IRC_BOT_PASS . EOL);

		sleep(5);

		if (!IRC_DEBUGGING) {
			// join our public channels
			$joinChannels = ['#smr', '#smr-bar'];

			// join all alliance channels
			$dbResult = $db->read('SELECT channel
						FROM irc_alliance_has_channel
						JOIN game USING (game_id)
						WHERE join_time < ' . time() . '
							AND end_time > ' . time());
			foreach ($dbResult->records() as $dbRecord) {
				$joinChannels[] = $dbRecord->getField('channel');
			}

			// now do the actual joining
			foreach ($joinChannels as $channel) {
				safefputs($fp, 'JOIN ' . $channel . EOL);
				sleep(1);
				safefputs($fp, 'WHO ' . $channel . EOL);
			}
		}

		stream_set_blocking($fp, true);
		while (!feof($fp)) {
			readFromStream($fp);
			// Close database connection between calls to avoid
			// stale or timed out server errors.
			$db->close();
		}
		fclose($fp); // close socket

	} catch (TimeoutException) {
		// Ignore the timeout exception, we'll loop round and reconnect.
	}
} // end of while running

function safefputs($fp, string $text): void {
	stream_set_blocking($fp, false);
	while (readFromStream($fp));
	fputs($fp, $text);
	stream_set_blocking($fp, true);
}

function readFromStream($fp): bool {
	global $last_ping;

	// timeout detection!
	if ($last_ping < time() - 300) {
		echo_r('TIMEOUT detected!');
		fclose($fp); // close socket
		throw new TimeoutException();
	}

	// we simply do some poll stuff here
	check_events($fp);

	// try to get message from the server
	$rdata = fgets($fp, 4096);
	if ($rdata === false) { // no message or error
		return false;
	}
	$rdata = preg_replace('/\s+/', ' ', $rdata);

	// log for reports (if enabled via command line (-log)
	if (IRC_LOGGING) {
		write_log_message($rdata);
	}

	// required!!! otherwise timeout!
	if (server_ping($fp, $rdata)) {
		return true;
	}

	// Since we close the database connection between polls, we will need
	// to reconnect before doing anything that requires the database. Note
	// that everything above this point does *not* need the database, but
	// we *may* need it beyond this point.
	$db = Smr\Database::getInstance();
	$db->reconnect();

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
