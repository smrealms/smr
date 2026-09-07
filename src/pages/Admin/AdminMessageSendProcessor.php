<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Database;
use Smr\Epoch;
use Smr\Html\Submit;
use Smr\Page\AccountPageProcessor;
use Smr\Player;
use Smr\Request;

class AdminMessageSendProcessor extends AccountPageProcessor {

	private const string ACTION = 'action';

	public readonly Submit $actionPreview;
	public readonly Submit $actionSend;

	public function __construct(
		private readonly int $sendGameID,
	) {
		$this->actionPreview = new Submit(self::ACTION, 'preview');
		$this->actionSend = new Submit(self::ACTION, 'send');
	}

	public function build(Account $account): never {
		$message = Request::get('message');
		$expire = Request::getFloat('expire');
		$game_id = $this->sendGameID;

		$action = Request::get(self::ACTION);
		if ($action === $this->actionPreview->value) {
			if ($game_id !== AdminMessageSend::ALL_GAMES_ID) {
				$sendPlayerID = Request::getInt('player_id');
			} else {
				$sendPlayerID = 0;
			}
			$container = new AdminMessageSend(
				sendGameID: $game_id,
				preview: $message,
				expireHours: $expire,
				sendPlayerID: $sendPlayerID,
			);
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
			$playerID = Request::getInt('player_id');
			if ($playerID === 0) {
				// Send to all players in the requested game
				$dbResult = $db->select('player', ['game_id' => $game_id], ['player_id']);
				foreach ($dbResult->records() as $dbRecord) {
					$receivers[] = $dbRecord->getInt('player_id');
				}
			} else {
				$receivers[] = $playerID;
			}
		} else {
			//send to all players in games that haven't ended yet
			$dbResult = $db->read('SELECT player_id FROM player JOIN game USING(game_id) WHERE end_time > :now', [
				'now' => $db->escapeNumber(Epoch::time()),
			]);
			foreach ($dbResult->records() as $dbRecord) {
				$receivers[] = $dbRecord->getInt('player_id');
			}
		}
		// Send the messages
		foreach ($receivers as $receiverPlayerID) {
			Player::sendMessageFromAdmin(
				receiverPlayerID: $receiverPlayerID,
				message: $message,
				expires: $expire,
			);
		}
		$msg = '<span class="green">SUCCESS: </span>Your message has been sent.';

		$container = new AdminTools($msg);
		$container->go();
	}

}
