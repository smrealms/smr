<?php declare(strict_types=1);

namespace Smr\Pages\Player\Rankings;

use Smr\AbstractPlayer;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Rankings;
use Smr\Template;

class PlayerNpcKills extends PlayerPage {

	public string $file = 'rankings_player_npc_kills.php';

	public function build(AbstractPlayer $player, Template $template): void {
		$template->assign('PageTopic', 'NPC Kill Rankings');

		Menu::rankings(0, 5);

		$hofCategory = ['Killing', 'NPC Kills'];
		$rankedStats = Rankings::playerStatsFromHOF($hofCategory, $player->getGameID());

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
