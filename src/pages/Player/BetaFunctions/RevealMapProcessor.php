<?php declare(strict_types=1);

namespace Smr\Pages\Player\BetaFunctions;

use AbstractSmrPlayer;
use Smr\Database;
use SmrPort;

class RevealMapProcessor extends BetaFunctionsPageProcessor {

	public function buildBetaFunctionsProcessor(AbstractSmrPlayer $player): void {
		$account_id = $player->getAccountID();
		$game_id = $player->getGameID();
		// delete all entries from the player_visited_sector/port table
		$db = Database::getInstance();
		$db->write('DELETE FROM player_visited_sector WHERE ' . $player->getSQL());

		// add port infos
		$dbResult = $db->read('SELECT * FROM port WHERE game_id = ' . $db->escapeNumber($game_id));
		foreach ($dbResult->records() as $dbRecord) {
			$port = SmrPort::getPort($game_id, $dbRecord->getInt('sector_id'), false, $dbRecord);
			$port->addCachePort($account_id);
		}
	}

}
