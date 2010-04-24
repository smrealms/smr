<?php
require_once(get_file_loc('smr_sector.inc'));
		$sector = new SMR_SECTOR($player->sector_id, SmrSession::$game_id, SmrSession::$old_account_id);
		require_once(get_file_loc("smr_force.inc"));
print_topic("SECTOR SCAN");

// initialize vars
$friendly_forces = 0;
$enemy_forces = 0;
$friendly_vessel = 0;
$enemy_vessel = 0;

// get our rank
$rank_id = $account->get_rank();

// iterate over all forces in the target sector
$db->query("SELECT * FROM sector_has_forces WHERE game_id = $player->game_id AND " .
												 "sector_id = " . $var["target_sector"]);
while ($db->next_record()) {

	// we may skip forces if this is a protected gal.
	if ($sector->is_protected_gal()) {

		$forces_account = new SMR_ACCOUNT();
		$forces_account->get_by_id($db->f("owner_id"));

		// if one is vet and the other is newbie we skip it
		if (different_level($rank_id, $forces_account->get_rank(), $account->veteran, $forces_account->veteran))
			continue;

	}

	// decide if it's an friendly or enemy stack
	$forces_owner	= new SMR_PLAYER($db->f("owner_id"), $player->game_id);
	$forces			= new SMR_FORCE($db->f("owner_id"), $var["target_sector"], $player->game_id);

	if ($player->alliance_id == 0 && $forces->owner_id == $player->account_id || $player->alliance_id != 0 && $player->alliance_id == $forces_owner->alliance_id)
		$friendly_forces += $forces->mines * 3 + $forces->combat_drones * 2 + $forces->scout_drones;
	else
		$enemy_forces += $forces->mines * 3 + $forces->combat_drones * 2 + $forces->scout_drones;

}

$last_active = time() - 259200;
$db->query("SELECT * FROM player WHERE game_id = $player->game_id AND " .
									  "sector_id = " . $var["target_sector"] . " AND " .
									  "last_active > $last_active AND " .
									  "land_on_planet = 'FALSE' AND " .
									  "account_id NOT IN (" . implode(',', $HIDDEN_PLAYERS) . ")");
while ($db->next_record()) {

	// we may skip player if this is a protected gal.
	if ($sector->is_protected_gal()) {

		$curr_account = new SMR_ACCOUNT();
		$curr_account->get_by_id($db->f("account_id"));

		// if one is vet and the other is newbie we skip it
		if (different_level($rank_id, $curr_account->get_rank(), $account->veteran, $curr_account->veteran))
			continue;

	}

	$curr_player	= new SMR_PLAYER($db->f("account_id"), $player->game_id);
	$curr_ship		= new SMR_SHIP($db->f("account_id"), $player->game_id);

	// he's a friend if he's in our alliance (and we are not in a 0 alliance
	if ($player->alliance_id != 0 && $curr_player->alliance_id == $player->alliance_id)
		$friendly_vessel += $curr_ship->attack_rating();
	else
		$enemy_vessel += $curr_ship->defense_rating() * 10;

}

print("<p>");
print("<table cellspacing=\"0\" cellpadding=\"3\" border=\"0\" class=\"standard\">");
print("<tr>");
print("<th>&nbsp;</th>");
print("<th align=\"center\">Scan Results</th>");
print("</tr>");
print("<tr>");
print("<td>Friendly vessels</td>");
print("<td align=\"center\">$friendly_vessel</td>");
print("</tr>");
print("<tr>");
print("<td>Enemy vessels</td>");
print("<td align=\"center\">$enemy_vessel</td>");
print("</tr>");
print("<tr>");
print("<td>Friendly forces</td>");
print("<td align=\"center\">$friendly_forces</td>");
print("</tr>");
print("<tr>");
print("<td>Enemy forces</td>");
print("<td align=\"center\">$enemy_forces</td>");
print("</tr>");
print("</table>");
print("</p>");

$target_sector = new SMR_SECTOR($var["target_sector"], SmrSession::$game_id, SmrSession::$old_account_id);

print("<p>");
print("<table cellspacing=\"0\" cellpadding=\"3\" border=\"0\" class=\"standard\">");
print("<tr>");
print("<td>Planet</td>");
print("<td>");
if ($target_sector->has_planet()) print("Yes"); else print("No");
print("</td>");
print("</tr>");
print("<tr>");
print("<td>Port</td>");
print("<td>");
if ($target_sector->has_port()) print("Yes"); else print("No");
print("</td>");
print("</tr>");
print("<tr>");
print("<td>Location</td>");
print("<td>");
if ($target_sector->has_location()) print("Yes"); else print("No");
print("</td>");
print("</tr>");
print("</table>");
print("</p>");

// is it a warp or a normal move?
if ($sector->warp == $var["target_sector"])
	$turns = 5;
else
	$turns = 1;

$container = array();
$container["url"]			= "sector_move_processing.php";
$container["target_page"]	= "current_sector.php";
transfer("target_sector");

print_form($container);
print_submit("Enter " . $var["target_sector"] . " ($turns)");
print("</form></p>");

?>