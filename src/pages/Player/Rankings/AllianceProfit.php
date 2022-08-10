<?php declare(strict_types=1);

namespace Smr\Pages\Player\Rankings;

use AbstractSmrPlayer;
use Menu;
use Rankings;
use Smr\Page\PlayerPage;
use Smr\Template;

class AllianceProfit extends PlayerPage {

	public string $file = 'rankings_alliance_profit.php';

	public function build(AbstractSmrPlayer $player, Template $template): void {
		$template->assign('PageTopic', 'Alliance Profit Rankings');
		Menu::rankings(1, 1);

		$hofCategory = ['Trade', 'Money', 'Profit'];
		$rankedStats = Rankings::allianceStatsFromHOF($hofCategory, $player->getGameID());
		$ourRank = 0;
		if ($player->hasAlliance()) {
			$ourRank = Rankings::ourRank($rankedStats, $player->getAllianceID());
			$template->assign('OurRank', $ourRank);
		}

		$template->assign('Rankings', Rankings::collectAllianceRankings($rankedStats, $player));

		$numAlliances = count($rankedStats);
		[$minRank, $maxRank] = Rankings::calculateMinMaxRanks($ourRank, $numAlliances);

		$template->assign('FilteredRankings', Rankings::collectAllianceRankings($rankedStats, $player, $minRank, $maxRank));

		$template->assign('FilterRankingsHREF', (new self())->href());
	}

}
