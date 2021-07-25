<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$player = $session->getPlayer();

if (!$player->isLandedOnPlanet()) {
	create_error('You are not on a planet!');
}

$planet = $player->getSectorPlanet();

if (Smr\Request::has('transfer')) {
	$planetOrderID = Smr\Request::getInt('transfer');
	if ($planet->hasMountedWeapon($planetOrderID)) {
		create_error('The planet already has a weapon mounted there!');
	}
	// transfer weapon to planet
	if (!Smr\Request::has('ship_order' . $planetOrderID)) {
		create_error('You must select a weapon to transfer!');
	}
	$shipOrderID = Smr\Request::getInt('ship_order' . $planetOrderID);
	$ship = $player->getShip();
	$weapon = $ship->getWeapons()[$shipOrderID];
	$planet->addMountedWeapon($weapon, $planetOrderID);
	$ship->removeWeapon($shipOrderID);
} elseif (Smr\Request::has('destroy')) {
	// Destroy the weapon on the planet (but only if all mounts are filled)
	if (count($planet->getMountedWeapons()) != $planet->getMaxMountedWeapons()) {
		create_error('You can only destroy a mounted weapon once all mounts are filled!');
	}
	$planet->removeMountedWeapon(Smr\Request::getInt('destroy'));
} elseif (Smr\Request::has('move_up')) {
	$planet->moveMountedWeaponUp(Smr\Request::getInt('move_up'));
} elseif (Smr\Request::has('move_down')) {
	$planet->moveMountedWeaponDown(Smr\Request::getInt('move_down'));
}

Page::create('skeleton.php', 'planet_defense.php')->go();
