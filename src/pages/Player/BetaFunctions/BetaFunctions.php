<?php declare(strict_types=1);

namespace Smr\Pages\Player\BetaFunctions;

use AbstractSmrPlayer;
use Globals;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Template;
use SmrShipType;
use SmrWeaponType;

class BetaFunctions extends PlayerPage {

	use ReusableTrait;

	public string $file = 'beta_functions.php';

	public function build(AbstractSmrPlayer $player, Template $template): void {
		if (!ENABLE_BETA) {
			create_error('Beta functions are disabled.');
		}

		$sector = $player->getSector();

		$template->assign('PageTopic', 'Beta Functions');

		// let them map all
		$container = new RevealMapProcessor();
		$template->assign('MapHREF', $container->href());

		// let them get money
		$container = new AddMoneyProcessor();
		$template->assign('MoneyHREF', $container->href());

		//next time for ship
		$container = new SetShipProcessor();
		$template->assign('ShipHREF', $container->href());
		$shipList = [];
		foreach (SmrShipType::getAll() as $shipTypeID => $shipType) {
			$shipList[$shipTypeID] = $shipType->getName();
		}
		asort($shipList); // sort by name
		$template->assign('ShipList', $shipList);

		//next weapons
		$container = new AddWeaponsProcessor();
		$template->assign('AddWeaponHREF', $container->href());
		$weaponList = [];
		foreach (SmrWeaponType::getAllWeaponTypes() as $weaponTypeID => $weaponType) {
			$weaponList[$weaponTypeID] = $weaponType->getName();
		}
		asort($weaponList); // sort by name
		$template->assign('WeaponList', $weaponList);

		//Remove Weapons
		$container = new RemoveWeaponsProcessor();
		$template->assign('RemoveWeaponsHREF', $container->href());

		//allow to get full hardware
		$container = new RepairShipProcessor();
		$template->assign('UnoHREF', $container->href());

		//move whereever you want
		$container = new SetSectorProcessor();
		$template->assign('WarpHREF', $container->href());

		//set turns
		$container = new SetTurnsProcessor();
		$template->assign('TurnsHREF', $container->href());

		//set experience
		$container = new SetExperienceProcessor();
		$template->assign('ExperienceHREF', $container->href());

		//Set alignment
		$container = new SetAlignmentProcessor();
		$template->assign('AlignmentHREF', $container->href());

		//add any type of hardware
		$container = new SetHardwareProcessor();
		$template->assign('HardwareHREF', $container->href());
		$hardware = [];
		foreach (Globals::getHardwareTypes() as $hardwareTypeID => $hardwareType) {
			$hardware[$hardwareTypeID] = $hardwareType['Name'];
		}
		$template->assign('Hardware', $hardware);

		//change personal relations
		$container = new SetPersonalRelationsProcessor();
		$template->assign('PersonalRelationsHREF', $container->href());

		//change race relations
		$container = new SetPoliticalRelationsProcessor();
		$template->assign('RaceRelationsHREF', $container->href());

		//change race
		$container = new SetRaceProcessor();
		$template->assign('ChangeRaceHREF', $container->href());

		if ($sector->hasPlanet()) {
			$container = new PlanetBuildingsProcessor();
			$template->assign('MaxBuildingsHREF', $container->href());

			$container = new PlanetDefensesProcessor();
			$template->assign('MaxDefensesHREF', $container->href());

			$container = new PlanetStockpileProcessor();
			$template->assign('MaxStockpileHREF', $container->href());
		}
	}

}
