<?php

require_once(get_file_loc('smr_sector.inc'));
$sector = new SMR_SECTOR($player->sector_id, SmrSession::$game_id, SmrSession::$old_account_id);


		require_once(get_file_loc("smr_force.inc"));
if ($player->newbie_turns > 0)
	create_error("You are under newbie protection!");

if ($player->turns < 3)
	create_error("You do not have enough turns to attack these forces!");

$forces = new SMR_FORCE($var["owner_id"], $player->sector_id, $player->game_id);

// take the turns
$player->take_turns(3);
$player->update();

// delete plotted course
$player->delete_plotted_course();

// send message if scouts are present
if ($forces->scout_drones > 0) {

	$message = "Your forces in sector $forces->sector_id are being attacked by $player->player_name";
	$player->send_message($forces->owner_id, MSG_SCOUT, format_string($message, false));
	//insert into ticker
	$owner_id = $var["owner_id"];
	$time = time();
	$db->query("SELECT * FROM player_has_ticker WHERE account_id = $owner_id AND game_id = $player->game_id AND type = 'scout'");
	if ($db->next_record()) {
				
		$db->query("SELECT * FROM player_has_ticker WHERE account_id = $player->account_id AND type = 'block'");
		if (!$db->next_record()) $db->query("UPDATE player_has_ticker SET recent = " . format_string($message, false) . ", time = $time WHERE account_id = $owner_id AND game_id = $player->game_id");
		
	}

}

$container = array();
$container["url"] = "skeleton.php";
$container["body"] = "forces_attack.php";
$container["continue"] = "yes";
$container["forced"] = "no";

// ********************************
// *
// * F o r c e s   a t t a c k
// *
// ********************************

$force_msg = array();

