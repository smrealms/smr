<?php
require_once(get_file_loc('smr_sector.inc'));
		$sector = new SMR_SECTOR($player->sector_id, SmrSession::$game_id, SmrSession::$old_account_id);
		require_once(get_file_loc("smr_port.inc"));
require_once(get_file_loc("smr_battle.inc"));

$port = new SMR_PORT($player->sector_id, $player->game_id);

if ($player->newbie_turns > 0)
	create_error("You are under newbie protection!");

if ($player->is_fed_protected())
	create_error("You are under federal protection! That wouldn't be fair.");

if (!$sector->has_port())
	create_error("There is no port in that sector!");

// check if he got enough turns
if ($player->get_info('turns') < 3)
	create_error("You do not have enough turns to attack this port!");
	
$db->query("SELECT * FROM ship_has_weapon NATURAL JOIN weapon_type " .
				   "WHERE account_id = $player->account_id AND " .
						 "game_id = $player->game_id " .
				   "ORDER BY order_id");
if (!$db->next_record()) {
	
	//do we have drones to attack?
	$db->query("SELECT * FROM ship_has_hardware WHERE hardware_type_id = HARDWARE_COMBAT AND account_id = $player->account_id AND game_id = $player->game_id");
	if (!$db->next_record())
		create_error("What are you gonna do? Insult it to death?");
		
}

//set the attack start time
$time = time();

//only 1 shot per 2 seconds (stop double port raids)
$db->lock("port_attack_times");
//init vars
$db->query("SELECT * FROM port_attack_times WHERE game_id = $player->game_id AND sector_id = $player->sector_id");
if ($db->next_record()) $att_time = $db->f("time");
else $att_time = 0;
while ($att_time + 2 >= $time) {
	
	usleep(50000);
	$db->query("SELECT * FROM port_attack_times WHERE game_id = $player->game_id AND sector_id = $player->sector_id");
	if ($db->next_record()) $att_time = $db->f("time");
	else $att_time = 0;
	$time = time();

}

if ($att_time == 0) $db->query("INSERT INTO port_attack_times (game_id, sector_id, time) VALUES ($player->game_id, $sector->sector_id, $time)");
else $db->query("UPDATE port_attack_times SET time = $time WHERE game_id = $player->game_id AND sector_id = $player->sector_id");
$db->unlock();

// take the turns
$player->take_turns(3);
$player->update();
// db objects for queries
$db2 = new SmrMySqlDatabase();
$db3 = new SmrMySqlDatabase();
$db6 = new SmrMySqlDatabase();

// log action
$account->log(7, "Attacks a level $port->level port", $player->sector_id);

//check to see if the player has been attacking to long.
//find out how much time we have

if ($port->refresh_defense < $time) {

	//defences restock
	$rich_mod = floor(($port->credits) * 1e-7);
	if($rich_mod < 0) $rich_mod = 0;

	$port->shields = ($port->level * 1000 + 1000) + ($rich_mod * 500);
	$port->armor = ($port->level * 1000 + 1000) + ($rich_mod * 500);
	$port->drones = ($port->level * 100 + 100) + ($rich_mod * 50);

	$port->attack_started = $time;
	$port->refresh_defense = $time + ($port->level * 5 * 60);
	$db->query("DELETE FROM player_attacks_port WHERE game_id = ".SmrSession::$game_id." AND sector_id = $sector->sector_id");

	//insert into the news that the port is being attacked.
	if ($player->alliance_id != 0)
		$attack_news = "members of $player->alliance_name";
	else
		$attack_news = $player->get_colored_name();
	$news_message = "<span style=\"color:red;\">*MAYDAY* *MAYDAY*</span> The port in sector <span style=\"color:yellow;\">$sector->sector_id</span> is being attacked by $attack_news. Immediate backup requested!";
	$db2->query("INSERT INTO news " .
				"(game_id, time, news_message) " .
				"VALUES(".SmrSession::$game_id.", " . time() . ", " . format_string($news_message, false) . ")");
	//make sure this msg goes first
	usleep(100000);

}

