<?php declare(strict_types=1);

namespace Smr\Pages\Player\Rankings;

use Smr\AbstractPlayer;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Rankings;
use Smr\Template;

class AllianceExperience extends PlayerPage {

	use ReusableTrait;

	public string $file = 'rankings_alliance_experience.php';

	public function build(AbstractPlayer $player, Template $template): void {
		$template->assign('PageTopic', 'Alliance Experience Rankings');
		Menu::rankings(1, 0);

		$rankedStats = Rankings::allianceStats('experience', $player->getGameID());
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