if ($player->alliance_id != 0) {

	$db->query("SELECT * FROM player " .
			   "WHERE game_id = $player->game_id AND " .
					 "alliance_id = $player->alliance_id AND " .
					 "sector_id = $player->sector_id AND " .
					 "land_on_planet = 'FALSE' AND " .
					 "newbie_turns = 0 " .
			   "ORDER BY rand() LIMIT 1");

} else {

	$db->query("SELECT * FROM player " .
			   "WHERE game_id = $player->game_id AND " .
					 "sector_id = $player->sector_id AND " .
					 "account_id = $player->account_id AND " .
					 "land_on_planet = 'FALSE' AND " .
					 "newbie_turns = 0 " .
			   "ORDER BY rand() LIMIT 1");

}
if ($db->next_record()) {

	$curr_attacker = new SMR_PLAYER($db->f("account_id"), SmrSession::$game_id);
	$curr_attacker_ship = new SMR_SHIP($db->f("account_id"), SmrSession::$game_id);
	
	// disable cloak
	$curr_attacker_ship->disable_cloak();
	
	// fed ships take half damage from mines
	if ($curr_attacker_ship->ship_type_id == 20 || $curr_attacker_ship->ship_type_id == 21 || $curr_attacker_ship->ship_type_id == 22)
		$forces_damage = 10;
	else
		$forces_damage = 20;

	// Mines attacking
	if ($forces->mines > 0) {

		//formula......100% - ((your level) + (rand(1,7)*rand(1,7))) mines will hit for 20 damage each.
		$percent_hitting = 100 - (($curr_attacker->level_id) + (mt_rand(1,7) * mt_rand(1,7)));
		//find out how many are going to attack you
		$number_hitting = round($forces->mines * ($percent_hitting / 100));

		// fed ships take half damage from mines
		$damage = $number_hitting * $forces_damage;

		//Does attacker have shields?
		if ($curr_attacker_ship->hardware[HARDWARE_SHIELDS] > 0 && $number_hitting > 0) {

			// do we make more damage than shields left?
			if ($damage > $curr_attacker_ship->hardware[HARDWARE_SHIELDS]) {

				// reduce damage to number of shields left
				$damage = $curr_attacker_ship->hardware[HARDWARE_SHIELDS];

				// calc how many are actually hitting
				$number_hitting = ceil( $damage / $forces_damage );

			}

			// add the force_damage
			$force_damage += $damage;

			// subtract the shield damage
			$curr_attacker_ship->hardware[HARDWARE_SHIELDS] -= $damage;

			// print message
			$force_msg[] = "<span style=\"color:yellow;\">$number_hitting</span> mines kamikaze themselves against <span style=\"color:yellow;\">$curr_attacker->player_name</span>'s ship for <span style=\"color:red;\">$damage</span> shields.";

			//subtract mines that hit
			$forces->mines -= $number_hitting;

		} elseif ($curr_attacker_ship->hardware[HARDWARE_ARMOR] > 0 && $number_hitting > 0) {

			// do we make more damage than armor left?
			if ($damage > $curr_attacker_ship->hardware[HARDWARE_ARMOR]) {

				// reduce damage to number of drones left
				$damage = $curr_attacker_ship->hardware[HARDWARE_ARMOR];

				// calc how many are actually hitting
				$number_hitting = ceil( $damage / $forces_damage );

			}

			//subtract the damage
			$curr_attacker_ship->hardware[HARDWARE_ARMOR] -= $damage;

			// add the force_damage
			$force_damage += $damage;

			// print message
			$force_msg[] = "<span style=\"color:yellow;\">$number_hitting</span> mines kamikaze themselves against <span style=\"color:yellow;\">$curr_attacker->player_name</span>'s ship destroying <span style=\"color:red;\">$damage</span> armor.";

			//subtract mines that hit
			$forces->mines -= $number_hitting;

		}

	}

	// is he dead now?
	if ($curr_attacker_ship->hardware[HARDWARE_SHIELDS] == 0 && $curr_attacker_ship->hardware[HARDWARE_ARMOR] == 0)
		$curr_attacker->mark_dead();

	if ($forces->scout_drones > 0 && $curr_attacker->dead == "FALSE") {

		$number_hitting = $forces->scout_drones;

		// fed ships take half damage from drones
		$damage = $number_hitting * $forces_damage;

		// does the attacker have shields left?
		if ($curr_attacker_ship->hardware[HARDWARE_SHIELDS] > 0) {

			//Can we destroy all the shields or do they not have enough?
			if ($damage > $curr_attacker_ship->hardware[HARDWARE_SHIELDS]) {

				// reduce damage to number of shields left
				$damage = $curr_attacker_ship->hardware[HARDWARE_SHIELDS];

				// calc how many are actually hitting
				$number_hitting = ceil( $damage / $forces_damage );

			}
			
			//scouts kamikaze
			$forces->scout_drones -= $number_hitting;
			
			// accumulate the force_damage
			$force_damage += $damage;

			// subtract the shield damage
			$curr_attacker_ship->hardware[HARDWARE_SHIELDS] -= $damage;

			// print message
			$force_msg[] = "<span style=\"color:yellow;\">$number_hitting</span> scout drones kamikaze themselves against <span style=\"color:yellow;\">$curr_attacker->player_name</span>'s ship destroying <span style=\"color:red;\">$damage</span> shields.";

		// does the attacker has drones left?
		} elseif ($curr_attacker_ship->hardware[HARDWARE_COMBAT] > 0) {

			// do we make more damage than drones left?
			if ($damage > $curr_attacker_ship->hardware[HARDWARE_COMBAT] * 3) {

				// reduce damage to number of drones left
				$damage = $curr_attacker_ship->hardware[HARDWARE_COMBAT] * 3;

				// calc how many are actually hitting
				$number_hitting = ceil( $damage / $forces_damage );

			}
			
			//scouts kamikaze
			$forces->scout_drones -= $number_hitting;

			// add the force_damage
			$force_damage += $damage;

			// subtract the shield damage
			$curr_attacker_ship->hardware[HARDWARE_COMBAT] -= round( $damage / 3 );

			// print message
			$force_msg[] = "<span style=\"color:yellow;\">$number_hitting</span> scout drones kamikaze themselves against <span style=\"color:yellow;\">$curr_attacker->player_name</span>'s ship destroying <span style=\"color:red;\">" . round( $damage / 3 ) . "</span> drones.";

		// does the attacker has armor left?
		} elseif ($curr_attacker_ship->hardware[HARDWARE_ARMOR] > 0) {

			//can we kill all armor?
			if ($damage > $curr_attacker_ship->hardware[HARDWARE_ARMOR]) {

				// reduce damage to number of armor left
				$damage = $curr_attacker_ship->hardware[HARDWARE_ARMOR];

				// calc how many are actually hitting
				$number_hitting = ceil( $damage / $forces_damage );

			}
			
			//scouts kamikaze
			$forces->scout_drones -= $number_hitting;

			// add the force_damage
			$force_damage += $damage;

			//subtract the damage
			$curr_attacker_ship->hardware[HARDWARE_ARMOR] -= $damage;

			// print message
			$force_msg[] = "<span style=\"color:yellow;\">$number_hitting</span> scout drones kamikaze themselves against <span style=\"color:yellow;\">$curr_attacker->player_name</span>'s ship destroying <span style=\"color:red;\">$damage</span> armor.";

		}

	} //end of scout drones

	// is he dead now?
	if ($curr_attacker_ship->hardware[HARDWARE_SHIELDS] == 0 && $curr_attacker_ship->hardware[HARDWARE_ARMOR] == 0)
		$curr_attacker->mark_dead();

	if ($forces->combat_drones > 0 && $curr_attacker->dead == "FALSE") {

		//find out how many are going to attack you
		$number_hitting = round($forces->combat_drones * mt_rand(3, 54) / 100);

		// for drones we adept the force damage.
		// mines and sd's doing 20 damage to normal ships
		// drones only 2 damage
		$forces_damage /= 10;

		// fed ships take half damage from drones
		$damage = $number_hitting * $forces_damage;
		//if we have dcs drones do less
		if ($curr_attacker_ship->hardware[HARDWARE_DCS] == 1)
			$damage = round( $damage / (4 / 3) );

		// does the attacker has shields left?
		if ($curr_attacker_ship->hardware[HARDWARE_SHIELDS] > 0) {

			//Can we destroy all the shields or do they not have enough?
			if ($damage > $curr_attacker_ship->hardware[HARDWARE_SHIELDS]) {

				// reduce damage to number of shields left
				$damage = $curr_attacker_ship->hardware[HARDWARE_SHIELDS];

				// calc how many are actually hitting
				$number_hitting = ceil( $damage / $forces_damage );

			}

			// accumulate the force_damage
			$force_damage += $damage;

			// subtract the shield damage
			$curr_attacker_ship->hardware[HARDWARE_SHIELDS] -= $damage;

			// print message
			$force_msg[] = "<span style=\"color:yellow;\">$number_hitting</span> combat drones drones launch at <span style=\"color:yellow;\">$curr_attacker->player_name</span> destroying <span style=\"color:red;\">$damage</span> shields.";

		// does the attacker has drones left?
		} elseif ($curr_attacker_ship->hardware[HARDWARE_COMBAT] > 0) {

			// do we make more damage than drones left?
			if ($damage > $curr_attacker_ship->hardware[HARDWARE_COMBAT] * 3) {

				// reduce damage to number of drones left
				$damage = $curr_attacker_ship->hardware[HARDWARE_COMBAT] * 3;

				// calc how many are actually hitting
				$number_hitting = ceil( $damage / $forces_damage );

			}

			// add the force_damage
			$force_damage += $damage;

			// subtract the shield damage
			$curr_attacker_ship->hardware[HARDWARE_COMBAT] -= round( $damage / 3 );

			// print message
			$force_msg[] = "<span style=\"color:yellow;\">$number_hitting</span> combat drones drones launch at <span style=\"color:yellow;\">$curr_attacker->player_name</span> destroying <span style=\"color:red;\">$damage</span> drones.";

		// does the attacker has armor left?
		} elseif ($curr_attacker_ship->hardware[HARDWARE_ARMOR] > 0) {

			//can we kill all armor?
			if ($damage > $curr_attacker_ship->hardware[HARDWARE_ARMOR]) {

				// reduce damage to number of armor left
				$damage = $curr_attacker_ship->hardware[HARDWARE_ARMOR];

				// calc how many are actually hitting
				$number_hitting = ceil( $damage / $forces_damage );

			}

			// add the force_damage
			$force_damage += $damage;

			//subtract the damage
			$curr_attacker_ship->hardware[HARDWARE_ARMOR] -= $damage;

			// print message
			$force_msg[] = "<span style=\"color:yellow;\">$number_hitting</span> combat drones drones launch at <span style=\"color:yellow;\">$curr_attacker->player_name</span> destroying <span style=\"color:red;\">$damage</span> armor.";

		}

	}

	// update ship
	$curr_attacker_ship->update_hardware();

	// is he dead now?
	if ($curr_attacker_ship->hardware[HARDWARE_SHIELDS] == 0 && $curr_attacker_ship->hardware[HARDWARE_ARMOR] == 0)
		$curr_attacker->mark_dead();

	// is he dead?
	if ($curr_attacker->dead == "TRUE") {

		// print message
		$force_msg[] = "<span style=\"color:yellow;\">$curr_attacker->player_name</span> is <span style=\"color:red;\">DESTROYED!</span>";

		// run through dead methods for player and ship
		$curr_attacker->died_by_forces($forces->owner_id);
		$curr_attacker_ship->get_pod();

		// if we are the guy who's dead
		if ($curr_attacker->account_id == $player->account_id) {

			// we don't want to get a pod screen
			$curr_attacker->dead = "FALSE";

			// and there shouldn't be a cont' button
			$container["continue"] = "no";

		}

		// make it permanent
		$curr_attacker->update();

	}
	if ($forces->mines < 1 && $forces->combat_drones < 1 && $forces->scout_drones < 1) {
		$attacker_msg[] = "Forces are <span style=\"color:red;\">DESTROYED!</span>";
		$container["continue"] = "no";
	}

}

