<?php declare(strict_types=1);

namespace Smr\Pages\Player\Bar;

use Smr\Database;
use Smr\Location;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Player;
use Smr\Template;

class BarMain extends PlayerPage {

	public function __construct(
		private readonly int $locationID,
		private readonly ?string $message = null,
	) {}

	public function build(Player $player, Template $template): void {
		//get bar name
		$location = Location::getLocation($player->getGameID(), $this->locationID);
		$template->pageTopic = 'Welcome to ' . $location->getName();
		Menu::bar($this->locationID);

		if ($this->message !== null) {
			$message = $this->message;
		} else {
			$message = '<i>You enter and take a seat at the bar.
			                              The bartender looks like the helpful type.</i>';
		}

		$winningTicket = false;
		//check for winner
		$db = Database::getInstance();
		$dbResult = $db->select('player_has_ticket', ['time' => 0, ...$player->SQLID], ['prize']);
		if ($dbResult->hasRecord()) {
			$winningTicket = $dbResult->record()->getInt('prize');

			$lottoClaimHREF = new LottoClaimProcessor($this->locationID)->href();
		} else {
			$lottoClaimHREF = null;
		}

		$template->pageRenderer = fn() => BarMainRenderer::render(
			Message: $message,
			LottoClaimHREF: $lottoClaimHREF,
			WinningTicket: $winningTicket,
			GossipHREF: new TalkToBartender($this->locationID)->href(),
			BuyDrinkHREF: new BuyDrinkProcessor($this->locationID, 'drink')->href(),
			BuyWaterHREF: new BuyDrinkProcessor($this->locationID, 'water')->href(),
			BuySystemHREF: new BuyTicker($this->locationID)->href(),
			BuyGalMapHREF: new BuyGalaxyMap($this->locationID)->href(),
			LottoBuyHREF: new LottoBuyTicket($this->locationID)->href(),
			BlackjackHREF: new PlayBlackjackBet($this->locationID)->href(),
		);
	}

}
