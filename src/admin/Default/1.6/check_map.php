<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$var = Smr\Session::getInstance()->getCurrentVar();

$game = SmrGame::getGame($var['game_id']);
$template->assign('PageTopic', 'Check Map : ' . $game->getDisplayName());

$container = Page::create('skeleton.php', '1.6/universe_create_sectors.php');
$container->addVar('game_id');
$container->addVar('gal_on');
$template->assign('BackHREF', $container->href());

$galaxies = SmrGalaxy::getGameGalaxies($var['game_id']);

// Check if any locations are missing
$existingLocs = [];
foreach ($galaxies as $galaxy) {
	foreach ($galaxy->getLocations() as $sectorLocs) {
		foreach (array_keys($sectorLocs) as $locID) {
			$existingLocs[$locID] = true;
		}
	}
}
$missingLocs = array_diff(array_keys(SmrLocation::getAllLocations()),
                          array_keys($existingLocs));
$missingLocNames = [];
foreach ($missingLocs as $locID) {
	$missingLocNames[] = SmrLocation::getLocation($locID)->getName();
}
$template->assign('MissingLocNames', $missingLocNames);

// Calculate the best trade routes for each galaxy
$tradeGoods = [GOODS_NOTHING => false];
foreach (array_keys(Globals::getGoods()) as $goodID) {
	$tradeGoods[$goodID] = true;
}
$tradeRaces = [RACE_NEUTRAL => true];
foreach (array_keys(Globals::getRaces()) as $raceID) {
	$tradeRaces[$raceID] = true;
}

$maxNumberOfPorts = 2;
$routesForPort = -1;
$numberOfRoutes = 1;
$maxDistance = 999;

$allGalaxyRoutes = [];
foreach (SmrGalaxy::getGameGalaxies($var['game_id']) as $galaxy) {
	$galaxy->getPorts(); // Efficiently construct the port cache
	$distances = Plotter::calculatePortToPortDistances($galaxy->getSectors(), $maxDistance, $galaxy->getStartSector(), $galaxy->getEndSector());
	$allGalaxyRoutes[$galaxy->getDisplayName()] = \Routes\RouteGenerator::generateMultiPortRoutes($maxNumberOfPorts, $galaxy->getSectors(), $tradeGoods, $tradeRaces, $distances, $routesForPort, $numberOfRoutes);
}
$template->assign('AllGalaxyRoutes', $allGalaxyRoutes);

$routeTypes = [
	\Routes\RouteGenerator::EXP_ROUTE => 'Experience',
	\Routes\RouteGenerator::MONEY_ROUTE => 'Profit',
];
$template->assign('RouteTypes', $routeTypes);

// Largest port sell multipliers per galaxy
$maxSellMultipliers = [];
foreach (SmrGalaxy::getGameGalaxies($var['game_id']) as $galaxy) {
	$max = [];
	foreach ($galaxy->getPorts() as $port) {
		foreach ($port->getSoldGoodIDs() as $goodID) {
			$distance = $port->getGoodDistance($goodID);
			if (empty($max) || $distance > $max['Distance']) {
				$max = [
					'Port' => $port,
					'GoodID' => $goodID,
					'Distance' => $distance,
				];
			}
		}
	}
	if (!empty($max)) {
		$output = $max['Distance'] . 'x ' . Globals::getGoodName($max['GoodID']) . ' at Port #' . $max['Port']->getSectorID() . ' (' . $max['Port']->getRaceName() . ')';
		$maxSellMultipliers[$galaxy->getDisplayName()] = $output;
	}
}
$template->assign('MaxSellMultipliers', $maxSellMultipliers);
