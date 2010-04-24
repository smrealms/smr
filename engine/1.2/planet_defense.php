<?php
		require_once(get_file_loc("smr_planet.inc"));
if ($player->land_on_planet == "FALSE") {
	
	print_error("You are not on a planet!");
	return;
	
}

// create planet object
$planet = new SMR_PLANET($player->sector_id, $player->game_id);
$planet->build();
print_topic("PLANET : $planet->planet_name [SECTOR #$player->sector_id]");

include(get_file_loc('menue.inc'));
print_planet_menue();

print("<p>");
print("<table cellspacing=\"0\" cellpadding=\"3\" border=\"0\" class=\"standard\">");

print("<tr>");
print("<th>Type</th>");
print("<th>Ship</th>");
print("<th>Planet</th>");
print("<th>Amount</th>");
print("<th>Transfer to</th>");
print("</tr>");

$container = array();
$container["url"] = "planet_defense_processing.php";
$container["type_id"] = 1;

print_form($container);

print("<tr>");
print("<td>Shields</td>");
print("<td align=\"center\">" . $ship->hardware[1] . "</td>");
print("<td align=\"center\">$planet->shields</td>");
print("<td align=\"center\"><input type=\"text\" name=\"amount\" value=\"0\" id=\"InputFields\" size=\"4\" style=\"text-align:center;\"></td>");
print("<td>");
print_submit("Ship");
print("&nbsp;");
print_submit("Planet");
print("</td>");
print("</tr>");
print("</form>");


$container = array();
$container["url"] = "planet_defense_processing.php";
$container["type_id"] = 4;

print_form($container);
print("<tr>");
print("<td>Combat Drones</td>");
print("<td align=\"center\">" . $ship->hardware[4] . "</td>");
print("<td align=\"center\">$planet->drones</td>");
print("<td align=\"center\"><input type=\"text\" name=\"amount\" value=\"0\" id=\"InputFields\" size=\"4\" style=\"text-align:center;\"></td>");
print("<td>");
print_submit("Ship");
print("&nbsp;");
print_submit("Planet");
print("</td>");
print("</tr>");
print("</form>");

print("</table>");
print("</p>");

?>