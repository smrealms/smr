<?php

if ($var['func'] == 'Map') {
	$account_id = $player->getAccountID();
	$game_id = $player->getGameID();
	// delete all entries from the player_visited_sector/port table
	$db->query('DELETE FROM player_visited_sector WHERE account_id = ' . $db->escapeNumber($account_id) . ' AND game_id = ' . $db->escapeNumber($game_id));

	// add port infos
	$db->query('SELECT * FROM port WHERE game_id = ' . $db->escapeNumber($game_id));
	while ($db->nextRecord()) {
		$port = SmrPort::getPort($game_id, $db->getField('sector_id'), false, $db);
		$port->addCachePort($account_id);
	}

}
elseif ($var['func'] == 'Money') {
	$player->setCredits(50000000);
}
elseif ($var['func'] == 'Ship' && $_REQUEST['ship_id'] <= 75 && $_REQUEST['ship_id'] != 68) {
	$ship_id = (int)$_REQUEST['ship_id'];
	
	$speed = $ship->getSpeed();
	// assign the new ship
	$ship->decloak();
	$ship->disableIllusion();
	$ship->setShipTypeID($ship_id);
	
	//now adapt turns
	$player->setTurns($player->getTurns() * ($speed / $ship->getSpeed()));
	setHardwareToMax($ship);
}
elseif ($var['func'] == 'Weapon') {
	$weapon_id = $_REQUEST['weapon_id'];
	$amount = $_REQUEST['amount'];
	for ($i = 1; $i <= $amount; $i++) {
		$ship->addWeapon($weapon_id);
	}
}
elseif ($var['func'] == 'Uno') {
	setHardwareToMax($ship);
}
elseif ($var['func'] == 'Warp') {
	$sector_to = trim($_REQUEST['sector_to']);
	if(!is_numeric($sector_to)) {
		create_error('Sector ID has to be a number.');
	}
	if (!SmrSector::sectorExists($player->getGameID(), $sector_to)) {
		create_error('Sector ID is not in any galaxy.');
	}
	$player->setSectorID($sector_to);
	$player->setLandedOnPlanet(false);
}
elseif ($var['func'] == 'Turns') {
	$player->setTurns((int)$_REQUEST['turns']);
}
elseif ($var['func'] == 'Exp') {
	$exp = min(500000, (int)$_REQUEST['exp']);
	$player->setExperience($exp);
}
elseif ($var['func'] == 'Align') {
	$align=max(-500, min(500, (int)$_REQUEST['align']));
	$player->setAlignment($align);
}
elseif ($var['func'] == 'RemWeapon') {
	$ship->removeAllWeapons();
}
elseif ($var['func'] == 'Hard_add') {
	$type_hard = (int)$_REQUEST['type_hard'];
	$amount_hard = (int)$_REQUEST['amount_hard'];
	$ship->setHardware($type_hard, $amount_hard);
	$ship->removeUnderAttack();
}
elseif ($var['func'] == 'Relations') {
	$amount = (int)$_REQUEST['amount'];
	$race = (int)$_REQUEST['race'];
	$player->setRelations($amount,$race);
}
elseif ($var['func'] == 'Race_Relations') {
	$amount = $_REQUEST['amount'];
	$race = $_REQUEST['race'];
	if(!is_numeric($amount) || !is_numeric($race)) {
		create_error('Amount and Race IDs have to be numbers.');
	}
	if($player->getRaceID()==$race) {
		create_error('You cannot change race relations with your own race.');
	}
	$db->query('UPDATE race_has_relation SET relation = ' . $db->escapeNumber($amount) . ' WHERE race_id_1 = ' . $db->escapeNumber($player->getRaceID()) . ' AND race_id_2 = ' . $db->escapeNumber($race) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()));
	$db->query('UPDATE race_has_relation SET relation = ' . $db->escapeNumber($amount) . ' WHERE race_id_1 = ' . $db->escapeNumber($race) . ' AND race_id_2 = ' . $db->escapeNumber($player->getRaceID()) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()));
} elseif ($var['func'] == 'Race') {
	$race = $_REQUEST['race'];
	if(!is_numeric($race)) {
		create_error('Amount and Race IDs have to be numbers.');
	}
	$player->setRaceID($race);
}
elseif ($var['func'] == 'planet_buildings') {
	$planet = $sector->getPlanet();
	foreach ($planet->getMaxBuildings() as $id => $amount) {
		$planet->setBuilding($id, $amount);
	}
}
elseif ($var['func'] == 'planet_defenses') {
	$planet = $sector->getPlanet();
	$planet->setShields($planet->getMaxShields());
	$planet->setCDs($planet->getMaxCDs());
	$planet->setArmour($planet->getMaxArmour());
}
elseif ($var['func'] == 'planet_stockpile') {
	$planet = $sector->getPlanet();
	foreach (Globals::getGoods() as $goodID => $good) {
		$planet->setStockpile($goodID, SmrPlanet::MAX_STOCKPILE);
	}
}

$container = create_container('skeleton.php', $var['body']);
forward($container);

/**
 * Set all hardware to its maximum value for this ship.
 */
function setHardwareToMax($ship) {
	$maxHardware = $ship->getMaxHardware();
	foreach($maxHardware as $key => $max) {
		$ship->setHardware($key,$max);
	}
	$ship->removeUnderAttack();
}
