<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\HardwareType;
use Smr\Location;
use Smr\Page\AccountPage;
use Smr\Request;
use Smr\ShipType;
use Smr\Template;
use Smr\WeaponType;

class EditLocations extends AccountPage {

	public string $file = 'admin/location_edit.php';

	public function __construct(
		private readonly ?int $locationTypeID = null
	) {}

	public function build(Account $account, Template $template): void {
		$template->assign('ViewAllLocationsLink', (new self())->href());

		// For the purposes of editing, the game ID doesn't matter (yet)
		$gameID = 0;

		if ($this->locationTypeID !== null) {
			$location = Location::getLocation($gameID, $this->locationTypeID);
			if (Request::has('save')) {
				$addShipID = Request::getInt('add_ship_id');
				if ($addShipID != 0) {
					$location->addShipSold($addShipID);
				}
				$addWeaponID = Request::getInt('add_weapon_id');
				if ($addWeaponID != 0) {
					$location->addWeaponSold($addWeaponID);
				}
				$addHardwareID = Request::getInt('add_hardware_id');
				if ($addHardwareID != 0) {
					$location->addHardwareSold($addHardwareID);
				}

				foreach (Request::getIntArray('remove_ships', []) as $shipTypeID) {
					$location->removeShipSold($shipTypeID);
				}
				foreach (Request::getIntArray('remove_weapons', []) as $weaponTypeID) {
					$location->removeWeaponSold($weaponTypeID);
				}
				foreach (Request::getIntArray('remove_hardware', []) as $hardwareTypeID) {
					$location->removeHardwareSold($hardwareTypeID);
				}

				$location->setFed(Request::has('fed'));
				$location->setBar(Request::has('bar'));
				$location->setBank(Request::has('bank'));
				$location->setHQ(Request::has('hq'));
				$location->setUG(Request::has('ug'));
			}

			$template->assign('Location', $location);
			$template->assign('ShipTypes', ShipType::getAll());
			$template->assign('Weapons', WeaponType::getAllWeaponTypes());
			$template->assign('AllHardware', HardwareType::getAll());
		} else {
			$template->assign('Locations', Location::getAllLocations($gameID));
		}
	}

}
