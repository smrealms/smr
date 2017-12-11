<?php
$sector =& $player->getSector();

$template->assign('PageTopic','Bounty Payout');

require_once(get_file_loc('menu.inc'));
if ($sector->hasHQ()) {
	create_hq_menu();
	$bounties = $player->getClaimableBounties('HQ');
}
else {
	create_ug_menu();
	$bounties = $player->getClaimableBounties('UG');
}

$claimText='';

if(!isset($var['ClaimText'])) {
	if (!empty($bounties)) {
		$claimText.=('You have claimed the following bounties<br /><br />');
	
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
			$claimText.=('<span class="yellow">'.$bounty['player']->getPlayerName().'</span> : <span class="creds">' . number_format($amount) . '</span> credits and <span class="red">' . number_format($smrCredits) . '</span> SMR credits<br />');
	
			// add HoF stat
			$player->increaseHOF(1,array('Bounties','Claimed','Results'), HOF_PUBLIC);
			$player->increaseHOF($amount,array('Bounties','Claimed','Money'), HOF_PUBLIC);
			$player->increaseHOF($smrCredits,array('Bounties','Claimed','SMR Credits'), HOF_PUBLIC);
	
			// delete bounty
			$db->query('DELETE FROM bounty
						WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
							AND claimer_id = ' . $db->escapeNumber($player->getAccountID()) . '
							AND bounty_id = ' . $db->escapeNumber($bounty['bounty_id']));
		}
	}
	else {
		$claimText.=('You have no claimable bounties<br /><br />');
	}
	
	SmrSession::updateVar('ClaimText',$claimText);
}
$PHP_OUTPUT.=$var['ClaimText'];
?>
