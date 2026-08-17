<?php declare(strict_types=1);

namespace Smr\Pages\Player\Planet;

use Smr\Page\ReusableTrait;
use Smr\Player;
use Smr\Template;
use Smr\TradeGood;

class Construction extends PlanetPage {

	use ReusableTrait;
	protected function buildPlanetPage(Player $player, Template $template): void {
		$template->pageRenderer = fn() => ConstructionRenderer::render(
			Goods: TradeGood::getAll(),
			ThisPlanet: $player->getSectorPlanet(),
			ThisPlayer: $player,
			ThisShip: $player->getShip(),
		);
	}

}
