<?php declare(strict_types=1);

namespace Smr\Pages\Player\Headquarters;

use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Player;
use Smr\Template;

class BountyPlaceConfirm extends PlayerPage {

	public function __construct(
		private readonly int $locationID,
		private readonly int $otherPlayerID,
		private readonly int $credits,
		private readonly int $smrCredits,
	) {}

	public function build(Player $player, Template $template): void {
		$template->pageTopic = 'Place Bounty';

		Menu::headquarters($this->locationID);

		// get this guy from db
		$bountyPlayer = Player::getPlayerByPlayerID($this->otherPlayerID, $player->getGameID());

		$template->pageRenderer = fn() => BountyPlaceConfirmRenderer::render(
			Amount: number_format($this->credits),
			SmrCredits: number_format($this->smrCredits),
			BountyPlayer: $bountyPlayer->getLinkedDisplayName(),
			ConfirmHREF: new BountyPlaceConfirmProcessor(
				locationID: $this->locationID,
				otherAccountID: $bountyPlayer->getAccountID(),
				credits: $this->credits,
				smrCredits: $this->smrCredits,
			)->href(),
			CancelHREF: new BountyPlace($this->locationID)->href(),
		);
	}

}