//make sure there is something to shoot at on the port
if ($port->shields == 0 && $port->drones == 0 && $port->armor == 0) {

	//the port is dead send the user to the next page for diplaying
	$container = array();
	$container["url"] = "skeleton.php";
	$container["body"] = "port_attack.php";
	$container["dead"] = "yes";
	forward($container);

}

$attacker_team = new SMR_BATTLE($player->account_id, $player->game_id, TRUE);

// ********************************
// *
// * P o r t	s h o o t s
// *
// ********************************

// the messages we deliver to next page
$port_msg = array();

// holds all dead people
$dead_traders = array();

// ports have 15 turrets regardless of level
for ($i = 0; $i < 15; $i++) {

	// get one attacker
	$curr_att_id = $attacker_team->next(false);
	$curr_attacker = new SMR_PLAYER($curr_att_id, SmrSession::$game_id);
	$curr_attacker_ship = new SMR_SHIP($curr_attacker->account_id, SmrSession::$game_id);
	if ($curr_attacker->dead == 'TRUE') {

		$port_msg[] = "The port fires at the debris that used to be <span style=\"color:yellow;\">$curr_attacker->player_name</span>.";
		continue;

	}
	$port_acc = ($port->level * 10) - ($curr_attacker->level_id / 2);
	if ($port_acc < 0)
		$port_acc = 0;

	// would the port hit?
	if (mt_rand(1, 100) <= $port_acc) {

		$damage = 250;

		// does the attacker have shields left?
		if ($curr_attacker_ship->hardware[HARDWARE_SHIELDS] > 0) {

			// do we do more damage than shields left?
			if ($damage > $curr_attacker_ship->hardware[HARDWARE_SHIELDS])
				$damage = $curr_attacker_ship->hardware[HARDWARE_SHIELDS];

			// accumulate the port_damage
			$port_damage += $damage;

			// subtract the shield damage from the player
			$curr_attacker_ship->hardware[HARDWARE_SHIELDS] -= $damage;

			// message
			$port_msg[] = "The port fires a turret at <span style=\"color:yellow;\">$curr_attacker->player_name</span> and destroys <span style=\"color:red;\">$damage</span> shields";

		// does the attacker has drones left?
		} elseif ($curr_attacker_ship->hardware[HARDWARE_COMBAT] > 0) {

			// adapt damage for drones
			$damage = round ($damage / 3);

			// do we do more damage than armor left?
			if ($damage > $curr_attacker_ship->hardware[HARDWARE_COMBAT])
				$damage = $curr_attacker_ship->hardware[HARDWARE_COMBAT];

			// accumulate the attacker_damage
			$port_damage += $damage;

			// subtract the armor damage
			$curr_attacker_ship->hardware[HARDWARE_COMBAT] -= $damage;

			// message
			$port_msg[] = "The port fires a turret at <span style=\"color:yellow;\">$curr_attacker->player_name</span> and destroys <span style=\"color:red;\">$damage</span> drones";

		// does the attacker has armor left?
		} elseif ($curr_attacker_ship->hardware[HARDWARE_ARMOR] > 0) {

			// do we do more damage than armor left?
			if ($damage > $curr_attacker_ship->hardware[HARDWARE_ARMOR])
				$damage = $curr_attacker_ship->hardware[HARDWARE_ARMOR];

			// accumulate the attacker_damage
			$port_damage += $damage;

			// subtract the armor damage
			$curr_attacker_ship->hardware[HARDWARE_ARMOR] -= $damage;

			// message
			$port_msg[] =   "The port fires a turret at <span style=\"color:yellow;\">$curr_attacker->player_name</span> and destroys <span style=\"color:red;\">$damage</span> armor";

		}

		// is he dead now?
		if ($curr_attacker_ship->hardware[HARDWARE_SHIELDS] == 0 && $curr_attacker_ship->hardware[HARDWARE_ARMOR] == 0 && !in_array($curr_attacker->account_id, $dead_traders)) {

			// write a message to the array
			$port_msg[] = "<span style=\"color:yellow;\">$curr_attacker->player_name</span> has been <span style=\"color:red;\">DESTROYED</span> by port forces";

			if ($curr_attacker->account_id != $player->account_id)
				$curr_attacker->set_info('dead', "TRUE");

			// add him to the array to process later
			$dead_traders[] = $curr_attacker->account_id;

		}

		//update the hardware
		$curr_attacker_ship->update_hardware();

	} else
		$port_msg[] = "The port fires a turret at <span style=\"color:yellow;\">$curr_attacker->player_name</span> and misses";

}

