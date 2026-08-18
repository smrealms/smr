<?php declare(strict_types=1);

namespace Smr\Pages\Player\Rankings;

use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Pages\Shared\RaceRankingsRenderer;
use Smr\Player;
use Smr\Rankings;
use Smr\Template;

class RaceExperience extends PlayerPage {

	use ReusableTrait;
	public function build(Player $player, Template $template): void {
		$template->pageTopic = 'Racial Standings';

		Menu::rankings(2, 0);

		$rankedStats = Rankings::raceStats('experience', $player->getGameID());

		$template->pageRenderer = fn() => RaceRankingsRenderer::render(
			RankingStat: 'Experience',
			Ranks: Rankings::collectRaceRankings($rankedStats, $player),
			ThisPlayer: $player,
		);
	}

}
