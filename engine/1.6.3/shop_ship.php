<?php
$db2 = new SmrMySqlDatabase();
$template->assign('PageTopic','Ship Dealer');

$db->query('SELECT ship_type_id
	FROM location, location_sells_ships
	WHERE location.sector_id=' . $player->getSectorID() . '
	AND location.game_id=' . $player->getGameID() . '
	AND location_sells_ships.location_type_id = location.location_type_id');
$shipsSold = array();
if ($db->getNumRows() > 0 )
{
	$container = array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'shop_ship.php';

	while ($db->nextRecord())
	{
		$shipTypeID = $db->getField('ship_type_id');
		$shipsSold[$shipTypeID] =& AbstractSmrShip::getBaseShip(Globals::getGameType($player->getGameID()),$shipTypeID);
		$container['ship_id'] = $shipTypeID;
        $container['level_needed'] = $shipsSold[$shipTypeID]['Level'];
        $shipsSoldHREF[$shipTypeID] = SmrSession::get_new_href($container);
	}
}
$template->assign('ShipsSold',$shipsSold);
$template->assign('ShipsSoldHREF',$shipsSoldHREF);

if (isset($var['ship_id']))
{
	$compareShip = AbstractSmrShip::getBaseShip(Globals::getGameType($player->getGameID()),$var['ship_id']);
	$compareShip['Speed'] *= Globals::getGameSpeed($player->getGameID());
	
	$container = array();
	$container['url']		= 'shop_ship_processing.php';
	transfer('ship_id');
	$compareShip['BuyHREF'] = SmrSession::get_new_href($container);
	
	$template->assign('CompareShip',$compareShip);
}

?>
