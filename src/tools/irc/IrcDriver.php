<?php declare(strict_types=1);

namespace Smr\Irc;

use Exception;
use Smr\Database;
use Smr\Irc\Exceptions\Timeout;

final class IrcDriver {

	private TimerState $timerState;
	private SupplyDemandState $supplyDemandState;
	private int $lastPing;

	public function __construct(private readonly bool $debugging) {
		$this->timerState = new TimerState();
		$this->supplyDemandState = new SupplyDemandState();
	}

	public function run(): never {
		while (true) {
			// Delete all seen stats that appear to be on. We do not want to take
			// something for granted that happened while we were away.
			$db = Database::getInstance();
			$db->delete('irc_seen', [
				'signed_off' => 0,
			]);

			echo_r('Connecting to ' . IRC_BOT_SERVER_ADDRESS);
			$fp = fsockopen(IRC_BOT_SERVER_ADDRESS, IRC_BOT_SERVER_PORT);

			if ($fp === false) {
				// Network troubles. Sleep and try again.
				echo_r('There was an error connecting to ' . IRC_BOT_SERVER_ADDRESS . '/' . IRC_BOT_SERVER_PORT);
				sleep(60);
				continue;
			}

			echo_r('Socket ' . $fp . ' is connected...');

			try {
				$this->runConnection($fp, $db);
			} catch (Timeout) {
				// Ignore the timeout exception, then loop round and reconnect.
			} finally {
				fclose($fp);
			}
		}
	}

	/**
	 * @param resource $fp
	 */
	private function runConnection($fp, Database $db): void {
		// Reset last ping each time we try connecting.
		$this->lastPing = time();

		echo_r('Identifying...');
		$this->safeWrite($fp, 'NICK ' . IRC_BOT_NICK . EOL);
		$this->safeWrite($fp, 'USER ' . IRC_BOT_USER . EOL);

		sleep(3);

		$this->safeWrite($fp, 'NICKSERV IDENTIFY ' . IRC_BOT_PASS . EOL);

		sleep(5);

		if (!$this->debugging) {
			// Join our public channels.
			$joinChannels = ['#smr', '#smr-bar'];

			// Join all alliance channels.
			$dbResult = $db->read('SELECT channel
						FROM irc_alliance_has_channel
						JOIN game USING (game_id)
						WHERE join_time < :now
							AND end_time > :now', [
				'now' => time(),
			]);
			foreach ($dbResult->records() as $dbRecord) {
				$joinChannels[] = $dbRecord->getString('channel');
			}

			// Now do the actual joining.
			foreach ($joinChannels as $channel) {
				$this->safeWrite($fp, 'JOIN ' . $channel . EOL);
				sleep(1);
				$this->safeWrite($fp, 'WHO ' . $channel . EOL);
			}
		}

