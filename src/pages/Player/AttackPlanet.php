<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Combat\Results\Full\PlanetFullCombatResults;
use Smr\Page\PlayerPage;
use Smr\Player;
use Smr\Sector;
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
		// Either player or planet may no longer be in sector
		$sector = Sector::getSector($player->getGameID(), $this->sectorID);
		if (!$sector->hasPlanet()) {
			new CurrentSector(message: 'The planet no longer exists!')->go();
		}
		$planet = $sector->getPlanet();

		$template->pageRenderer = fn() => AttackPlanetRenderer::render(
			template: $template,
			FullPlanetCombatResults: $this->results,
			OverrideDeath: $player->isDead(),
			Planet: $planet,
			ThisPlayer: $player,
		);
	}

}
