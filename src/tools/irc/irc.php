<?php declare(strict_types=1);

function echo_r(string $message): void {
	echo date('Y-m-d H:i:s => ') . $message . EOL;
}

// config file
require_once(realpath(dirname(__FILE__)) . '/../../bootstrap.php');

// timer events
$events = [];

// supply/demand list
$sds = [];

// on<something> actions
$actions = [];

$debugging = false;
foreach ($argv as $arg) {
	if ($arg == '-debug') {
		$debugging = true;
	}
}
define('IRC_DEBUGGING', $debugging);

require_once(TOOLS . 'irc/stream.php');

// after a timeout we start over
while (true) {

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
				$joinChannels[] = $dbRecord->getString('channel');
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

	} catch (Smr\Irc\Exceptions\Timeout) {
		// Ignore the timeout exception, we'll loop round and reconnect.
	}
} // end of while running
