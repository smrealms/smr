<?php declare(strict_types=1);

namespace Smr\Pages\Player\Rankings;

use AbstractSmrPlayer;
use Menu;
use Rankings;
use Smr\Page\PlayerPage;
use Smr\Template;

class PlayerAssists extends PlayerPage {

	public string $file = 'rankings_player_assists.php';

	public function build(AbstractSmrPlayer $player, Template $template): void {
		$template->assign('PageTopic', 'Assist Rankings');

		Menu::rankings(0, 4);

		$rankedStats = Rankings::playerStats('assists', $player->getGameID());

		// what rank are we?
		$ourRank = Rankings::ourRank($rankedStats, $player->getPlayerID());
		$template->assign('OurRank', $ourRank);

		$template->assign('Rankings', Rankings::collectRankings($rankedStats, $player));

		$totalPlayers = count($rankedStats);
		[$minRank, $maxRank] = Rankings::calculateMinMaxRanks($ourRank, $totalPlayers);

		$template->assign('FilterRankingsHREF', (new self())->href());

		$template->assign('FilteredRankings', Rankings::collectRankings($rankedStats, $player, $minRank, $maxRank));
	}

}
