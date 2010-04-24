<?php
require_once(get_file_loc('smr_sector.inc'));
		$sector = new SMR_SECTOR($player->sector_id, SmrSession::$game_id, SmrSession::$old_account_id);

print_topic("Military Payment Center");

include(get_file_loc('menue.inc'));
if ($sector->has_hq())
	print_hq_menue();
else
	print_ug_menue();

if ($player->military_payment > 0) {

	print("For your military help you have been paid <font color=yellow>$player->military_payment</font> credits");

	$player->update_stat("military_claimed", $player->military_payment);

	// add to our cash
	$player->credits += $player->military_payment;
	$player->military_payment = 0;
	$player->update();

} else
	print("You have done nothing worthy of military payment");

?>