if ($port->drones > 0) {

	// get one to be shot by drones
	$curr_att_id = $attacker_team->next(false);
	$curr_attacker = new SMR_PLAYER($curr_att_id, SmrSession::$game_id);
	$curr_attacker_ship = new SMR_SHIP($curr_attacker->account_id, SmrSession::$game_id);

	// damage that these drones can do.
	$damage = $port->drones;
	$num_att = $port->drones;

	// adept damage if we carry a dcs
	if ($curr_attacker_ship->hardware[HARDWARE_DCS] == 1)
		$damage = round($damage / (4 / 3) );

	// does the attacker have shields left?
	if ($curr_attacker_ship->hardware[HARDWARE_SHIELDS] > 0) {

		// assume we use all the damage to kill his shields
		$drones_to_shield = $damage;

		// do we do more damage than shields left?
		if ($drones_to_shield > $curr_attacker_ship->hardware[HARDWARE_SHIELDS])
			$drones_to_shield = $curr_attacker_ship->hardware[HARDWARE_SHIELDS];

		// accumulate the attacker_damage
		$port_damage += $drones_to_shield;

		// subtract the shield damage
		$curr_attacker_ship->hardware[HARDWARE_SHIELDS] -= $drones_to_shield;

		// subtract the actual damage we did to his shields
		$damage -= $drones_to_shield;

	}

	// does the attacker have drones left?
	if ($curr_attacker_ship->hardware[HARDWARE_COMBAT] > 0 && $damage > 0) {

		// assume we use all the damage to kill his drones
		$drones_to_drones = $damage;

		// do we do more damage than drones left?
		if ($drones_to_drones > $curr_attacker_ship->hardware[HARDWARE_COMBAT] * 3)
			$drones_to_drones = $curr_attacker_ship->hardware[HARDWARE_COMBAT] * 3;

		// accumulate the attacker_damage
		$port_damage += $drones_to_drones;

		// subtract the drone damage
		$curr_attacker_ship->hardware[HARDWARE_COMBAT] -= round($drones_to_drones / 3);

		// subtract the actual damage we did to his drones
		$damage -= $drones_to_drones;

	}

	// does the attacker has armor left?
	if ($curr_attacker_ship->hardware[HARDWARE_ARMOR] > 0 && $damage > 0) {

		// assume we use all the damage to kill his armor
		$drones_to_armor = $damage;

		// do we do more damage than armor left?
		if ($drones_to_armor > $curr_attacker_ship->hardware[HARDWARE_ARMOR])
			$drones_to_armor = $curr_attacker_ship->hardware[2];

		// accumulate the attacker_damage
		$port_damage += $drones_to_armor;

		// subtract the armor damage
		$curr_attacker_ship->hardware[HARDWARE_ARMOR] -= $drones_to_armor;

		// subtract the actual damage we did to his armor
		$damage -= $drones_to_armor;

	}

	$msg = "The port launches $num_att drones at <span style=\"color:yellow;\">$curr_attacker->player_name</span> ";

	if ($drones_to_shield > 0) {

		if ($drones_to_shield == 1)
			$msg .= "and destroys <span style=\"color:red;\">1</span> shield";
		else
			$msg .= "and destroys <span style=\"color:red;\">$drones_to_shield</span> shields";

		if ($drones_to_drones > 0 && $drones_to_armor > 0)
			$msg .= ", ";
		elseif ($drones_to_drones > 0 || $drones_to_armor > 0)
			$msg .= " and ";

	}

	if ($drones_to_drones > 0) {

		if ($drones_to_shield == 0)
			$msg .= "and destroys ";
		if (floor($drones_to_drones / 3) == 1)
			$msg .= "<span style=\"color:red;\">1</span> drone";
		else
			$msg .= "<span style=\"color:red;\">" . floor($drones_to_drones / 3) . "</span> drones";

		if ($drones_to_armor > 0)
			$msg .= " and ";

	}

	if ($drones_to_armor > 0) {

		if ($drones_to_shield == 0 && $drones_to_drones == 0)
			$msg .= "and destroys ";
		if ($drones_to_armor == 1)
			$msg .= "<span style=\"color:red;\">1</span> plate of armor";
		else
			$msg .= "<span style=\"color:red;\">$drones_to_armor</span> plates of armor";

	}

	// add this to the outgoing message array
	$port_msg[] = $msg . ".";

	// is he dead now?
	if ($curr_attacker_ship->hardware[HARDWARE_SHIELDS] == 0 && $curr_attacker_ship->hardware[HARDWARE_ARMOR] == 0 && !in_array($curr_attacker->account_id, $dead_traders)) {

		// write a message to the array
		$port_msg[] = "<span style=\"color:yellow;\">$curr_attacker->player_name</span> has been <span style=\"color:red;\">DESTROYED</span> by port forces";

		if ($curr_attacker->account_id != $player->account_id)
			$curr_attacker->set_info('dead', "TRUE");

		// add him to the array to process later
		$dead_traders[] = $curr_attacker->account_id;

	}

	// update ship
	$curr_attacker_ship->update_hardware();

}

