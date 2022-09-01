<?php declare(strict_types=1);

use Smr\BountyType;
use Smr\Database;

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$account = $session->getAccount();
$player = $session->getPlayer();

$template->assign('PageTopic', 'Bounty Payout');

Menu::headquarters($var['LocationID']);

if (!isset($var['ClaimText'])) {
	// Determine if we're claiming Fed or UG bounties
	$location = SmrLocation::getLocation($var['LocationID']);
	if ($location->isHQ()) {
		$bounties = $player->getClaimableBounties(BountyType::HQ);
	} elseif ($location->isUG()) {
		$bounties = $player->getClaimableBounties(BountyType::UG);
	}

	$claimText = '';
	if (!empty($bounties)) {
		$claimText .= ('You have claimed the following bounties<br /><br />');

		$db = Database::getInstance();
		foreach ($bounties as $bounty) {
			// get bounty id from db
			$amount = $bounty['credits'];
			$smrCredits = $bounty['smr_credits'];
			// no interest on bounties
			// $time = Epoch::time();
			// $days = ($time - $db->getField('time')) / 60 / 60 / 24;
			// $amount = round($db->getField('amount') * pow(1.05,$days));

			// add bounty to our cash
			$player->increaseCredits($amount);
			$account->increaseSmrCredits($smrCredits);
			$claimText .= ($bounty['player']->getDisplayName() . ' : <span class="creds">' . number_format($amount) . '</span> credits and <span class="red">' . number_format($smrCredits) . '</span> SMR credits<br />');

			// add HoF stat
			$player->increaseHOF(1, ['Bounties', 'Claimed', 'Results'], HOF_PUBLIC);
			$player->increaseHOF($amount, ['Bounties', 'Claimed', 'Money'], HOF_PUBLIC);
			$player->increaseHOF($smrCredits, ['Bounties', 'Claimed', 'SMR Credits'], HOF_PUBLIC);

			// delete bounty
			$db->write('DELETE FROM bounty
						WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
							AND claimer_id = ' . $db->escapeNumber($player->getAccountID()) . '
							AND bounty_id = ' . $db->escapeNumber($bounty['bounty_id']));
		}
	} else {
		$claimText .= ('You have no claimable bounties<br /><br />');
	}

	$var['ClaimText'] = $claimText;
}
$template->assign('ClaimText', $var['ClaimText']);
