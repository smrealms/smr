<?php declare(strict_types=1);

namespace Smr\Pages\Player\Bar;

use Smr\Epoch;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Player;
use Smr\Template;

class BuyTicker extends PlayerPage {

	public function __construct(
		private readonly int $locationID,
	) {}

	public function build(Player $player, Template $template): void {
		$template->pageTopic = 'Buy System';
		Menu::bar($this->locationID);

		//they can buy the ticker...first we need to find out what they want
		$tickers = [];
		foreach ($player->getTickers() as $ticker) {
			$type = $ticker['Type'];
			if ($ticker['Type'] === 'NEWS') {
				$type = 'News Ticker';
			}
			if ($ticker['Type'] === 'SCOUT') {
				$type = 'Scout Message Ticker';
			}
			if ($ticker['Type'] === 'BLOCK') {
				$type = 'Scout Message Blocker';
			}
			$tickers[$type] = $ticker['Expires'] - Epoch::time();
		}
		$template->pageRenderer = fn() => BuyTickerRenderer::render(
			BuyHREF: new BuyTickerProcessor($this->locationID)->href(),
			Tickers: $tickers,
		);
	}

}
