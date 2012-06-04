<?
require_once(get_file_loc('smr_sector.inc'));
		$sector = new SMR_SECTOR($player->sector_id, SmrSession::$game_id, SmrSession::$account_id);
		require_once(get_file_loc("smr_force.inc"));
if ($player->newbie_turns > 0)
	create_error("You are under newbie protection!");

//turns are taken b4 player fires.

$forces = new SMR_FORCE($owner_id, $player->sector_id, $player->game_id);

if ($forces->mines == 0)
	create_error("No mines in sector! You should never be see this! Why are you looking at this! STOP! NOW! GO AWAY! PLEASE!!! STOP READING THIS NOW!!! PLEASE!!!");

// delete plotted course
$player->delete_plotted_course();

// send message if scouts are present
if ($forces->scout_drones > 0) {

	$message = "Your forces in sector $forces->sector_id are being attacked by $player->player_name";
	$player->send_message($forces->owner_id, MSG_SCOUT, format_string($message, false));
	//insert into ticker
	$time = time();
	$db->query("SELECT * FROM player_has_ticker WHERE account_id = $owner_id AND game_id = $player->game_id AND type = 'scout'");
	if ($db->next_record()) {
				
		$db->query("SELECT * FROM player_has_ticker WHERE account_id = $player->account_id AND type = 'block'");
		if (!$db->next_record()) $db->query("UPDATE player_has_ticker SET recent = " . format_string($message, false) . ", time = $time WHERE account_id = $owner_id AND game_id = $player->game_id");
		
	}

}

$force_msg = array();


$container = array();
$container["url"] = "skeleton.php";
$container["body"] = "forces_attack.php";
$container["continue"] = "yes";
$container["forced"] = "yes";

// ********************************
// *
// * F o r c e s   a t t a c k
// *
// ********************************
$num_exits = 0;
if ($sector->link_up > 0)
	$num_exits += 1;
if ($sector->link_down > 0)
	$num_exits += 1;
if ($sector->link_left > 0)
	$num_exits += 1;
if ($sector->link_right > 0)
	$num_exits += 1;



// fed ships take half damage from mines
if ($ship->ship_type_id == 20 || $ship->ship_type_id == 21 || $ship->ship_type_id == 22)
	$forces_damage = 10;
else
	$forces_damage = 20;

//formula......100% - ((your level) + (rand(1,7)*rand(1,7))) mines will hit for 20 damage each.
$percent_hitting = 100 - (($player->level_id) + (mt_rand(1,7) * mt_rand(1,7)));
//find out how many are going to attack you
$number_hitting = round($forces->mines * ($percent_hitting / 100));

if ($num_exits >= 3)
	$number_hitting = round($number_hitting / 2);

// fed ships take half damage from mines
$damage = $number_hitting * $forces_damage;

// If you hit mines with an active cloak you can blow your cloak
//if($number_hitting > 0 && $ship->cloak_overload()) {
//	$force_msg[] = '<span class="red bold"">WARNING:</span> Feedback through your shields has <span class="red">DESTROYED</span> your cloaking device.<br />';
//}
//else {
	// Whatever happens the cloak gets disabled
	$ship->disable_cloak();
//}



// Does attacker have shields?
if ($ship->hardware[HARDWARE_SHIELDS] > 0 && $number_hitting > 0) {

	// do we make more damage than shields left?
	if ($damage > $ship->hardware[HARDWARE_SHIELDS]) {

		// reduce damage to number of shields left
		$damage = $ship->hardware[HARDWARE_SHIELDS];

		// calc how many are actually hitting
		$number_hitting = ceil( $damage / $forces_damage );

	}

	// add the force_damage
	$force_damage += $damage;

	// subtract the shield damage
	$ship->hardware[HARDWARE_SHIELDS] -= $damage;

	// print message
	$force_msg[] = "<span style=\"color:yellow;\">$number_hitting</span> mines kamikaze themselves against <span style=\"color:yellow;\">$player->player_name</span>'s ship for <span style=\"color:red;\">$damage</span> shields.";

	//subtract mines that hit
	$forces->mines -= $number_hitting;

} elseif ($ship->hardware[HARDWARE_ARMOR] > 0 && $number_hitting > 0) {

	// do we make more damage than armor left?
	if ($damage > $ship->hardware[HARDWARE_ARMOR]) {

		// reduce damage to number of drones left
		$damage = $ship->hardware[HARDWARE_ARMOR];

		// calc how many are actually hitting
		$number_hitting = ceil( $damage / $forces_damage );

	}

	//subtract the damage
	$ship->hardware[HARDWARE_ARMOR] -= $damage;

	// add the force_damage
	$force_damage += $damage;

	// print message
	$force_msg[] = "<span style=\"color:yellow;\">$number_hitting</span> mines kamikaze themselves against <span style=\"color:yellow;\">$player->player_name</span>'s ship destroying <span style=\"color:red;\">$damage</span> armor.";

	//subtract mines that hit
	$forces->mines -= $number_hitting;

} elseif ($number_hitting == 0) {
	
	// print message
	$force_msg[] = "The mines fail to hit <span style=\"color:yellow;\">$player->player_name</span>.";
	
}

