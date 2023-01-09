<?php declare(strict_types=1);

namespace Smr\Pages\Player\Rankings;

use Smr\AbstractPlayer;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Rankings;
use Smr\Template;

class RaceKills extends PlayerPage {

	use ReusableTrait;

	public string $file = 'rankings_race_kills.php';

	public function build(AbstractPlayer $player, Template $template): void {
		$template->assign('PageTopic', 'Racial Standings');

		Menu::rankings(2, 1);

		$rankedStats = Rankings::raceStats('kills', $player->getGameID());
		$template->assign('Ranks', Rankings::collectRaceRankings($rankedStats, $player));
	}

}
