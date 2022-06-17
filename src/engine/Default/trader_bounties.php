<?php declare(strict_types=1);

use Smr\BountyType;

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$template->assign('PageTopic', 'Bounties');

Menu::trader();

foreach (BountyType::cases() as $type) {
	if ($player->hasCurrentBounty($type)) {
		$bounty = $player->getCurrentBounty($type);
		$msg = number_format($bounty['Amount']) . ' credits and ' . number_format($bounty['SmrCredits']) . ' SMR credits';
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
