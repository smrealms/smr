<?
require_once(get_file_loc('SmrPort.class.inc'));
require_once(get_file_loc('SmrSector.class.inc'));
$sector =& SmrSector::getSector(SmrSession::$game_id, $player->getSectorID());

$alliance_ids = array();

// get a list of alliance member
$db->query('SELECT * FROM player
			WHERE alliance_id = '.$player->getAllianceID().' AND
				  game_id = '.$player->getGameID().' AND
				  account_id != '.$player->getAccountID());
while ($db->nextRecord()) {

	// a list for direct sql's
	if ($alliance_list)
		$alliance_list .= ', ';
	$alliance_list .= $db->getField('account_id');

	// an array for later use
	$alliance_ids[] = $db->getField('account_id');

}

// end here if we are alone in the alliance
if (empty($alliance_list))
	forward(create_container('skeleton.php', 'alliance_roster.php'));

// get min and max sectors
$db->query('SELECT MIN(sector_id), MAX(sector_id)
			FROM sector
			WHERE game_id = '.SmrSession::$game_id);
if ($db->nextRecord()) {

	$min_sector = $db->getField('MIN(sector_id)');
	$max_sector = $db->getField('MAX(sector_id)');

}

$unvisitted_sectors = array();

// get the sectors the user hasn't visited yet
$db->query('SELECT sector_id
			FROM player_visited_sector
			WHERE game_id = '.SmrSession::$game_id.' AND
				  account_id = '.SmrSession::$account_id);
while ($db->nextRecord())
	$unvisitted_sectors[$db->getField('sector_id')] = true;

// invert it and get a list of visited sectors
for ($i = $min_sector; $i <= $max_sector; $i++) {

	// when it's not an unvisitted sector
	if (!isset($unvisitted_sectors[$i]) || !$unvisitted_sectors[$i]) {

		// it has to be a sector where we've already been
		if ($visitted_sector_list)
			$visitted_sector_list .= ', ';
		$visitted_sector_list .= $i;

	}

}

// free some memory
unset($unvisitted_sectors);

// do we have any visited sectors?
if ($visitted_sector_list) {

	// delete all visited sectors from the table of all our alliance mates
	$db->query('DELETE
				FROM player_visited_sector
				WHERE account_id IN ('.$alliance_list.') AND
					  game_id = '.SmrSession::$game_id.' AND
					  sector_id IN ('.$visitted_sector_list.')');

}

$port_visitted = array();
$port_info = array();

// get a list of all visited ports
$db->query('SELECT sector_id,visited
			FROM player_visited_port
			WHERE account_id = '.SmrSession::$account_id.' AND
				  game_id = '.SmrSession::$game_id);
while ($db->nextRecord())
{
	$cachedPort =& SmrPort::getPort(SmrSession::$game_id,$db->getField('sector_id'));
	$visited	= $db->getField('visited');
	$port_info[$db->getField('sector_id')]		= $db->getField('port_info');
	foreach ($alliance_ids as $id)
	{
		// need to insert this entry first
		// ignore if it exists
		$cachedPort->addCachePort($id,false);
	}
	
	// update all port infos
	$db->query('UPDATE player_visited_port
				SET port_info = ' . $db->escape_string(serialize($cachedPort)) . '
				WHERE account_id IN ('.$alliance_list.') AND
					  game_id = '.SmrSession::$game_id.' AND
					  sector_id = '.$sector_id.' AND
					  visited < '.$visited);
}

forward(create_container('skeleton.php', 'alliance_roster.php'));

?>