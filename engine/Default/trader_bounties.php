<?php

$template->assign('PageTopic','Bounties');

Menu::trader();

foreach (array('HQ', 'UG') as $type) {
	if ($player->hasCurrentBounty($type)) {
		$bounty = $player->getCurrentBounty($type);
		$msg = number_format($bounty['Amount']).' credits and '.number_format($bounty['SmrCredits']).' SMR credits';
	} else {
		$msg = 'None';
	}
	$template->assign('Bounty'.$type, $msg);
}

$template->assign('AllClaims', array($player->getClaimableBounties('HQ'),
                                     $player->getClaimableBounties('UG')));
