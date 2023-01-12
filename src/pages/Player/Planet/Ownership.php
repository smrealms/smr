<?php declare(strict_types=1);

namespace Smr\Pages\Player\Planet;

use Smr\AbstractPlayer;
use Smr\Page\ReusableTrait;
use Smr\Template;

class Ownership extends PlanetPage {

	use ReusableTrait;

	public string $file = 'planet_ownership.php';

	protected function buildPlanetPage(AbstractPlayer $player, Template $template): void {
		$container = new OwnershipProcessor();
		$template->assign('ProcessingHREF', $container->href());

		$template->assign('Planet', $player->getSectorPlanet());

		// Check if this player already owns a planet
		$playerPlanet = $player->getPlanet();
		if ($playerPlanet !== null) {
			$template->assign('PlayerPlanet', $playerPlanet->getSectorID());
		}
	}

}
