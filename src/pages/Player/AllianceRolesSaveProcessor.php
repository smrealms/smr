<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Database;
use Smr\Page\PlayerPageProcessor;
use Smr\Request;

class AllianceRolesSaveProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $allianceID,
	) {}

	public function build(AbstractPlayer $player): never {
		foreach (Request::getIntArray('role', []) as $accountID => $roleID) {
			$db = Database::getInstance();
			$db->replace('player_has_alliance_role', [
				'account_id' => $accountID,
				'game_id' => $player->getGameID(),
				'role_id' => $roleID,
				'alliance_id' => $this->allianceID,
			]);
		}

		$container = new AllianceRoster($this->allianceID, true);
		$container->go();
	}

}
