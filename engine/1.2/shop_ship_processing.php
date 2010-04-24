<?php

$speed		= $var["speed"];
$cost		= $var["cost"] - $ship->cost / 2;
$race_id	= $var["race_id"];

// trade master 33
// trip maker 30
//(22,25,23,75,43,55,61,24,21,38,67,33,49)
// Top racials minus ATM + top UG/FED are restricted 

// remove newbie gals
//if ($account->get_rank() < FLEDGLING && $account->veteran == "FALSE" && in_array($var["ship_id"], array(22,25,75,43,55,61,38,67,49)))
//	create_error("You can't buy that ship while still ranked as Newbie or Beginner!");

if ($var["buyer_restriction"] == 2 && $player->alignment > -100)
	create_error("You can't buy smuggler ships!");

if ($var["buyer_restriction"] == 1 && $player->alignment < 100)
	create_error("You can't buy federal ships!");

if ($race_id != 1 && $player->race_id != $race_id)
	create_error("You can't buy racial ships!");

/*if ($player->account_id == 101)
	create_error("Cheaters do NOT get ships!");*/
	
$db->query("SELECT * FROM game WHERE game_id = $player->game_id");
$db->next_record();
$game_speed = $db->f("game_speed");

// do we have enough cash?
if ($player->credits < $cost)
	create_error("You do not have enough cash to purchase this ship!");

// max turns are dependent on game speed
$max_turns = 400 * $game_speed;

// adapt turns
$player->turns = $player->turns * $speed / $ship->speed;
if ($player->turns > $max_turns)
	$player->turns = $max_turns;

// take the money from the user
$player->credits -= $cost;
$player->update();

// assign the new ship
$ship->ship_type_id = $var["ship_id"];

// delete cargo
$ship->remove_all_cargo();

// update
$ship->update();

// get new ship object
$ship = new SMR_SHIP(SmrSession::$old_account_id, SmrSession::$game_id);

// adapt hardware
$db->query("SELECT * FROM hardware_type");
while ($db->next_record()) {

	$hardware_type_id = $db->f("hardware_type_id");

	// take hardware we don't support
	if ($ship->hardware[$hardware_type_id] > $ship->max_hardware[$hardware_type_id])
		$ship->hardware[$hardware_type_id] = $ship->max_hardware[$hardware_type_id];
}

// take weapons that we can't carry
$ship->weapon = array_slice($ship->weapon, 0, $ship->hardpoint);

// disable hardware
$db->query("DELETE FROM ship_is_cloaked WHERE account_id = $player->account_id AND " .
											 "game_id = $player->game_id");
$db->query("DELETE FROM ship_has_illusion WHERE account_id = $player->account_id AND " .
											   "game_id = $player->game_id");

// if we changed max hardware we need to compensate this.
$ship->mark_seen();

// update again
$ship->update_hardware();
$ship->update_weapon();
$ship->mark_seen();

$account->log(10, "Buys a $ship->ship_name for $cost credits", $player->sector_id);

forward(create_container("skeleton.php", "current_sector.php"));

?>
