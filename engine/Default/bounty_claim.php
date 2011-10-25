<?php
$sector =& $player->getSector();

$template->assign('PageTopic','Bounty Payout');

include(get_file_loc('menue.inc'));
if ($sector->has_hq())
{
	$PHP_OUTPUT.=create_hq_menue();
	$db->query('SELECT * FROM bounty WHERE game_id = '.$player->getGameID().' AND claimer_id = '.$player->getAccountID().' AND type = \'HQ\'');
}
else
{
	$PHP_OUTPUT.=create_ug_menue();
	$db->query('SELECT * FROM bounty WHERE game_id = '.$player->getGameID().' AND claimer_id = '.$player->getAccountID().' AND type = \'UG\'');
}

$claimText='';

if(!isset($var['ClaimText']))
{
	if ($db->getNumRows())
	{
		$claimText.=('You have claimed the following bounties<br /><br />');
		$db2 = new SmrMySqlDatabase();
	
		while ($db->nextRecord())
		{
			// get bounty id from db
			$bounty_id = $db->getField('bounty_id');
			$acc_id = $db->getField('account_id');
			$amount = $db->getField('amount');
			$smrCredits = $db->getField('smr_credits');
			// no interest on bounties
			// $time = TIME;
			// $days = ($time - $db->getField('time')) / 60 / 60 / 24;
	    	// $amount = round($db->getField('amount') * pow(1.05,$days));
	
			// add bounty to our cash
			$player->increaseCredits($amount);
			$account->increaseSmrCredits($smrCredits);
			$name =& SmrPlayer::getPlayer($acc_id, $player->getGameID());
			$claimText.=('<span class="yellow">'.$name->getPlayerName().'</span> : <span class="creds">' . number_format($amount) . '</span> credits and <span class="red">' . number_format($smrCredits) . '</span> SMR credits<br />');
	
			// add HoF stat
			$player->increaseHOF(1,array('Bounties','Claimed','Results'), HOF_PUBLIC);
			$player->increaseHOF($amount,array('Bounties','Claimed','Money'), HOF_PUBLIC);
			$player->increaseHOF($smrCredits,array('Bounties','Claimed','SMR Credits'), HOF_PUBLIC);
	
			// delete bounty
			$db2->query('DELETE FROM bounty
						 WHERE game_id = '.$player->getGameID().' AND
						 	   claimer_id = '.$player->getAccountID().' AND
						 	   bounty_id = '.$bounty_id);
		}
	}
	else
		$claimText.=('You have no claimable bounties<br /><br />');
	
	SmrSession::updateVar('ClaimText',$claimText);
}
$PHP_OUTPUT.=$var['ClaimText'];
?>