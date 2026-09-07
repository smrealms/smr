<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Database;
use Smr\Page\PlayerPageProcessor;
use Smr\Player;
use Smr\Port;

class AllianceShareMapsProcessor extends PlayerPageProcessor {

	public function build(Player $player): never {
		// get a list of alliance member (remove current player)
		$alliance = $player->getAlliance();
		$memberPlayerIDs = array_keys($alliance->getMembers(includeNpc: false));
		$alliancePlayerIDs = array_diff($memberPlayerIDs, [$player->getPlayerID()]);

		// end here if we are alone in the alliance
		if (count($alliancePlayerIDs) === 0) {
			create_error('Who exactly are you sharing maps with?');
		}

		$unvisitedSectors = $player->getUnvisitedSectors();

		// delete all visited sectors from the table of all our alliance mates
		$db = Database::getInstance();
		$query = 'DELETE
					FROM player_visited_sector
					WHERE player_id IN (:player_ids)';
		$sqlParams = [
			'player_ids' => $db->escapeArray($alliancePlayerIDs),
		];
		if (count($unvisitedSectors) > 0) {
			$sqlParams['sector_ids'] = $db->escapeArray($unvisitedSectors);
			$db->write($query . ' AND sector_id NOT IN (:sector_ids)', $sqlParams);
		} else {
			$db->write($query, $sqlParams);
		}

		// free some memory
		unset($unvisitedSectors);

		// get a list of all visited ports
		$dbResult = $db->select('player_visited_port', $player->SQLID, ['sector_id']);
		foreach ($dbResult->records() as $dbRecord) {
			$cachedPort = Port::getCachedPort(
				gameID: $player->getGameID(),
				sectorID: $dbRecord->getInt('sector_id'),
				playerID: $player->getPlayerID(),
			);
			$cachedPort->addCachePorts($alliancePlayerIDs);
		}

		new AllianceRoster()->go();
	}

}
