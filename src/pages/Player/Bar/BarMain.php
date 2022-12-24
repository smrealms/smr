<?php declare(strict_types=1);

namespace Smr\Pages\Player\Bar;

use AbstractSmrPlayer;
use Menu;
use Smr\Database;
use Smr\Page\PlayerPage;
use Smr\Template;
use SmrLocation;

class BarMain extends PlayerPage {

	public string $file = 'bar_main.php';

	public function __construct(
		private readonly int $locationID,
		private readonly ?string $message = null
	) {}

	public function build(AbstractSmrPlayer $player, Template $template): void {
		//get bar name
		$location = SmrLocation::getLocation($player->getGameID(), $this->locationID);
		$template->assign('PageTopic', 'Welcome to ' . $location->getName());
		Menu::bar($this->locationID);

		if ($this->message !== null) {
			$template->assign('Message', $this->message);
		} else {
			$template->assign('Message', '<i>You enter and take a seat at the bar.
			                              The bartender looks like the helpful type.</i>');
		}

		$winningTicket = false;
		//check for winner
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT prize FROM player_has_ticket WHERE ' . $player->getSQL() . ' AND time = 0');
		if ($dbResult->hasRecord()) {
			$winningTicket = $dbResult->record()->getInt('prize');

			$container = new LottoClaimProcessor($this->locationID);
			$template->assign('LottoClaimHREF', $container->href());
		}
		$template->assign('WinningTicket', $winningTicket);

		$container = new TalkToBartender($this->locationID);
		$template->assign('GossipHREF', $container->href());

		$container = new BuyDrinkProcessor($this->locationID, 'drink');
		$template->assign('BuyDrinkHREF', $container->href());
		$container = new BuyDrinkProcessor($this->locationID, 'water');
		$template->assign('BuyWaterHREF', $container->href());

		$container = new BuyTicker($this->locationID);
		$template->assign('BuySystemHREF', $container->href());

		$container = new BuyGalaxyMap($this->locationID);
		$template->assign('BuyGalMapHREF', $container->href());

		$container = new LottoBuyTicket($this->locationID);
		$template->assign('LottoBuyHREF', $container->href());

		$container = new PlayBlackjackBet($this->locationID);
		$template->assign('BlackjackHREF', $container->href());
	}

}
