<?php
if (!$player->isLandedOnPlanet())
	create_error('You are not on a planet!');

// create planet object
$planet =& $player->getSectorPlanet();

$template->assign('PageTopic','Planet : '.$planet->getName().' [Sector #'.$planet->getSectorID().']');

require_once(get_file_loc('menu.inc'));
create_planet_menu($planet);

$goodInfo = array();
foreach (Globals::getGoods() as $goodID => $good) {
	if (!$ship->hasCargo($goodID) && !$planet->hasStockpile($goodID)) {
		continue;
	}

	$container = create_container('planet_stockpile_processing.php');
	$container['good_id'] = $goodID;

	$goodInfo[] = array(
		'Name' => $good['Name'],
		'ImageLink' => $good['ImageLink'],
		'ShipAmount' => $ship->getCargo($goodID),
		'PlanetAmount' => $planet->getStockpile($goodID),
		'DefaultAmount' => min($ship->getCargo($goodID), $planet->getRemainingStockpile($goodID)),
		'HREF' => SmrSession::getNewHREF($container),
	);
}

$template->assign('GoodInfo', $goodInfo);

?>