// get the overall damage
if ($port_damage > 0)
	$port_msg[] = "This port does a total of <span style=\"color:red;\">$port_damage</span> damage in this round of combat.";
else
	$port_msg[] = "This port does no damage at all. It needs a better attack coordinator";


// ********************************
// *
// * A t t a c k e r	s h o o t s
// *
// ********************************

// array for next page
$damage_msgs = array();
$atts = array();
// iterate over whole fleet
for ($i = 0; $i < $attacker_team->get_fleet_size(); $i++) {

	// get attacker
	$curr_att_id = $attacker_team->next(true);
	$curr_attacker = new SMR_PLAYER($curr_att_id, SmrSession::$game_id);
	$curr_attacker_ship = new SMR_SHIP($curr_attacker->account_id, SmrSession::$game_id);
	
	//this player has successfully shot the port.
	$db->query("SELECT * FROM player_attacks_port WHERE game_id = ".SmrSession::$game_id." AND sector_id = $sector->sector_id AND account_id = $curr_attacker->account_id");
	//is he already recorded?  If so we don't want to lower the lvl of the port he is attacking.
	if (!$db->next_record())
		$db->query("REPLACE INTO player_attacks_port (game_id, account_id, sector_id, time, level) VALUES (".SmrSession::$game_id.", $curr_attacker->account_id, $sector->sector_id, $time, $port->level)");
	$atts[] = $curr_att_id;
	// reduce his relations
	$curr_attacker->get_relations();
	$curr_attacker->relations[$port->race_id] -= 5;
	if ($curr_attacker->relations[$port->race_id] < -500)
		$curr_attacker->relations[$port->race_id] = -500;

	// save what we got so far
	$curr_attacker->update();

	// disable cloak
	$curr_attacker_ship->disable_cloak();

	// the damage this attacker is going to do
	$attacker_damage = 0;

	// and his message array
	$damage_msg = array();

	$weapon = new SmrMySqlDatabase();
	$weapon->query("SELECT * FROM ship_has_weapon NATURAL JOIN weapon_type " .
				   "WHERE account_id = $curr_attacker->account_id AND " .
						 "game_id = $curr_attacker->game_id " .
				   "ORDER BY order_id");

	// iterate over all existing weapons
	while ($weapon->next_record()) {

		//vars
		$weapon_name = $weapon->f("weapon_name");
		$shield_damage = $weapon->f("shield_damage");
		$armor_damage = $weapon->f("armor_damage");
		$accuracy = $weapon->f("accuracy");

		// calc accuracy for this weapon
		$hit = round(($accuracy + $curr_attacker->level_id) - ($port->level / 2));

		// did we hit with this weapon?
		if (mt_rand(0, 100) < $hit) {

			// does the port has shields?
			if ($port->shields > 0) {

				if ($shield_damage > 0) {

					// do we do more damage than shields left?
					if ($shield_damage > $port->shields)
						$shield_damage = $port->shields;

					// accumulate the attacker_damage
					$attacker_damage += $shield_damage;

					// subtract the shield damage
					$port->shields -= $shield_damage;

					// text
					$damage_msg[] = "<span style=\"color:yellow;\">$curr_attacker->player_name</span> fires a $weapon_name destroying <span style=\"color:red;\">$shield_damage</span> port shields.";

				} elseif ($armor_damage > 0 && $port->drones > 0) {

					//the player has a chance at hitting some combat drones but not many due to strong shields
					$drones_hit = floor ($armor_damage / 60);

					// accumulate the attacker_damage
					$attacker_damage += $drones_hit * 3;

					// subtract destroyed drones
					$port->drones -= $drones_hit;

					if ($drones_hit > 0)
						$damage_msg[] = "<span style=\"color:yellow;\">$curr_attacker->player_name</span> fires a $weapon_name destroying <span style=\"color:red;\">$drones_hit</span> port drones.";

				} else
					$damage_msg[] = "<span style=\"color:yellow;\">$curr_attacker->player_name</span> fires a $weapon_name which does no damage against the port shields.";

			// does the port has drones?
			} elseif ($port->drones > 0) {

				if ($armor_damage > 0) {

					// do we do more damage than armor left?
					if ($armor_damage > $port->drones * 3)
						$armor_damage = $port->drones * 3;

					// accumulate the attacker_damage
					$attacker_damage += $armor_damage;

					// subtract the armor damage
					$port->drones -= round($armor_damage / 3);

					// print message
					$damage_msg[] = "<span style=\"color:yellow;\">$curr_attacker->player_name</span> fires a $weapon_name destroying <span style=\"color:red;\"> " . round ($armor_damage / 3) . " </span> port drones.";

				} elseif ($shield_damage > 0)
					$damage_msg[] = "<span style=\"color:yellow;\">$curr_attacker->player_name</span> fires a $weapon_name which proves to be ineffective against the port drones.";

			// does the port has armor?
			} elseif ($port->armor > 0) {

				if ($armor_damage > 0) {

					// do we do more damage than armor left?
					if ($armor_damage > $port->armor)
						$armor_damage = $port->armor;

					// accumulate the attacker_damage
					$attacker_damage += $armor_damage;

					// subtract the armor damage
					$port->armor -= $armor_damage;

					// text
					$damage_msg[] = "<span style=\"color:yellow;\">$curr_attacker->player_name</span> fires a $weapon_name destroying <span style=\"color:red;\">$armor_damage</span> plates of armor.";

				} elseif ($shield_damage > 0)
					$damage_msg[] = "<span style=\"color:yellow;\">$curr_attacker->player_name</span> fires a $weapon_name which proves to be ineffective against the ports armor.";
			}

		} else
			$damage_msg[] = "<span style=\"color:yellow;\">$curr_attacker->player_name</span> fires a $weapon_name and misses.";

	} // end of weapons

	// do we have drones?
	if ($curr_attacker_ship->hardware[HARDWARE_COMBAT] > 0 && ($port->shields > 0 || $port->drones > 0 || $port->armor > 0)) {

		// Random(3 to 54) + Random(Attacker level/4 to Attacker level)
		$percent_attacking = (mt_rand(3, 53) + mt_rand($curr_attacker->level_id / 4, $curr_attacker->level_id)) / 100;
		$number_attacking = round($percent_attacking * $curr_attacker_ship->hardware[4]);

//$damage_msg[] = "percent_attacking: $percent_attacking - number_attacking: $number_attacking";

		// can not more attacking than we carry
		if ($number_attacking > $curr_attacker_ship->hardware[HARDWARE_COMBAT])
			$number_attacking = $curr_attacker_ship->hardware[HARDWARE_COMBAT];

//$damage_msg[] = "number_attacking: $number_attacking";
		//RESET VARS
		$killed_shields = 0;
		$killed_drones = 0;
		$killed_armor = 0;
		// are there shields left?
		if ($port->shields > 0) {

			// can we do more damage than shields left?
			if ($number_attacking * 2 > $port->shields) {

				// destroy all shields that are left
				$killed_shields = $port->shields;

			} else
				$killed_shields = $number_attacking * 2;

			// take shields
			$port->shields -= $killed_shields;

			// accumulate attacker damage
			$attacker_damage += $killed_shields;

			// subtract the number of drones that hit for shields
			// from the total number of attacking drones
			$number_attacking -= ceil($killed_shields / 2);

		}

		// are there drones left?
		if ($port->drones > 0 && $number_attacking > 0) {

			// can we do more damage than drones left?
			if ($number_attacking * 2 > $port->drones * 3) {

				// destroy all drones that are left
				$killed_drones = $port->drones;

			} else
				$killed_drones = ceil($number_attacking * 2 / 3);

			// take drones
			$port->drones -= $killed_drones;

			// accumulate attacker damage
			$attacker_damage += $killed_drones;

			// subtract the number of drones that hit for drones
			// from the total number of attacking drones
			$number_attacking -= ceil($killed_drones * 3 / 2);

		}

		// are there armor left?
		if ($port->armor > 0 && $number_attacking > 0) {

			// can we do more damage than armor left?
			if ($number_attacking * 2 > $port->armor) {

				// destroy all armor that is left
				$killed_armor = $port->armor;

			} else
				$killed_armor = $number_attacking * 2;

			// take armor
			$port->armor -= $killed_armor;

			// accumulate attacker damage
			$attacker_damage += $killed_armor;

			// subtract the number of drones that hit for armor
			// from the total number of attacking drones
			$number_attacking -= ceil($killed_armor / 2);

		}

		// build text
		$msg = "<span style=\"color:yellow;\">$curr_attacker->player_name</span> launches " . ceil($killed_shields / 2 + $killed_drones * 3 / 2 + $killed_armor / 2) . " drones hitting the port and destroying ";

		if ($killed_shields > 0) {

			if ($killed_shields == 1)
				$msg .= "<span style=\"color:red;\">1</span> port shield";
			else
				$msg .= "<span style=\"color:red;\">$killed_shields</span> port shields";

			if ($killed_drones > 0 && $killed_armor > 0)
				$msg .= ", ";
			elseif ($killed_drones > 0 || $killed_armor > 0)
				$msg .= " and ";

		}

		if ($killed_drones > 0) {

			if ($killed_drones == 1)
				$msg .= "<span style=\"color:red;\">1</span> port drone";
			else
				$msg .= "<span style=\"color:red;\">$killed_drones</span> port drones";

			if ($killed_armor > 0)
				$msg .= " and ";

		}

		if ($killed_armor > 0) {

			if ($killed_armor == 1)
				$msg .= "<span style=\"color:red;\">1</span> plate of port armor";
			else
				$msg .= "<span style=\"color:red;\">$killed_armor</span> plates of port armor";

		}

		// add this to the outgoing message array
		$damage_msg[] = $msg . ".";

	} // end of 'do we have drones?'

	// print the overall damage
	if ($attacker_damage > 0) {

		// is port taken?
		if ($port->shields == 0 && $port->drones == 0 && $port->armor == 0) {

			$damage_msg[] = "Port defenses are <span style=\"color:red;\">DESTROYED!</span>";

			//$damage_msg[] = "<br>Start<br>";
			//itterate through since the last port reset
			$db->query("SELECT * FROM player_attacks_port WHERE game_id = ".SmrSession::$game_id." AND sector_id = $sector->sector_id AND time < $port->refresh_defense");
			while ($db->next_record()) {
				
				$update_attacker = new SMR_PLAYER($db->f("account_id"), SmrSession::$game_id);
				$update_attacker->update_stat("port_raids", 1);
				$update_attacker->update_stat("port_raid_levels", $db->f("level"));
				$port_original_level = $db->f("level");
				$db2->query("DELETE FROM player_attacks_port WHERE game_id = ".SmrSession::$game_id." AND sector_id = $sector->sector_id AND account_id = $update_attacker->account_id");
				
			}
			// Attacker gets the port's money
			$curr_attacker->credits += $port->credits;
			$damage_msg[] = "<span style=\"color:yellow;\">$curr_attacker->player_name</span> claims <span style=\"color:yellow;\">$port->credits</span> credits.";
			$port->credits = 0;

			// news message
			if ($player->alliance_id != 0)
				$attack_news = "Members of $player->alliance_name";
			else
				$attack_news = $player->get_colored_name();
			$news_message = "$attack_news successfully raided the port located in sector $player->sector_id";
			$db->query("INSERT INTO news " .
				"(game_id, time, news_message) " .
				"VALUES(".SmrSession::$game_id.", " . time() . ", " . format_string($news_message, false) . ")");


		}

		$damage_msg[] = "<span style=\"color:yellow;\">$curr_attacker->player_name</span> does a total of <span style=\"color:red;\">$attacker_damage</span> damage.<br><br>";

		// 5% of the damage goes to xp
		$curr_attacker->experience += $attacker_damage * .05;
		$curr_attacker->update();

	} else
		$damage_msg[] = "<span style=\"color:yellow;\">$curr_attacker->player_name</span> does absolutely no damage this round. Send the worthless lout back to the academy!<br><br>";

	$attacker_team_damage += $attacker_damage;
	$curr_attacker->update_stat("port_damage", $attacker_damage);
	$attacker[] = $damage_msg;

	//reset the array
	$damage_msg = array();

}

