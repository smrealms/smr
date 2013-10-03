<?php
require_once(get_file_loc('Research.class.inc'));

$template->assign('PageTopic','Ship Dealer');

$shipsSold = array();

$research = new Research();
$gameResearch = $research->getGameResearch($player->getGameID());
$researchableShips = null;

if($gameResearch && $gameResearch['ship_research']){
    $researchableShips = $research->getGameResearchableShips($gameResearch['id']);

}

$db->query('SELECT ship_type_id FROM location
        JOIN location_sells_ships USING (location_type_id)
        WHERE sector_id = ' . $db->escapeNumber($player->getSectorID()) . '
            AND game_id = ' . $db->escapeNumber($player->getGameID()) . '
            AND location_type_id = '.$db->escapeNumber($var['LocationID']));


if ($db->getNumRows() > 0 ) {
	$container = create_container('skeleton.php','shop_ship.php');
	transfer('LocationID');

	while ($db->nextRecord()) {
        $shipTypeID = $db->getField('ship_type_id');
        $container['ship_id'] = $shipTypeID;
        // if research enabled, ensure that no ship is displayed that has not been researched
        if($researchableShips != null){
            $shipTypePresent = false;
            foreach($researchableShips AS $rs){
                if($rs['ship_type_id'] == $shipTypeID){
                    $shipTypePresent = true;
                    if($rs['alliance_id'] == $player->getAllianceID()){
                        $shipsSold[$shipTypeID] =& AbstractSmrShip::getBaseShip(Globals::getGameType($player->getGameID()),$shipTypeID);
                        $container['level_needed'] = $shipsSold[$shipTypeID]['Level'];
                        $shipsSoldHREF[$shipTypeID] = SmrSession::getNewHREF($container);
                        break;
                    }
                }
            }
            // ship_type in location and not researchable, display it
            if(!$shipTypePresent){
                $shipsSold[$shipTypeID] =& AbstractSmrShip::getBaseShip(Globals::getGameType($player->getGameID()),$shipTypeID);
                $container['level_needed'] = $shipsSold[$shipTypeID]['Level'];
                $shipsSoldHREF[$shipTypeID] = SmrSession::getNewHREF($container);
            }
        }else{
            $shipsSold[$shipTypeID] =& AbstractSmrShip::getBaseShip(Globals::getGameType($player->getGameID()),$shipTypeID);
            $container['level_needed'] = $shipsSold[$shipTypeID]['Level'];
            $shipsSoldHREF[$shipTypeID] = SmrSession::getNewHREF($container);
        }
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


?>
