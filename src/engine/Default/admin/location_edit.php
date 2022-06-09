<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();

$template->assign('ViewAllLocationsLink', Page::create('admin/location_edit.php')->href());

if (isset($var['location_type_id'])) {
	$location = SmrLocation::getLocation($var['location_type_id']);
	if (Smr\Request::has('save')) {
		$addShipID = Smr\Request::getInt('add_ship_id');
		if ($addShipID != 0) {
			$location->addShipSold($addShipID);
		}
		$addWeaponID = Smr\Request::getInt('add_weapon_id');
		if ($addWeaponID != 0) {
			$location->addWeaponSold($addWeaponID);
		}
		$addHardwareID = Smr\Request::getInt('add_hardware_id');
		if ($addHardwareID != 0) {
			$location->addHardwareSold($addHardwareID);
		}

		foreach (Smr\Request::getIntArray('remove_ships', []) as $shipTypeID) {
			$location->removeShipSold($shipTypeID);
		}
		foreach (Smr\Request::getIntArray('remove_weapons', []) as $weaponTypeID) {
			$location->removeWeaponSold($weaponTypeID);
		}
		foreach (Smr\Request::getIntArray('remove_hardware', []) as $hardwareTypeID) {
			$location->removeHardwareSold($hardwareTypeID);
		}

		$location->setFed(Smr\Request::has('fed'));
		$location->setBar(Smr\Request::has('bar'));
		$location->setBank(Smr\Request::has('bank'));
		$location->setHQ(Smr\Request::has('hq'));
		$location->setUG(Smr\Request::has('ug'));
	}

	$template->assign('Location', $location);
	$template->assign('ShipTypes', SmrShipType::getAll());
	$template->assign('Weapons', SmrWeaponType::getAllWeaponTypes());
	$template->assign('AllHardware', Globals::getHardwareTypes());
} else {
	$template->assign('Locations', SmrLocation::getAllLocations());
}
