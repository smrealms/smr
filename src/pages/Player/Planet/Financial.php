<?php declare(strict_types=1);

namespace Smr\Pages\Player\Planet;

use Smr\Page\ReusableTrait;
use Smr\Player;
use Smr\Template;

class Financial extends PlanetPage {

	use ReusableTrait;

	public string $file = 'planet_financial.php';

	protected function buildPlanetPage(Player $player, Template $template): void {
	}

}
