<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Page\PlayerPage;
use Smr\Player;
use Smr\Template;

class BountyView extends PlayerPage {

	public string $file = 'bounty_view.php';

	public function __construct(
		private readonly int $otherAccountID
	) {}

	public function build(AbstractPlayer $player, Template $template): void {
		$bountyPlayer = Player::getPlayer($this->otherAccountID, $player->getGameID());
		$template->assign('PageTopic', 'Viewing Bounties');
		$template->assign('BountyPlayer', $bountyPlayer);
	}

}
