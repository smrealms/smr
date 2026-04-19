<?php declare(strict_types=1);

namespace Smr\Pages\Player\Planet;

use Smr\Page\ReusableTrait;
use Smr\Player;
use Smr\Template;
use Smr\TradeGood;

class Construction extends PlanetPage {

	use ReusableTrait;

	public string $file = 'planet_construction.php';

	protected function buildPlanetPage(Player $player, Template $template): void {
		$template->assign('Goods', TradeGood::getAll());
	}

}