// print the overall damage
if ($force_damage > 0)
	$force_msg[] = "<br>This team does a total of <span style=\"color:red;\">$force_damage</span> damage in this round of combat.";
else
	$force_msg[] = "<br>This team does no damage at all. You call that a team? They need a better recruiter.";

// ********************************
// *
// * A t t a c k e r   s h o o t s
// *
// ********************************
$attacker_total_msg = array();
$attacker_msg = array();

if ($player->alliance_id != 0) {

	$db->query("SELECT * FROM player " .
			   "WHERE game_id = $player->game_id AND " .
					 "alliance_id = $player->alliance_id AND " .
					 "sector_id = $player->sector_id AND " .
					 "land_on_planet = 'FALSE' AND " .
					 "newbie_turns = 0 " .
			   "ORDER BY rand() LIMIT 10");

} else {

	$db->query("SELECT * FROM player " .
			   "WHERE game_id = $player->game_id AND " .
					 "sector_id = $player->sector_id AND " .
					 "account_id = $player->account_id AND " .
					 "land_on_planet = 'FALSE' AND " .
					 "newbie_turns = 0");

}

$db2 = new SmrMySqlDatabase();

while ($db->next_record() && ($forces->combat_drones > 0 || $forces->scout_drones > 0 || $forces->mines > 0)) {

	$curr_attacker = new SMR_PLAYER($db->f("account_id"), SmrSession::$game_id);
	$curr_attacker_ship = new SMR_SHIP($db->f("account_id"), SmrSession::$game_id);

	// disable cloak
	$curr_attacker_ship->disable_cloak();

	$db2->query("SELECT * FROM ship_has_weapon, weapon_type " .
				"WHERE account_id = $curr_attacker->account_id AND " .
					  "game_id = ".SmrSession::$game_id." AND " .
					  "ship_has_weapon.weapon_type_id = weapon_type.weapon_type_id " .
				"ORDER BY order_id");

	// iterate over all existing weapons
	while ($db2->next_record() && ($forces->combat_drones > 0 || $forces->scout_drones > 0 || $forces->mines > 0)) {

		$weapon_name = $db2->f("weapon_name");
		$shield_damage = $db2->f("shield_damage");
		$armor_damage = $db2->f("armor_damage");
		$accuracy = $db2->f("accuracy");

		if ($forces->mines > 0) {

			if ($armor_damage > 0) {

				// mines take 20 armor damage each
				$mines_dead = round($armor_damage / 20);

				// more damage than mines?
				if ($mines_dead > $forces->mines)
					$mines_dead = $forces->mines;

				// subtract mines that died
				$forces->mines -= $mines_dead;

				// add damage we did
				$attacker_damage += $mines_dead * 20;

				// print message
				$attacker_msg[] = "<span style=\"color:yellow;\">$curr_attacker->player_name</span> fires a $weapon_name at the forces destroying <span style=\"color:red;\">$mines_dead</span> mines.";

			} elseif ($shield_damage > 0)
				$attacker_msg[] = "<span style=\"color:yellow;\">$curr_attacker->player_name</span> fires a $weapon_name at the forces but it proves to be ineffective against the mines.";

		} elseif ($forces->combat_drones > 0) {

			if ($armor_damage > 0 && $forces->combat_drones > 0) {

				// combat drones take 3 armor damage each
				$drones_dead = floor( $armor_damage / 3 );

				// more damage than combat drones?
				if ($drones_dead > $forces->combat_drones)
					$drones_dead = $forces->combat_drones;

				// subtract scouts that died
				$forces->combat_drones -= $drones_dead;

				// add damage we did
				$attacker_damage += $drones_dead * 3;

				// print message
				$attacker_msg[] = "<span style=\"color:yellow;\">$curr_attacker->player_name</span> fires a $weapon_name at the forces and destroys <span style=\"color:red;\">$drones_dead</span> combat drones.";

			} elseif ($armor_damage == 0 && $shield_damage > 0)
				$attacker_msg[] = "<span style=\"color:yellow;\">$curr_attacker->player_name</span> fires a $weapon_name at the forces but it proves to be ineffective against the armor of the drones";

		} elseif ($forces->scout_drones > 0) {

			if ($armor_damage > 0) {

				// scouts take 20 armor damage each
				$scouts_dead = round($armor_damage / 20);

				// more damage than scouts?
				if ($scouts_dead > $forces->scout_drones)
					$scouts_dead = $forces->scout_drones;

				// subtract scouts that died
				$forces->scout_drones -= $scouts_dead;

				// add damage we did
				$attacker_damage += $scouts_dead * 20;

				// print message
				$attacker_msg[] = "<span style=\"color:yellow;\">$curr_attacker->player_name</span> fires a $weapon_name at the forces and destroys <font color=red>$scouts_dead</font> scout drones.";

			} else
				$attacker_msg[] = "<span style=\"color:yellow;\">$curr_attacker->player_name</span> fires a $weapon_name at the forces but it proves to be ineffective against the armor of the drones";

		}

	}

	// do we have drones?
	if ($curr_attacker_ship->hardware[HARDWARE_COMBAT] > 0 && ($forces->mines > 0 || $forces->combat_drones > 0 || $forces->scout_drones > 0)) {

		// Random(3 to 54) + Random(Attacker level/4 to Attacker level)
		$percent_attacking = (mt_rand(3, 53) + mt_rand($curr_attacker->level_id / 4, $curr_attacker->level_id)) / 100;
		$number_attacking = round($percent_attacking * $curr_attacker_ship->hardware[HARDWARE_COMBAT]);

		// can not more attacking than we carry
		if ($number_attacking > $curr_attacker_ship->hardware[HARDWARE_COMBAT])
			$number_attacking = $curr_attacker_ship->hardware[HARDWARE_COMBAT];

		if ($forces->mines > 0) {

			// can we do more damage than mines left?
			if ($number_attacking > $forces->mines)
				$number_attacking = $forces->mines;

			// take mines
			$forces->mines -= $number_attacking;
			$curr_attacker_ship->hardware[HARDWARE_COMBAT] -= $number_attacking;

			// accumulate attacker damage
			$attacker_damage += $number_attacking;

			// print message
			$attacker_msg[] = "<span style=\"color:yellow;\">$number_attacking</span> combat drones kamikaze themselves against the forces destroying <span style=\"color:red;\">$number_attacking</span> mines.";

		// are there drones left?
		} elseif ($forces->combat_drones > 0) {

			// can we do more damage than drones left?
			if ($number_attacking * 2 > $forces->combat_drones * 3)
				$number_attacking = ceil($forces->combat_drones * 3 / 2);

			// cd's doing 2 damage
			$damage = $number_attacking * 2;

			// cd's take 3 damage each
			$forces->combat_drones -= floor($damage / 3);

			// accumulate attacker damage
			$attacker_damage += $damage;

			// print message
			$attacker_msg[] = "<span style=\"color:yellow;\">$curr_attacker->player_name</span> launches <span style=\"color:yellow;\">$number_attacking</span> drones at the forces destroying <span style=\"color:red;\">" . floor ($damage / 20) . "</span> combat drones.";

		// are there scouts left?
		} elseif ($forces->scout_drones > 0) {

			// can we do more damage than scouts left?
			if ($number_attacking > $forces->scout_drones * 10)
				$number_attacking = $forces->scout_drones * 10;

			// cd's doing 2 damage
			$damage = $number_attacking * 2;

			// scouts take 20 damage each
			$forces->scout_drones -= floor($damage / 20);

			// accumulate attacker damage
			$attacker_damage += $damage;

			// print message
			$attacker_msg[] = "<span style=\"color:yellow;\">$curr_attacker->player_name</span> launches <span style=\"color:yellow;\">$number_attacking</span> drones at the forces destroying <span style=\"color:red;\">" . floor ($damage / 20) . "</span> scout drones.";

		}

	} // end of 'do we have drones'

	// are forces dead?
	if ($forces->mines < 1 && $forces->combat_drones < 1 && $forces->scout_drones < 1) {

		$attacker_msg[] = "Forces are <span style=\"color:red;\">DESTROYED!</span>";
		$container["continue"] = "no";

	}

	// print the overall damage
	if ($attacker_damage > 0) {

		$attacker_msg[] = "<span style=\"color:yellow;\">$curr_attacker->player_name</span> does a total of <span style=\"color:red;\">$attacker_damage</span> damage.";

		// 25% of the damage goes to xp
		$curr_attacker->experience += $attacker_damage * .05;

	} else
		$attacker_msg[] = "<span style=\"color:yellow;\">$curr_attacker->player_name</span> does absolutely no damage this round. Send the worthless lout back to the academy!";

	$attacker_team_damage += $attacker_damage;
	$attacker_total_msg[] = $attacker_msg;

	//reset damage for each person and the array
	$attacker_damage = 0;
	$attacker_msg = array();

	$curr_attacker->update();
	$curr_attacker_ship->update_hardware();

}

// recalc forces expiration date
if($forces->combat_drones == 0 && $forces->mines == 0 && $forces->scout_drones == 1) {
	$days = 2;
}
else {
	$days = ceil(($forces->combat_drones + $forces->scout_drones + $forces->mines) / 10);
}
if ($days > 5) $days = 5;
$forces->expire = time() + ($days * 86400);

// update forces
$forces->update();

// print the overall damage
if ($attacker_team_damage > 0)
	$attacker_msg[] = "<br>This team does a total of <span style=\"color:red;\">$attacker_team_damage</span> damage in this round of combat.";
else
	$attacker_msg[] = "<br>This team does no damage at all. You call that a team? They need a better recruiter.";

$attacker_total_msg[] = $attacker_msg;

// info for the next page
$container["force_msg"] = $force_msg;
$container["attacker_total_msg"] = $attacker_total_msg;
transfer("owner_id");
forward($container);

?>
