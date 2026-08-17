<?php declare(strict_types=1);

namespace Smr\Pages\Player\BetaFunctions;

use Smr\HardwareType;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Player;
use Smr\ShipType;
use Smr\Template;
use Smr\WeaponType;

class BetaFunctions extends PlayerPage {

	use ReusableTrait;
	public function build(Player $player, Template $template): void {
		if (!ENABLE_BETA) {
			create_error('Beta functions are disabled.');
		}

		$sector = $player->getSector();

		$template->pageTopic = 'Beta Functions';

		$shipList = [];
		foreach (ShipType::getAll() as $shipTypeID => $shipType) {
			$shipList[$shipTypeID] = $shipType->getName();
		}
		asort($shipList); // sort by name

		$weaponList = [];
		foreach (WeaponType::getAllWeaponTypes() as $weaponTypeID => $weaponType) {
			$weaponList[$weaponTypeID] = $weaponType->getName();
		}
		asort($weaponList); // sort by name

		$hardware = [];
		foreach (HardwareType::getAll() as $hardwareTypeID => $hardwareType) {
			$hardware[$hardwareTypeID] = $hardwareType->name;
		}

		if ($sector->hasPlanet()) {
			$maxBuildingsHREF = new PlanetBuildingsProcessor()->href();
			$maxDefensesHREF = new PlanetDefensesProcessor()->href();
			$maxStockpileHREF = new PlanetStockpileProcessor()->href();
		} else {
			$maxBuildingsHREF = null;
			$maxDefensesHREF = null;
			$maxStockpileHREF = null;
		}

		$template->pageRenderer = fn() => BetaFunctionsRenderer::render(
			MapHREF: new RevealMapProcessor()->href(),
			MoneyHREF: new AddMoneyProcessor()->href(),
			ShipHREF: new SetShipProcessor()->href(),
			ShipList: $shipList,
			AddWeaponHREF: new AddWeaponsProcessor()->href(),
			WeaponList: $weaponList,
			RemoveWeaponsHREF: new RemoveWeaponsProcessor()->href(),
			UnoHREF: new RepairShipProcessor()->href(),
			WarpHREF: new SetSectorProcessor()->href(),
			TurnsHREF: new SetTurnsProcessor()->href(),
			ExperienceHREF: new SetExperienceProcessor()->href(),
			AlignmentHREF: new SetAlignmentProcessor()->href(),
			HardwareHREF: new SetHardwareProcessor()->href(),
			Hardware: $hardware,
			PersonalRelationsHREF: new SetPersonalRelationsProcessor()->href(),
			RaceRelationsHREF: new SetPoliticalRelationsProcessor()->href(),
			ChangeRaceHREF: new SetRaceProcessor()->href(),
			MaxBuildingsHREF: $maxBuildingsHREF,
			MaxDefensesHREF: $maxDefensesHREF,
			MaxStockpileHREF: $maxStockpileHREF,
			ThisPlayer: $player,
			ThisSector: $player->getSector(),
		);
	}

}
