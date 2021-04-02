<?php declare(strict_types=1);

$galaxy = SmrGalaxy::getGalaxy($var['game_id'], $var['gal_on']);

// Efficiently construct the caches before proceeding
$galaxy->getPorts();
$galaxy->getPlanets();
$galaxy->getLocations();

$galaxy->setConnectivity(100);

// Remove all ports, planets, locations, and warps
foreach ($galaxy->getSectors() as $galSector) {
	$galSector->removeAllFixtures();
}

SmrSector::saveSectors();

$container = Page::create('skeleton.php', '1.6/universe_create_sectors.php');
$container->addVar('game_id');
$container->addVar('gal_on');
$container['message'] = '<span class="green">Success</span> : reset galaxy.';
$container->go();
