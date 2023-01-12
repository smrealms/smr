<?php declare(strict_types=1);

namespace Smr\Pages\Player\Rankings;

use Smr\AbstractPlayer;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Rankings;
use Smr\Template;

class PlayerDeaths extends PlayerPage {

	use ReusableTrait;

	public string $file = 'rankings_player_death.php';

	public function build(AbstractPlayer $player, Template $template): void {
		$template->assign('PageTopic', 'Death Rankings');

		Menu::rankings(0, 3);

		$rankedStats = Rankings::playerStats('deaths', $player->getGameID());

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
