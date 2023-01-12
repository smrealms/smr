<?php declare(strict_types=1);

namespace Smr\Pages\Player\Planet;

use Smr\AbstractPlayer;
use Smr\Page\ReusableTrait;
use Smr\Template;

class Financial extends PlanetPage {

	use ReusableTrait;

	public string $file = 'planet_financial.php';

	protected function buildPlanetPage(AbstractPlayer $player, Template $template): void {
	}

}
