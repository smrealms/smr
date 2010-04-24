<?php
require_once(get_file_loc('smr_sector.inc'));
		$sector = new SMR_SECTOR($player->sector_id, SmrSession::$game_id, SmrSession::$old_account_id);
		require_once(get_file_loc("smr_port.inc"));
//kill the dead people
/*
$dead_traders = $var["dead_traders"];
foreach ($dead_traders as $acc_id) {

	$dead_acc = new SMR_PLAYER($acc_id, $player->game_id);
	$dead_acc->died_by_port();
	$dead_ship = new SMR_SHIP($acc_id, $player->game_id);
	$dead_ship->get_pod();

}*/
//make sure the planet is actually alive
if (!isset($var["dead"])) {

	print("<p><big><b>Port Results</b></big></p>");

	foreach ($var["portdamage"] as $msg) {

		//all this info comes from the last page look there for more info
		//this is the planet shooting.
		print("$msg<br>");

	}

	// port shot show the pic
	print("<p><img src=\"images/creonti_cruiser.jpg\"></p>");
	print("<p><big><b>Attacker Results</b></big></p>");

	//now we need to get the player shooting results
	foreach ($var["attacker"] as $playerdamage) {

		foreach ($playerdamage as $msg) {

			//same as above...came from port_attack_processing.php
			//players have shot here
			print("$msg<br>");

		}

	}

} else
	print("The port has no more defences!<br><br>");

//now we have all the info we need so lets check if this player can fire again
if ($sector->has_port()) {

	$port = new SMR_PORT($player->sector_id, $player->game_id);
	if ($port->shields > 0 || $port->drones > 0 || $port->armor > 0) {

		//we can fire again
		print_form(create_container("port_attack_processing.php", ""));
		print_submit("Continue Attack (3)");
		print("</form>");

	} else {

		//we can now claim
		print_form(create_container("port_claim_processing.php", ""));
		print_submit("Claim the port for your race");
		print("</form>");

		print_form(create_container("skeleton.php", "port_loot.php"));
		print_submit("Loot the port");
		print("</form>");

	}

}

?>