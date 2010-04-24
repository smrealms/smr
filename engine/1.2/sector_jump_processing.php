<?php
require_once(get_file_loc('smr_sector.inc'));
$sector = new SMR_SECTOR($player->sector_id, SmrSession::$game_id, SmrSession::$old_account_id);
if (isset($_POST["to"])) $to = $_POST["to"];
else $to = $var['to'];
//allow hidden players (admins that don't play) to move without pinging, hitting mines, losing turns
if (in_array($player->account_id, $HIDDEN_PLAYERS)) {
	$player->last_sector_id = $player->sector_id;
	$player->sector_id = $to;
	$player->update();
	// We only bother updating if there's a port in sector 
	if(!$sector->visited || $sector->port_visited) $sector->mark_visited();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'current_sector.php';
	forward($container);
}
// include helper funtions
include("course_plot.inc");
$action = $_REQUEST['action'];
if ($action == "No")
	forward(create_container("skeleton.php", $var["target_page"]));

// get from and to sectors
$from = $player->sector_id;

if (empty($to) || $to == '')
	create_error("Where do you want to go today?");
// initialize random generator.
mt_srand((double)microtime()*1000000);

// get our rank
$rank_id = $account->get_rank();

// you can't move while on planet
if ($player->land_on_planet == "TRUE")
	create_error("You are on a planet! You must launch first!");
	
if ($sector->has_forces()) {

	$db->query("SELECT * FROM sector_has_forces, player " .
			   "WHERE sector_has_forces.game_id = player.game_id AND " .
					 "sector_has_forces.owner_id = player.account_id AND " .
					 "sector_has_forces.game_id = $player->game_id AND " .
					 "sector_has_forces.sector_id = $player->sector_id AND " .
					 "mines > 0 AND " .
					 "owner_id != $player->account_id AND " .
					 "(alliance_id != $player->alliance_id OR alliance_id = 0)");
	while ($db->next_record()) {

		// we may skip forces if this is a protected gal.
		if ($sector->is_protected_gal()) {

			$forces_account = new SMR_ACCOUNT();
			$forces_account->get_by_id($db->f("owner_id"));

			// if one is vet and the other is newbie we skip it
			if (different_level($rank_id, $forces_account->get_rank(), $account->veteran, $forces_account->veteran))
				continue;

		}

		create_error("You cant jump when there are unfriendly forces in the sector!");

	}

}

// check for turns
if ($player->turns < 15)
	create_error("You don't have enough turns for that jump!");

// if no 'to' is given we forward to plot
if (empty($to))
	create_error("Where do you want to go today?");

if (!is_numeric($to))
	create_error("Please enter only numbers!");

// ok we can only get the leave save heaven if we go through a warp
if ($action != "Yes") {

// remove newbie gals
//	// are we a noob
//	if ($rank_id < FLEDGLING && $account->veteran == "FALSE") {
//
//		// get new sector object
//		$new_sector = new SMR_SECTOR($to, $player->game_id, $player->account_id);
//
//		// are we going to leave the save heaven?
//		if ($sector->is_protected_gal() && !$new_sector->is_protected_gal()) {
//
//			$container = create_container("skeleton.php", "leaving_newbie_galaxy.php");
//			$container["target_sector"] = $to;
//			$container["method"] = "jump";
//
//			transfer("target_page");
//
//			forward($container);
//
//		}
//
//	}

}

// create sector object for target sector
$target_sector = new SMR_SECTOR($to, SmrSession::$game_id, SmrSession::$old_account_id);

// check if we would jump more than 1 warp
if ($sector->galaxy_id != $target_sector->galaxy_id) {

	//we need to see if they can jump this many gals
	$db->query("SELECT * FROM warp WHERE game_id = $player->game_id");
	while($db->next_record()) {

		$warp_sector1 = new SMR_SECTOR($db->f("sector_id_1"), SmrSession::$game_id, SmrSession::$old_account_id);
		$warp_sector2 = new SMR_SECTOR($db->f("sector_id_2"), SmrSession::$game_id, SmrSession::$old_account_id);

		if ($warp_sector1->galaxy_id == $target_sector->galaxy_id && $warp_sector2->galaxy_id == $sector->galaxy_id)
			$allowed = true;
		if ($warp_sector1->galaxy_id == $sector->galaxy_id && $warp_sector2->galaxy_id == $target_sector->galaxy_id)
			$allowed = true;

	}

} else
	$allowed = true;

if (!$allowed)
	create_error("You can not jump that many galaxies away");

// for ingal jumps we use different algorithm
if ($sector->galaxy_id == $target_sector->galaxy_id) {

	//FIXME: We should use the distance plotter here, but the course plotter is better tested
	$plotter = new Course_Plotter();
	$plotter->set_course($from,$to,$player->game_id);
	$plotter->plot();
	$distance=$plotter->plotted_course[0];

	// calculate the number of free sectors per jump
	$free_sector = 15 + floor($player->level_id / 10);

	// the rest gets a 10% failure per sector
	if ($distance > $free_sector)
		$failure_chance = 10 * ($distance - $free_sector);
	else
		$failure_chance = 0;

	$failure_distance = round($failure_chance / 10);

} else {

	$failure_chance = 75;
	$failure_distance = round(0.1 * mt_rand(10, 30 + (50 - $player->level_id)));

}

