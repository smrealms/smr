<?php
require_once(get_file_loc('smr_sector.inc'));
		$sector = new SMR_SECTOR($player->sector_id, SmrSession::$game_id, SmrSession::$old_account_id);

print_topic("Bounty Payout");

include(get_file_loc('menue.inc'));
if ($sector->has_hq()) {
	print_hq_menue();
	$db->query("SELECT * FROM bounty WHERE game_id = $player->game_id AND claimer_id = $player->account_id AND type = 'HQ'");
} else {
	print_ug_menue();
	$db->query("SELECT * FROM bounty WHERE game_id = $player->game_id AND claimer_id = $player->account_id AND type = 'UG'");
}

$db2 = new SmrMySqlDatabase();


if ($db->nf()) {

	print("You have claimed the following bounties<br><br>");

	while ($db->next_record()) {

		// get bounty id from db
		$bounty_id = $db->f("bounty_id");
		$acc_id = $db->f("account_id");
		$amount = $db->f("amount");
		// no interest on bounties
		// $time = time();
		// $days = ($time - $db->f("time")) / 60 / 60 / 24;
    	// $amount = round($db->f("amount") * pow(1.05,$days));

		// add bounty to our cash
		$player->credits += $amount;
		$player->update();
		$name = new SMR_PLAYER($acc_id, $player->game_id);
		print("<span style=\"color:yellow;\">$name->player_name</span> : <span style=\"color:red;\">" . number_format($amount) . "</span><br>");

		// add HoF stat
		$player->update_stat("bounties_claimed", 1);
		$player->update_stat("bounty_amount_claimed", $amount);

		// delete bounty
		$db2->query("DELETE FROM bounty
					 WHERE game_id = $player->game_id AND
					 	   claimer_id = $player->account_id AND
					 	   bounty_id = $bounty_id");

	}

} else
	print("You have no claimable bounties<br><br>");

?>