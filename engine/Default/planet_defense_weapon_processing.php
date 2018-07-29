<?php
if (!$player->isLandedOnPlanet())
	create_error('You are not on a planet!');

$planet = $player->getSectorPlanet();

$action = $_REQUEST['action'];
$planetOrderID = $_REQUEST['planet_order'];

if ($action == 'Transfer') {
	// transfer weapon to planet
	if (!isset($_REQUEST['ship_order'])) {
		create_error('You must select a weapon to transfer!');
	}
	$shipOrderID = $_REQUEST['ship_order'];
	$weaponTypeID = $ship->getWeapons()[$shipOrderID]->getWeaponTypeID();
	$planet->addMountedWeapon($weaponTypeID, $planetOrderID);
	$ship->removeWeapon($shipOrderID);
} elseif ($action == 'Destroy') {
	// Destroy the weapon on the planet
	$planet->removeMountedWeapon($planetOrderID);
}

forward(create_container('skeleton.php', 'planet_defense.php'));
