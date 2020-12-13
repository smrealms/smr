<?php declare(strict_types=1);

require_once(TOOLS . 'chat_helpers/channel_msg_seedlist.php');

function shared_channel_msg_forces($player, $option) {
	$db = MySqlDatabase::getInstance();
	if (empty($option)) {
		$db->query('SELECT sector_has_forces.sector_id AS sector, expire_time
			FROM sector_has_forces
			WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
				AND owner_id IN (
					SELECT account_id FROM player
					WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
					AND alliance_id = ' . $db->escapeNumber($player->getAllianceID()) . '
				)
			ORDER BY expire_time ASC LIMIT 1'
		);
	} elseif ($option == "seedlist") {
		// are we restricting to the seedlist?
		$seedlist = get_seedlist($player);
		if (count($seedlist) == 0) {
			return array("Your alliance does not have a seedlist yet.");
		}
		$db->query('SELECT sector_has_forces.sector_id AS sector, expire_time
			FROM sector_has_forces
			WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
				AND sector_id IN (' . $db->escapeArray($seedlist) . ')
				AND owner_id IN (
					SELECT account_id FROM player
					WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
					AND alliance_id = ' . $db->escapeNumber($player->getAllianceID()) . '
				)
			ORDER BY expire_time ASC LIMIT 1'
		);
	} else {
		// did we get a galaxy name?
		$db->query('SELECT galaxy_id FROM game_galaxy WHERE galaxy_name = ' . $db->escapeString($option));
		if ($db->nextRecord()) {
			$galaxyId = $db->getInt('galaxy_id');
		} else {
			return array("Could not find a galaxy named '$option'.");
		}
		$db->query('SELECT sector_has_forces.sector_id AS sector, expire_time
					FROM sector_has_forces
					LEFT JOIN sector USING (sector_id, game_id)
					WHERE sector_has_forces.game_id = ' . $db->escapeNumber($player->getGameID()) . '
						AND galaxy_id = ' . $db->escapeNumber($galaxyId) . '
						AND owner_id IN (
							SELECT account_id FROM player
							WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
								AND alliance_id = ' . $db->escapeNumber($player->getAllianceID()) . '
						)
					ORDER BY expire_time ASC LIMIT 1'
		);
	}

	if ($db->nextRecord()) {
		$sectorId = $db->getInt('sector');
		$expire = $db->getInt('expire_time');

		return array('Forces in sector ' . $sectorId . ' will expire in ' . format_time($expire - time()));
	} else {
		return array('Your alliance does not own any forces in these sectors.');
	}
}