// ********************************
// *
// * A t t a c k e r   s h o o t s
// *
// ********************************
$attacker_total_msg = array();
$attacker_msg = array();
if ($player->turns < 3) {

	// clear green exit	
	$player->last_sector_id = 0;
	$player->update();
	
	$attacker_msg[] = "You do not have enough turns to return fire at the mines!";
	//return empty set so we dont gointo next part
	$db->query("SELECT * FROM player WHERE account_id = 0 AND player_id = 0 AND player_name = '0'");
	$cds = "no";
	
} else {
	
	$db->query("SELECT * FROM ship_has_weapon, weapon_type " .
		   "WHERE account_id = $player->account_id AND " .
				 "game_id = SmrSession::$game_id AND " .
				 "ship_has_weapon.weapon_type_id = weapon_type.weapon_type_id " .
		   "ORDER BY order_id");
	// take the turns
	if ($db->nf()) {
		$taken = "taken";
		$player->take_turns(3);
	}
	$player->update();
	$cds = "yes";
	
}

// iterate over all existing weapons
while ($db->next_record() && ($forces->combat_drones > 0 || $forces->scout_drones > 0 || $forces->mines > 0)) {

	$weapon_name = $db->f("weapon_name");
	$shield_damage = $db->f("shield_damage");
	$armor_damage = $db->f("armor_damage");
	$accuracy = $db->f("accuracy");

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
			$attacker_msg[] = "<span style=\"color:yellow;\">$player->player_name</span> fires a $weapon_name at the forces destroying <span style=\"color:red;\">$mines_dead</span> mines.";

		} elseif ($shield_damage > 0)
			$attacker_msg[] = "<span style=\"color:yellow;\">$player->player_name</span> fires a $weapon_name at the forces but it proves to be ineffective against the mines.";

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
			$attacker_msg[] = "<span style=\"color:yellow;\">$player->player_name</span> fires a $weapon_name at the forces and destroys <span style=\"color:red;\">$drones_dead</span> combat drones.";

		} elseif ($armor_damage == 0 && $shield_damage > 0)
			$attacker_msg[] = "<span style=\"color:yellow;\">$player->player_name</span> fires a $weapon_name at the forces but it proves to be ineffective against the armor of the drones";

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
			$attacker_msg[] = "<span style=\"color:yellow;\">$player->player_name</span> fires a $weapon_name at the forces and destroys <font color=red>$scouts_dead</font> scout drones.";

		} else
			$attacker_msg[] = "<span style=\"color:yellow;\">$player->player_name</span> fires a $weapon_name at the forces but it proves to be ineffective against the armor of the drones";

	}

}

// do we have drones?
if ($ship->hardware[HARDWARE_COMBAT] > 0 && ($forces->mines > 0 || $forces->combat_drones > 0 || $forces->scout_drones > 0) && $cds == "yes") {

	if (empty($taken)) $player->take_turns(3);
	$player->update();
	// Random(3 to 54) + Random(Attacker level/4 to Attacker level)
	$percent_attacking = (mt_rand(3, 53) + mt_rand($player->level_id / 4, $player->level_id)) / 100;
	$number_attacking = round($percent_attacking * $ship->hardware[HARDWARE_COMBAT]);

	// can not more attacking than we carry
	if ($number_attacking > $ship->hardware[HARDWARE_COMBAT])
		$number_attacking = $ship->hardware[HARDWARE_COMBAT];

	if ($forces->mines > 0) {

		// can we do more damage than mines left?
		if ($number_attacking > $forces->mines)
			$number_attacking = $forces->mines;

		// take mines
		$forces->mines -= $number_attacking;
		$ship->hardware[HARDWARE_COMBAT] -= $number_attacking;

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
		$attacker_msg[] = "<span style=\"color:yellow;\">$player->player_name</span> launches <span style=\"color:yellow;\">$number_attacking</span> drones at the forces destroying <span style=\"color:red;\">" . floor ($damage / 3) . "</span> combat drones.";

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
		$attacker_msg[] = "<span style=\"color:yellow;\">$player->player_name</span> launches <span style=\"color:yellow;\">$number_attacking</span> drones at the forces destroying <span style=\"color:red;\">" . floor ($damage / 20) . "</span> scout drones.";

	}

} // end of 'do we have drones'

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

// are forces dead?
if ($forces->mines == 0 && $forces->combat_drones == 0 && $forces->scout_drones == 0) {

	$attacker_msg[] = "Forces are <span style=\"color:red;\">DESTROYED!</span>";
	$container["continue"] = "no";

}

// print the overall damage
if ($attacker_damage > 0) {

	$attacker_msg[] = "<span style=\"color:yellow;\">$player->player_name</span> does a total of <span style=\"color:red;\">$attacker_damage</span> damage.";
	// 5% of the damage goes to xp
	$player->experience += $attacker_damage * .05;
	$player->update();

} else
	$attacker_msg[] = "<span style=\"color:yellow;\">$player->player_name</span> does absolutely no damage this round. Send the worthless lout back to the academy!";

$attacker_team_damage += $attacker_damage;
$attacker_total_msg[] = $attacker_msg;

$ship->update_hardware();

// is he dead now?
if ($ship->hardware[HARDWARE_SHIELDS] == 0 && $ship->hardware[HARDWARE_ARMOR] == 0)
	$player->mark_dead();

if ($player->dead == "TRUE") {

	$force_msg[] = "<span style=\"color:yellow;\">$player->player_name</span> is <span style=\"color:red;\">DESTROYED!</span>";
	$player->died_by_forces($forces->owner_id);
	$ship->get_pod();

	$player->dead = "FALSE";
	$player->update();

	$container["continue"] = "no";

}


// info for the next page
$container["force_msg"] = $force_msg;
$container["attacker_total_msg"] = $attacker_total_msg;
$container["owner_id"] = $owner_id;
forward($container);

?>
