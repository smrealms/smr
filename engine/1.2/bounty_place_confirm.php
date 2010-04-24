<?php
require_once(get_file_loc('smr_sector.inc'));
		$sector = new SMR_SECTOR($player->sector_id, SmrSession::$game_id, SmrSession::$old_account_id);

// get request variables
$amount = $_REQUEST['amount'];
$account_id = $_REQUEST['account_id'];
if ($account_id == 0) {
	
	print_error("Uhhh...who is [Please Select]?");
	return;
	
}
if (!is_numeric($amount)) {

	print_error("Numbers only please");
	return;

}
$amount = round($amount);
if ($player->credits < $amount) {

	print_error("You dont have that much money.");
	return;

}

if ($amount <= 0) {

	print_error("You must enter an amount greater than 0");
	return;

}

if (empty($amount) || empty($account_id)) {

	print_error("Dont you want to place bounty?");
	return;

}

print_topic("Placing a bounty");

include(get_file_loc('menue.inc'));
if ($sector->has_hq()) print_hq_menue();
else print_ug_menue();

// get this guy from db
$bounty_guy = new SMR_PLAYER($account_id, $player->game_id);

print("Are you sure you want to place a <span style=\"color:yellow;\">" . number_format($amount) .
	  "</span> bounty on <span style=\"color:yellow;\">$bounty_guy->player_name</span>?");

$container = array();
$container["url"] = "bounty_place_processing.php";
$container["account_id"] = $bounty_guy->account_id;
$container["amount"] = $amount;

print_form($container);
print_submit("Yes");
print("&nbsp;&nbsp;");
print_submit("No");
print("</form>");

?>