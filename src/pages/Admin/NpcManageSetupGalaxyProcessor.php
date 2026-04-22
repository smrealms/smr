<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Alliance;
use Smr\Combat\Weapon\Weapon;
use Smr\Force;
use Smr\Galaxy;
use Smr\Page\AccountPageProcessor;
use Smr\PlanetTypes\PlanetType;
use Smr\Request;

class NpcManageSetupGalaxyProcessor extends AccountPageProcessor {

	public function __construct(
		private readonly int $selectedGameID,
	) {}

	public function build(Account $account): never {
		$galaxyID = Request::getInt('galaxy_id');
		$allianceID = Request::getInt('alliance_id');

		$galaxy = Galaxy::getGalaxy($this->selectedGameID, $galaxyID);
		$alliance = Alliance::getAlliance($allianceID, $this->selectedGameID);

		// Add forces to all non-Location/non-warp sectors for each member
		Force::getGalaxyForces($this->selectedGameID, $galaxyID); // populate cache (in case forces exist already)
		$expireTime = $alliance->getGame()->getEndTime(); // expire time only updates when forces are fired on (not bumped)
		// Note, this is very slow compared to doing all insertions in a single query.
		// However, that command would not be verifiable without some additional API
		// for multiple insertions.
		foreach ($alliance->getMembers(includeNpc: true) as $player) {
			foreach ($galaxy->getSectors() as $sector) {
				if ($sector->hasWarp()) {
					continue;
				}
				$force = Force::getForce(
					gameID: $this->selectedGameID,
					sectorID: $sector->getSectorID(),
					ownerID: $player->getAccountID(),
				);
				$force->setForcesToMax();
				$force->setExpire($expireTime);
			}
		}
		Force::saveForces();

		// Add Sentinel Outpost planet owned by leader
		$sectors = array_filter($galaxy->getSectors(), fn($sector) => !$sector->hasPlanet());
		$planetSector = array_rand_value($sectors);
		$planet = $planetSector->createPlanet(PlanetType::TYPE_OUTPOST, inhabitableTime: 0);
		$planet->setOwnerID($alliance->getLeaderID());
		$planet->setBuildingsToMax();
		$planet->setDefensesToMax();
		$weapons = [
			Weapon::getWeapon(WEAPON_TYPE_LASER),
			Weapon::getWeapon(WEAPON_TYPE_LASER),
			Weapon::getWeapon(WEAPON_TYPE_LASER),
			Weapon::getWeapon(WEAPON_TYPE_LARGE_PULSE_LASER),
			Weapon::getWeapon(WEAPON_TYPE_LARGE_PULSE_LASER),
			Weapon::getWeapon(WEAPON_TYPE_LARGE_PULSE_LASER),
			Weapon::getWeapon(WEAPON_TYPE_HUGE_PULSE_LASER),
			Weapon::getWeapon(WEAPON_TYPE_HUGE_PULSE_LASER),
			Weapon::getWeapon(WEAPON_TYPE_PLANETARY_PULSE_LASER),
			Weapon::getWeapon(WEAPON_TYPE_HELL_BLASTER),
		];
		foreach ($weapons as $orderID => $weapon) {
			$planet->addMountedWeapon($weapon, $orderID);
		}
		$planet->update();

		$message = '<span class="green">SUCCESS: </span> Set up galaxy ' . $galaxy->getDisplayName() . ' for alliance ' . $alliance->getAllianceDisplayName();
		(new NpcManage($this->selectedGameID, $message))->go();
	}

}
