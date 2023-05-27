<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Database;
use Smr\Page\PlayerPageProcessor;
use Smr\Request;

class AllianceSetOpProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly bool $cancel = false,
	) {}

	public function build(AbstractPlayer $player): never {
		$db = Database::getInstance();
		$account = $player->getAccount();

		if ($this->cancel) {
			// just get rid of op
			$db->delete('alliance_has_op', [
				'alliance_id' => $player->getAllianceID(),
				'game_id' => $player->getGameID(),
			]);
			$db->delete('alliance_has_op_response', [
				'alliance_id' => $player->getAllianceID(),
				'game_id' => $player->getGameID(),
			]);

			// Delete the announcement from alliance members message boxes
			$db->write('DELETE FROM message WHERE game_id = :game_id AND sender_id = :sender_id AND account_id IN (:account_ids)', [
				'game_id' => $db->escapeNumber($player->getGameID()),
				'sender_id' => $db->escapeNumber(ACCOUNT_ID_OP_ANNOUNCE),
				'account_ids' => $db->escapeArray($player->getAlliance()->getMemberIDs()),
			]);

			// NOTE: for simplicity we don't touch `player_has_unread_messages` here,
			// so they may get an errant alliance message icon if logged in.
		} else {
			// schedule an op
			$date = Request::get('date');
			if ($date === '') {
				$this->error('You must specify a date for the operation!');
			}

			$time = strtotime($date);
			if ($time === false) {
				$this->error('The specified date is not in a valid format.');
			}

			// add op to db
			$db->insert('alliance_has_op', [
				'alliance_id' => $player->getAllianceID(),
				'game_id' => $player->getGameID(),
				'time' => $time,
			]);

			// Send an alliance message that expires at the time of the op.
			// Since the message is procedural, don't exclude this player.
			$message = $player->getBBLink() . ' has scheduled an operation for ' . date($account->getDateTimeFormat(), $time) . '. Navigate to your Alliance console to respond!';
			foreach ($player->getAlliance()->getMemberIDs() as $memberAccountID) {
				$player->sendMessageFromOpAnnounce($memberAccountID, $message, $time);
			}
		}

		(new AllianceSetOp())->go();
	}

	public function error(string $error): never {
		$message = '<span class="bold red">ERROR:</span> ' . $error;
		(new AllianceSetOp($message))->go();
	}

}
