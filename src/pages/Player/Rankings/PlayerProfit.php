<?php declare(strict_types=1);

namespace Smr\Pages\Player\Rankings;

use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Pages\Shared\PlayerRankingsRenderer;
use Smr\Player;
use Smr\Rankings;
use Smr\Template;

class PlayerProfit extends PlayerPage {

	use ReusableTrait;
	public function build(Player $player, Template $template): void {
		$template->pageTopic = 'Profit Rankings';

		Menu::rankings(0, 1);

		$hofCategory = ['Trade', 'Money', 'Profit'];
		$rankedStats = Rankings::playerStatsFromHOF($hofCategory, $player->getGameID());

		// what rank are we?
		$ourRank = Rankings::ourRank($rankedStats, $player->getPlayerID());

		$totalPlayers = count($rankedStats);
		[$minRank, $maxRank] = Rankings::calculateMinMaxRanks($ourRank, $totalPlayers);

		$template->pageRenderer = fn() => PlayerRankingsRenderer::render(
			RankingStat: 'Profit',
			OurRank: $ourRank,
			Rankings: Rankings::collectRankings($rankedStats, $player),
			FilterRankingsHREF: (new self())->href(),
			FilteredRankings: Rankings::collectRankings($rankedStats, $player, $minRank, $maxRank),
			MinRank: $minRank,
			MaxRank: $maxRank,
			TotalRanks: $totalPlayers,
			ThisPlayer: $player,
		);
	}

}
