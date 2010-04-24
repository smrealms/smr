<?php

		require_once(get_file_loc("smr_force.inc"));
if (isset($var["owner_id"])) {
	
	$owner = new SMR_PLAYER($var["owner_id"], SmrSession::$game_id);
	print_topic("CHANGE $owner->player_name's FORCES");
    $owner_id = $var["owner_id"];

} else {

	print_topic("DROP FORCES");
    $owner_id = $player->account_id;

}

$forces = new SMR_FORCE($owner_id, $player->sector_id, $player->game_id);

$container = array();
$container["url"]		= "forces_drop_processing.php";
$container["owner_id"]	= $owner_id;

print_form($container);

print_table();
print("<tr>");
print("<th align=\"center\">Force</th>");
print("<th align=\"center\">On Ship</th>");
print("<th align=\"center\">In Sector</th>");
print("<th align=\"center\">Drop</th>");
print("<th align=\"center\">Take</th>");
print("</tr>");

print("<tr>");
print("<td align=\"center\">Mines</td>");
print("<td align=\"center\">" . $ship->hardware[HARDWARE_MINE] . "</td>");
print("<td align=\"center\">$forces->mines</td>");
print("<td align=\"center\"><input type=\"text\" name=\"drop_mines\" value=\"0\" id=\"InputFields\" style=\"width:100px;text-align:center;\"></td>");
print("<td align=\"center\"><input type=\"text\" name=\"take_mines\" value=\"0\" id=\"InputFields\" style=\"width:100px;text-align:center;\"></td>");
print("</tr>");

print("<tr>");
print("<td align=\"center\">Combat Drones</td>");
print("<td align=\"center\">" . $ship->hardware[HARDWARE_COMBAT] . "</td>");
print("<td align=\"center\">$forces->combat_drones</td>");
print("<td align=\"center\"><input type=\"text\" name=\"drop_combat_drones\" value=\"0\" id=\"InputFields\" style=\"width:100px;text-align:center;\"></td>");
print("<td align=\"center\"><input type=\"text\" name=\"take_combat_drones\" value=\"0\" id=\"InputFields\" style=\"width:100px;text-align:center;\"></td>");
print("</tr>");

print("<tr>");
print("<td align=\"center\">Scout Drones</td>");
print("<td align=\"center\">" . $ship->hardware[HARDWARE_SCOUT] . "</td>");
print("<td align=\"center\">$forces->scout_drones</td>");
print("<td align=\"center\"><input type=\"text\" name=\"drop_scout_drones\" value=\"0\" id=\"InputFields\" style=\"width:100px;text-align:center;\"></td>");
print("<td align=\"center\"><input type=\"text\" name=\"take_scout_drones\" value=\"0\" id=\"InputFields\" style=\"width:100px;text-align:center;\"></td>");
print("</tr>");

print("<tr>");
print("<td align=\"center\" colspan=\"3\">&nbsp;</td>");
print("<td align=\"center\" colspan=\"2\">");
print_submit("Drop/Take");
print("</td>");
print("</tr>");
print("</table>");

print("</form>")

?>