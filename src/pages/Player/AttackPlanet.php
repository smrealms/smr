<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Combat\Results\Full\PlanetFullCombatResults;
use Smr\Page\PlayerPage;
use Smr\Planet;
use Smr\Player;
use Smr\Template;

class AttackPlanet extends PlayerPage {

	public function __construct(
		private readonly int $sectorID,
		private readonly PlanetFullCombatResults $results,
		bool $playerDied,
	) {
		// If the player died, make sure they see combat results
		$this->skipRedirect = $playerDied;
	}

	public function build(Player $player, Template $template): void {
		// Note that either player (due to death) or planet (due to being deleted,
		// e.g. Sentinel Outpost) may no longer be in sector. In the case of a
		// deleted planet, Planet::getPlanet returns an empty Planet object, which
		// will allow the final combat result to be displayed.
		$template->pageRenderer = fn() => AttackPlanetRenderer::render(
			template: $template,
			FullPlanetCombatResults: $this->results,
			OverrideDeath: $player->isDead(),
			Planet: Planet::getPlanet($player->getGameID(), $this->sectorID),
			ThisPlayer: $player,
		);
	}

}
