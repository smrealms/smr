<?php declare(strict_types=1);

namespace Smr\Pages\Player\Council;

use Smr\CouncilVoting;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Player;
use Smr\Race;
use Smr\Template;

class ViewCouncil extends PlayerPage {

	use ReusableTrait;
	public function __construct(
		private readonly int $raceID,
	) {}

	public function build(Player $player, Template $template): void {
		$raceID = $this->raceID;

		$template->pageTopic = 'Ruling Council Of ' . Race::getName($raceID);

		Menu::council($raceID);

		// check for relations here
		CouncilVoting::modifyRelations($raceID, $player->getGameID());
		CouncilVoting::checkPacts($raceID, $player->getGameID());

		$template->pageRenderer = fn() => ViewCouncilRenderer::render(
			template: $template,
			RaceID: $raceID,
			ThisPlayer: $player,
		);
	}

}
