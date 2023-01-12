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
			$db->write('UPDATE message SET sender_delete = ' . $db->escapeBoolean(true) . '
						WHERE sender_id = ' . $db->escapeNumber($player->getAccountID()) . '
							AND game_id = ' . $db->escapeNumber($player->getGameID()));
		} else {
			$db->write('UPDATE message SET receiver_delete = ' . $db->escapeBoolean(true) . '
						WHERE account_id = ' . $db->escapeNumber($player->getAccountID()) . '
							AND game_id = ' . $db->escapeNumber($player->getGameID()) . '
							AND message_type_id = ' . $db->escapeNumber($this->folderID) . '
							AND msg_read = ' . $db->escapeBoolean(true));
		}

		(new MessageBox())->go();
	}

}
