<?php declare(strict_types=1);

$var = Smr\Session::getInstance()->getCurrentVar();

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

$container = Page::create('admin/unigen/universe_create_sectors.php');
$container->addVar('game_id');
$container->addVar('gal_on');
$container['message'] = '<span class="green">Success</span> : reset galaxy.';
$container->go();
