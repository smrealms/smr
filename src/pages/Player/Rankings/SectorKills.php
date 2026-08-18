<?php declare(strict_types=1);

namespace Smr\Pages\Player\Rankings;

use Smr\Database;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Player;
use Smr\Rankings;
use Smr\Template;

class SectorKills extends PlayerPage {

	use ReusableTrait;
	public function build(Player $player, Template $template): void {
		$template->pageTopic = 'Sector Death Rankings';

		Menu::rankings(3, 0);

		$db = Database::getInstance();
		$dbResult = $db->read('SELECT sector_id, battles as amount FROM sector WHERE game_id = :game_id ORDER BY battles DESC, sector_id', [
			'game_id' => $db->escapeNumber($player->getGameID()),
		]);
		$rankedStats = [];
		foreach ($dbResult->records() as $dbRecord) {
			$rankedStats[$dbRecord->getInt('sector_id')] = $dbRecord;
		}

		// Calculate the rank of the sector the player is currently in
		$ourRank = Rankings::ourRank($rankedStats, $player->getSectorID());

		$totalSectors = count($rankedStats);
		[$minRank, $maxRank] = Rankings::calculateMinMaxRanks($ourRank, $totalSectors);

		$template->pageRenderer = fn() => SectorKillsRenderer::render(
			TopTen: Rankings::collectSectorRankings($rankedStats, $player),
			SubmitHREF: new self()->href(),
			TopCustom: Rankings::collectSectorRankings($rankedStats, $player, $minRank, $maxRank),
			MinRank: $minRank,
			MaxRank: $maxRank,
		);
	}

}
