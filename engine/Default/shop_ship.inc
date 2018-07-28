<?php
$template->assign('PageTopic','Ship Dealer');

$shipsSold = array();
if ($db->getNumRows() > 0 ) {
	$container = create_container('skeleton.php','shop_ship.php');
	transfer('LocationID');

	while ($db->nextRecord()) {
		$shipTypeID = $db->getField('ship_type_id');
		$shipsSold[$shipTypeID] =& AbstractSmrShip::getBaseShip(Globals::getGameType($player->getGameID()),$shipTypeID);
		$container['ship_id'] = $shipTypeID;
		$container['level_needed'] = $shipsSold[$shipTypeID]['Level'];
		$shipsSoldHREF[$shipTypeID] = SmrSession::getNewHREF($container);
	}
}
$template->assign('ShipsSold',$shipsSold);
$template->assign('ShipsSoldHREF',$shipsSoldHREF);

if (isset($var['ship_id'])) {
	$compareShip = AbstractSmrShip::getBaseShip(Globals::getGameType($player->getGameID()),$var['ship_id']);
	$compareShip['RealSpeed'] = $compareShip['Speed'] * Globals::getGameSpeed($player->getGameID());
	$compareShip['Turns'] = round($player->getTurns()*$compareShip['Speed']/$ship->getSpeed());

	$container = create_container('shop_ship_processing.php');
	transfer('LocationID');
	transfer('ship_id');
	$compareShip['BuyHREF'] = SmrSession::getNewHREF($container);

	$template->assign('CompareShip',$compareShip);
}
