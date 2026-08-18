<?php declare(strict_types=1);

namespace Smr\Pages\Player\Headquarters;

use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Player;
use Smr\Template;

class BountyClaim extends PlayerPage {

	public function __construct(
		private readonly int $locationID,
		private readonly string $claimText,
	) {}

	public function build(Player $player, Template $template): void {
		$template->pageTopic = 'Bounty Payout';

		Menu::headquarters($this->locationID);

		$template->pageRenderer = fn() => BountyClaimRenderer::render($this->claimText);
	}

}
