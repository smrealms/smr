<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Database;
use Smr\Page\PlayerPageProcessor;

class AllianceMessageBoardDeleteReplyProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $allianceID,
		private readonly AllianceMessageBoardView $lastPage,
		private readonly int $threadID,
		private readonly int $replyID
	) {}

	public function build(AbstractPlayer $player): never {
		$db = Database::getInstance();
		$db->delete('alliance_thread', [
			'game_id' => $db->escapeNumber($player->getGameID()),
			'alliance_id' => $db->escapeNumber($this->allianceID),
			'thread_id' => $db->escapeNumber($this->threadID),
			'reply_id' => $db->escapeNumber($this->replyID),
		]);
		$this->lastPage->go();
	}

}
