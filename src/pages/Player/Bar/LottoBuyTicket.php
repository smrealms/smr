<?php declare(strict_types=1);

namespace Smr\Pages\Player\Bar;

use Smr\AbstractPlayer;
use Smr\Lotto;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Template;

class LottoBuyTicket extends PlayerPage {

	public string $file = 'bar_lotto_buy.php';

	public function __construct(
		private readonly int $locationID,
	) {}

	public function build(AbstractPlayer $player, Template $template): void {
		$template->assign('PageTopic', 'Galactic Lotto');
		Menu::bar($this->locationID);

		Lotto::checkForLottoWinner($player->getGameID());
		$lottoInfo = Lotto::getLottoInfo($player->getGameID());
		$template->assign('LottoInfo', $lottoInfo);

		$container = new LottoBuyTicketProcessor($this->locationID);
		$template->assign('BuyTicketHREF', $container->href());
	}

}
