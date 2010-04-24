<?php
		require_once(get_file_loc("smr_force.inc"));
// initialize random generator.
mt_srand((double)microtime()*1000000);

// creates a new player object for attacker and defender
$attacker		= new SMR_PLAYER(SmrSession::$old_account_id, SmrSession::$game_id);
$attacker_ship	= new SMR_SHIP(SmrSession::$old_account_id, SmrSession::$game_id);
$forces_owner	= new SMR_PLAYER($var["owner_id"], SmrSession::$game_id);
$forces			= new SMR_FORCE($var["owner_id"], $attacker->sector_id, $attacker->game_id);

// first check if both ship and forces are in same sector
if ($attacker->sector_id != $forces->sector_id) {

	print_error("Those forces are no longer here!");
	return;

}

print("<h1>EXAMINE FORCES</h1>");

// should we display an attack button
if (($attacker_ship->attack_rating() > 0 || $attacker_ship->hardware[HARDWARE_COMBAT] > 0) &&
	!$attacker->is_fed_protected() &&
	$attacker->newbie_turns == 0 &&
	$attacker->land_on_planet == 'FALSE' &&
	($attacker->alliance_id == 0 || $forces_owner->alliance_id == 0 || $forces_owner->alliance_id != $attacker->alliance_id) &&
	$attacker->account_id != $forces_owner->account_id) {

	$container = array();
	$container["url"] = "forces_attack_processing.php";
	transfer("target");
	transfer("owner_id");
	print_form($container);
	print_submit("Attack Forces (3)");
	print("</form>");

} elseif ($attacker->is_fed_protected())
	print("<p><big style=\"color:#3333FF;\">You are under federal protection! That wouldn't be fair.</big></p>");
elseif ($attacker->newbie_turns > 0)
	print("<p><big style=\"color:#33FF33;\">You are under newbie protection!</big></p>");
elseif ($owner->alliance_id == $attacker->alliance_id && $attacker->alliance_id != 0)
	print("<p><big style=\"color:#33FF33;\">These are your alliance's forces!</big></p>");
elseif ($owner->account_id == $attacker->account_id)
	print("<p><big style=\"color:#33FF33;\">These are your forces!</big></p>");

print("<div align=\"center\">");
print("<table cellspacing=\"0\" cellpadding=\"5\" border=\"0\" class=\"standard\" width=\"95%\">");
print("<tr>");
print("<th width=\"50%\">Attacker</th>");
print("<th width=\"50%\">Forces</th>");
print("</tr>");
print("<tr>");

// ********************************
// *
// * A t t a c k e r
// *
// ********************************

if ($attacker->alliance_id > 0) {

	$db->query("SELECT * FROM player WHERE game_id = ".SmrSession::$game_id." AND " .
													  "alliance_id = $attacker->alliance_id AND " .
													  "sector_id = $attacker->sector_id AND " .
													  "land_on_planet = 'FALSE' AND " .
													  "newbie_turns = 0");

	while ($db->next_record()) {

		$curr_player = new SMR_PLAYER($db->f("account_id"), SmrSession::$game_id);

		if (!$curr_player->is_fed_protected()) {

			if ($attacker_list) $attacker_list .= ",";
			$attacker_list .= $curr_player->account_id;

		}

	}

	$attacker_list = "(" . $attacker_list . ")";

} else {

	// mhh. we are not in an alliance.
	// so we fighting alone.
	$attacker_list = "(" . $attacker->account_id . ")";

}

print("<td valign=\"top\">");

if ($attacker_list == "()") {

	print("&nbsp;");

} else {

	$db->query("SELECT * FROM player WHERE game_id = ".SmrSession::$game_id." AND " .
													  "account_id IN $attacker_list");
	while ($db->next_record()) {

		 $curr_player = new SMR_PLAYER($db->f("account_id"), SmrSession::$game_id);
		 $curr_ship = new SMR_SHIP($db->f("account_id"), SmrSession::$game_id);

		 print("$player->level_name<br>");
		 print("<span style=\"color:yellow;\">$curr_player->player_name ($curr_player->player_id)</span><br>");
		 print("Race: $curr_player->race_name<br>");
		 print("Level: $curr_player->level_id<br>");
		 print("Alliance: $curr_player->alliance_name<br><br>");
		 print("<small>");
		 print("$curr_ship->ship_name<br>");
		 print("Rating : " . $curr_ship->attack_rating() . "/" . $curr_ship->defense_rating() . "<br>");
		 print("Shields : " . $curr_ship->shield_low() . "-" . $curr_ship->shield_high() . "<br>");
		 print("Armor : " . $curr_ship->armor_low() . "-" . $curr_ship->armor_high() . "<br>");
		 print("Hard Points: $curr_ship->weapon_used<br>");
		 print("Combat Drones: " . $curr_ship->combat_drones_low() . "-" . $curr_ship->combat_drones_high());
		 print("</small><br><br><br>");

	}

}

print("</td>");
print("<td valign=\"top\">");

// ********************************
// *
// * F o r c e s
// *
// ********************************

if ($attacker->alliance_id != 0 && $forces_owner->alliance_id == $attacker->alliance_id) {

	// you can't attack ur own alliance forces.

	print("&nbsp;</td>");
	print(" </tr>");
	print(" </table>");
	print("</div>");
	return;

}

print("Mines: $forces->mines<br>");
print("Combat Drones: $forces->combat_drones<br>");
print("Scouts: $forces->scout_drones<br>");
print("Alliance: $forces_owner->alliance_name<br><br>");


print("</td>");
print("	 </tr>");
print("	 </table>");
print("</div>");

?>