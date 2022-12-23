<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use AbstractSmrPlayer;
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

	public function build(AbstractSmrPlayer $player): never {
		$db = Database::getInstance();
		$db->write('DELETE FROM alliance_thread
						WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
						AND alliance_id = ' . $db->escapeNumber($this->allianceID) . '
						AND thread_id = ' . $db->escapeNumber($this->threadID));
		$db->write('DELETE FROM alliance_thread_topic
						WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
						AND alliance_id = ' . $db->escapeNumber($this->allianceID) . '
						AND thread_id = ' . $db->escapeNumber($this->threadID));
		$this->lastPage->go();
	}

}
