<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Database;
use Smr\Epoch;
use Smr\Page\PlayerPageProcessor;
use Smr\Request;

class MessageReportProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $folderID,
		private readonly int $messageID
	) {}

	public function build(AbstractPlayer $player): never {
		$container = new MessageView($this->folderID);

		if (Request::getBool('action') === false) {
			$container->go();
		}

		// get next id
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT IFNULL(max(notify_id)+1, 0) as next_notify_id FROM message_notify WHERE game_id = :game_id ORDER BY notify_id DESC', [
			'game_id' => $db->escapeNumber($player->getGameID()),
		]);
		$notify_id = $dbResult->record()->getInt('next_notify_id');

		// get message form db
		$dbResult = $db->read('SELECT account_id, sender_id, message_text
					FROM message
					WHERE message_id = :message_id AND receiver_delete = \'FALSE\'', [
			'message_id' => $this->messageID,
		]);
		if (!$dbResult->hasRecord()) {
			create_error('Could not find the message you selected!');
		}
		$dbRecord = $dbResult->record();

		// insert
		$db->insert('message_notify', [
			'notify_id' => $db->escapeNumber($notify_id),
			'game_id' => $db->escapeNumber($player->getGameID()),
			'from_id' => $dbRecord->getInt('sender_id'),
			'to_id' => $dbRecord->getInt('account_id'),
			'text' => $db->escapeString($dbRecord->getString('message_text')),
			'sent_time' => $db->escapeNumber($dbRecord->getInt('send_time')),
			'notify_time' => $db->escapeNumber(Epoch::time()),
		]);

		$container->go();
	}

}