if (mt_rand(1, 100) <= $failure_chance) {

	// we missed the sector

	// initialize the queue. all sectors are queued here during the iterations
	$sector_queue = array();

	// keeps the distance to the start sector
	$sector_distance = array();

	// putting start sector in queues
	array_push($sector_queue, $target_sector->sector_id);
	$sector_distance[$target_sector->sector_id] = 0;

	while (sizeof($sector_queue) > 0) {

		// get current sector and
		$curr_sector_id = array_shift($sector_queue);

		// get the distance for this sector from the source
		$distance = $sector_distance[$curr_sector_id];

		// create a new sector object
		$curr_sector = new SMR_SECTOR($curr_sector_id, SmrSession::$game_id, SmrSession::$old_account_id);

		// enqueue all neighbours
		if ($curr_sector->link_up > 0 && (!isset($sector_distance[$curr_sector->link_up]) || $sector_distance[$curr_sector->link_up] > $distance + 1) && $failure_distance > $distance) {

			array_push($sector_queue, $curr_sector->link_up);
			$sector_distance[$curr_sector->link_up] = $distance + 1;

		}

		if ($curr_sector->link_down > 0 && (!isset($sector_distance[$curr_sector->link_down]) || $sector_distance[$curr_sector->link_down] > $distance + 1) && $failure_distance > $distance) {

			array_push($sector_queue, $curr_sector->link_down);
			$sector_distance[$curr_sector->link_down] = $distance + 1;

		}

		if ($curr_sector->link_left > 0 && (!isset($sector_distance[$curr_sector->link_left]) || $sector_distance[$curr_sector->link_left] > $distance + 1) && $failure_distance > $distance) {

			array_push($sector_queue, $curr_sector->link_left);
			$sector_distance[$curr_sector->link_left] = $distance + 1;

		}

		if ($curr_sector->link_right > 0 && (!isset($sector_distance[$curr_sector->link_right]) || $sector_distance[$curr_sector->link_right] > $distance + 1) && $failure_distance > $distance) {

			array_push($sector_queue, $curr_sector->link_right);
			$sector_distance[$curr_sector->link_right] = $distance + 1;

		}

	}

	// returns an array that only contain the failure distance
	while (empty($temp_array)) {
		
		$temp_array = array_keys($sector_distance, $failure_distance);
		//gal isn't big enough for that big of a misjump...take 1 of the failure
		$failure_distance -= 1;
		
	}

    // get one element
	$player->sector_id = $temp_array[ array_rand($temp_array) ];

} else {

	// we hit it. exactly
	$player->sector_id = $target_sector->sector_id;

}

// log action
$account->log(5, "Jumps to sector: $to but hits: $player->sector_id", $sector->sector_id);

// send scout msg
$sector->leaving_sector();

//set the last sector
$player->last_sector_id = $sector->sector_id;

// Move the user around (Must be done while holding both sector locks)
$player->take_turns(15);
$player->sector_change();
$player->detected = 'false';
$player->update();

// We need to release the lock on our old sector
release_lock();

// We need a lock on the new sector so that more than one person isn't hitting the same mines
acquire_lock($player->sector_id);



// delete plotted course
$db->query("DELETE FROM player_plotted_course " .
		   "WHERE account_id = $player->account_id AND " .
				 "game_id = $player->game_id");

// get new sector object
$sector = new SMR_SECTOR($player->sector_id, $player->game_id, $player->account_id);

// make current sector visible to him
$sector->mark_visited();

// send scout msg
$sector->entering_sector();

$db->query("SELECT * FROM sector_has_forces, player " .
		   "WHERE sector_has_forces.game_id = player.game_id AND " .
				 "sector_has_forces.owner_id = player.account_id AND " .
				 "sector_has_forces.game_id = $player->game_id AND " .
				 "sector_has_forces.sector_id = $player->sector_id AND " .
				 "mines > 0 AND " .
				 "owner_id != $player->account_id AND " .
				 "(alliance_id != $player->alliance_id OR alliance_id = 0)");

while ($db->next_record()) {

	// we may skip forces if this is a protected gal.
	if ($sector->is_protected_gal()) {

		$forces_account = new SMR_ACCOUNT();
		$forces_account->get_by_id($db->f("owner_id"));

		// if one is vet and the other is newbie we skip it
		if (different_level($rank_id, $forces_account->get_rank(), $account->veteran, $forces_account->veteran))
			continue;

	}

	if ($player->newbie_turns > 0) {

		$container = array();
		$container["url"]		= "skeleton.php";
		$container["body"]		= "current_sector.php";;
		$container["msg"]		= "You have just flown past a sprinkle of mines.<br>Because of your newbie status you have been spared from the harsh reality of the forces.";
		forward($container);

	} else {

    	$owner_id = $db->f("owner_id");
    	include("forces_minefield_processing.php");
    	exit;

	}

}

forward(create_container("skeleton.php", $var["target_page"]));

?>
