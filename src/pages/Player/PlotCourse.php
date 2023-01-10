<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use AbstractSmrPlayer;
use Menu;
use Smr\HardwareType;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\PlotGroup;
use Smr\Session;
use Smr\Template;
use SmrLocation;
use SmrShipType;
use SmrWeaponType;

class PlotCourse extends PlayerPage {

	use ReusableTrait;

	public string $file = 'course_plot.php';

	public function build(AbstractSmrPlayer $player, Template $template): void {
		$session = Session::getInstance();

		$template->assign('PageTopic', 'Plot A Course');

		Menu::navigation($player);

		$container = new PlotCourseConventionalProcessor();
		$template->assign('PlotCourseFormLink', $container->href());

		$container = new PlotCourseNearestProcessor();
		$template->assign('PlotNearestFormLink', $container->href());

		if ($player->getShip()->hasJump()) {
			$container = new SectorJumpProcessor();
			$template->assign('JumpDriveFormLink', $container->href());
		}

		$container = new self();
		$template->assign('PlotToNearestHREF', $container->href());

		$xtype = $session->getRequestVar('xtype', PlotGroup::Technology->value);
		$xtype = PlotGroup::from($xtype);
		$template->assign('XType', $xtype);
		$template->assign('AllXTypes', PlotGroup::cases());

		$options = [];
		switch ($xtype) {
			case PlotGroup::Technology:
				$hardwares = HardwareType::getAll();
				foreach ($hardwares as $hardware) {
					$options[$hardware->typeID] = $hardware->name;
				}
				break;

			case PlotGroup::Ships:
				$ships = SmrShipType::getAll();
				foreach ($ships as $ship) {
					$options[$ship->getTypeID()] = $ship->getName();
				}
				asort($options); // sort by ship name
				break;

			case PlotGroup::Weapons:
				$weapons = SmrWeaponType::getAllSoldWeaponTypes($player->getGameID());
				foreach ($weapons as $weapon) {
					$options[$weapon->getWeaponTypeID()] = $weapon->getName();
				}
				asort($options); // sort by weapon name
				break;

			case PlotGroup::Locations:
				$locations = SmrLocation::getAllLocations($player->getGameID());
				foreach ($locations as $location) {
					$options[$location->getTypeID()] = $location->getName();
				}
				asort($options); // sort by location name

				// prefix location collections
				$options = [
					'Bank' => 'Any Bank',
					'Bar' => 'Any Bar',
					'SafeFed' => 'Any Safe Fed',
					'HQ' => 'Any Headquarters',
					'UG' => 'Any Underground',
					'Hardware' => 'Any Hardware Shop',
					'Ship' => 'Any Ship Shop',
					'Weapon' => 'Any Weapon Shop',
				] + $options;
				break;

			case PlotGroup::SellGoods:
			case PlotGroup::BuyGoods:
				$goods = $player->getVisibleGoods();
				foreach ($goods as $goodID => $good) {
					$options[$goodID] = $good->name;
				}
				break;

			case PlotGroup::Galaxies:
				foreach ($player->getGame()->getGalaxies() as $galaxy) {
					$options[$galaxy->getGalaxyID()] = $galaxy->getDisplayName();
				}
				break;
		}
		$template->assign('XTypeOptions', $options);

		// get saved destinations
		$template->assign('StoredDestinations', $player->getStoredDestinations());
		$container = new PlotCourseDestinationProcessor();
		$template->assign('ManageDestination', $container->href());
	}

}
