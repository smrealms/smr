<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use AbstractSmrPlayer;
use Menu;
use Smr\BountyType;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Template;

class TraderBounties extends PlayerPage {

	use ReusableTrait;

	public string $file = 'trader_bounties.php';

	public function build(AbstractSmrPlayer $player, Template $template): void {
		$template->assign('PageTopic', 'Bounties');

		Menu::trader();

		foreach (BountyType::cases() as $type) {
			if ($player->hasActiveBounty($type)) {
				$bounty = $player->getActiveBounty($type);
				$msg = number_format($bounty->getCredits()) . ' credits and ' . number_format($bounty->getSmrCredits()) . ' SMR credits';
			} else {
				$msg = 'None';
			}
			$template->assign('Bounty' . $type->value, $msg);
		}

		$allClaims = [
			$player->getClaimableBounties(BountyType::HQ),
			$player->getClaimableBounties(BountyType::UG),
		];
		$template->assign('AllClaims', $allClaims);
	}

}
