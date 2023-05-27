<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Database;
use Smr\Page\PlayerPageProcessor;

class MessageBoxDeleteProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $folderID,
	) {}

	public function build(AbstractPlayer $player): never {
		$db = Database::getInstance();

		if ($this->folderID === MSG_SENT) {
			$db->update(
				'message',
				['sender_delete' => $db->escapeBoolean(true)],
				[
					'sender_id' => $player->getAccountID(),
					'game_id' => $player->getGameID(),
				],
			);
		} else {
			$db->update(
				'message',
				['receiver_delete' => $db->escapeBoolean(true)],
				[
					'account_id' => $player->getAccountID(),
					'game_id' => $player->getGameID(),
					'message_type_id' => $this->folderID,
					'msg_read' => $db->escapeBoolean(true),
				],
			);
		}

		(new MessageBox())->go();
	}

}
