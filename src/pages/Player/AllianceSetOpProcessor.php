<?php declare(strict_types=1);

use Smr\Database;
use Smr\Request;

		$db = Database::getInstance();
		$session = Smr\Session::getInstance();
		$var = $session->getCurrentVar();
		$account = $session->getAccount();
		$player = $session->getPlayer();

		function error_on_page(string $error): never {
			$message = '<span class="bold red">ERROR:</span> ' . $error;
			Page::create('alliance_set_op.php', ['message' => $message])->go();
		}

		if (!empty($var['cancel'])) {
			// just get rid of op
			$db->write('DELETE FROM alliance_has_op WHERE alliance_id=' . $db->escapeNumber($player->getAllianceID()) . ' AND game_id=' . $db->escapeNumber($player->getGameID()));
			$db->write('DELETE FROM alliance_has_op_response WHERE alliance_id=' . $db->escapeNumber($player->getAllianceID()) . ' AND game_id=' . $db->escapeNumber($player->getGameID()));

			// Delete the announcement from alliance members message boxes
			$db->write('DELETE FROM message WHERE game_id=' . $db->escapeNumber($player->getGameID()) . ' AND sender_id=' . $db->escapeNumber(ACCOUNT_ID_OP_ANNOUNCE) . ' AND account_id IN (' . $db->escapeArray($player->getAlliance()->getMemberIDs()) . ')');

			// NOTE: for simplicity we don't touch `player_has_unread_messages` here,
			// so they may get an errant alliance message icon if logged in.
		} else {
			// schedule an op
			$date = Request::get('date');
			if (empty($date)) {
				error_on_page('You must specify a date for the operation!');
			}

			$time = strtotime($date);
			if ($time === false) {
				error_on_page('The specified date is not in a valid format.');
			}

			// add op to db
			$db->insert('alliance_has_op', [
				'alliance_id' => $db->escapeNumber($player->getAllianceID()),
				'game_id' => $db->escapeNumber($player->getGameID()),
				'time' => $db->escapeNumber($time),
			]);

			// Send an alliance message that expires at the time of the op.
			// Since the message is procedural, don't exclude this player.
			$message = $player->getBBLink() . ' has scheduled an operation for ' . date($account->getDateTimeFormat(), $time) . '. Navigate to your Alliance console to respond!';
			foreach ($player->getAlliance()->getMemberIDs() as $memberAccountID) {
				$player->sendMessageFromOpAnnounce($memberAccountID, $message, $time);
			}
		}

		Page::create('alliance_set_op.php')->go();
