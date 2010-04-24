<?php
require_once(get_file_loc('smr_sector.inc'));
$sector = new SMR_SECTOR($player->sector_id, SmrSession::$game_id, SmrSession::$old_account_id);

$alliance_ids = array();

// get a list of alliance member
$db->query("SELECT * FROM player
			WHERE alliance_id = $player->alliance_id AND
				  game_id = $player->game_id AND
				  account_id != $player->account_id");
while ($db->next_record()) {

	// a list for direct sql's
	if ($alliance_list)
		$alliance_list .= ", ";
	$alliance_list .= $db->f("account_id");

	// an array for later use
	$alliance_ids[] = $db->f("account_id");

}

// end here if we are alone in the alliance
if (empty($alliance_list))
	forward(create_container("skeleton.php", "alliance_roster.php"));

// get min and max sectors
$db->query("SELECT MIN(sector_id), MAX(sector_id)
			FROM sector
			WHERE game_id = ".SmrSession::$game_id);
if ($db->next_record()) {

	$min_sector = $db->f("MIN(sector_id)");
	$max_sector = $db->f("MAX(sector_id)");

}

$unvisitted_sectors = array();

// get the sectors the user hasn't visited yet
$db->query("SELECT sector_id
			FROM player_visited_sector
			WHERE game_id = ".SmrSession::$game_id." AND
				  account_id = ".SmrSession::$old_account_id);
while ($db->next_record())
	$unvisitted_sectors[$db->f("sector_id")] = true;

// invert it and get a list of visited sectors
for ($i = $min_sector; $i <= $max_sector; $i++) {

	// when it's not an unvisitted sector
	if (!$unvisitted_sectors[$i]) {

		// it has to be a sector where we've already been
		if ($visitted_sector_list)
			$visitted_sector_list .= ", ";
		$visitted_sector_list .= $i;

	}

}

// free some memory
unset($unvisitted_sectors);

// do we have any visited sectors?
if ($visitted_sector_list) {

	// delete all visited sectors from the table of all our alliance mates
	$db->query("DELETE
				FROM player_visited_sector
				WHERE account_id IN ($alliance_list) AND
					  game_id = ".SmrSession::$game_id." AND
					  sector_id IN ($visitted_sector_list)");

}

$port_visitted = array();
$port_info = array();

// get a list of all visited ports
$db->query("SELECT sector_id, visited, port_info
			FROM player_visited_port
			WHERE account_id = ".SmrSession::$old_account_id." AND
				  game_id = ".SmrSession::$game_id);
while ($db->next_record()) {

	$port_visitted[$db->f("sector_id")]	= $db->f("visited");
	$port_info[$db->f("sector_id")]		= $db->f("port_info");

}

foreach ($port_visitted as $sector_id => $visitted) {

	foreach ($alliance_ids as $id) {

		// need to insert this entry first
		// ignore if it exists
		$db->query("INSERT IGNORE INTO player_visited_port
					(account_id, game_id, sector_id, visited, port_info)
					VALUES ($id, ".SmrSession::$game_id.", $sector_id, $visitted, '" . $port_info[$sector_id] . "')");

	}

	// update all port infos
	$db->query("UPDATE player_visited_port
				SET port_info = '" . $port_info[$sector_id] . "'
				WHERE account_id IN ($alliance_list) AND
					  game_id = ".SmrSession::$game_id." AND
					  sector_id = $sector_id AND
					  visited < $visitted");

}

forward(create_container("skeleton.php", "alliance_roster.php"));

?>