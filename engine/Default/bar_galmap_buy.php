<?php declare(strict_types=1);

$timeUntilMaps = $player->getGame()->getStartTime() + TIME_MAP_BUY_WAIT - TIME;
if ($timeUntilMaps > 0) {
	create_error('You cannot buy maps for another ' . format_time($timeUntilMaps) . '!');
}

if ($account->getTotalSmrCredits() < CREDITS_PER_GAL_MAP) {
	create_error('You don\'t have enough SMR Credits. Donate to SMR to gain SMR Credits!');
}

//gal map buy
if (isset($var['process'])) {
	$galaxyID = trim($_REQUEST['gal_id']);
	if (!is_numeric($galaxyID) || $galaxyID == 0) {
		create_error('You must select a galaxy to buy the map of!');
	}
	
	//get start sector
	$galaxy = SmrGalaxy::getGalaxy($player->getGameID(), $galaxyID);
	$low = $galaxy->getStartSector();
	//get end sector
	$high = $galaxy->getEndSector();

	// Have they already got this map? (Are there any unexplored sectors?
	$db->query('SELECT * FROM player_visited_sector WHERE sector_id >= ' . $db->escapeNumber($low) . ' AND sector_id <= ' . $db->escapeNumber($high) . ' AND account_id = ' . $db->escapeNumber($player->getAccountID()) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()) . ' LIMIT 1');
	if (!$db->nextRecord()) {
		create_error('You already have maps of this galaxy!');
	}
	
	$player->increaseHOF(1, array('Bar', 'Maps Bought'), HOF_PUBLIC);
	//take money
	$account->decreaseTotalSmrCredits(CREDITS_PER_GAL_MAP);
	//now give maps
	
	// delete all entries from the player_visited_sector/port table
	$db->query('DELETE FROM player_visited_sector WHERE sector_id >= ' . $db->escapeNumber($low) . ' AND sector_id <= ' . $db->escapeNumber($high) . ' AND account_id = ' . $db->escapeNumber($player->getAccountID()) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()));
	//start section
	
	// add port infos
	foreach ($galaxy->getPorts() as $port) {
		$port->addCachePort($player->getAccountID());
	}
	
	$container = create_container('skeleton.php', 'bar_main.php');
	transfer('LocationID');
	$container['message'] = '<div class="center">Galaxy maps have been added. Enjoy!</div><br />';
	forward($container);
} else {
	// This is a display page!
	$template->assign('PageTopic', 'Buy Galaxy Maps');
	Menu::bar();

	//find what gal they want
	$container = create_container('skeleton.php', 'bar_galmap_buy.php');
	transfer('LocationID');
	$container['process'] = true;
	$template->assign('BuyHREF', SmrSession::getNewHREF($container));
}
