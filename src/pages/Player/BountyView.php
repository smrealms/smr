<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use AbstractSmrPlayer;
use Smr\Page\PlayerPage;
use Smr\Template;
use SmrPlayer;

class BountyView extends PlayerPage {

	public string $file = 'bounty_view.php';

	public function __construct(
		private readonly int $otherAccountID
	) {}

	public function build(AbstractSmrPlayer $player, Template $template): void {
		$bountyPlayer = SmrPlayer::getPlayer($this->otherAccountID, $player->getGameID());
		$template->assign('PageTopic', 'Viewing Bounties');
		$template->assign('BountyPlayer', $bountyPlayer);
	}

}
