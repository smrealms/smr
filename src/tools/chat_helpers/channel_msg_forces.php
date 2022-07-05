<?php declare(strict_types=1);

require_once(TOOLS . 'chat_helpers/channel_msg_seedlist.php');

function shared_channel_msg_forces(AbstractSmrPlayer $player, ?string $option = null): array {
	$db = Smr\Database::getInstance();
	if (empty($option)) {
		$dbResult = $db->read('SELECT sector_has_forces.sector_id AS sector, expire_time
			FROM sector_has_forces
			WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
				AND owner_id IN (
					SELECT account_id FROM player
					WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
					AND alliance_id = ' . $db->escapeNumber($player->getAllianceID()) . '
				)
			ORDER BY expire_time ASC LIMIT 1');
	} elseif ($option == 'seedlist') {
		// are we restricting to the seedlist?
		$seedlist = get_seedlist($player);
		if (count($seedlist) == 0) {
			return ['Your alliance does not have a seedlist yet.'];
		}
		$dbResult = $db->read('SELECT sector_has_forces.sector_id AS sector, expire_time
			FROM sector_has_forces
			WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
				AND sector_id IN (' . $db->escapeArray($seedlist) . ')
				AND owner_id IN (
					SELECT account_id FROM player
					WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
					AND alliance_id = ' . $db->escapeNumber($player->getAllianceID()) . '
				)
			ORDER BY expire_time ASC LIMIT 1');
	} else {
		// did we get a galaxy name?
		$dbResult = $db->read('SELECT galaxy_id FROM game_galaxy WHERE galaxy_name = ' . $db->escapeString($option) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()));
		if (!$dbResult->hasRecord()) {
			return ["Could not find a galaxy named '$option'."];
		}
		$galaxyId = $dbResult->record()->getInt('galaxy_id');
		$dbResult = $db->read('SELECT sector_has_forces.sector_id AS sector, expire_time
					FROM sector_has_forces
					LEFT JOIN sector USING (sector_id, game_id)
					WHERE sector_has_forces.game_id = ' . $db->escapeNumber($player->getGameID()) . '
						AND galaxy_id = ' . $db->escapeNumber($galaxyId) . '
						AND owner_id IN (
							SELECT account_id FROM player
							WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
								AND alliance_id = ' . $db->escapeNumber($player->getAllianceID()) . '
						)
					ORDER BY expire_time ASC LIMIT 1');
	}

	if (!$dbResult->hasRecord()) {
		return ['Your alliance does not own any forces in these sectors.'];
	}
	$dbRecord = $dbResult->record();
	$sectorId = $dbRecord->getInt('sector');
	$expire = $dbRecord->getInt('expire_time');

	return ['Forces in sector ' . $sectorId . ' will expire in ' . format_time($expire - time())];
}
