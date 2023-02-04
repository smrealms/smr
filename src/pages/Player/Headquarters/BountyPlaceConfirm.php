<?php declare(strict_types=1);

namespace Smr\Pages\Player\Headquarters;

use Smr\AbstractPlayer;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Player;
use Smr\Template;

class BountyPlaceConfirm extends PlayerPage {

	public string $file = 'bounty_place_confirm.php';

	public function __construct(
		private readonly int $locationID,
		private readonly int $otherPlayerID,
		private readonly int $credits,
		private readonly int $smrCredits
	) {}

	public function build(AbstractPlayer $player, Template $template): void {
		$template->assign('PageTopic', 'Place Bounty');

		Menu::headquarters($this->locationID);

		// get this guy from db
		$bountyPlayer = Player::getPlayerByPlayerID($this->otherPlayerID, $player->getGameID());

		$template->assign('Amount', number_format($this->credits));
		$template->assign('SmrCredits', number_format($this->smrCredits));
		$template->assign('BountyPlayer', $bountyPlayer->getLinkedDisplayName());

		$container = new BountyPlaceConfirmProcessor(
			locationID: $this->locationID,
			otherAccountID: $bountyPlayer->getAccountID(),
			credits: $this->credits,
			smrCredits: $this->smrCredits,
		);
		$template->assign('ProcessingHREF', $container->href());
	}

}
