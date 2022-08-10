<?php declare(strict_types=1);

use Smr\PlanetTypes\PlanetType;

		$template = Smr\Template::getInstance();
		$session = Smr\Session::getInstance();
		$var = $session->getCurrentVar();

		$session->getRequestVarInt('gal_on');
		$template->assign('Galaxies', SmrGalaxy::getGameGalaxies($var['game_id']));

		$container = Page::create('admin/unigen/universe_create_planets.php');
		$container->addVar('game_id');
		$template->assign('JumpGalaxyHREF', $container->href());

		// Get a list of all available planet types
		$allowedTypes = [];
		foreach (array_keys(PlanetType::PLANET_TYPES) as $PlanetTypeID) {
			$allowedTypes[$PlanetTypeID] = PlanetType::getTypeInfo($PlanetTypeID)->name();
		}
		$template->assign('AllowedTypes', $allowedTypes);

		// Initialize all planet counts to zero
		$numberOfPlanets = [];
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
		$container = Page::create('admin/unigen/universe_create_save_processing.php', $var);
		$container['forward_to'] = 'admin/unigen/universe_create_sectors.php';
		$template->assign('CreatePlanetsFormHREF', $container->href());

		// HREF to cancel and return to the previous page
		$container = Page::create('admin/unigen/universe_create_sectors.php', $var);
		$template->assign('CancelHREF', $container->href());
