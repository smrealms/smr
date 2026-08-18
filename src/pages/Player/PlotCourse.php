<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\HardwareType;
use Smr\Location;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Player;
use Smr\PlotGroup;
use Smr\Session;
use Smr\ShipType;
use Smr\Template;
use Smr\WeaponType;

class PlotCourse extends PlayerPage {

	use ReusableTrait;
	public function build(Player $player, Template $template): void {
		$session = Session::getInstance();

		$template->pageTopic = 'Plot A Course';

		Menu::navigation($player);

		$jumpDrivePage = $player->getShip()->hasJump() ? new SectorJumpProcessor() : null;

		$xtype = $session->getRequestVar('xtype', PlotGroup::Technology->value);
		$xtype = PlotGroup::from($xtype);

		$options = [];
		switch ($xtype) {
			case PlotGroup::Technology:
				$hardwares = HardwareType::getAll();
				foreach ($hardwares as $hardware) {
					$options[$hardware->typeID] = $hardware->name;
				}
				break;

			case PlotGroup::Ships:
				$ships = ShipType::getAll();
				foreach ($ships as $ship) {
					$options[$ship->getTypeID()] = $ship->getName();
				}
				asort($options); // sort by ship name
				break;

			case PlotGroup::Weapons:
				$weapons = WeaponType::getAllSoldWeaponTypes($player->getGameID());
				foreach ($weapons as $weapon) {
					$options[$weapon->getWeaponTypeID()] = $weapon->getName();
				}
				asort($options); // sort by weapon name
				break;

			case PlotGroup::Locations:
				$locations = Location::getAllLocations($player->getGameID());
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

		$template->pageRenderer = fn() => PlotCourseRenderer::render(
			template: $template,
			PlotCourseFormLink: new PlotCourseConventionalProcessor()->href(),
			PlotNearestFormLink: new PlotCourseNearestProcessor()->href(),
			JumpDrivePage: $jumpDrivePage,
			PlotToNearestHREF: new self()->href(),
			XType: $xtype,
			AllXTypes: PlotGroup::cases(),
			XTypeOptions: $options,
			StoredDestinations: $player->getStoredDestinations(),
			ManageDestination: new PlotCourseDestinationProcessor()->href(),
			ThisPlayer: $player,
		);
	}

}
