<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Exception;
use Smr\AbstractPlayer;
use Smr\Database;
use Smr\Page\PlayerPageProcessor;
use Smr\Request;

class MessageDeleteProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $folderID
	) {}

	public function build(AbstractPlayer $player): never {
		$db = Database::getInstance();

		// If not deleting marked messages, we are deleting entire folders
		if (Request::get('action') == 'All Messages') {
			$container = new MessageBoxDeleteProcessor($this->folderID);
			$container->go();
		}

		if (!Request::has('message_id') && !Request::has('group_id')) {
			create_error('You must choose the messages you want to delete.');
		}

		// Delete any individually selected messages
		$message_id_list = Request::getIntArray('message_id', []);
		if (!empty($message_id_list)) {
			if ($this->folderID == MSG_SENT) {
				$db->write('UPDATE message SET sender_delete = ' . $db->escapeBoolean(true) . ' WHERE message_id IN (' . $db->escapeArray($message_id_list) . ')');
			} else {
				$db->write('UPDATE message SET receiver_delete = ' . $db->escapeBoolean(true) . ' WHERE message_id IN (' . $db->escapeArray($message_id_list) . ')');
			}
		}

		// Delete any scout message groups
		foreach (Request::getArray('group_id', []) as $groupID) {
			[$senderID, $minTime, $maxTime] = unserialize(base64_decode($groupID));
			if (!is_int($senderID) || !is_int($minTime) || !is_int($maxTime)) {
				throw new Exception('Unexpected deserialized types: ' . $groupID);
			}
			$db->write('UPDATE message SET receiver_delete = ' . $db->escapeBoolean(true) . '
						WHERE sender_id = ' . $db->escapeNumber($senderID) . '
						AND game_id = ' . $db->escapeNumber($player->getGameID()) . '
						AND send_time >= ' . $db->escapeNumber($minTime) . '
						AND send_time <= ' . $db->escapeNumber($maxTime) . '
						AND account_id = ' . $db->escapeNumber($player->getAccountID()) . '
						AND message_type_id = ' . $db->escapeNumber(MSG_SCOUT) . '
						AND receiver_delete = ' . $db->escapeBoolean(false));
		}

		$container = new MessageView($this->folderID);
		$container->go();
	}

}
