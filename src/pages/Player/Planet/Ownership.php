<?php declare(strict_types=1);

namespace Smr\Pages\Player\Planet;

use Smr\Page\ReusableTrait;
use Smr\Player;
use Smr\Template;

class Ownership extends PlanetPage {

	use ReusableTrait;
	protected function buildPlanetPage(Player $player, Template $template): void {
		$container = new OwnershipProcessor();

		// Check if this player already owns a planet
		$playerPlanet = $player->getPlanet()?->getSectorID();

		$template->pageRenderer = fn() => OwnershipRenderer::render(
			ProcessingPage: $container,
			Planet: $player->getSectorPlanet(),
			PlayerPlanet: $playerPlanet,
			ThisPlayer: $player,
		);
	}

}
