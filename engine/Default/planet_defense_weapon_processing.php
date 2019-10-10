<?php
if (!$player->isLandedOnPlanet()) {
	create_error('You are not on a planet!');
}

$planet = $player->getSectorPlanet();

if (isset($_REQUEST['transfer'])) {
	$planetOrderID = $_REQUEST['transfer'];
	if ($planet->hasMountedWeapon($planetOrderID)) {
		create_error('The planet already has a weapon mounted there!');
	}
	// transfer weapon to planet
	if (!isset($_REQUEST['ship_order' . $planetOrderID])) {
		create_error('You must select a weapon to transfer!');
	}
	$shipOrderID = $_REQUEST['ship_order' . $planetOrderID];
	$weaponTypeID = $ship->getWeapons()[$shipOrderID]->getWeaponTypeID();
	$planet->addMountedWeapon($weaponTypeID, $planetOrderID);
	$ship->removeWeapon($shipOrderID);
} elseif (isset($_REQUEST['destroy'])) {
	// Destroy the weapon on the planet
	$planet->removeMountedWeapon($_REQUEST['destroy']);
} elseif (isset($_REQUEST['move_up'])) {
	$planet->moveMountedWeaponUp($_REQUEST['move_up']);
} elseif (isset($_REQUEST['move_down'])) {
	$planet->moveMountedWeaponDown($_REQUEST['move_down']);
}

forward(create_container('skeleton.php', 'planet_defense.php'));
