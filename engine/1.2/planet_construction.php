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

if ($planet->build()) {

	$db->query("SELECT * FROM planet_build_construction NATURAL JOIN planet_construction " .
						"WHERE game_id = $player->game_id AND " .
							  "sector_id = $player->sector_id");
	if ($db->next_record()) {

		$construction_name	= $db->f("construction_name");
		$construction_id	= $db->f("construction_id");
		$time_left			= $db->f("time_complete") - time();

	}

	$hours = floor($time_left / 3600);
	$minutes = floor(($time_left - $hours * 3600) / 60);
	$seconds = $time_left - $hours * 3600 - $minutes * 60;

	print("<p>You are currently building: $construction_name.<br>");
	print("Finished in ");

	if ($hours > 0) {

		if ($hours == 1)
			print("$hours hour");
		else
			print("$hours hours");

		if ($minutes > 0 && $seconds > 0)
			print(", ");
		elseif
			($minutes > 0 || $seconds > 0) print(" and ");
		else
			print(".");
	}

	if ($minutes > 0) {

		if ($minutes == 1)
			print("$minutes minute");
		else
			print("$minutes minutes");

		if ($seconds > 0)
			print(" and ");
	}

	if ($seconds > 0)
		if ($seconds == 1)
			print("$seconds second");
		else
			print("$seconds seconds");

	// esp. if no time left...
	if ($hours == 0 && $minutes == 0 && $seconds == 0)
		print("0 seconds");

	$container = array();
	$container["url"] = "planet_construction_processing.php";
	$container["id"] = $construction_id;
	print_form($container);
	print_submit("Cancel");
	print("</form>");

} else
	print("<p>You are currently building: Nothing</p>");

print("<p>");
print("<div align=\"center\">");
print("<table cellspacing=\"0\" cellpadding=\"3\" border=\"0\" class=\"standard\">");

print("<tr>");
print("<th>Type</th>");
print("<th>Description</th>");
print("<th>Current</th>");
print("<th>Cost</th>");
print("<th>Build</th>");
print("</tr>");

// get game speed
$db->query("SELECT * FROM game WHERE game_id = $player->game_id");
if ($db->next_record())
	$game_speed = $db->f("game_speed");

$db2 = new SmrMySqlDatabase();
$db->query("SELECT * FROM planet_construction ORDER BY construction_id");
while ($db->next_record()) {

	$construction_id			= $db->f("construction_id");
	$construction_name			= $db->f("construction_name");
	$construction_description	= $db->f("construction_description");

	$db2->query("SELECT * FROM planet_cost_credits WHERE construction_id = $construction_id");
	if ($db2->next_record())
		$cost = $db2->f("amount");

	/*$container = array();
	$container["url"] = "planet_construction_processing.php";
	$container["construction_id"] = $construction_id;
	$container["cost"] = $cost;

	print_form($container);*/
	print("<tr>");
	print("<td>$construction_name</td>");
	print("<td>$construction_description</td>");
	print("<td align=\"center\">");
	print($planet->construction[$construction_id]);
	print("/");
	print($planet->max_construction[$construction_id]);
	print("</td>");
	print("<td>");
	$missing_good = false;
	$db2->query("SELECT * FROM planet_cost_good, good " .
						"WHERE planet_cost_good.good_id = good.good_id AND " .
							  "construction_id = $construction_id " .
						"ORDER BY good.good_id");
	while ($db2->next_record()) {

		$good_id	= $db2->f("good_id");
		$good_name	= $db2->f("good_name");
		$amount		= $db2->f("amount");

		if ($planet->stockpile[$good_id] < $amount) {

			print("<span style=\"color:red;\">$amount-$good_name, </span>");
			$missing_good = true;

		} else
			print("$amount-$good_name, ");

	}

	$missing_credits = false;
	if ($player->credits < $cost) {

		print("<span style=\"color:red;\">$cost-credits, </span>");
		$missing_credits = true;

	} else
		print("$cost-credits, ");

	$db2->query("SELECT * FROM planet_cost_time WHERE construction_id = $construction_id");
	if ($db2->next_record())
		print(($db2->f("amount") / 3600 / $game_speed) . "-hours");

	print("</td>");
	print("<td>");
	if (!$missing_good && !$missing_credits && !$planet->build() && $planet->construction[$construction_id] < $planet->max_construction[$construction_id])
	{
		$container = array();
		$container["url"] = "planet_construction_processing.php";
		$container["construction_id"] = $construction_id;
		$container["cost"] = $cost;
		print_form($container);
		print_submit("Build");
		print("</form>");
	}
	else
		print("&nbsp;");
	print("</td>");
	print("</tr>");
	//print("</form>");

}

print("</table>");
print("</div>");
print("</p>");

print("<p>Your stockpile contains :</p>");
print("<ul>");
foreach ($planet->stockpile as $id => $amount)
	if ($amount > 0) {

		$db->query("SELECT * FROM good WHERE good_id = $id");
		if ($db->next_record())
			print("<li>" . $db->f("good_name") . ": $amount</li>");

	}
print("</ul>");

?>