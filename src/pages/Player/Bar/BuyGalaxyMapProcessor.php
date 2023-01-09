<?php declare(strict_types=1);

namespace Smr\Pages\Player\Bar;

use Smr\AbstractPlayer;
use Smr\Database;
use Smr\Epoch;
use Smr\Galaxy;
use Smr\Page\PlayerPageProcessor;
use Smr\Request;

class BuyGalaxyMapProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $locationID
	) {}

	public function build(AbstractPlayer $player): never {
		$account = $player->getAccount();

		$timeUntilMaps = $player->getGame()->getStartTime() + TIME_MAP_BUY_WAIT - Epoch::time();
		if ($timeUntilMaps > 0) {
			create_error('You cannot buy maps for another ' . format_time($timeUntilMaps) . '!');
		}

		if ($account->getTotalSmrCredits() < CREDITS_PER_GAL_MAP) {
			create_error('You don\'t have enough SMR Credits. Donate to SMR to gain SMR Credits!');
		}

		//gal map buy
		$galaxyID = Request::getInt('gal_id');

		//get start sector
		$galaxy = Galaxy::getGalaxy($player->getGameID(), $galaxyID);
		$low = $galaxy->getStartSector();
		//get end sector
		$high = $galaxy->getEndSector();

		// Have they already got this map? (Are there any unexplored sectors?
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT 1 FROM player_visited_sector WHERE sector_id >= ' . $db->escapeNumber($low) . ' AND sector_id <= ' . $db->escapeNumber($high) . ' AND ' . $player->getSQL() . ' LIMIT 1');
		if (!$dbResult->hasRecord()) {
			create_error('You already have maps of this galaxy!');
		}

		$player->increaseHOF(1, ['Bar', 'Maps Bought'], HOF_PUBLIC);
		//take money
		$account->decreaseTotalSmrCredits(CREDITS_PER_GAL_MAP);
		//now give maps

		// delete all entries from the player_visited_sector/port table
		$db->write('DELETE FROM player_visited_sector WHERE sector_id >= ' . $db->escapeNumber($low) . ' AND sector_id <= ' . $db->escapeNumber($high) . ' AND ' . $player->getSQL());
		//start section

		// add port infos
		foreach ($galaxy->getPorts() as $port) {
			$port->addCachePort($player->getAccountID());
		}

		$message = '<div class="center">Galaxy maps have been added. Enjoy!</div><br />';
		$container = new BarMain($this->locationID, $message);
		$container->go();
	}

}
