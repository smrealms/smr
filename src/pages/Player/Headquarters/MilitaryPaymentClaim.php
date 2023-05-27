<?php declare(strict_types=1);

namespace Smr\Pages\Player\Headquarters;

use Smr\AbstractPlayer;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Template;

class MilitaryPaymentClaim extends PlayerPage {

	public string $file = 'military_payment_claim.php';

	public function __construct(
		private readonly int $locationID,
		private readonly string $claimText,
	) {}

	public function build(AbstractPlayer $player, Template $template): void {
		$template->assign('PageTopic', 'Military Payment Center');

		Menu::headquarters($this->locationID);

		$template->assign('ClaimText', $this->claimText);
	}

}
