<?php declare(strict_types=1);

namespace Smr\Pages\Player\Rankings;

use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Pages\Shared\AllianceRankingsRenderer;
use Smr\Player;
use Smr\Rankings;
use Smr\Template;

class AllianceProfit extends PlayerPage {

	public function build(Player $player, Template $template): void {
		$template->pageTopic = 'Alliance Profit Rankings';
		Menu::rankings(1, 1);

		$hofCategory = ['Trade', 'Money', 'Profit'];
		$rankedStats = Rankings::allianceStatsFromHOF($hofCategory, $player->getGameID());
		$ourRank = Rankings::ourAllianceRank($rankedStats, $player);

		$numAlliances = count($rankedStats);
		[$minRank, $maxRank] = Rankings::calculateMinMaxRanks($ourRank, $numAlliances);

		$template->pageRenderer = fn() => AllianceRankingsRenderer::render(
			RankingStat: 'Profit',
			OurRank: $ourRank,
			Rankings: Rankings::collectAllianceRankings($rankedStats, $player),
			FilteredRankings: Rankings::collectAllianceRankings($rankedStats, $player, $minRank, $maxRank),
			FilterRankingsHREF: (new self())->href(),
			MinRank: $minRank,
			MaxRank: $maxRank,
			TotalRanks: $numAlliances,
		);
	}

}
