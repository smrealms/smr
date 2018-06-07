<?php
require_once(get_file_loc('SmrPort.class.inc'));

$alliance_ids = array();

// get a list of alliance member
$db->query('SELECT * FROM player
			WHERE alliance_id = ' . $db->escapeNumber($player->getAllianceID()) . '
				AND game_id = ' . $db->escapeNumber($player->getGameID()) . '
				AND account_id != ' . $db->escapeNumber($player->getAccountID()));
while ($db->nextRecord()) {
	// an array for later use
	$alliance_ids[] = $db->getInt('account_id');
}

// end here if we are alone in the alliance
if (empty($alliance_ids)) {
	create_error('Who exactly are you sharing maps with?');
}

$unvisitedSectors = array(0);

// get the sectors the user hasn't visited yet
$db->query('SELECT sector_id
			FROM player_visited_sector
			WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
				AND account_id = ' . $db->escapeNumber($player->getAccountID()));
while ($db->nextRecord()) {
	$unvisitedSectors[] = $db->getInt('sector_id');
}

// delete all visited sectors from the table of all our alliance mates
$db->query('DELETE
			FROM player_visited_sector
			WHERE account_id IN (' . $db->escapeArray($alliance_ids) . ')
				AND game_id = ' . $db->escapeNumber($player->getGameID()) . '
				AND sector_id NOT IN (' . $db->escapeArray($unvisitedSectors) . ')');

// free some memory
unset($unvisitedSectors);

// get a list of all visited ports
$db->query('SELECT sector_id
			FROM player_visited_port
			WHERE account_id = ' . $db->escapeNumber($player->getAccountID()) . '
				AND game_id = ' . $db->escapeNumber($player->getGameID()));
while ($db->nextRecord()) {
	$cachedPort = SmrPort::getCachedPort($player->getGameID(),$db->getInt('sector_id'),$player->getAccountID());
	$cachedPort->addCachePorts($alliance_ids);
}

forward(create_container('skeleton.php', 'alliance_roster.php'));
