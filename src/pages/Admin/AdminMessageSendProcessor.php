<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Database;
use Smr\Epoch;
use Smr\Page\AccountPageProcessor;
use Smr\Player;
use Smr\Request;

class AdminMessageSendProcessor extends AccountPageProcessor {

	public function __construct(
		private readonly int $sendGameID,
	) {}

	public function build(Account $account): never {
		$message = Request::get('message');
		$expire = Request::getFloat('expire');
		$game_id = $this->sendGameID;

		if (Request::get('action') === 'Preview message') {
			if ($game_id !== AdminMessageSend::ALL_GAMES_ID) {
				$sendAccountID = Request::getInt('account_id');
			} else {
				$sendAccountID = 0;
			}
			$container = new AdminMessageSend($game_id, $message, $expire, $sendAccountID);
			$container->go();
		}

		$expire = IRound($expire * 3600); // convert hours to seconds
		// When expire==0, message will not expire
		if ($expire > 0) {
			$expire += Epoch::time();
		}

		$db = Database::getInstance();

		$receivers = [];
		if ($game_id !== AdminMessageSend::ALL_GAMES_ID) {
			$account_id = Request::getInt('account_id');
			if ($account_id === 0) {
				// Send to all players in the requested game
				$dbResult = $db->read('SELECT account_id FROM player WHERE game_id = :game_id', [
					'game_id' => $db->escapeNumber($game_id),
				]);
				foreach ($dbResult->records() as $dbRecord) {
					$receivers[] = [$game_id, $dbRecord->getInt('account_id')];
				}
			} else {
				$receivers[] = [$game_id, $account_id];
			}
		} else {
			//send to all players in games that haven't ended yet
			$dbResult = $db->read('SELECT game_id,account_id FROM player JOIN game USING(game_id) WHERE end_time > :now', [
				'now' => $db->escapeNumber(Epoch::time()),
			]);
			foreach ($dbResult->records() as $dbRecord) {
				$receivers[] = [$dbRecord->getInt('game_id'), $dbRecord->getInt('account_id')];
			}
		}
		// Send the messages
		foreach ($receivers as $receiver) {
			Player::sendMessageFromAdmin($receiver[0], $receiver[1], $message, $expire);
		}
		$msg = '<span class="green">SUCCESS: </span>Your message has been sent.';

		$container = new AdminTools($msg);
		$container->go();
	}

}
