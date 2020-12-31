<?php declare(strict_types=1);

if ($var['func'] == 'Map') {
	$account_id = $player->getAccountID();
	$game_id = $player->getGameID();
	// delete all entries from the player_visited_sector/port table
	$db->query('DELETE FROM player_visited_sector WHERE ' . $player->getSQL());

	// add port infos
	$db->query('SELECT * FROM port WHERE game_id = ' . $db->escapeNumber($game_id));
	while ($db->nextRecord()) {
		$port = SmrPort::getPort($game_id, $db->getInt('sector_id'), false, $db);
		$port->addCachePort($account_id);
	}

} elseif ($var['func'] == 'Money') {
	$player->setCredits(50000000);
} elseif ($var['func'] == 'Ship') {
	$ship_id = Request::getInt('ship_id');
	if ($ship_id <= 75 && $ship_id != 68) {
		// assign the new ship
		$ship->decloak();
		$ship->disableIllusion();
		$ship->setShipTypeID($ship_id);
		$ship->setHardwareToMax();
	}
} elseif ($var['func'] == 'Weapon') {
	$weapon = SmrWeapon::getWeapon(Request::getInt('weapon_id'));
	$amount = Request::getInt('amount');
	for ($i = 1; $i <= $amount; $i++) {
		$ship->addWeapon($weapon);
	}
} elseif ($var['func'] == 'Uno') {
	$ship->setHardwareToMax();
} elseif ($var['func'] == 'Warp') {
	$sector_to = Request::getInt('sector_to');
	if (!SmrSector::sectorExists($player->getGameID(), $sector_to)) {
		create_error('Sector ID is not in any galaxy.');
	}
	$player->setSectorID($sector_to);
	$player->setLandedOnPlanet(false);
} elseif ($var['func'] == 'Turns') {
	$player->setTurns(Request::getInt('turns'));
} elseif ($var['func'] == 'Exp') {
	$exp = min(500000, Request::getInt('exp'));
	$player->setExperience($exp);
} elseif ($var['func'] == 'Align') {
	$align = max(-500, min(500, Request::getInt('align')));
	$player->setAlignment($align);
} elseif ($var['func'] == 'RemWeapon') {
	$ship->removeAllWeapons();
} elseif ($var['func'] == 'Hard_add') {
	$type_hard = Request::getInt('type_hard');
	$amount_hard = Request::getInt('amount_hard');
	$ship->setHardware($type_hard, $amount_hard);
	$ship->removeUnderAttack();
} elseif ($var['func'] == 'Relations') {
	$amount = Request::getInt('amount');
	$race = Request::getInt('race');
	$player->setRelations($amount, $race);
} elseif ($var['func'] == 'Race_Relations') {
	$amount = Request::getInt('amount');
	$race = Request::getInt('race');
	if ($player->getRaceID() == $race) {
		create_error('You cannot change race relations with your own race.');
	}
	$db->query('UPDATE race_has_relation SET relation = ' . $db->escapeNumber($amount) . ' WHERE race_id_1 = ' . $db->escapeNumber($player->getRaceID()) . ' AND race_id_2 = ' . $db->escapeNumber($race) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()));
	$db->query('UPDATE race_has_relation SET relation = ' . $db->escapeNumber($amount) . ' WHERE race_id_1 = ' . $db->escapeNumber($race) . ' AND race_id_2 = ' . $db->escapeNumber($player->getRaceID()) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()));
} elseif ($var['func'] == 'Race') {
	$race = Request::getInt('race');
	$player->setRaceID($race);
} elseif ($var['func'] == 'planet_buildings') {
	$planet = $sector->getPlanet();
	foreach ($planet->getMaxBuildings() as $id => $amount) {
		$planet->setBuilding($id, $amount);
	}
} elseif ($var['func'] == 'planet_defenses') {
	$planet = $sector->getPlanet();
	$planet->setShields($planet->getMaxShields());
	$planet->setCDs($planet->getMaxCDs());
	$planet->setArmour($planet->getMaxArmour());
} elseif ($var['func'] == 'planet_stockpile') {
	$planet = $sector->getPlanet();
	foreach (Globals::getGoods() as $goodID => $good) {
		$planet->setStockpile($goodID, SmrPlanet::MAX_STOCKPILE);
	}
}

$container = create_container('skeleton.php', $var['body']);
forward($container);
