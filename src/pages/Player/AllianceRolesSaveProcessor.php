<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Database;
use Smr\Page\PlayerPageProcessor;
use Smr\Player;
use Smr\Request;

class AllianceRolesSaveProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $allianceID,
	) {}

	public function build(Player $player): never {
		foreach (Request::getIntArray('role', []) as $playerID => $roleID) {
			$db = Database::getInstance();
			$db->replace('player_has_alliance_role', [
				'player_id' => $playerID,
				'game_id' => $player->getGameID(),
				'role_id' => $roleID,
				'alliance_id' => $this->allianceID,
			]);
		}

		$container = new AllianceRoster($this->allianceID, true);
		$container->go();
	}

}
