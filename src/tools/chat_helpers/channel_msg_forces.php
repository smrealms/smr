<?php declare(strict_types=1);

use Smr\AbstractPlayer;
use Smr\Database;

require_once(TOOLS . 'chat_helpers/channel_msg_seedlist.php');

/**
 * @return array<string>
 */
function shared_channel_msg_forces(AbstractPlayer $player, ?string $option = null): array {
	$db = Database::getInstance();
	if (empty($option)) {
		$dbResult = $db->read('SELECT sector_has_forces.sector_id AS sector, expire_time
			FROM sector_has_forces
			WHERE game_id = :game_id
				AND owner_id IN (
					SELECT account_id FROM player
					WHERE game_id = :game_id
					AND alliance_id = :alliance_id
				)
			ORDER BY expire_time ASC LIMIT 1', [
			'game_id' => $db->escapeNumber($player->getGameID()),
			'alliance_id' => $db->escapeNumber($player->getAllianceID()),
		]);
	} elseif ($option == 'seedlist') {
		// are we restricting to the seedlist?
		$seedlist = get_seedlist($player);
		if (count($seedlist) == 0) {
			return ['Your alliance does not have a seedlist yet.'];
		}
		$dbResult = $db->read('SELECT sector_has_forces.sector_id AS sector, expire_time
			FROM sector_has_forces
			WHERE game_id = :game_id
				AND sector_id IN (:sector_ids)
				AND owner_id IN (
					SELECT account_id FROM player
					WHERE game_id = :game_id
					AND alliance_id = :alliance_id
				)
			ORDER BY expire_time ASC LIMIT 1', [
			'game_id' => $db->escapeNumber($player->getGameID()),
			'alliance_id' => $db->escapeNumber($player->getAllianceID()),
			'sector_ids' => $db->escapeArray($seedlist),
		]);
	} else {
		// did we get a galaxy name?
		$dbResult = $db->read('SELECT galaxy_id FROM game_galaxy WHERE galaxy_name = :galaxy_name AND game_id = :game_id', [
			'galaxy_name' => $db->escapeString($option),
			'game_id' => $db->escapeNumber($player->getGameID()),
		]);
		if (!$dbResult->hasRecord()) {
			return ["Could not find a galaxy named '$option'."];
		}
		$galaxyId = $dbResult->record()->getInt('galaxy_id');
		$dbResult = $db->read('SELECT sector_has_forces.sector_id AS sector, expire_time
					FROM sector_has_forces
					LEFT JOIN sector USING (sector_id, game_id)
					WHERE sector_has_forces.game_id = :game_id
						AND galaxy_id = :galaxy_id
						AND owner_id IN (
							SELECT account_id FROM player
							WHERE game_id = :game_id
								AND alliance_id = :alliance_id
						)
					ORDER BY expire_time ASC LIMIT 1', [
			'game_id' => $db->escapeNumber($player->getGameID()),
			'galaxy_id' => $db->escapeNumber($galaxyId),
			'alliance_id' => $db->escapeNumber($player->getAllianceID()),
		]);
	}

	if (!$dbResult->hasRecord()) {
		return ['Your alliance does not own any forces in these sectors.'];
	}
	$dbRecord = $dbResult->record();
	$sectorId = $dbRecord->getInt('sector');
	$expire = $dbRecord->getInt('expire_time');

	return ['Forces in sector ' . $sectorId . ' will expire in ' . format_time($expire - time())];
}
