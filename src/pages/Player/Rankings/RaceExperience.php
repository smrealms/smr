<?php declare(strict_types=1);

namespace Smr\Pages\Player\Rankings;

use AbstractSmrPlayer;
use Menu;
use Rankings;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Template;

class RaceExperience extends PlayerPage {

	use ReusableTrait;

	public string $file = 'rankings_race.php';

	public function build(AbstractSmrPlayer $player, Template $template): void {
		$template->assign('PageTopic', 'Racial Standings');

		Menu::rankings(2, 0);

		$rankedStats = Rankings::raceStats('experience', $player->getGameID());
		$template->assign('Ranks', Rankings::collectRaceRankings($rankedStats, $player));
	}

}
