<?php declare(strict_types=1);

function get_seed_message(SmrPlayer $player): string {
	// get a list of seedlist sectors that the player hasn't seeded
	$db = Smr\Database::getInstance();
	$dbResult = $db->read('SELECT sector_id
		FROM alliance_has_seedlist
		WHERE alliance_id = ' . $db->escapeNumber($player->getAllianceID()) . '
			AND game_id = ' . $db->escapeNumber($player->getGameID()) . '
			AND sector_id NOT IN (
				SELECT sector_id
				FROM sector_has_forces
				WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
					AND owner_id = ' . $db->escapeNumber($player->getAccountID()) . '
			)');

	$missingSeeds = [];
	foreach ($dbResult->records() as $dbRecord) {
		$missingSeeds[] = $dbRecord->getInt('sector_id');
	}

	if (count($missingSeeds) == 0) {
		return $player->getPlayerName() . ' has seeded all sectors.';
	} else {
		return $player->getPlayerName() . ' (' . count($missingSeeds) . ' missing) : ' . implode(' ', $missingSeeds);
	}
}

function shared_channel_msg_seed(SmrPlayer $player): array {
	// Check to see how many sectors are in the seedlist
	$db = Smr\Database::getInstance();
	$dbResult = $db->read('SELECT count(*)
		FROM alliance_has_seedlist
		WHERE alliance_id = ' . $db->escapeNumber($player->getAllianceID()) . '
			AND game_id = ' . $db->escapeNumber($player->getGameID()));
	$numSectors = $dbResult->record()->getInt('count(*)');

	if ($numSectors == 0) {
		return ['Your alliance has not set up a seedlist yet.'];
	}

	// Get seed status for each player we have access to
	$result = array_map('get_seed_message', $player->getSharingPlayers(true));

	// Prepend the total number of sectors to seed
	array_unshift($result, "There are $numSectors sectors in the seedlist.");

  return $result;
}
