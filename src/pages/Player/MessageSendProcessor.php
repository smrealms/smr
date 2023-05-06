<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Database;
use Smr\Page\PlayerPageProcessor;
use Smr\Request;

class MessageSendProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly ?int $receiverAccountID = null,
		private readonly ?int $allianceID = null
	) {}

	public function build(AbstractPlayer $player): never {
		$message = htmlentities(Request::get('message'), ENT_COMPAT, 'utf-8');

		if (Request::get('action') === 'Preview message') {
			if ($this->allianceID !== null) {
				$container = new AllianceBroadcast($this->allianceID, $message);
			} else {
				$container = new MessageSend($this->receiverAccountID, $message);
			}
			$container->go();
		}

		if (empty($message)) {
			create_error('You have to enter a message to send!');
		}

		if ($this->allianceID !== null) {
			$db = Database::getInstance();
			$dbResult = $db->read('SELECT account_id FROM player
						WHERE game_id = :game_id
						AND alliance_id = :alliance_id
						AND account_id != :account_id', [ //No limit in case they are over limit - ie NHA
				'game_id' => $db->escapeNumber($player->getGameID()),
				'alliance_id' => $this->allianceID,
				'account_id' => $db->escapeNumber($player->getAccountID()),
			]);
			foreach ($dbResult->records() as $dbRecord) {
				$player->sendMessage($dbRecord->getInt('account_id'), MSG_ALLIANCE, $message, false);
			}
			$player->sendMessage($player->getAccountID(), MSG_ALLIANCE, $message, true, false);
		} elseif ($this->receiverAccountID !== null) {
			$player->sendMessage($this->receiverAccountID, MSG_PLAYER, $message);
		} else {
			$player->sendGlobalMessage($message);
		}

		$msg = '<span class="green">SUCCESS: </span>Your message has been sent.';
		$container = new CurrentSector(message: $msg);
		$container->go();
	}

}
