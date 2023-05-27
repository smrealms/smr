<?php declare(strict_types=1);

namespace Smr\Pages\Player\Bar;

use Smr\AbstractPlayer;
use Smr\Epoch;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Template;

class BuyGalaxyMap extends PlayerPage {

	public string $file = 'bar_galmap_buy.php';

	public function __construct(
		private readonly int $locationID,
	) {}

	public function build(AbstractPlayer $player, Template $template): void {

		$timeUntilMaps = $player->getGame()->getStartTime() + TIME_MAP_BUY_WAIT - Epoch::time();
		if ($timeUntilMaps > 0) {
			create_error('You cannot buy maps for another ' . format_time($timeUntilMaps) . '!');
		}

		$template->assign('PageTopic', 'Buy Galaxy Maps');
		Menu::bar($this->locationID);

		//find what gal they want
		$container = new BuyGalaxyMapProcessor($this->locationID);
		$template->assign('BuyHREF', $container->href());
	}

}
