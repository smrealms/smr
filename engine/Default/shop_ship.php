<?php

$location = SmrLocation::getLocation($var['LocationID']);
$template->assign('PageTopic', $location->getName());

$shipsSold = $location->getShipsSold();
if ($shipsSold) {
	$container = create_container('skeleton.php','shop_ship.php');
	transfer('LocationID');

	foreach (array_keys($shipsSold) as $shipTypeID) {
		$container['ship_id'] = $shipTypeID;
		$shipsSoldHREF[$shipTypeID] = SmrSession::getNewHREF($container);
	}
}
$template->assign('ShipsSold',$shipsSold);
$template->assign('ShipsSoldHREF',$shipsSoldHREF);

if (isset($var['ship_id'])) {
	$compareShip = $shipsSold[$var['ship_id']];
	$compareShip['RealSpeed'] = $compareShip['Speed'] * Globals::getGameSpeed($player->getGameID());
	$compareShip['Turns'] = round($player->getTurns()*$compareShip['Speed']/$ship->getSpeed());

	$container = create_container('shop_ship_processing.php');
	transfer('LocationID');
	transfer('ship_id');
	$compareShip['BuyHREF'] = SmrSession::getNewHREF($container);

	$template->assign('CompareShip',$compareShip);
}
