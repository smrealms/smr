<?php declare(strict_types=1);

namespace Smr\Pages\Player\Rankings;

use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Pages\Shared\RaceRankingsRenderer;
use Smr\Player;
use Smr\Rankings;
use Smr\Template;

class RaceKills extends PlayerPage {

	use ReusableTrait;
	public function build(Player $player, Template $template): void {
		$template->pageTopic = 'Racial Standings';

		Menu::rankings(2, 1);

		$rankedStats = Rankings::raceStats('kills', $player->getGameID());

		$template->pageRenderer = fn() => RaceRankingsRenderer::render(
			RankingStat: 'Kills',
			Ranks: Rankings::collectRaceRankings($rankedStats, $player),
			ThisPlayer: $player,
		);
	}

}
