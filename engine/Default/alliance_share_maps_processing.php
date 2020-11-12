<?php declare(strict_types=1);

$memberPlayerIDs = $player->getAlliance()->getMemberPlayerIDs();

// end here if we are alone in the alliance
if (empty($memberPlayerIDs)) {
	create_error('Who exactly are you sharing maps with?');
}

$unvisitedSectors = array(0);

// get the sectors the user hasn't visited yet
$db->query('SELECT sector_id FROM player_visited_sector WHERE ' . $player->getSQL());
while ($db->nextRecord()) {
	$unvisitedSectors[] = $db->getInt('sector_id');
}

// delete all visited sectors from the table of all our alliance mates
$db->query('DELETE
			FROM player_visited_sector
			WHERE player_id IN (' . $db->escapeArray($memberPlayerIDs) . ')
				AND game_id = ' . $db->escapeNumber($player->getGameID()) . '
				AND sector_id NOT IN (' . $db->escapeArray($unvisitedSectors) . ')');

// free some memory
unset($unvisitedSectors);

// get a list of all visited ports
$db->query('SELECT sector_id FROM player_visited_port WHERE ' . $player->getSQL());
while ($db->nextRecord()) {
	$cachedPort = SmrPort::getCachedPort($player->getGameID(), $db->getInt('sector_id'), $player->getPlayerID());
	$cachedPort->addCachePorts($memberPlayerIDs);
}

forward(create_container('skeleton.php', 'alliance_roster.php'));
