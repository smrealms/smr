<?php declare(strict_types=1);

namespace Smr\Pages\Player\Bar;

use Smr\Database;
use Smr\Page\PlayerPageProcessor;
use Smr\Player;
use Smr\Request;

class TalkToBartenderGossipProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $locationID,
	) {}

	public function build(Player $player): never {
		$gossip = Request::get('gossip_tell');
		if ($gossip !== '') {
			$db = Database::getInstance();
			$dbResult = $db->read('SELECT IFNULL(MAX(message_id)+1, 0) AS next_message_id FROM bar_tender WHERE game_id = :game_id', [
				'game_id' => $db->escapeNumber($player->getGameID()),
			]);
			$messageID = $dbResult->record()->getInt('next_message_id');

			$db->insert('bar_tender', [
				'game_id' => $player->getGameID(),
				'message_id' => $messageID,
				'message' => $gossip,
			]);
			$player->sendMessageToBox(BOX_BARTENDER, $gossip);

			$message = 'Huh, that\'s news to me...<br /><br />Got anything else to tell me?';
		} else {
			$message = 'So you\'re the tight-lipped sort, eh? No matter, no matter...<br /><br /><i>The bartender slowly scans the room with squinted eyes and then leans in close.</i><br /><br />Must be a sensational story you\'ve got there. Don\'t worry, I can keep a secret. What\'s on your mind?';
		}

		$container = new TalkToBartender($this->locationID, $message);
		$container->go();
	}

}
