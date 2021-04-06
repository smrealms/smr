<?php declare(strict_types=1);

$session = SmrSession::getInstance();

$session->getRequestVarInt('gal_on');
$template->assign('Galaxies', SmrGalaxy::getGameGalaxies($var['game_id']));

$container = Page::create('skeleton.php', '1.6/universe_create_planets.php');
$container->addVar('game_id');
$template->assign('JumpGalaxyHREF', $container->href());

// Get a list of all available planet types
$allowedTypes = array();
foreach (array_keys(SmrPlanetType::PLANET_TYPES) as $PlanetTypeID) {
	$allowedTypes[$PlanetTypeID] = SmrPlanetType::getTypeInfo($PlanetTypeID)->name();
}
$template->assign('AllowedTypes', $allowedTypes);

// Initialize all planet counts to zero
$numberOfPlanets = array();
foreach (array_keys($allowedTypes) as $ID) {
	$numberOfPlanets[$ID] = 0;
}

// Get the current number of each type of planet
$galaxy = SmrGalaxy::getGalaxy($var['game_id'], $var['gal_on']);
foreach ($galaxy->getSectors() as $galSector) {
	if ($galSector->hasPlanet()) {
		$numberOfPlanets[$galSector->getPlanet()->getTypeID()]++;
	}
}

$template->assign('Galaxy', $galaxy);
$template->assign('NumberOfPlanets', $numberOfPlanets);

// Form to make planet changes
$container = Page::create('1.6/universe_create_save_processing.php',
                              '1.6/universe_create_sectors.php', $var);
$template->assign('CreatePlanetsFormHREF', $container->href());

// HREF to cancel and return to the previous page
$container = Page::create('skeleton.php', '1.6/universe_create_sectors.php', $var);
$template->assign('CancelHREF', $container->href());
