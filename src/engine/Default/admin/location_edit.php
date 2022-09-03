<?php declare(strict_types=1);

use Smr\Request;

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();

$template->assign('ViewAllLocationsLink', Page::create('admin/location_edit.php')->href());

if (isset($var['location_type_id'])) {
	$location = SmrLocation::getLocation($var['location_type_id']);
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
	$template->assign('ShipTypes', SmrShipType::getAll());
	$template->assign('Weapons', SmrWeaponType::getAllWeaponTypes());
	$template->assign('AllHardware', Globals::getHardwareTypes());
} else {
	$template->assign('Locations', SmrLocation::getAllLocations());
}
