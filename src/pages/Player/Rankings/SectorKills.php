<?php declare(strict_types=1);

namespace Smr\Pages\Player\Rankings;

use AbstractSmrPlayer;
use Menu;
use Rankings;
use Smr\Database;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Template;

class SectorKills extends PlayerPage {

	use ReusableTrait;

	public string $file = 'rankings_sector_kill.php';

	public function build(AbstractSmrPlayer $player, Template $template): void {

		$template->assign('PageTopic', 'Sector Death Rankings');

		Menu::rankings(3, 0);

		$db = Database::getInstance();
		$dbResult = $db->read('SELECT sector_id, battles as amount FROM sector WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' ORDER BY battles DESC, sector_id');
		$rankedStats = [];
		foreach ($dbResult->records() as $dbRecord) {
			$rankedStats[$dbRecord->getInt('sector_id')] = $dbRecord;
		}
		$template->assign('TopTen', Rankings::collectSectorRankings($rankedStats, $player));

		// Calculate the rank of the sector the player is currently in
		$ourRank = Rankings::ourRank($rankedStats, $player->getSectorID());

		$totalSectors = count($rankedStats);
		[$minRank, $maxRank] = Rankings::calculateMinMaxRanks($ourRank, $totalSectors);

		$container = new self();
		$template->assign('SubmitHREF', $container->href());

		$template->assign('TopCustom', Rankings::collectSectorRankings($rankedStats, $player, $minRank, $maxRank));
	}

}
