<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\BountyType;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Player;
use Smr\Template;

class TraderBounties extends PlayerPage {

	use ReusableTrait;
	public function build(Player $player, Template $template): void {
		$template->pageTopic = 'Bounties';

		Menu::trader();

		$bounties = [];
		foreach (BountyType::cases() as $type) {
			if ($player->hasActiveBounty($type)) {
				$bounty = $player->getActiveBounty($type);
				$msg = number_format($bounty->getCredits()) . ' credits and ' . number_format($bounty->getSmrCredits()) . ' SMR credits';
			} else {
				$msg = 'None';
			}
			$bounties[$type->value] = $msg;
		}

		$allClaims = [
			$player->getClaimableBounties(BountyType::HQ),
			$player->getClaimableBounties(BountyType::UG),
		];

		$template->pageRenderer = fn() => TraderBountiesRenderer::render(
			AllClaims: $allClaims,
			BountyHQ: $bounties[BountyType::HQ->value],
			BountyUG: $bounties[BountyType::UG->value],
		);
	}

}
