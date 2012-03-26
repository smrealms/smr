<?php
$template->assign('PageTopic','Ship Dealer');

$db->query('SELECT ship_type_id FROM location
			JOIN location_sells_ships USING (location_type_id)
			WHERE sector_id = ' . $db->escapeNumber($player->getSectorID()) . '
				AND game_id = ' . $db->escapeNumber($player->getGameID()) . '
				AND location_type_id = '.$db->escapeNumber($var['LocationID']));
$shipsSold = array();
if ($db->getNumRows() > 0 ) {
	$container = create_container('skeleton.php','shop_ship.php');
	transfer('LocationID');

	while ($db->nextRecord()) {
		$shipTypeID = $db->getField('ship_type_id');
		$shipsSold[$shipTypeID] =& AbstractSmrShip::getBaseShip(Globals::getGameType($player->getGameID()),$shipTypeID);
		$container['ship_id'] = $shipTypeID;
		$container['level_needed'] = $shipsSold[$shipTypeID]['Level'];
		$shipsSoldHREF[$shipTypeID] = SmrSession::get_new_href($container);
	}
}
$template->assign('ShipsSold',$shipsSold);
$template->assign('ShipsSoldHREF',$shipsSoldHREF);

if (isset($var['ship_id'])) {
	$compareShip = AbstractSmrShip::getBaseShip(Globals::getGameType($player->getGameID()),$var['ship_id']);
	$compareShip['Speed'] *= Globals::getGameSpeed($player->getGameID());

	$container = create_container('shop_ship_processing.php');
	transfer('LocationID');
	transfer('ship_id');
	$compareShip['BuyHREF'] = SmrSession::get_new_href($container);

	$template->assign('CompareShip',$compareShip);
}

?>
