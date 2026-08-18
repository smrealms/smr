<?php declare(strict_types=1);

namespace Smr\Pages\Player\Headquarters;

use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Player;
use Smr\Template;

class MilitaryPaymentClaim extends PlayerPage {

	public function __construct(
		private readonly int $locationID,
		private readonly string $claimText,
	) {}

	public function build(Player $player, Template $template): void {
		$template->pageTopic = 'Military Payment Center';

		Menu::headquarters($this->locationID);

		$template->pageRenderer = fn() => MilitaryPaymentClaimRenderer::render($this->claimText);
	}

}