// print the overall damage
if ($attacker_team_damage > 0)
	$damage_msg[] = "This team does a total of <span style=\"color:red;\">$attacker_team_damage</span> damage in this round of combat.";
else
	$damage_msg[] = "This team does no damage at all. You call that a team? They need a better recruiter.<br>";


//check for port downgrade.
if ($port->level > 1)
	$display = "yes";
if ($attacker_team_damage > 500) {

	$chances = floor($attacker_team_damage / 500);

	$i = 0;
	while ($i < $chances) {

		$rand = mt_rand(1, 100);
		if ($rand <= 5) {

			//we downgraded it
			$port->downgrade();

			// Ports do NOT set new defences when they are downgraded
			// Their defences will be reset when the port resets

			if ($display == "yes")
				$damage_msg[] = "The port has lost a level";

			//only one per shot
			$i += $chances + 1;

		} else
			$i += 1;

   }

   // update local mpa with new port infos
   $sector->mark_visited();

}

$attacker[] = $damage_msg;

// update port
$port->update();

if ($port->shields == 0 && $port->drones == 0 && $port->armor == 0) {

	// Trigger gets an alignment change and a bounty if port is taken
	$db->query("SELECT * FROM bounty WHERE game_id = $player->game_id AND account_id = $player->account_id " .
		"AND claimer_id = 0 AND type = 'HQ'");
	$amount = $curr_attacker->experience * $port->level;
	if ($db->next_record()) {
		//include interest
		$bounty_id = $db->f("bounty_id");
		$curr_amount = $db->f("amount");
		$new_amount = $curr_amount + $amount;
		$db->query("UPDATE bounty SET amount = $new_amount, time = $time WHERE game_id = $player->game_id AND bounty_id = $bounty_id");
	} else {
		$time = time();
		$db->query("INSERT INTO bounty (account_id, game_id, bounty_id, type, amount, claimer_id, time) VALUES " .
			"($player->account_id, $player->game_id, NULL, 'HQ', $amount, 0, $time)");
	}
	$player->get_relations();
	if($port->race_id > 1) {
		$new_relations = $player->relations[$port->race_id] - 45;
		if ($new_relations < -500) $new_relations = -500;

		$db->query("REPLACE INTO player_has_relation (account_id, game_id, race_id, relation) VALUES($player->account_id, $player->game_id, $port->race_id, $new_relations)");
	}

	// also we change alignment
	if ($player->relations_global_rev[$port->race_id] < -299)
	   $new_alignment = $player->alignment + $port_original_level * 2;
	else
	   $new_alignment = $player->alignment - $port_original_level * 2;
		$db->query("UPDATE player SET alignment=$new_alignment WHERE account_id=$player->account_id AND game_id=$player->game_id LIMIT 1");

}

//kill the dead people
foreach ($dead_traders as $acc_id) {

	$dead_acc = new SMR_PLAYER($acc_id, $player->game_id);
	$dead_acc->died_by_port();
	$dead_ship = new SMR_SHIP($acc_id, $player->game_id);
	$dead_ship->get_pod();

}

$container = array();
$container["url"] = "skeleton.php";
$container["body"] = "port_attack.php";
$container["attacker"] = $attacker;
$container["portdamage"] = $port_msg;
$container["dead_traders"] = $dead_traders;
forward($container);

?>
