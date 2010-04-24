<?php

// check if our alignment is high enough
if ($player->alignment <= -100) {
	print_error("You are not allowed to enter our Government HQ!");
	return;
}

// get the name of this facility
$db->query("SELECT * FROM location NATURAL JOIN location_type " .
		   "WHERE game_id = $player->game_id AND " .
		   "sector_id = $player->sector_id AND " .
		   "location.location_type_id >= 103 AND " .
		   "location.location_type_id <= 110");
if ($db->next_record()) {

	$location_type_id = $db->f("location_type_id");
	$location_name = $db->f("location_name");

	$race_id = $location_type_id - 101;

}

// did we get a result
if (empty($race_id)) {
  print_error("There is no headquarter. Obviously.");
  return;
}

// are we at war?
$db->query("SELECT * FROM race_has_relation WHERE game_id = ".SmrSession::$game_id." AND race_id_1 = $race_id AND race_id_2 = $player->race_id");
if ($db->next_record() && $db->f("relation") <= -300) {
	print_error("We are at WAR with your race! Get outta here before I call the guards!");
	return;
}

// topic
if (isset($location_type_id))
	print_topic($location_name);
else
	print_topic("FEDERAL HQ");

// header menue
include(get_file_loc('menue.inc'));
print_hq_menue();

// secondary db object
$db2 = new SmrMySqlDatabase();

if (isset($location_type_id)) {

	print("<div align=\"center\">We are at WAR with<br><br>");
	$db->query("SELECT * FROM race_has_relation WHERE game_id = $player->game_id AND race_id_1 = $race_id");
	while($db->next_record()) {

		$relation = $db->f("relation");
		$race_2 = $db->f("race_id_2");

		$db2->query("SELECT * FROM race WHERE race_id = $race_2");
		$db2->next_record();
		$race_name = $db2->f("race_name");
		if ($relation <= -300)
			print("<span style=\"color:red;\">The $race_name<br></span>");

	}

	print("<br>The government will PAY for the destruction of their ships!");

}

$db->query("SELECT * FROM bounty WHERE game_id = $player->game_id AND type = 'HQ' AND claimer_id = 0 ORDER BY amount DESC");

print("<p>&nbsp;</p>");
if ($db->nf()) {

	print("<div align=\"center\">Most Wanted by Federal Government</div><br>");
	print_table();
	print("<tr>");
	print("<th>Player Name</th>");
	print("<th>Bounty Amount</th>");
	print("</tr>");

	while ($db->next_record()) {

		$id = $db->f("account_id");
		$db2->query("SELECT * FROM player WHERE game_id = $player->game_id AND account_id = $id");
		if ($db2->next_record())
			$name = stripslashes($db2->f("player_name"));
		$amount = $db->f("amount");
		print("<tr>");
		print("<td align=\"center\"><font color=yellow>$name</font></td>");
		print("<td align=\"center\"><font color=red> " . number_format($amount) . " </font></td>");
		print("</tr>");

	}

	print("</table>");

}

if ($player->alignment >= -99 && $player->alignment <= 100) {

	print_form(create_container("government_processing.php", ""));
	print_submit("Become a deputy");
	print("</form>");

}

?>