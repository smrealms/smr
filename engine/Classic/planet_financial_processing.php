<?php
		require_once(get_file_loc("smr_planet.inc"));
$planet = new SMR_PLANET($player->sector_id, $player->game_id);
$action = $_REQUEST['action'];
$amount = $_REQUEST['amount'];
if ($action == "Deposit" || $action == "Withdraw") {

	if (!is_numeric($amount))
		create_error("Numbers only please");

	// only whole numbers allowed
	$amount = floor($amount);

	// no negative amounts are allowed
	if ($amount <= 0)
		create_error("You must actually enter an amount > 0!");

	if ($action == "Deposit") {

		if ($player->credits < $amount)
			create_error("You don't own that much money!");

		$player->credits -= $amount;
		$planet->credits += $amount;
		$account->log(4, "Player puts $amount credits on planet", $player->sector_id);

	} elseif ($action == "Withdraw") {

		if ($planet->credits < $amount)
			create_error("There are not enough credits in the planetary account!");

		$player->credits += $amount;
		$planet->credits -= $amount;
		$account->log(4, "Player takes $amount credits from planet", $player->sector_id);

	}

	$player->update();
	$planet->update();

} elseif ($action == "Bond It!") {

	// add it to bond
	$planet->bonds += $planet->credits;

	// set free cash to 0
	$planet->credits = 0;

	// initialize time
	$planet->maturity = time() + round(172800 / $player->game_speed);

	// save to db
	$planet->update();
	$account->log(4, "Player bonds $planet->credits credits at planet.", $player->sector_id);
}

forward(create_container("skeleton.php", "planet_financial.php"));

?>