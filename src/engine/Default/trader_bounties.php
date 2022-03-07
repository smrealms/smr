<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$template->assign('PageTopic', 'Bounties');

Menu::trader();

foreach (['HQ', 'UG'] as $type) {
	if ($player->hasCurrentBounty($type)) {
		$bounty = $player->getCurrentBounty($type);
		$msg = number_format($bounty['Amount']) . ' credits and ' . number_format($bounty['SmrCredits']) . ' SMR credits';
	} else {
		$msg = 'None';
	}
	$template->assign('Bounty' . $type, $msg);
}

$allClaims = [
	$player->getClaimableBounties('HQ'),
	$player->getClaimableBounties('UG'),
];
$template->assign('AllClaims', $allClaims);
