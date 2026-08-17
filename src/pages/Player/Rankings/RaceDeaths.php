<?php declare(strict_types=1);

namespace Smr\Pages\Player\Rankings;

use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Pages\Shared\RaceRankingsRenderer;
use Smr\Player;
use Smr\Rankings;
use Smr\Template;

class RaceDeaths extends PlayerPage {

	use ReusableTrait;
	public function build(Player $player, Template $template): void {
		$template->pageTopic = 'Racial Standings';

		Menu::rankings(2, 2);

		$rankedStats = Rankings::raceStats('deaths', $player->getGameID());

		$template->pageRenderer = fn() => RaceRankingsRenderer::render(
			RankingStat: 'Deaths',
			Ranks: Rankings::collectRaceRankings($rankedStats, $player),
			ThisPlayer: $player,
		);
	}

}
