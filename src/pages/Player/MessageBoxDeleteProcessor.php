<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Database;
use Smr\Page\PlayerPageProcessor;
use Smr\Player;

class MessageBoxDeleteProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $folderID,
	) {}

	public function build(Player $player): never {
		$db = Database::getInstance();

		if ($this->folderID === MSG_SENT) {
			$db->update(
				'message',
				['sender_delete' => $db->escapeBoolean(true)],
				[
					'sender_player_id' => $player->getPlayerID(),
				],
			);
		} else {
			$db->update(
				'message',
				['receiver_delete' => $db->escapeBoolean(true)],
				[
					...$player->SQLID,
					'message_type_id' => $this->folderID,
					'msg_read' => $db->escapeBoolean(true),
				],
			);
		}

		new MessageBox()->go();
	}

}
