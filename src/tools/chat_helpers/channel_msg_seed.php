<?php declare(strict_types=1);

use Smr\Database;
use Smr\Player;

function get_seed_message(Player $player): string {
	// get a list of seedlist sectors that the player hasn't seeded
	$db = Database::getInstance();
	$dbResult = $db->read('SELECT sector_id
		FROM alliance_has_seedlist
		WHERE alliance_id = :alliance_id
			AND game_id = :game_id
			AND sector_id NOT IN (
				SELECT sector_id
				FROM sector_has_forces
				WHERE game_id = :game_id
					AND owner_id = :owner_id
			)', [
		'alliance_id' => $db->escapeNumber($player->getAllianceID()),
		'game_id' => $db->escapeNumber($player->getGameID()),
		'owner_id' => $db->escapeNumber($player->getAccountID()),
	]);

	$missingSeeds = [];
	foreach ($dbResult->records() as $dbRecord) {
		$missingSeeds[] = $dbRecord->getInt('sector_id');
	}

	if (count($missingSeeds) === 0) {
		return $player->getPlayerName() . ' has seeded all sectors.';
	}
	return $player->getPlayerName() . ' (' . count($missingSeeds) . ' missing) : ' . implode(' ', $missingSeeds);
}

/**
 * @return array<string>
 */
function shared_channel_msg_seed(Player $player): array {
	// Check to see how many sectors are in the seedlist
	$db = Database::getInstance();
	$numSectors = $db->count('alliance_has_seedlist', $player->getAlliance()->SQLID);

	if ($numSectors === 0) {
		return ['Your alliance has not set up a seedlist yet.'];
	}

	// Get seed status for each player we have access to
	$result = array_map('get_seed_message', $player->getSharingPlayers(true));

	// Prepend the total number of sectors to seed
	array_unshift($result, "There are $numSectors sectors in the seedlist.");

	return $result;
}
