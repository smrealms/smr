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
		$db->write('DELETE FROM alliance_thread
					WHERE game_id = :game_id
					AND alliance_id = :alliance_id
					AND thread_id = :thread_id
					AND reply_id = :reply_id', [
			'game_id' => $db->escapeNumber($player->getGameID()),
			'alliance_id' => $db->escapeNumber($this->allianceID),
			'thread_id' => $db->escapeNumber($this->threadID),
			'reply_id' => $db->escapeNumber($this->replyID),
		]);
		$this->lastPage->go();
	}

}
