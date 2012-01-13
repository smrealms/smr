#!/usr/bin/php -q
<?php

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
	$logFile = fopen('/home/r/irc.log','a+');//"/var/log/irc/" . date("Ymd") . ".log", "a+");
	fwrite($logFile, round(microtime(true) * 1000) . ' ' . $msg . EOL);
	fclose($logFile);
}

function fill_string($str, $length)
{

	while (strlen($str) < $length)
		$str .= ' ';

	return $str;

}

// config file
include(realpath(dirname(__FILE__)) . '/../htdocs/config.inc');

include(LIB . '/Default/SmrMySqlDatabase.class.inc');

include(ENGINE . '/Default/smr.inc');

$address = 'ice.coldfront.net';
$port = 6667;
define('IRC_BOT_NICK', 'Caretaker');
$pass = 'smr4ever';

// timer events
$events = array();

// supply/demand list
$sds = array();

// on<something> actions
$actions = array();

$answers = array(
	'Signs point to yes.',
	'Yes.',
	'Reply hazy, try again.',
	'Without a doubt.',
	'My sources say no.',
	'As I see it, yes.',
	'You may rely on it.',
	'Concentrate and ask again.',
	'Outlook not so good.',
	'It is decidedly so.',
	'Better not tell you now.',
	'Very doubtful.',
	'Yes - definitely.',
	'It is certain.',
	'Cannot predict now.',
	'Most likely.',
	'Ask again later.',
	'My reply is no.',
	'Outlook good.',
	'Don\'t count on it.'
);

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

// include all sub files
require_once('irc/server.php');
require_once('irc/ctcp.php');
require_once('irc/invite.php');
//require_once('irc/rank.php');
//require_once('irc/ship.php');
require_once('irc/user.php');
require_once('irc/query.php');
require_once('irc/notice.php');
//require_once('irc/weapon.php');
//require_once('irc/level.php');
require_once('irc/channel.php');
require_once('irc/channel_action.php');
require_once('irc/channel_msg.php');
require_once('irc/channel_msg_op.php');
require_once('irc/channel_msg_sd.php');
require_once('irc/channel_msg_seed.php');
require_once('irc/channel_msg_sms.php');
require_once('irc/maintenance.php');

// delete all seen stats that appear to be on (we do not want to take something for granted that happend while we were away)
$db = new SmrMySqlDatabase();
$db->query('DELETE from irc_seen WHERE signed_off = 0');

// just in case we need to exit for good
$running = true;

// after a timeout we start over
while ($running) {

	echo_r('Connecting to ' . $address);
	$fp = fsockopen($address, $port);
	if ($fp) {
		stream_set_blocking($fp, TRUE);
		echo_r('Socket ' . $fp . ' is connected... Identifying...');

		fputs($fp, 'NICK CareGhost' . EOL);
		fputs($fp, 'USER ' . strtolower(IRC_BOT_NICK) . ' oberon smrealms.de :Official SMR bot' . EOL);

		// kill any other user that is using our nick
		fputs($fp, 'NICKSERV GHOST ' . IRC_BOT_NICK . ' ' . $pass . EOL);

		sleep(1);

		fputs($fp, 'NICK ' . IRC_BOT_NICK . EOL);
		fputs($fp, 'NICKSERV IDENTIFY ' . $pass . EOL);

		// join our public channel
		if (!$debugging) {
			fputs($fp, 'JOIN #smr' . EOL);
			fputs($fp, 'JOIN #smr-bar' . EOL);
			sleep(1);
			fputs($fp, 'WHO #smr' . EOL);
			fputs($fp, 'WHO #smr-bar' . EOL);

			// join all alliance channels
			$db->query('SELECT    channel ' .
					   'FROM      irc_alliance_has_channel ' .
					   'LEFT JOIN game USING (game_id) ' .
					   'WHERE     start_date < ' . time() .
					   '  AND     end_date > ' . time());
			while ($db->nextRecord()) {
				$alliance_channel = $db->getField('channel');

				// join channels
				fputs($fp, 'JOIN ' . $alliance_channel . EOL);
				sleep(1);
				fputs($fp, 'WHO ' . $alliance_channel . EOL);
			}

		}

		while (!feof($fp))
		{

			$rdata = fgets($fp, 4096);
			$rdata = preg_replace('/\s+/', ' ', $rdata);

			// log for reports (if enabled via command line (-log)
			if ($logging && strlen($rdata) > 0)
				write_log_message($rdata);

			// remember the last time we got something from the server
			if (strlen($rdata) > 0)
				$last_ping = time();

			// we simply do some poll stuff here
			check_planet_builds($fp);
			check_events($fp);
			check_sms_dlr($fp);
			check_sms_response($fp);

			// required!!! otherwise timeout!
			if (server_ping($fp, $rdata))
				continue;

			// server msg
			if (server_msg_307($fp, $rdata))
				continue;
			if (server_msg_318($fp, $rdata))
				continue;
			if (server_msg_352($fp, $rdata))
				continue;
			if (server_msg_401($fp, $rdata))
				continue;
			
			//Are they using a linked nick instead
			if(notice_nickserv_registered_user($fp, $rdata))
				continue;
			if(notice_nickserv_unknown_user($fp, $rdata))
				continue;

			// some nice things
			if (ctcp_version($fp, $rdata))
				continue;
			if (ctcp_finger($fp, $rdata))
				continue;
			if (ctcp_time($fp, $rdata))
				continue;
			if (ctcp_ping($fp, $rdata))
				continue;

			if (invite($fp, $rdata))
				continue;

			// join and part
			if (channel_join($fp, $rdata))
				continue;
			if (channel_part($fp, $rdata))
				continue;

			// nick change and quit
			if (user_nick($fp, $rdata))
				continue;
			if (user_quit($fp, $rdata))
				continue;

			if (channel_action_slap($fp, $rdata))
				continue;

			// channel msg (!xyz) without registration
			if (channel_msg_help($fp, $rdata))
				continue;
			if (channel_msg_seedlist($fp, $rdata))
				continue;
			if (channel_msg_op($fp, $rdata))
				continue;
			if (channel_msg_timer($fp, $rdata))
				continue;
			if (channel_msg_8ball($fp, $rdata, $answers))
				continue;
			if (channel_msg_seen($fp, $rdata))
				continue;
			if (channel_msg_sd($fp, $rdata))
				continue;
			if (channel_msg_sms($fp, $rdata))
				continue;

			// channel msg (!xyz) with registration
			if (channel_msg_with_registration($fp, $rdata))
				continue;

			// MrSpock can use this to send commands as caretaker
			if (query_command($fp, $rdata))
				continue;

			// timeout detection!
			if ($last_ping < time() - 300) {
				echo_r('TIMEOUT detected!');
				break;
			}


			// debug
			if ($debugging && strlen($rdata) > 0) {
				echo_r('[UNKNOWN] ' . $rdata);
				continue;
			}

		}

		fclose($fp); // close socket

	} else {

		// network troubles
		echo_r('There was an error connecting to ' . $address . '/' . $port);

		// sleep and try again!
		sleep(60);

	}

} // end of while running

?>
