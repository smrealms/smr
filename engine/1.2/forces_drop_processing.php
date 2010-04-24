<?php
require_once(get_file_loc('smr_sector.inc'));
		$sector = new SMR_SECTOR($player->sector_id, SmrSession::$game_id, SmrSession::$old_account_id);

		require_once(get_file_loc("smr_force.inc"));
$forces_owner	= new SMR_PLAYER($var["owner_id"], $player->game_id);
$forces			= new SMR_FORCE($var["owner_id"], $player->sector_id, $player->game_id);

if ($player->newbie_turns > 0)
	create_error("You can't take/drop forces under newbie protection!");

$db->query("SELECT *
			FROM location
			WHERE game_id = $player->game_id AND
				  sector_id = $player->sector_id
		   ");
if ($db->nf() > 0)
	create_error("You can't drop forces in a sector with a location!");

if ($player->land_on_planet == 'TRUE')
	create_error("You must first launch to drop forces");

// take either from container or request, prefer container
if (isset($var["drop_mines"]))
	$drop_mines = $var["drop_mines"]; else $drop_mines = $_REQUEST['drop_mines'];
if (isset($var["drop_combat_drones"]))
	$drop_combat_drones = $var["drop_combat_drones"]; else $drop_combat_drones = $_REQUEST['drop_combat_drones'];
if (isset($var["drop_scout_drones"]))
	$drop_scout_drones = $var["drop_scout_drones"]; else $drop_scout_drones = $_REQUEST['drop_scout_drones'];
if (isset($var["take_mines"]))
	$take_mines = $var["take_mines"]; else $take_mines = $_REQUEST['take_mines'];
if (isset($var["take_combat_drones"]))
	$take_combat_drones = $var["take_combat_drones"]; else $take_combat_drones = $_REQUEST['take_combat_drones'];
if (isset($var["take_scout_drones"]))
	$take_scout_drones = $var["take_scout_drones"]; else $take_scout_drones = $_REQUEST['take_scout_drones'];

// do we have numbers?
if (isset($drop_mines) && !is_numeric($drop_mines)) create_error("Only numbers as input allowed");
if (isset($drop_combat_drones) && !is_numeric($drop_combat_drones)) create_error("Only numbers as input allowed");
if (isset($drop_scout_drones) && !is_numeric($drop_scout_drones)) create_error("Only numbers as input allowed");
if (isset($take_mines) && !is_numeric($take_mines)) create_error("Only numbers as input allowed");
if (isset($take_combat_drones) && !is_numeric($take_combat_drones)) create_error("Only numbers as input allowed");
if (isset($take_scout_drones) && !is_numeric($take_scout_drones)) create_error("Only numbers as input allowed");

// round if necessary
$drop_mines = round($drop_mines);
$drop_combat_drones = round($drop_combat_drones);
$drop_scout_drones = round($drop_scout_drones);
$take_mines = round($take_mines);
$take_combat_drones = round($take_combat_drones);
$take_scout_drones = round($take_scout_drones);

// so how many forces do we take/add per type?
$change_mines = $drop_mines - $take_mines;
$change_combat_drones = $drop_combat_drones - $take_combat_drones;
$change_scout_drones = $drop_scout_drones - $take_scout_drones;

include(get_file_loc('mine_change.php'));
// check max on that stack
if ($forces->mines + $change_mines > 50)
	create_error("This stack can only take up to 50 mines!");

if ($forces->combat_drones + $change_combat_drones > 50)
	create_error("This stack can only take up to 50 combat drones!");

if ($forces->scout_drones + $change_scout_drones > 5)
	create_error("This stack can only take up to 5 scout drones!");

// do we have any action at all?
if ($change_mines == 0 && $change_combat_drones == 0 && $change_scout_drones == 0)
	create_error("You want to add/remove 0 forces?");

// combat drones
if ($change_combat_drones != 0) {

	// we can't take more forces than are in sector
	if ($forces->combat_drones + $change_combat_drones < 0)
		create_error("You can't take more combat drones than are on this stack!");

	if ($ship->hardware[HARDWARE_COMBAT] - $change_combat_drones > $ship->max_hardware[HARDWARE_COMBAT])
		create_error("Your ships supports not more than " . $ship->max_hardware[HARDWARE_COMBAT] . " combat drones!");

	if ($ship->hardware[HARDWARE_COMBAT] - $change_combat_drones < 0)
		create_error("You can't drop more combat drones than you carry!");

	// remove from ship
	$ship->hardware[HARDWARE_COMBAT] -= $change_combat_drones;
	$ship->old_hardware[HARDWARE_COMBAT] = $ship->hardware[HARDWARE_COMBAT];

	// drop in sector
	$forces->combat_drones += $change_combat_drones;

}

if ($change_scout_drones != 0) {

	// we can't take more forces than are in sector
	if ($forces->scout_drones + $change_scout_drones < 0)
		create_error("You can't take more scout drones than are on this stack!");

	if ($ship->hardware[HARDWARE_SCOUT] - $change_scout_drones > $ship->max_hardware[HARDWARE_SCOUT])
		create_error("Your ships supports not more than " . $ship->max_hardware[HARDWARE_SCOUT] . " scout drones!");

	if ($ship->hardware[HARDWARE_SCOUT] - $change_scout_drones < 0)
		create_error("You can't drop more scout drones than you carry!");

	// remove from ship
	$ship->hardware[HARDWARE_SCOUT] -= $change_scout_drones;

	// drop in sector
	$forces->scout_drones += $change_scout_drones;

}

if ($change_mines != 0) {

	// we can't take more forces than are in sector
	if ($forces->mines + $change_mines < 0)
		create_error("You can't take more mines than are on this stack!");

	if ($ship->hardware[HARDWARE_MINE] - $change_mines > $ship->max_hardware[HARDWARE_MINE])
		create_error("Your ships supports not more than " . $ship->max_hardware[HARDWARE_MINE] . " mines!");

	if ($ship->hardware[HARDWARE_MINE] - $change_mines < 0)
		create_error("You can't drop more mines than you carry!");

	// remove from ship
	$ship->hardware[HARDWARE_MINE] -= $change_mines;

	// drop in sector
	$forces->mines += $change_mines;

}

// message to send out
if ($var["owner_id"] != $player->account_id) {

	if ($change_mines > 0)
		$mines_message = "added " . ($drop_mines - $take_mines) . " mine";
	elseif ($change_mines < 0)
		$mines_message = "removed " . abs($drop_mines - $take_mines) . " mine";
	//add s to mine if necesary
	if (abs($change_mines) > 1)
		$mines_message .= "s";

	if ($change_combat_drones > 0)
		$combat_drones_message = "added " . ($drop_combat_drones - $take_combat_drones) . " combat drone";
	elseif ($change_combat_drones < 0)
		$combat_drones_message = "removed " . abs($drop_combat_drones - $take_combat_drones) . " combat drone";
	//add s to drone if necesary
	if (abs($change_combat_drones) > 1)
		$combat_drones_message .= "s";

	if ($change_scout_drones > 0)
		$scout_drones_message = "added " . ($drop_scout_drones - $take_scout_drones) . " scout drone";
	elseif ($change_scout_drones < 0)
		$scout_drones_message = "removed " . abs($drop_scout_drones - $take_scout_drones) . " scout drone";
	//add s to drone if necesary
	if (abs($change_scout_drones) > 1)
		$scout_drones_message .= "s";

	// now compile it together
	$message = "$player->player_name has " . $mines_message;

	if (isset($mines_message) && isset($combat_drones_message) && !isset($scout_drones_message))
		$message .= " and $combat_drones_message";
	elseif (isset($mines_message) && isset($combat_drones_message))
		$message .= ", $combat_drones_message";
	elseif (!isset($mines_message) && isset($combat_drones_message))
		$message .= "$combat_drones_message";

	if (isset($mines_message) && isset($combat_drones_message) && isset($scout_drones_message))
		$message .= ", and $scout_drones_message";
	elseif ((isset($mines_message) || isset($combat_drones_message)) && isset($scout_drones_message))
		$message .= " and $scout_drones_message";
	elseif (!isset($mines_message) && !isset($combat_drones_message) && isset($scout_drones_message))
		$message .= "$scout_drones_message";

	$message .= " from/to your stack in sector #$sector->sector_id.";

	$player->send_message($forces_owner->account_id, MSG_SCOUT, format_string($message, true));

}

$account->log(9, "$change_combat_drones combat drones, $change_scout_drones scout drones, $change_mines mines", $player->sector_id);

// Changed (26/10/05) - scout drones count * 2
if($forces->combat_drones == 0 && $forces->mines == 0 && $forces->scout_drones == 1) {
	$days = 2;
}
else {
	$days = ceil(($forces->combat_drones + $forces->scout_drones + $forces->mines) / 10);
}
if ($days > 5) $days = 5;
$forces->expire = time() + ($days * 86400);

$ship->update_hardware();
$ship->mark_seen();
$forces->update();

forward(create_container("skeleton.php", "current_sector.php"));

?>
