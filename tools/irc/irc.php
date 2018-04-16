#!/usr/bin/php -q
<?php

class TimeoutException extends Exception {}

function echo_r($message)
{
	if (is_array($message)) {
		foreach ($message as $msg)
			echo_r($msg);
	}
	else
		echo date("d.m.Y H:i:s => ") . $message . EOL;
}

// not keeping the filehandle might not be the wisest idea.
function write_log_message($msg)
{
	$logFile = fopen("/var/log/irc/" . date("Ymd") . ".log", "a+");
	fwrite($logFile, round(microtime(true) * 1000) . ' ' . $msg . EOL);
	fclose($logFile);
}

// config file
require_once(realpath(dirname(__FILE__)) . '/../../htdocs/config.inc');
// bot config
require_once(CONFIG . 'irc/config.specific.php');
// some libs
require_once(LIB . 'Default/Globals.class.inc');
require_once(get_file_loc('smr.inc'));

// timer events
$events = array();

// supply/demand list
$sds = array();

// on<something> actions
$actions = array();

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
//require_once(TOOLS . 'irc/rank.php');
//require_once('TOOLS . irc/ship.php');
require_once(TOOLS . 'irc/user.php');
require_once(TOOLS . 'irc/query.php');
require_once(TOOLS . 'irc/notice.php');
//require_once(TOOLS . 'irc/weapon.php');
//require_once(TOOLS . 'irc/level.php');
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

// delete all seen stats that appear to be on (we do not want to take something for granted that happend while we were away)
$db = new SmrMySqlDatabase();
$db->query('DELETE from irc_seen WHERE signed_off = 0');

// just in case we need to exit for good
$running = true;

// after a timeout we start over
while ($running) {
	try {
		// Reset last ping each time we try connecting.
		$last_ping = time();
		echo_r('Connecting to ' . IRC_BOT_SERVER_ADDRESS);
		$fp = fsockopen(IRC_BOT_SERVER_ADDRESS, IRC_BOT_SERVER_PORT);
		if ($fp) {
			echo_r('Socket ' . $fp . ' is connected... Identifying...');

			safefputs($fp, 'NICK ' . IRC_BOT_NICK . EOL);
			safefputs($fp, 'USER ' . IRC_BOT_USER . EOL);

			sleep(3);

			safefputs($fp, 'NICKSERV IDENTIFY ' . IRC_BOT_PASS . EOL);

            sleep(5);

			// join our public channel
			if (!IRC_DEBUGGING) {
				safefputs($fp, 'JOIN #smr' . EOL);
				safefputs($fp, 'JOIN #smr-bar' . EOL);
				sleep(1);
				safefputs($fp, 'WHO #smr' . EOL);
				safefputs($fp, 'WHO #smr-bar' . EOL);

				// join all alliance channels
				$db->query('SELECT channel
							FROM irc_alliance_has_channel
							JOIN game USING (game_id)
							WHERE start_date < ' . time() . '
								AND end_date > ' . time());
				while ($db->nextRecord()) {
					$alliance_channel = $db->getField('channel');
					// join channels
					safefputs($fp, 'JOIN ' . $alliance_channel . EOL);
					sleep(1);
					safefputs($fp, 'WHO ' . $alliance_channel . EOL);
				}

			}

			stream_set_blocking($fp, true);
			while (!feof($fp)) {
				readFromStream($fp);
			}
			fclose($fp); // close socket

		} else {

			// network troubles
			echo_r('There was an error connecting to ' . IRC_BOT_SERVER_ADDRESS . '/' . IRC_BOT_SERVER_PORT);

			// sleep and try again!
			sleep(60);

		}
	}
	catch (TimeoutException $e) {
		// Ignore the timeout exception, we'll loop round and reconnect.
	}
} // end of while running

function safefputs($fp, $text) {
	stream_set_blocking($fp, false);
	while(readFromStream($fp)!==false);
	fputs($fp, $text);
	stream_set_blocking($fp, true);
}

function readFromStream($fp) {
	global $last_ping;

	$rdata = fgets($fp, 4096);
	$rdata = preg_replace('/\s+/', ' ', $rdata);

	// log for reports (if enabled via command line (-log)
	if (IRC_LOGGING && strlen($rdata) > 0)
		write_log_message($rdata);

	// remember the last time we got something from the server
	if (strlen($rdata) > 0)
		$last_ping = time();

	// timeout detection!
	if ($last_ping < time() - 300) {
		echo_r('TIMEOUT detected!');
		fclose($fp); // close socket
		throw new TimeoutException();
	}

	// we simply do some poll stuff here
	check_events($fp);

	if (strlen($rdata) == 0) {
		return false;
	}

	// required!!! otherwise timeout!
	if (server_ping($fp, $rdata))
		return;

	// server msg
	if (server_msg_307($fp, $rdata))
		return;
	if (server_msg_318($fp, $rdata))
		return;
	if (server_msg_352($fp, $rdata))
		return;
	if (server_msg_401($fp, $rdata))
		return;

	//Are they using a linked nick instead
	if(notice_nickserv_registered_user($fp, $rdata))
		return;
	if(notice_nickserv_unknown_user($fp, $rdata))
		return;

	// some nice things
	if (ctcp_version($fp, $rdata))
		return;
	if (ctcp_finger($fp, $rdata))
		return;
	if (ctcp_time($fp, $rdata))
		return;
	if (ctcp_ping($fp, $rdata))
		return;

	if (invite($fp, $rdata))
		return;

	// join and part
	if (channel_join($fp, $rdata))
		return;
	if (channel_part($fp, $rdata))
		return;

	// nick change and quit
	if (user_nick($fp, $rdata))
		return;
	if (user_quit($fp, $rdata))
		return;

	if (channel_action_slap($fp, $rdata))
		return;

	// channel msg (!xyz) without registration
	if (channel_msg_help($fp, $rdata))
		return;
	if (channel_msg_seedlist($fp, $rdata))
		return;
	if (channel_msg_op($fp, $rdata))
		return;
	if (channel_msg_timer($fp, $rdata))
		return;
	if (channel_msg_8ball($fp, $rdata))
		return;
	if (channel_msg_seen($fp, $rdata))
		return;
	if (channel_msg_sd($fp, $rdata))
		return;

	// channel msg (!xyz) with registration
	if (channel_msg_with_registration($fp, $rdata))
		return;

	// MrSpock can use this to send commands as caretaker
	if (query_command($fp, $rdata))
		return;


	// debug
	if (IRC_DEBUGGING) {
		echo_r('[UNKNOWN] ' . $rdata);
		return;
	}
}
