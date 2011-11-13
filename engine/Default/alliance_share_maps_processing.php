<?php
require_once(get_file_loc('SmrPort.class.inc'));
$sector =& $player->getSector();

$alliance_ids = array();
$alliance_list = '';

// get a list of alliance member
$db->query('SELECT * FROM player
			WHERE alliance_id = '.$player->getAllianceID().' AND
				  game_id = '.$player->getGameID().' AND
				  account_id != '.$player->getAccountID());
while ($db->nextRecord())
{
	// a list for direct sql's
	if ($alliance_list)
		$alliance_list .= ', ';
	$alliance_list .= $db->getInt('account_id');

	// an array for later use
	$alliance_ids[] = $db->getInt('account_id');
}

// end here if we are alone in the alliance
if (empty($alliance_list))
	forward(create_container('skeleton.php', 'alliance_roster.php'));

// get min and max sectors
$db->query('SELECT MIN(sector_id), MAX(sector_id)
			FROM sector
			WHERE game_id = '.$player->getGameID());
if ($db->nextRecord())
{
	$min_sector = $db->getInt('MIN(sector_id)');
	$max_sector = $db->getInt('MAX(sector_id)');
}

$unvisited_sectors = array();

// get the sectors the user hasn't visited yet
$db->query('SELECT sector_id
			FROM player_visited_sector
			WHERE game_id = '.$player->getGameID().' AND
				  account_id = '.$player->getAccountID());
while ($db->nextRecord())
	$unvisited_sectors[$db->getInt('sector_id')] = true;

$visited_sector_list = '';

// invert it and get a list of visited sectors
for ($i = $min_sector; $i <= $max_sector; $i++)
{
	// when it's not an unvisited sector
	if (!isset($unvisited_sectors[$i]) || !$unvisited_sectors[$i])
	{
		// it has to be a sector where we've already been
		if ($visited_sector_list)
			$visited_sector_list .= ', ';
		$visited_sector_list .= $i;

	}

}

// free some memory
unset($unvisited_sectors);

// do we have any visited sectors?
if ($visited_sector_list)
{
	// delete all visited sectors from the table of all our alliance mates
	$db->query('DELETE
				FROM player_visited_sector
				WHERE account_id IN ('.$alliance_list.') AND
					  game_id = '.$player->getGameID().' AND
					  sector_id IN ('.$visited_sector_list.')');
}

// get a list of all visited ports
$db->query('SELECT sector_id
			FROM player_visited_port
			WHERE account_id = '.$player->getAccountID().' AND
				  game_id = '.$player->getGameID());
while ($db->nextRecord())
{
	$cachedPort =& SmrPort::getCachedPort($player->getGameID(),$db->getInt('sector_id'),$player->getAccountID());
	$cachedPort->addCachePorts($alliance_ids);
}

forward(create_container('skeleton.php', 'alliance_roster.php'));

?>