<?php declare(strict_types=1);

namespace Smr\Pages\Player\Planet;

use Smr\AbstractPlayer;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Template;
use Smr\TradeGood;

class Construction extends PlayerPage {

	use ReusableTrait;

	public string $file = 'planet_construction.php';

	public function build(AbstractPlayer $player, Template $template): void {
		require_once(LIB . 'Default/planet.inc.php');
		planet_common();

		$template->assign('Goods', TradeGood::getAll());
	}

}
