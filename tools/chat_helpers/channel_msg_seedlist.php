<?php declare(strict_types=1);

function get_seedlist($player) {
	// Return the seedlist
	$db = new SmrMySqlDatabase();
	$db->query('SELECT sector_id FROM alliance_has_seedlist
						WHERE alliance_id = ' . $db->escapeNumber($player->getAllianceID()) . '
							AND game_id = ' . $db->escapeNumber($player->getGameID()));
	$seedlist = array();
	while ($db->nextRecord()) {
		$seedlist[] = $db->getInt('sector_id');
	}
	return $seedlist;
}


function shared_channel_msg_seedlist($player) {
	// get the seedlist
	$seedlist = get_seedlist($player);

	if (count($seedlist) == 0) {
		return array('Your alliance has not set up a seedlist yet.');
	} else {
		$result = array('Your alliance has a ' . count($seedlist) . ' sector seedlist:');
		$result[] = join(' ', $seedlist);
		return $result;
	}
}

function shared_channel_msg_seedlist_add($player, $sectors) {
	// check if $nick is leader
	if (!$player->isAllianceLeader(true)) {
		return array('Only the leader of the alliance manages the seedlist.');
	}

	if (empty($sectors)) {
		return array('You must specify sectors to add.');
	}

	// see if the sectors are numeric
	foreach ($sectors as $sector) {
		if (!is_numeric($sector)) {
			return array("The specified sector '$sector' is not numeric.");
		}
	}

	$result = array();

	// Get the initial seedlist
	$currentSeedlist = get_seedlist($player);
	$initSizeSeedlist = count($currentSeedlist);

	$db = new SmrMySqlDatabase();
	foreach ($sectors as $sector) {
		// check if the sector is a part of the game
		$db->query('SELECT sector_id
					FROM sector
					WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
						AND  sector_id = ' . $db->escapeNumber($sector)
		);
		if (!$db->nextRecord()) {
			$result[] = "WARNING: The sector '$sector' does not exist in the current game.";
			continue;
		}

		// check if the sector is already in the seedlist
		if (in_array($sector, $currentSeedlist)) {
			$result[] = "WARNING: The sector '$sector' is already in the seedlist.";
			continue;
		}

		// add sector to db (and the current seedlist)
		$db->query('INSERT INTO alliance_has_seedlist
					(alliance_id, game_id, sector_id)
					VALUES (' . $db->escapeNumber($player->getAllianceID()) . ', ' . $db->escapeNumber($player->getGameID()) . ', ' . $db->escapeNumber($sector) . ')');
		$currentSeedlist[] = $sector;
	}

	// Summarize action
	$finalSizeSeedlist = count($currentSeedlist);
	$numSectorsAdded = $finalSizeSeedlist - $initSizeSeedlist;
	$result[] = "Added $numSectorsAdded sectors to the seedlist.";
	$result[] = "New total: $finalSizeSeedlist";
	return $result;
}


function shared_channel_msg_seedlist_del($player, $sectors) {
	// check if $nick is leader
	if (!$player->isAllianceLeader(true)) {
		return array('Only the leader of the alliance manages the seedlist.');
	}

	if (empty($sectors)) {
		return array('You must specify sectors to delete.');
	}

	// see if the sectors are numeric
	foreach ($sectors as $sector) {
		if (!is_numeric($sector)) {
			return array("The specified sector '$sector' is not numeric.");
		}
	}

	// remove sectors from the db
	$db = new SmrMySqlDatabase();
	$db->query('DELETE FROM alliance_has_seedlist
				WHERE alliance_id = ' . $player->getAllianceID() . '
					AND game_id = ' . $player->getGameID() . '
					AND sector_id IN (' . $db->escapeArray($sectors) . ')'
	);

	return array('The specified sectors have been removed from the seedlist.');
}
