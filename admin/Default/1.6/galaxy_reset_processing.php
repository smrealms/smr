<?php

$galaxy = SmrGalaxy::getGalaxy($var['game_id'], $var['gal_on']);

// Efficiently construct the caches before proceeding
$galaxy->getPorts();
$galaxy->getPlanets();
$galaxy->getLocations();

$galaxy->setConnectivity(100);

foreach ($galaxy->getSectors() as $galSector) {
	if ($galSector->hasPort()) {
		$galSector->removePort();
	}

	if ($galSector->hasPlanet()) {
		$galSector->removePlanet();
	}

	if ($galSector->hasLocation()) {
		$galSector->removeAllLocations();
	}

	if ($galSector->hasWarp()) {
		$galSector->removeWarp();
	}
}

SmrSector::saveSectors();

$container = create_container('skeleton.php', '1.6/universe_create_sectors.php');
transfer('game_id');
transfer('gal_on');
$container['message'] = '<span class="green">Success</span> : reset galaxy.';
forward($container);
