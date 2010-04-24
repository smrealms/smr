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
print("<th>Good</th>");
print("<th>Ship</th>");
print("<th>Planet</th>");
print("<th>Amount</th>");
print("<th>Transfer to</th>");
print("</tr>");

$db->query("SELECT * FROM good ORDER BY good_id");
while($db->next_record()) {

	$good_id	= $db->f("good_id");
	$good_name	= $db->f("good_name");

	if (empty($ship->cargo[$good_id]) && empty($planet->stockpile[$good_id])) continue;

	$container = array();
	$container["url"] = "planet_stockpile_processing.php";
	$container["good_id"] = $good_id;

	print_form($container);
	print("<tr>");
	print("<td>$good_name</td>");
	print("<td align=\"center\">" . $ship->cargo[$good_id] . "</td>");
	print("<td align=\"center\">" . $planet->stockpile[$good_id] . "</td>");
	print("<td align=\"center\"><input type=\"text\" name=\"amount\" value=\"0\" id=\"InputFields\" size=\"4\" style=\"text-align:center;\"></td>");
	print("<td>");
	print_submit("Ship");
	print("&nbsp;");
	print_submit("Planet");
	print("</td>");
	print("</tr>");
	print("</form>");

}

print("</table>");
print("</p>");

?>