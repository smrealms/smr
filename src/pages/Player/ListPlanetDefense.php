<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Pages\Shared\PlanetListRenderer;
use Smr\PlanetList;
use Smr\Player;
use Smr\Template;

class ListPlanetDefense extends PlayerPage {

	use ReusableTrait;
	public function __construct(
		private readonly int $allianceID,
	) {}

	public function build(Player $player, Template $template): void {
		Menu::planetList($this->allianceID, 0);
		$planetList = PlanetList::common($this->allianceID, true);
		$template->pageTopic = ($planetList['Alliance'] === null)
			? 'Planet'
			: 'Planets : ' . $planetList['Alliance']->getAllianceDisplayName();

		$template->pageRenderer = fn() => PlanetListRenderer::render(
			template: $template,
			Alliance: $planetList['Alliance'],
			AllPlanets: $planetList['AllPlanets'],
			PlayerPlanet: $planetList['PlayerPlanet'],
			ThisPlayer: $player,
			Financial: false,
		);
	}

}
