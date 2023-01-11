<?php declare(strict_types=1);

namespace Smr\Pages\Player\Planet;

use Smr\AbstractPlayer;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Template;

class Ownership extends PlayerPage {

	use ReusableTrait;

	public string $file = 'planet_ownership.php';

	public function build(AbstractPlayer $player, Template $template): void {
		require_once(LIB . 'Default/planet.inc.php');
		planet_common();

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
