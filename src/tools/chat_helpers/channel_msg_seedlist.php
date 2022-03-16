<?php declare(strict_types=1);

function get_seedlist(SmrPlayer $player): array {
	// Return the seedlist
	$db = Smr\Database::getInstance();
	$dbResult = $db->read('SELECT sector_id FROM alliance_has_seedlist
						WHERE alliance_id = ' . $db->escapeNumber($player->getAllianceID()) . '
							AND game_id = ' . $db->escapeNumber($player->getGameID()));
	$seedlist = [];
	foreach ($dbResult->records() as $dbRecord) {
		$seedlist[] = $dbRecord->getInt('sector_id');
	}
	return $seedlist;
}


function shared_channel_msg_seedlist(SmrPlayer $player): array {
	// get the seedlist
	$seedlist = get_seedlist($player);

	if (count($seedlist) == 0) {
		return ['Your alliance has not set up a seedlist yet.'];
	}
	$result = ['Your alliance has a ' . count($seedlist) . ' sector seedlist:'];
	$result[] = implode(' ', $seedlist);
	return $result;
}

function shared_channel_msg_seedlist_add(SmrPlayer $player, ?array $sectors): array {
	// check if $nick is leader
	if (!$player->isAllianceLeader(true)) {
		return ['Only the leader of the alliance manages the seedlist.'];
	}

	if (empty($sectors)) {
		return ['You must specify sectors to add.'];
	}

	// see if the sectors are numeric
	foreach ($sectors as $sector) {
		if (!is_numeric($sector)) {
			return ["The specified sector '$sector' is not numeric."];
		}
	}

	$result = [];

	// Get the initial seedlist
	$currentSeedlist = get_seedlist($player);
	$initSizeSeedlist = count($currentSeedlist);

	$db = Smr\Database::getInstance();
	foreach ($sectors as $sector) {
		// check if the sector is a part of the game
		$dbResult = $db->read('SELECT 1
					FROM sector
					WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
						AND  sector_id = ' . $db->escapeNumber($sector));
		if (!$dbResult->hasRecord()) {
			$result[] = "WARNING: The sector '$sector' does not exist in the current game.";
			continue;
		}

		// check if the sector is already in the seedlist
		if (in_array($sector, $currentSeedlist)) {
			$result[] = "WARNING: The sector '$sector' is already in the seedlist.";
			continue;
		}

		// add sector to db (and the current seedlist)
		$db->insert('alliance_has_seedlist', [
			'alliance_id' => $db->escapeNumber($player->getAllianceID()),
			'game_id' => $db->escapeNumber($player->getGameID()),
			'sector_id' => $db->escapeNumber($sector),
		]);
		$currentSeedlist[] = $sector;
	}

	// Summarize action
	$finalSizeSeedlist = count($currentSeedlist);
	$numSectorsAdded = $finalSizeSeedlist - $initSizeSeedlist;
	$result[] = "Added $numSectorsAdded sectors to the seedlist.";
	$result[] = "New total: $finalSizeSeedlist";
	return $result;
}


function shared_channel_msg_seedlist_del(SmrPlayer $player, ?array $sectors): array {
	// check if $nick is leader
	if (!$player->isAllianceLeader(true)) {
		return ['Only the leader of the alliance manages the seedlist.'];
	}

	if (empty($sectors)) {
		return ['You must specify sectors to delete.'];
	}

	if (count($sectors) === 1 && $sectors[0] === 'all') {
		$sectors = get_seedlist($player);
	}

	// see if the sectors are numeric
	foreach ($sectors as $sector) {
		if (!is_numeric($sector)) {
			return ["The specified sector '$sector' is not numeric."];
		}
	}

	// remove sectors from the db
	$db = Smr\Database::getInstance();
	$db->write('DELETE FROM alliance_has_seedlist
				WHERE alliance_id = ' . $player->getAllianceID() . '
					AND game_id = ' . $player->getGameID() . '
					AND sector_id IN (' . $db->escapeArray($sectors) . ')');

	return ['The following sectors have been removed from the seedlist:' . implode(' ', $sectors)];
}
