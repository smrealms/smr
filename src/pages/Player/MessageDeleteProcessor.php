<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Exception;
use Smr\AbstractPlayer;
use Smr\Database;
use Smr\Page\PlayerPageProcessor;
use Smr\Request;

class MessageDeleteProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $folderID,
	) {}

	public function build(AbstractPlayer $player): never {
		$db = Database::getInstance();

		// If not deleting marked messages, we are deleting entire folders
		if (Request::get('action') === 'All Messages') {
			$container = new MessageBoxDeleteProcessor($this->folderID);
			$container->go();
		}

		if (!Request::has('message_id') && !Request::has('group_id')) {
			create_error('You must choose the messages you want to delete.');
		}

		// Delete any individually selected messages
		$message_id_list = Request::getIntArray('message_id', []);
		if (count($message_id_list) > 0) {
			if ($this->folderID === MSG_SENT) {
				$db->write('UPDATE message SET sender_delete = :sender_delete WHERE message_id IN (:message_ids)', [
					'sender_delete' => $db->escapeBoolean(true),
					'message_ids' => $db->escapeArray($message_id_list),
				]);
			} else {
				$db->write('UPDATE message SET receiver_delete = :receiver_delete WHERE message_id IN (:message_ids)', [
					'receiver_delete' => $db->escapeBoolean(true),
					'message_ids' => $db->escapeArray($message_id_list),
				]);
			}
		}

		// Delete any scout message groups
		foreach (Request::getArray('group_id', []) as $groupID) {
			$decoded = base64_decode($groupID, true);
			if ($decoded === false) {
				throw new Exception('Unexpected encoded group ID: ' . $groupID);
			}
			[$senderID, $minTime, $maxTime] = unserialize($decoded);
			if (!is_int($senderID) || !is_int($minTime) || !is_int($maxTime)) {
				throw new Exception('Unexpected deserialized types: ' . $decoded);
			}
			$db->write('UPDATE message SET receiver_delete = :receiver_delete_new
						WHERE sender_id = :sender_id
						AND ' . AbstractPlayer::SQL . '
						AND send_time >= :min_time
						AND send_time <= :max_time
						AND message_type_id = :message_type_id
						AND receiver_delete = :receiver_delete_old', [
				'receiver_delete_new' => $db->escapeBoolean(true),
				'sender_id' => $db->escapeNumber($senderID),
				...$player->SQLID,
				'min_time' => $db->escapeNumber($minTime),
				'max_time' => $db->escapeNumber($maxTime),
				'message_type_id' => $db->escapeNumber(MSG_SCOUT),
				'receiver_delete_old' => $db->escapeBoolean(false),
			]);
		}

		$container = new MessageView($this->folderID);
		$container->go();
	}

}
