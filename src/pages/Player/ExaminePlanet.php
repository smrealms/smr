<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Database;
use Smr\Page\PlayerPage;
use Smr\Template;

class ExaminePlanet extends PlayerPage {

	public string $file = 'planet_examine.php';

	public function build(AbstractPlayer $player, Template $template): void {
		$template->assign('PageTopic', 'Examine Planet');

		$planet = $player->getSectorPlanet();
		$template->assign('ThisPlanet', $planet);

		$planetLand =
			!$planet->hasOwner()
			|| $planet->getOwner()->sameAlliance($player)
			|| $player->isObserver();

		if (!$planetLand) {
			// Only check treaties if we can't otherwise land.
			$ownerAllianceID = 0;
			if ($planet->hasOwner()) {
				$ownerAllianceID = $planet->getOwner()->getAllianceID();
			}
			$db = Database::getInstance();
			$dbResult = $db->read('
				SELECT 1
				FROM alliance_treaties
				WHERE (alliance_id_1 = :owner_alliance_id OR alliance_id_1 = :player_alliance_id)
				AND (alliance_id_2 = :owner_alliance_id OR alliance_id_2 = :player_alliance_id)
				AND game_id = :game_id
				AND planet_land = 1
				AND official = :official', [
				'owner_alliance_id' => $db->escapeNumber($ownerAllianceID),
				'player_alliance_id' => $db->escapeNumber($player->getAllianceID()),
				'game_id' => $db->escapeNumber($player->getGameID()),
				'official' => $db->escapeBoolean(true),
			]);
			$planetLand = $dbResult->hasRecord();
		}
		$template->assign('PlanetLand', $planetLand);

		if ($planetLand) {
			$eligibleAttackers = []; // no option to attack if we can land
		} else {
			$eligibleAttackers = $player->getSector()->getFightingTradersAgainstPlanet($player, $planet, allEligible: true);
		}
		$template->assign('VisiblePlayers', $eligibleAttackers);
		$template->assign('SectorPlayersLabel', 'Attackers');
	}

}
