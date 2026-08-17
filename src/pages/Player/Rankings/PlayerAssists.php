<?php declare(strict_types=1);

namespace Smr\Pages\Player\Rankings;

use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Pages\Shared\PlayerRankingsRenderer;
use Smr\Player;
use Smr\Rankings;
use Smr\Template;

class PlayerAssists extends PlayerPage {

	public function build(Player $player, Template $template): void {
		$template->pageTopic = 'Assist Rankings';

		Menu::rankings(0, 4);

		$rankedStats = Rankings::playerStats('assists', $player->getGameID());

		// what rank are we?
		$ourRank = Rankings::ourRank($rankedStats, $player->getPlayerID());

		$totalPlayers = count($rankedStats);
		[$minRank, $maxRank] = Rankings::calculateMinMaxRanks($ourRank, $totalPlayers);

		$template->pageRenderer = fn() => PlayerRankingsRenderer::render(
			RankingStat: 'Assists',
			OurRank: $ourRank,
			Rankings: Rankings::collectRankings($rankedStats, $player),
			FilterRankingsHREF: new self()->href(),
			FilteredRankings: Rankings::collectRankings($rankedStats, $player, $minRank, $maxRank),
			MinRank: $minRank,
			MaxRank: $maxRank,
			TotalRanks: $totalPlayers,
			ThisPlayer: $player,
		);
	}

}
