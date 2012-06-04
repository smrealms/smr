<?
require_once(get_file_loc('SmrSector.class.inc'));
$sector =& SmrSector::getSector(SmrSession::$game_id, $player->getSectorID(), SmrSession::$account_id);

$smarty->assign('PageTopic','Bounty Payout');

include(ENGINE . 'global/menue.inc');
if ($sector->has_hq()) {
	$PHP_OUTPUT.=create_hq_menue();
	$db->query('SELECT * FROM bounty WHERE game_id = '.$player->getGameID().' AND claimer_id = '.$player->getAccountID().' AND type = \'HQ\'');
} else {
	$PHP_OUTPUT.=create_ug_menue();
	$db->query('SELECT * FROM bounty WHERE game_id = '.$player->getGameID().' AND claimer_id = '.$player->getAccountID().' AND type = \'UG\'');
}

$db2 = new SmrMySqlDatabase();


if ($db->nf()) {

	$PHP_OUTPUT.=('You have claimed the following bounties<br /><br />');

	while ($db->next_record()) {

		// get bounty id from db
		$bounty_id = $db->f('bounty_id');
		$acc_id = $db->f('account_id');
		$amount = $db->f('amount');
		// no interest on bounties
		// $time = TIME;
		// $days = ($time - $db->f('time')) / 60 / 60 / 24;
    	// $amount = round($db->f('amount') * pow(1.05,$days));

		// add bounty to our cash
		$player->increaseCredits($amount);
		$name =& SmrPlayer::getPlayer($acc_id, $player->getGameID());
		$PHP_OUTPUT.=('<span style="color:yellow;">'.$name->getPlayerName().'</span> : <span style="color:red;">' . number_format($amount) . '</span><br />');

		// add HoF stat
		$player->increaseHOF(1,'bounties_claimed');
		$player->increaseHOF($amount,'bounty_amount_claimed');

		// delete bounty
		$db2->query('DELETE FROM bounty
					 WHERE game_id = '.$player->getGameID().' AND
					 	   claimer_id = '.$player->getAccountID().' AND
					 	   bounty_id = '.$bounty_id);

	}

} else
	$PHP_OUTPUT.=('You have no claimable bounties<br /><br />');

?>