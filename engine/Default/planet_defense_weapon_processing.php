<?php declare(strict_types=1);
if (!$player->isLandedOnPlanet()) {
	create_error('You are not on a planet!');
}

$planet = $player->getSectorPlanet();

if (Request::has('transfer')) {
	$planetOrderID = Request::getInt('transfer');
	if ($planet->hasMountedWeapon($planetOrderID)) {
		create_error('The planet already has a weapon mounted there!');
	}
	// transfer weapon to planet
	if (!Request::has('ship_order' . $planetOrderID)) {
		create_error('You must select a weapon to transfer!');
	}
	$shipOrderID = Request::getInt('ship_order' . $planetOrderID);
	$weaponTypeID = $ship->getWeapons()[$shipOrderID]->getWeaponTypeID();
	$planet->addMountedWeapon($weaponTypeID, $planetOrderID);
	$ship->removeWeapon($shipOrderID);
} elseif (Request::has('destroy')) {
	// Destroy the weapon on the planet
	$planet->removeMountedWeapon(Request::getInt('destroy'));
} elseif (Request::has('move_up')) {
	$planet->moveMountedWeaponUp(Request::getInt('move_up'));
} elseif (Request::has('move_down')) {
	$planet->moveMountedWeaponDown(Request::getInt('move_down'));
}

forward(create_container('skeleton.php', 'planet_defense.php'));
