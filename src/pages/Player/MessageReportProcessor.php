<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Database;
use Smr\Epoch;
use Smr\Page\PlayerPageProcessor;
use Smr\Player;

class MessageReportProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $folderID,
		private readonly int $messageID,
	) {}

	public function build(Player $player): never {
		$container = new MessageView($this->folderID);

		// get next id
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT IFNULL(max(notify_id)+1, 0) as next_notify_id FROM message_notify WHERE game_id = :game_id ORDER BY notify_id DESC', [
			'game_id' => $db->escapeNumber($player->getGameID()),
		]);
		$notify_id = $dbResult->record()->getInt('next_notify_id');

		// get message form db
		$dbResult = $db->select(
			'message',
			[
				'receiver_delete' => $db->escapeBoolean(false),
				'message_id' => $this->messageID,
				'player_id' => $player->getPlayerID(),
			],
			['player_id', 'sender_player_id', 'message_text', 'send_time'],
		);
		if (!$dbResult->hasRecord()) {
			create_error('Could not find the message you selected!');
		}
		$dbRecord = $dbResult->record();

		// insert
		$db->insert('message_notify', [
			'notify_id' => $notify_id,
			'game_id' => $player->getGameID(),
			'from_player_id' => $dbRecord->getInt('sender_player_id'),
			'to_player_id' => $dbRecord->getInt('player_id'),
			'text' => $dbRecord->getString('message_text'),
			'sent_time' => $dbRecord->getInt('send_time'),
			'notify_time' => Epoch::time(),
		]);

		$container->go();
	}

}
