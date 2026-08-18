<?php declare(strict_types=1);

namespace Smr\Pages\Player\Council;

use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Player;
use Smr\Race;
use Smr\Template;

class MessageCouncil extends PlayerPage {

	use ReusableTrait;
	public function __construct(
		private readonly int $raceID,
	) {}

	public function build(Player $player, Template $template): void {
		$raceName = Race::getName($this->raceID);

		$template->pageTopic = 'Send message to Ruling Council of the ' . $raceName;

		Menu::messages();

		$template->pageRenderer = fn() => MessageCouncilRenderer::render(
			RaceName: $raceName,
			SendHREF: new MessageCouncilProcessor($this->raceID)->href(),
			ThisPlayer: $player,
		);
	}

}
