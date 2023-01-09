<?php declare(strict_types=1);

namespace Smr\Pages\Player\Planet;

use Smr\AbstractPlayer;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Template;

class Financial extends PlayerPage {

	use ReusableTrait;

	public string $file = 'planet_financial.php';

	public function build(AbstractPlayer $player, Template $template): void {
		require_once(LIB . 'Default/planet.inc.php');
		planet_common();
	}

}
