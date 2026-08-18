<?php declare(strict_types=1);

namespace Smr\Pages\Player\Bar;

use Smr\Lotto;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Player;
use Smr\Template;

class LottoBuyTicket extends PlayerPage {

	public function __construct(
		private readonly int $locationID,
	) {}

	public function build(Player $player, Template $template): void {
		$template->pageTopic = 'Galactic Lotto';
		Menu::bar($this->locationID);

		Lotto::checkForLottoWinner($player->getGameID());
		$lottoInfo = Lotto::getLottoInfo($player->getGameID());
		$template->pageRenderer = fn() => LottoBuyTicketRenderer::render(
			BuyTicketHREF: new LottoBuyTicketProcessor($this->locationID)->href(),
			LottoInfo: $lottoInfo,
		);
	}

}
