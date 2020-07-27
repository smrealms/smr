<?php declare(strict_types=1);

$template->assign('PageTopic', 'Bounty Payout');

Menu::headquarters();

if (!isset($var['ClaimText'])) {
	// Determine if we're claiming Fed or UG bounties
	$location = SmrLocation::getLocation($var['LocationID']);
	if ($location->isHQ()) {
		$bounties = $player->getClaimableBounties('HQ');
	} elseif ($location->isUG()) {
		$bounties = $player->getClaimableBounties('UG');
	}

	$claimText = '';
	if (!empty($bounties)) {
		$claimText .= ('You have claimed the following bounties<br /><br />');
	
		foreach ($bounties as $bounty) {
			// get bounty id from db
			$amount = $bounty['credits'];
			$smrCredits = $bounty['smr_credits'];
			// no interest on bounties
			// $time = TIME;
			// $days = ($time - $db->getField('time')) / 60 / 60 / 24;
			// $amount = round($db->getField('amount') * pow(1.05,$days));
	
			// add bounty to our cash
			$player->increaseCredits($amount);
			$account->increaseSmrCredits($smrCredits);
			$claimText .= ($bounty['player']->getDisplayName() . ' : <span class="creds">' . number_format($amount) . '</span> credits and <span class="red">' . number_format($smrCredits) . '</span> SMR credits<br />');
	
			// add HoF stat
			$player->increaseHOF(1, array('Bounties', 'Claimed', 'Results'), HOF_PUBLIC);
			$player->increaseHOF($amount, array('Bounties', 'Claimed', 'Money'), HOF_PUBLIC);
			$player->increaseHOF($smrCredits, array('Bounties', 'Claimed', 'SMR Credits'), HOF_PUBLIC);
	
			// delete bounty
			$db->query('DELETE FROM bounty
						WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
							AND claimer_id = ' . $db->escapeNumber($player->getAccountID()) . '
							AND bounty_id = ' . $db->escapeNumber($bounty['bounty_id']));
		}
	} else {
		$claimText .= ('You have no claimable bounties<br /><br />');
	}
	
	SmrSession::updateVar('ClaimText', $claimText);
}
$template->assign('ClaimText', $var['ClaimText']);
