<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Database;
use Smr\Page\PlayerPageProcessor;
use Smr\Port;

class AllianceShareMapsProcessor extends PlayerPageProcessor {

	public function build(AbstractPlayer $player): never {
		// get a list of alliance member (remove current player)
		$memberIDs = $player->getAlliance()->getMemberIDs();
		$alliance_ids = array_diff($memberIDs, [$player->getAccountID()]);

		// end here if we are alone in the alliance
		if (count($alliance_ids) == 0) {
			create_error('Who exactly are you sharing maps with?');
		}

		$unvisitedSectors = $player->getUnvisitedSectors();

		// delete all visited sectors from the table of all our alliance mates
		$db = Database::getInstance();
		$query = 'DELETE
					FROM player_visited_sector
					WHERE account_id IN (:account_ids)
						AND game_id = :game_id';
		$sqlParams = [
			'account_ids' => $db->escapeArray($alliance_ids),
			'game_id' => $db->escapeNumber($player->getGameID()),
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
		$dbResult = $db->read('SELECT sector_id FROM player_visited_port WHERE ' . AbstractPlayer::SQL, $player->SQLID);
		foreach ($dbResult->records() as $dbRecord) {
			$cachedPort = Port::getCachedPort($player->getGameID(), $dbRecord->getInt('sector_id'), $player->getAccountID());
			$cachedPort->addCachePorts($alliance_ids);
		}

		(new AllianceRoster())->go();
	}

}
