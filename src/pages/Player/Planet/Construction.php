<?php declare(strict_types=1);

namespace Smr\Pages\Player\Planet;

use Smr\AbstractPlayer;
use Smr\Page\ReusableTrait;
use Smr\Template;
use Smr\TradeGood;

class Construction extends PlanetPage {

	use ReusableTrait;

	public string $file = 'planet_construction.php';

	protected function buildPlanetPage(AbstractPlayer $player, Template $template): void {
		$template->assign('Goods', TradeGood::getAll());
	}

}
