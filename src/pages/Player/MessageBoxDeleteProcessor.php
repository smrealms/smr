<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Database;
use Smr\Page\PlayerPageProcessor;

class MessageBoxDeleteProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $folderID
	) {}

	public function build(AbstractPlayer $player): never {
		$db = Database::getInstance();

		if ($this->folderID == MSG_SENT) {
			$db->update(
				'message',
				['sender_delete' => $db->escapeBoolean(true)],
				[
					'sender_id' => $db->escapeNumber($player->getAccountID()),
					'game_id' => $db->escapeNumber($player->getGameID()),
				],
			);
		} else {
			$db->update(
				'message',
				['receiver_delete' => $db->escapeBoolean(true)],
				[
					'account_id' => $db->escapeNumber($player->getAccountID()),
					'game_id' => $db->escapeNumber($player->getGameID()),
					'message_type_id' => $db->escapeNumber($this->folderID),
					'msg_read' => $db->escapeBoolean(true),
				],
			);
		}

		(new MessageBox())->go();
	}

}
