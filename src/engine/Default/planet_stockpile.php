<?php declare(strict_types=1);

require_once(LIB . 'Default/planet.inc.php');
planet_common();

$session = Smr\Session::getInstance();
$player = $session->getPlayer();
$planet = $player->getSectorPlanet();
$ship = $player->getShip();

$goodInfo = [];
foreach (Globals::getGoods() as $goodID => $good) {
	if (!$ship->hasCargo($goodID) && !$planet->hasStockpile($goodID)) {
		continue;
	}

	$container = Page::create('planet_stockpile_processing.php');
	$container['good_id'] = $goodID;

	$goodInfo[] = [
		'Name' => $good['Name'],
		'ImageLink' => $good['ImageLink'],
		'ShipAmount' => $ship->getCargo($goodID),
		'PlanetAmount' => $planet->getStockpile($goodID),
		'DefaultAmount' => min($ship->getCargo($goodID), $planet->getRemainingStockpile($goodID)),
		'HREF' => $container->href(),
	];
}

$template = Smr\Template::getInstance();
$template->assign('GoodInfo', $goodInfo);