		stream_set_blocking($fp, true);
		while (!feof($fp)) {
			$this->readFromStream($fp);
			// Close database connection between calls to avoid stale or timed out
			// server errors.
			Database::resetInstance();
		}
	}

	/**
	 * @param resource $fp
	 */
	private function safeWrite($fp, string $text): void {
		stream_set_blocking($fp, false);
		while ($this->readFromStream($fp));
		fwrite($fp, $text);
		stream_set_blocking($fp, true);
	}

	/**
	 * @param resource $fp
	 */
	private function readFromStream($fp): bool {
		// Timeout detection!
		if ($this->lastPing < time() - 300) {
			echo_r('TIMEOUT detected!');
			throw new Timeout();
		}

		$this->sendExpiredTimerEvents($fp);

		// Try to get message from the server.
		$rdata = fgets($fp, 4096);
		if ($rdata === false) { // No message or error.
			return false;
		}
		$rdata = preg_replace('/\s+/', ' ', $rdata);
		if ($rdata === null) {
			throw new Exception('An error occurred in preg_replace!');
		}

		if ($this->handleServerPing($fp, $rdata)) {
			return true;
		}

		// Server message.
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

		// Are they using a linked nick instead?
		if (notice_nickserv_registered_user($fp, $rdata)) {
			return true;
		}
		if (notice_nickserv_unknown_user($fp, $rdata)) {
			return true;
		}

		// Some nice things.
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

		// Join and part.
		if (channel_join($fp, $rdata)) {
			return true;
		}
		if (channel_part($fp, $rdata)) {
			return true;
		}

		// Nick change and quit.
		if (user_nick($rdata)) {
			return true;
		}
		if (user_quit($rdata)) {
			return true;
		}

		if (preg_match('/^:(?P<nick>.*)!(?P<user>.*)@(?P<host>.*)\sPRIVMSG\s(?P<channel>.*)\s:(?P<text>.*)/i', $rdata, $args) === 1) {
			$msg = new Message(
				nick: $args['nick'],
				user: $args['user'],
				host: $args['host'],
				channel: $args['channel'],
				text: strtolower(trim($args['text'])),
			);

			if (channel_action_slap($fp, $msg)) {
				return true;
			}

			// Channel message (!xyz) without registration.
			if (channel_msg_help($fp, $msg)) {
				return true;
			}
			if (channel_msg_seedlist($fp, $msg)) {
				return true;
			}
			if (channel_msg_op($fp, $msg)) {
				return true;
			}
			if (channel_msg_timer($fp, $msg, $this->timerState)) {
				return true;
			}
			if (channel_msg_8ball($fp, $msg)) {
				return true;
			}
			if (channel_msg_seen($fp, $msg)) {
				return true;
			}
			if (channel_msg_sd($fp, $msg)) {
				return true;
			}

			// Channel message (!xyz) with registration.
			if ($this->handleRegisteredChannelMessage($fp, $msg)) {
				return true;
			}
		}

		// MrSpock can use this to send commands as caretaker.
		if (query_command($fp, $rdata)) {
			return true;
		}

		// If here, we have some unhandled response (print and move on).
		echo_r('[UNKNOWN] ' . $rdata);
		return true;
	}

	/**
	 * Very important! If we do not answer the ping from the server, we will
	 * be disconnected.
	 *
	 * @param resource $fp
	 */
	private function handleServerPing($fp, string $rdata): bool {
		if (preg_match('/^PING\s:(.*)\s/i', $rdata, $msg) !== 1) {
			return false;
		}

		$server = $msg[1];

		// Remember the last time we got a ping from the server.
		$this->lastPing = time();

		// This message is very spammy.
		if (defined('IRC_BOT_VERBOSE_PING') && IRC_BOT_VERBOSE_PING) {
			echo_r('[PING] from ' . $server);
		}

		fwrite($fp, 'PONG ' . $server . EOL);
		return true;
	}

	/**
	 * @param resource $fp
	 */
	private function sendExpiredTimerEvents($fp): void {
		foreach ($this->timerState->takeExpired(time()) as $event) {
			echo_r('[TIMER] finished. Sending a note to ' . $event->channel);
			fwrite($fp, 'NOTICE ' . $event->channel . ' :' . $event->message . EOL);
		}
	}

	/**
	 * @param resource $fp
	 */
	private function handleRegisteredChannelMessage($fp, Message $msg): bool {
		if (preg_match('/^!(money|forces|seed|seedlist|op|sd)\s/i', $msg->text) !== 1) {
			return false;
		}

		$nick = $msg->nick;
		$channel = $msg->channel;

		// Disallow these queries in public channels
		if ($channel === '#smr' || $channel === '#smr-bar') {
			fwrite($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', that command can only be used in an alliance controlled channel.' . EOL);
			return true;
		}

		$callback = function() use ($fp, $msg): bool {
			return $this->handleRegisteredChannelMessage($fp, $msg);
		};
		$player = check_for_registration($fp, $nick, $channel, $callback);
		if ($player === false) {
			return true;
		}

		if (channel_msg_money($fp, $msg, $player)) {
			return true;
		}
		if (channel_msg_forces($fp, $msg, $player)) {
			return true;
		}
		if (channel_msg_seed($fp, $msg, $player)) {
			return true;
		}
		if (channel_msg_seedlist_add($fp, $msg, $player)) {
			return true;
		}
		if (channel_msg_seedlist_del($fp, $msg, $player)) {
			return true;
		}
		if (channel_msg_op_info($fp, $msg, $player)) {
			return true;
		}
		if (channel_msg_op_cancel($fp, $msg, $player)) {
			return true;
		}
		if (channel_msg_op_set($fp, $msg, $player)) {
			return true;
		}
		if (channel_msg_op_turns($fp, $msg, $player)) {
			return true;
		}
		if (channel_msg_op_response($fp, $msg, $player)) {
			return true;
		}
		if (channel_msg_op_list($fp, $msg, $player)) {
			return true;
		}
		if (channel_msg_sd_set($fp, $msg, $this->supplyDemandState)) {
			return true;
		}
		if (channel_msg_sd_del($fp, $msg, $this->supplyDemandState)) {
			return true;
		}
		return channel_msg_sd_list($fp, $msg, $player, $this->supplyDemandState);
	}

}
