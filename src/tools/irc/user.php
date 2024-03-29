<?php declare(strict_types=1);

use Smr\Database;

function user_quit(string $rdata): bool {

	// :Fubar!Mibbit@coldfront-77C78B7B.dyn.optonline.net QUIT :Quit: http://www.mibbit.com ajax IRC Client
	if (preg_match('/^:(.*)!(.*)@(.*)\sQUIT\s:(.*)\s$/i', $rdata, $msg) === 1) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$quit_msg = $msg[4];

		echo_r('[QUIT] ' . $nick . '!' . $user . '@' . $host . ' stated ' . $quit_msg);

		// database object
		$db = Database::getInstance();

		$dbResult = $db->read('SELECT * FROM irc_seen WHERE nick = :nick', [
			'nick' => $db->escapeString($nick),
		]);

		// sign off all nicks
		foreach ($dbResult->records() as $dbRecord) {

			$seen_id = $dbRecord->getInt('seen_id');

			$db->update(
				'irc_seen',
				['signed_off' => time()],
				['seen_id' => $seen_id],
			);

		}

		return true;

	}

	return false;

}

/**
 * Someone changed his nick
 */
function user_nick(string $rdata): bool {

	if (preg_match('/^:(.*)!(.*)@(.*)\sNICK\s:(.*)\s$/i', $rdata, $msg) === 1) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$new_nick = $msg[4];

		echo_r('[NICK] ' . $nick . ' -> ' . $new_nick);

		// database object
		$db = Database::getInstance();

		$channel_list = [];

		// 'sign off' all active old_nicks (multiple channels)
		$dbResult = $db->read('SELECT * FROM irc_seen WHERE nick = :nick AND signed_off = 0', [
			'nick' => $db->escapeString($nick),
		]);
		foreach ($dbResult->records() as $dbRecord) {

			$seen_id = $dbRecord->getInt('seen_id');

			// remember channels where this nick was active
			$channel_list[] = $dbRecord->getString('channel');

			$db->update(
				'irc_seen',
				['signed_off' => time()],
				['seen_id' => $seen_id],
			);

		}

		// now sign in the new_nick in every channel
		foreach ($channel_list as $channel) {

			// 'sign in' the new nick
			$dbResult = $db->read('SELECT * FROM irc_seen WHERE nick = :nick AND channel = :channel', [
				'nick' => $db->escapeString($new_nick),
				'channel' => $db->escapeString($channel),
			]);

			if ($dbResult->hasRecord()) {
				// exiting nick?
				$seen_id = $dbResult->record()->getInt('seen_id');

				$db->update(
					'irc_seen',
					[
						'signed_on' => time(),
						'signed_off' => 0,
						'user' => $user,
						'host' => $host,
						'registered' => null,
					],
					['seen_id' => $seen_id],
				);

			} else {
				// new nick?
				$db->insert('irc_seen', [
					'nick' => $new_nick,
					'user' => $user,
					'host' => $host,
					'channel' => $channel,
					'signed_on' => time(),
				]);
			}

		}

		return true;

	}

	return false;

}
