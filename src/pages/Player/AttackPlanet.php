<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Combat\Results\PlanetFullCombatResults;
use Smr\Page\PlayerPage;
use Smr\Planet;
use Smr\Player;
use Smr\Template;

class AttackPlanet extends PlayerPage {

	public function __construct(
		private readonly Planet $planet,
		private readonly PlanetFullCombatResults $results,
		bool $playerDied,
	) {
		// If the player died, make sure they see combat results
		$this->skipRedirect = $playerDied;
	}

	public function build(Player $player, Template $template): void {
		$template->pageRenderer = fn() => AttackPlanetRenderer::render(
			template: $template,
			FullPlanetCombatResults: $this->results,
			OverrideDeath: $player->isDead(),
			Planet: $this->planet,
			ThisPlayer: $player,
		);
	}

}
