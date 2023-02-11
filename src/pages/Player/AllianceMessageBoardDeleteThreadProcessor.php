<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Database;
use Smr\Page\PlayerPageProcessor;
use Smr\Page\ReusableTrait;

class AllianceMessageBoardDeleteThreadProcessor extends PlayerPageProcessor {

	use ReusableTrait;

	public function __construct(
		private readonly int $allianceID,
		private readonly AllianceMessageBoard $lastPage,
		private readonly int $threadID
	) {}

	public function build(AbstractPlayer $player): never {
		$db = Database::getInstance();
		$sql = 'WHERE game_id = :game_id
				AND alliance_id = :alliance_id
				AND thread_id = :thread_id';
		$sqlParams = [
			'game_id' => $db->escapeNumber($player->getGameID()),
			'alliance_id' => $db->escapeNumber($this->allianceID),
			'thread_id' => $db->escapeNumber($this->threadID),
		];
		$db->write('DELETE FROM alliance_thread ' . $sql, $sqlParams);
		$db->write('DELETE FROM alliance_thread_topic ' . $sql, $sqlParams);
		$this->lastPage->go();
	}

}
