<?php declare(strict_types=1);

namespace Smr\Pages\Player\Rankings;

use AbstractSmrPlayer;
use Menu;
use Rankings;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Template;

class PlayerExperience extends PlayerPage {

	use ReusableTrait;

	public string $file = 'rankings_player_experience.php';

	public function build(AbstractSmrPlayer $player, Template $template): void {
		$template->assign('PageTopic', 'Experience Rankings');

		Menu::rankings(0, 0);

		$rankedStats = Rankings::playerStats('experience', $player->getGameID());

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
