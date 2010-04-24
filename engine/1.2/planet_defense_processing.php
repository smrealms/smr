<?php
		require_once(get_file_loc("smr_planet.inc"));
$amount = $_REQUEST['amount'];
if (!is_numeric($amount))
	create_error("Numbers only please");
    
// only whole numbers allowed
$amount = round($amount);

if ($amount <= 0)
    create_error("You must actually enter an amount > 0!");
if ($player->newbie_turns > 0)
	create_error("You can't drop defenses under newbie protection!");
// get a planet from the sector where the player is in
$planet = new SMR_PLANET($player->sector_id, SmrSession::$game_id);

$type_id = $var["type_id"];
$action = $_REQUEST['action'];
// transfer to ship
if ($action == "Ship") {
	
	include(get_file_loc('planet_defenses_disallow.php'));
    // do the user wants to transfer shields?
    if ($type_id == 1) {

        // do we want transfer more than we have?
        if ($amount > $planet->shields)
            create_error("You can't take more shields from planet than are on it!");

        // do we want to transfer more than we can carry?
        if ($amount > $ship->max_hardware[1] - $ship->hardware[1])
            create_error("You can't take more shields than you can carry!");

        // now transfer
        $planet->shields -= $amount;
        $ship->hardware[1] += $amount;
        $account->log(11, "Player takes $amount shields from planet.", $player->sector_id);

    // do the user wants to transfer drones?
    } else if ($type_id == 4) {

        // do we want transfer more than we have?
        if ($amount > $planet->drones)
            create_error("You can't take more drones from planet than are on it!");

        // do we want to transfer more than we can carry?
        if ($amount > $ship->max_hardware[4] - $ship->hardware[4])
            create_error("You can't take more drones than you can carry!");

        // now transfer
        $planet->drones -= $amount;
        $ship->hardware[4] += $amount;
        $account->log(11, "Player takes $amount drones from planet.", $player->sector_id);

    }

} elseif ($action == "Planet") {

	include(get_file_loc('planet_defenses_disallow.php'));
    // do the user wants to transfer shields?
    if ($type_id == 1) {

        // do we want transfer more than we have?
        if ($amount > $ship->hardware[1])
            create_error("You can't transfer more shields than you carry!");

        // do we want to transfer more than the planet can hold?
        if ($amount + $planet->shields > $planet->construction[1] * 100)
            create_error("The planet can't hold more than " . ($planet->construction[1] * 100) . " shields!");

        // now transfer
        $planet->shields += $amount;
        $ship->hardware[1] -= $amount;
		$account->log(11, "Player puts $amount shields on planet.", $player->sector_id);
    // do the user wants to transfer drones?
    } else if ($type_id == 4) {

        // do we want transfer more than we have?
        if ($amount > $ship->hardware[4])
            create_error("You can't transfer more combat drones than you carry!");

        // do we want to transfer more than we can carry?
        if ($amount + $planet->drones > $planet->construction[2] * 20)
            create_error("The planet can't hold more than " . ($planet->construction[2] * 20) . " drones!");

        // now transfer
        $planet->drones += $amount;
        $ship->hardware[4] -= $amount;
        $account->log(11, "Player puts $amount drones on planet.", $player->sector_id);

    }

} else
    create_error("You must choose if you want to transfer to planet or to the ship!");

$ship->mark_seen();

// update both
$planet->update();

$ship->update_hardware();
$ship->mark_seen();

forward(create_container("skeleton.php", "planet_defense.php"));

?>