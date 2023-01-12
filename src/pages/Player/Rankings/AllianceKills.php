<?php declare(strict_types=1);

namespace Smr\Pages\Player\Rankings;

use Smr\AbstractPlayer;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Rankings;
use Smr\Template;

class AllianceKills extends PlayerPage {

	use ReusableTrait;

	public string $file = 'rankings_alliance_kills.php';

	public function build(AbstractPlayer $player, Template $template): void {
		$template->assign('PageTopic', 'Alliance Kill Rankings');
		Menu::rankings(1, 2);

		$rankedStats = Rankings::allianceStats('kills', $player->getGameID());
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
