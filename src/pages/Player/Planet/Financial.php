<?php declare(strict_types=1);

namespace Smr\Pages\Player\Planet;

use Smr\Page\ReusableTrait;
use Smr\Player;
use Smr\Template;

class Financial extends PlanetPage {

	use ReusableTrait;
	protected function buildPlanetPage(Player $player, Template $template): void {
		$template->pageRenderer = fn() => FinancialRenderer::render(
			ProcessorPage: new FinancialProcessor(),
			ThisPlanet: $player->getSectorPlanet(),
		);
	}

}
