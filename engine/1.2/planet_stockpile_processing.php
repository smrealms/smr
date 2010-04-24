<?php
		require_once(get_file_loc("smr_planet.inc"));
$amount = $_REQUEST['amount'];
if (!is_numeric($amount))
	create_error("Numbers only please");
    
$amount = floor($amount);

if ($amount <= 0)
	create_error("You must actually enter an ammount > 0!");

// get a planet from the sector where the player is in
$planet = new SMR_PLANET($player->sector_id, SmrSession::$game_id);
$action = $_REQUEST['action'];
// transfer to ship
if ($action == "Ship") {

	// do we want transfer more than we have?
	if ($amount > $planet->stockpile[$var["good_id"]])
		create_error("You can't take more than on planet!");

	// do we want to transfer more than we can carry?
	if ($amount > $ship->cargo_left)
		create_error("You can't take more than you can carry!");

	// now transfer
	$planet->stockpile[$var["good_id"]] -= $amount;
	$ship->cargo[$var["good_id"]] += $amount;
	$db->query("SELECT * FROM good WHERE good_id = $var[good_id]");
	$db->next_record();
	$good_name = $db->f("good_name");
	$account->log(11, "Player takes $amount $good_name from planet.", $player->sector_id);

// transfer to planet
} elseif ($action == "Planet") {

	// do we want transfer more than we have?
	if ($amount > $ship->cargo[$var["good_id"]])
		create_error("You can't store more than you carry!");

	// do we want to transfer more than the planet can hold?
	if ($amount > $planet->stockpile_left($var["good_id"]))
		create_error("You can only put 600 per item at planet!");

	// now transfer
	$planet->stockpile[$var["good_id"]] += $amount;
	$ship->cargo[$var["good_id"]] -= $amount;

}

// update both
$planet->update();
$ship->update_cargo();

forward(create_container("skeleton.php", "planet_stockpile.php"));

?>