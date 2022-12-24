<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use AbstractSmrPlayer;
use Smr\Database;
use Smr\Page\PlayerPageProcessor;

class AllianceMessageBoardDeleteReplyProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $allianceID,
		private readonly AllianceMessageBoardView $lastPage,
		private readonly int $threadID,
		private readonly ?int $replyID
	) {}

	public function build(AbstractSmrPlayer $player): never {
		$db = Database::getInstance();
		$db->write('DELETE FROM alliance_thread
					WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
					AND alliance_id = ' . $db->escapeNumber($this->allianceID) . '
					AND thread_id = ' . $db->escapeNumber($this->threadID) . '
					AND reply_id = ' . $db->escapeNumber($this->replyID));
		$this->lastPage->go();
	}

}