<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();
$ship = $player->getShip();
$sector = $player->getSector();

if ($var['func'] == 'Map') {
	$account_id = $player->getAccountID();
	$game_id = $player->getGameID();
	// delete all entries from the player_visited_sector/port table
	$db = Smr\Database::getInstance();
	$db->write('DELETE FROM player_visited_sector WHERE ' . $player->getSQL());

	// add port infos
	$dbResult = $db->read('SELECT * FROM port WHERE game_id = ' . $db->escapeNumber($game_id));
	foreach ($dbResult->records() as $dbRecord) {
		$port = SmrPort::getPort($game_id, $dbRecord->getInt('sector_id'), false, $dbRecord);
		$port->addCachePort($account_id);
	}

} elseif ($var['func'] == 'Money') {
	$player->setCredits(50000000);
} elseif ($var['func'] == 'Ship') {
	$shipTypeID = Smr\Request::getInt('ship_type_id');
	if ($shipTypeID <= 75 && $shipTypeID != 68) {
		// assign the new ship
		$ship->decloak();
		$ship->disableIllusion();
		$ship->setTypeID($shipTypeID);
		$ship->setHardwareToMax();
	}
} elseif ($var['func'] == 'Weapon') {
	$weapon = SmrWeapon::getWeapon(Smr\Request::getInt('weapon_id'));
	$amount = Smr\Request::getInt('amount');
	for ($i = 1; $i <= $amount; $i++) {
		$ship->addWeapon($weapon);
	}
} elseif ($var['func'] == 'Uno') {
	$ship->setHardwareToMax();
} elseif ($var['func'] == 'Warp') {
	$sector_to = Smr\Request::getInt('sector_to');
	if (!SmrSector::sectorExists($player->getGameID(), $sector_to)) {
		create_error('Sector ID is not in any galaxy.');
	}
	$player->setSectorID($sector_to);
	$player->setLandedOnPlanet(false);
	// Update sector lock
	$player->update();
	$lock = Smr\SectorLock::getInstance();
	$lock->release();
	$lock->acquireForPlayer($player);
} elseif ($var['func'] == 'Turns') {
	$player->setTurns(Smr\Request::getInt('turns'));
} elseif ($var['func'] == 'Exp') {
	$exp = min(500000, Smr\Request::getInt('exp'));
	$player->setExperience($exp);
} elseif ($var['func'] == 'Align') {
	$align = max(-500, min(500, Smr\Request::getInt('align')));
	$player->setAlignment($align);
} elseif ($var['func'] == 'RemWeapon') {
	$ship->removeAllWeapons();
} elseif ($var['func'] == 'Hard_add') {
	$type_hard = Smr\Request::getInt('type_hard');
	$amount_hard = Smr\Request::getInt('amount_hard');
	$ship->setHardware($type_hard, $amount_hard);
} elseif ($var['func'] == 'Relations') {
	$amount = Smr\Request::getInt('amount');
	$race = Smr\Request::getInt('race');
	$player->setRelations($amount, $race);
} elseif ($var['func'] == 'Race_Relations') {
	$amount = Smr\Request::getInt('amount');
	$race = Smr\Request::getInt('race');
	if ($player->getRaceID() == $race) {
		create_error('You cannot change race relations with your own race.');
	}
	$db = Smr\Database::getInstance();
	$db->write('UPDATE race_has_relation SET relation = ' . $db->escapeNumber($amount) . ' WHERE race_id_1 = ' . $db->escapeNumber($player->getRaceID()) . ' AND race_id_2 = ' . $db->escapeNumber($race) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()));
	$db->write('UPDATE race_has_relation SET relation = ' . $db->escapeNumber($amount) . ' WHERE race_id_1 = ' . $db->escapeNumber($race) . ' AND race_id_2 = ' . $db->escapeNumber($player->getRaceID()) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()));
} elseif ($var['func'] == 'Race') {
	$race = Smr\Request::getInt('race');
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

$container = Page::create('beta_functions.php');
$container->go();
