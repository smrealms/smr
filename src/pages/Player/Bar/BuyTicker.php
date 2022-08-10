<?php declare(strict_types=1);

namespace Smr\Pages\Player\Bar;

use AbstractSmrPlayer;
use Menu;
use Smr\Epoch;
use Smr\Page\PlayerPage;
use Smr\Template;

class BuyTicker extends PlayerPage {

	public string $file = 'bar_ticker_buy.php';

	public function __construct(
		private readonly int $locationID
	) {}

	public function build(AbstractSmrPlayer $player, Template $template): void {
		$template->assign('PageTopic', 'Buy System');
		Menu::bar($this->locationID);

		//they can buy the ticker...first we need to find out what they want
		$tickers = [];
		foreach ($player->getTickers() as $ticker) {
			$type = $ticker['Type'];
			if ($ticker['Type'] == 'NEWS') {
				$type = 'News Ticker';
			}
			if ($ticker['Type'] == 'SCOUT') {
				$type = 'Scout Message Ticker';
			}
			if ($ticker['Type'] == 'BLOCK') {
				$type = 'Scout Message Blocker';
			}
			$tickers[$type] = $ticker['Expires'] - Epoch::time();
		}
		$template->assign('Tickers', $tickers);

		$container = new BuyTickerProcessor($this->locationID);
		$template->assign('BuyHREF', $container->href());
	}

}
