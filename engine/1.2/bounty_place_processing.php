<?php
require_once(get_file_loc('smr_sector.inc'));
		$sector = new SMR_SECTOR($player->sector_id, SmrSession::$game_id, SmrSession::$old_account_id);

$container = array();
$container["url"] = "skeleton.php";
if ($sector->has_hq()) {

	$container["body"] = "government.php";
	$type = "HQ";

} else {

	$container["body"] = "underground.php";
	$type = "UG";

}
$action = $_REQUEST['action'];
// if we don't have a yes we leave immediatly
if ($action != "Yes")
	forward($container);

// get values from container
$amount = $var["amount"];
$account_id = $var["account_id"];
if (!$amount)
	create_error("You must enter an amount");
if ($amount < 0)
	create_error("You must enter a positive amount");
// take the bounty from the cash
$player->credits -= $amount;
$player->update();

$db2 = new SmrMySqlDatabase();

$db->query("SELECT * FROM bounty " .
		   "WHERE game_id = $player->game_id AND " .
				 "account_id = $account_id AND " .
				 "claimer_id = 0 AND " .
				 "type = '$type' LIMIT 1");
$time = time();
if ($db->nf()) {

	$db->next_record();
	//$days = ($time - $db->f("time")) / 60 / 60 / 24;
	//$curr_amount = $db->f("amount") * pow(1.05,$days);
	$curr_amount = $db->f("amount");
	$new_amount = $curr_amount + $amount;
	$db2->query("UPDATE bounty SET amount = $new_amount, time = $time WHERE game_id = $player->game_id AND account_id = $account_id AND claimer_id = 0 AND type = '$type'");
	//print("Added bounty....$curr_amount + $amount<br>UPDATE bounty SET amount = $new_amount, time = $time WHERE game_id = $player->game_id AND account_id = $account_id AND type = '$type'");

} else {

	$db->query("INSERT INTO bounty (account_id, game_id, bounty_id, type, claimer_id, amount, time) VALUES ($account_id, $player->game_id, NULL, '$type' , 0, $amount, $time)");
	//print("First<br>INSERT INTO bounty (account_id, game_id, bounty_id, type, claimer_id, amount, time) VALUES ($account_id, $player->game_id, $bounty_id, '$type' , 0, $amount, $time)");

}

$placed = new SMR_PLAYER($account_id, $player->game_id);
$placed->update_stat("bounty_amount_on", $amount);

forward($container);

?>
