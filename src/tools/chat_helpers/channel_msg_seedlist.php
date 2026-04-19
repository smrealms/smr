<?php declare(strict_types=1);

use Smr\Database;
use Smr\Player;

/**
 * @return array<int>
 */
function get_seedlist(Player $player): array {
	// Return the seedlist
	$db = Database::getInstance();
	$dbResult = $db->select('alliance_has_seedlist', $player->getAlliance()->SQLID, ['sector_id']);
	$seedlist = [];
	foreach ($dbResult->records() as $dbRecord) {
		$seedlist[] = $dbRecord->getInt('sector_id');
	}
	return $seedlist;
}

/**
 * @return array<string>
 */
function shared_channel_msg_seedlist(Player $player): array {
	// get the seedlist
	$seedlist = get_seedlist($player);

	if (count($seedlist) === 0) {
		return ['Your alliance has not set up a seedlist yet.'];
	}
	$result = ['Your alliance has a ' . count($seedlist) . ' sector seedlist:'];
	$result[] = implode(' ', $seedlist);
	return $result;
}

/**
 * @param ?array<string> $sectors
 * @return array<string>
 */
function shared_channel_msg_seedlist_add(Player $player, ?array $sectors): array {
	// check if $nick is leader
	if (!$player->isAllianceLeader(true)) {
		return ['Only the leader of the alliance manages the seedlist.'];
	}

	if ($sectors === null || count($sectors) === 0) {
		return ['You must specify sectors to add.'];
	}

	$result = [];

	// Get the initial seedlist
	$currentSeedlist = get_seedlist($player);
	$initSizeSeedlist = count($currentSeedlist);

	$db = Database::getInstance();
	foreach ($sectors as $sectorString) {
		$sector = filter_var($sectorString, FILTER_VALIDATE_INT);
		if ($sector === false) {
			return ['The specified sector is not valid: ' . $sectorString];
		}

		// check if the sector is a part of the game
		$dbResult = $db->select('sector', [
			'game_id' => $player->getGameID(),
			'sector_id' => $sector,
		]);
		if (!$dbResult->hasRecord()) {
			$result[] = "WARNING: The sector '$sector' does not exist in the current game.";
			continue;
		}

		// check if the sector is already in the seedlist
		if (in_array($sector, $currentSeedlist, true)) {
			$result[] = "WARNING: The sector '$sector' is already in the seedlist.";
			continue;
		}

		// add sector to db (and the current seedlist)
		$db->insert('alliance_has_seedlist', [
			'alliance_id' => $player->getAllianceID(),
			'game_id' => $player->getGameID(),
			'sector_id' => $sector,
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

/**
 * @param ?array<string> $sectors
 * @return array<string>
 */
function shared_channel_msg_seedlist_del(Player $player, ?array $sectors): array {
	// check if $nick is leader
	if (!$player->isAllianceLeader(true)) {
		return ['Only the leader of the alliance manages the seedlist.'];
	}

	if ($sectors === null || count($sectors) === 0) {
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
	$db = Database::getInstance();
	$db->write('DELETE FROM alliance_has_seedlist
				WHERE alliance_id = :alliance_id
					AND game_id = :game_id
					AND sector_id IN (:sector_ids)', [
		'alliance_id' => $player->getAllianceID(),
		'game_id' => $player->getGameID(),
		'sector_ids' => $db->escapeArray($sectors),
	]);

	return ['The following sectors have been removed from the seedlist: ' . implode(' ', $sectors)];
}
