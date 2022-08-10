<?php declare(strict_types=1);

namespace Smr\Pages\Player\Headquarters;

use AbstractSmrPlayer;
use Menu;
use Smr\Page\PlayerPage;
use Smr\Template;

class BountyClaim extends PlayerPage {

	public string $file = 'bounty_claim.php';

	public function __construct(
		private readonly int $locationID,
		private readonly string $claimText
	) {}

	public function build(AbstractSmrPlayer $player, Template $template): void {
		$template->assign('PageTopic', 'Bounty Payout');

		Menu::headquarters($this->locationID);

		$template->assign('ClaimText', $this->claimText);
	}

}
