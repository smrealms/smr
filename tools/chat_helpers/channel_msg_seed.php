<?php declare(strict_types=1);

function get_seed_message($player) {
	// get a list of seedlist sectors that the player hasn't seeded
	$db = new SmrMySqlDatabase();
	$db->query('SELECT sector_id
		FROM alliance_has_seedlist
		WHERE alliance_id = ' . $db->escapeNumber($player->getAllianceID()) . '
			AND game_id = ' . $db->escapeNumber($player->getGameID()) . '
			AND sector_id NOT IN (
				SELECT sector_id
				FROM sector_has_forces
				WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
					AND owner_id = ' . $db->escapeNumber($player->getAccountID()) . '
			)');

	$missingSeeds = array();
	while ($db->nextRecord()) {
		$missingSeeds[] = $db->getInt('sector_id');
	}

	if (count($missingSeeds) == 0) {
		return $player->getPlayerName() . ' has seeded all sectors.';
	} else {
		return $player->getPlayerName() . ' (' . count($missingSeeds) . ' missing) : ' . join(' ', $missingSeeds);
	}
}

function shared_channel_msg_seed($player) {
	// Check to see how many sectors are in the seedlist
	$db = new SmrMySqlDatabase();
	$db->query('SELECT count(*)
		FROM alliance_has_seedlist
		WHERE alliance_id = ' . $db->escapeNumber($player->getAllianceID()) . '
			AND game_id = ' . $db->escapeNumber($player->getGameID()));
	$db->requireRecord();
	$numSectors = $db->getInt('count(*)');

	if ($numSectors == 0) {
		return array('Your alliance has not set up a seedlist yet.');
	}

	// Get seed status for each player we have access to
	$result = array_map('get_seed_message', $player->getSharingPlayers(true));

	// Prepend the total number of sectors to seed
	array_unshift($result, "There are $numSectors sectors in the seedlist.");

  return $result;
}
