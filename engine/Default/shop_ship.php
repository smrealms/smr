<?php declare(strict_types=1);

$location = SmrLocation::getLocation($var['LocationID']);
$template->assign('PageTopic', $location->getName());

$shipsSold = $location->getShipsSold();

// Move any locked ships to a separate array so that they can't be bought.
// Note: Only Raider-class ships and PSF can be locked.
$timeUntilUnlock = $player->getGame()->timeUntilShipUnlock();
$shipsUnavailable = [];
foreach ($shipsSold as $shipTypeID => $shipSold) {
	if ($timeUntilUnlock > 0 && ($shipSold['ShipClassID'] == SmrShip::SHIP_CLASS_RAIDER || $shipSold['ShipTypeID'] == SHIP_TYPE_PLANETARY_SUPER_FREIGHTER)) {
		$shipSold['TimeUntilUnlock'] = $timeUntilUnlock;
		$shipsUnavailable[] = $shipSold;
		unset($shipsSold[$shipTypeID]);
	}
}
$template->assign('ShipsUnavailable', $shipsUnavailable);

if ($shipsSold) {
	$container = create_container('skeleton.php', 'shop_ship.php');
	transfer('LocationID');

	foreach (array_keys($shipsSold) as $shipTypeID) {
		$container['ship_id'] = $shipTypeID;
		$shipsSoldHREF[$shipTypeID] = SmrSession::getNewHREF($container);
	}
}
$template->assign('ShipsSold', $shipsSold);
$template->assign('ShipsSoldHREF', $shipsSoldHREF);

if (isset($var['ship_id'])) {
	$compareShip = $shipsSold[$var['ship_id']];
	$compareShip['RealSpeed'] = $compareShip['Speed'] * $player->getGame()->getGameSpeed();
	$compareShip['Turns'] = round($player->getTurns() * $compareShip['Speed'] / $ship->getSpeed());

	$container = create_container('shop_ship_processing.php');
	transfer('LocationID');
	transfer('ship_id');
	$compareShip['BuyHREF'] = SmrSession::getNewHREF($container);

	$template->assign('CompareShip', $compareShip);
	$template->assign('TradeInValue', floor($ship->getCost() * SHIP_REFUND_PERCENT));
	$template->assign('TotalCost', $ship->getCostToUpgrade($compareShip['ShipTypeID']));
}
