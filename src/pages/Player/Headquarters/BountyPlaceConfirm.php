<?php declare(strict_types=1);

namespace Smr\Pages\Player\Headquarters;

use AbstractSmrPlayer;
use Menu;
use Smr\Page\PlayerPage;
use Smr\Template;
use SmrPlayer;

class BountyPlaceConfirm extends PlayerPage {

	public string $file = 'bounty_place_confirm.php';

	public function __construct(
		private readonly int $locationID,
		private readonly int $otherPlayerID,
		private readonly int $credits,
		private readonly int $smrCredits
	) {}

	public function build(AbstractSmrPlayer $player, Template $template): void {
		$template->assign('PageTopic', 'Place Bounty');

		Menu::headquarters($this->locationID);

		// get this guy from db
		$bountyPlayer = SmrPlayer::getPlayerByPlayerID($this->otherPlayerID, $player->getGameID());

		$template->assign('Amount', number_format($this->credits));
		$template->assign('SmrCredits', number_format($this->smrCredits));
		$template->assign('BountyPlayer', $bountyPlayer->getLinkedDisplayName());

		$container = new BountyPlaceConfirmProcessor(
			$this->locationID,
			$bountyPlayer->getAccountID(),
			$this->credits,
			$this->smrCredits
		);
		$template->assign('ProcessingHREF', $container->href());
	}

}
