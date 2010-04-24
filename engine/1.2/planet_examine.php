<?php
require_once(get_file_loc("smr_planet.inc"));
// get a planet from the sector where the player is in
$planet = new SMR_PLANET($player->sector_id, $player->game_id);
// owner of planet
if ($planet->owner_id != 0) {
	$planet_owner = new SMR_PLAYER($planet->owner_id, SmrSession::$game_id);
	$ownerAllianceID = $planet_owner->alliance_id;
} else $ownerAllianceID = 0;
print_topic("Examine Planet");
print("<table>");
print("<tr><td><b>Planet Name:</b></td><td>$planet->planet_name</td></tr>");
print("<tr><td><b>Level:</b></td><td>" . $planet->level() . "</td></tr>");
print("<tr><td><b>Owner:</b></td><td>");
if ($planet->owner_id != 0)
	print($planet_owner->player_name);
else
	print("Unclaimed");

print("</td></tr>");
print("<tr><td><b>Alliance:</b></td><td>");

if ($planet->owner_id != 0)
	print($planet_owner->alliance_name);
else
	print("none");

print("</td></tr>");
print("</table>");

print("<div align=\"center\">");

// land or attack?
//check for treaty
$planetLand = FALSE;
$db->query("SELECT planet_land FROM alliance_treaties
				WHERE (alliance_id_1 = $ownerAllianceID OR alliance_id_1 = $player->alliance_id)
				AND (alliance_id_2 = $ownerAllianceID OR alliance_id_2 = $player->alliance_id)
				AND game_id = $player->game_id
				AND planet_land = 1 AND official = 'TRUE'");
if ($db->next_record()) $planetLand = TRUE;
if (in_array($player->account_id, $HIDDEN_PLAYERS)) $planetLand = TRUE;
if ($player->alliance_id == $ownerAllianceID && $ownerAllianceID != 0) $planetLand = TRUE;
if ($planet->owner_id == $player->account_id) $planetLand = TRUE;
if ($planet->owner_id == 0) $planetLand = TRUE;
if (!$planetLand)
	print_button(create_container("planet_attack_processing.php", ""), 'Attack Planet (3)');
elseif ($planet->inhabitable_time < time())
	print_button(create_container("planet_land_processing.php", ""), 'Land on Planet (1)');
else
	print("The planet is <font color=red>uninhabitable</font> at this time.");
print("</form>");
print("</div>");

?>