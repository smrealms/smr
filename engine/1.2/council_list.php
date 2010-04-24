<?php

include(get_file_loc("council.inc"));
include(get_file_loc('menue.inc'));

$race_id = $var["race_id"];
if (empty($race_id))
	$race_id = $player->race_id;

$db->query("SELECT * FROM race " .
		   "WHERE race_id = $race_id");
if ($db->next_record())
	print_topic("RULING COUNCIL OF " . $db->f("race_name"));

$president = getPresident($race_id);

print_council_menue($race_id, $president);

// check for relations here
modifyRelations($race_id);

checkPacts($race_id);

print("<div align=\"center\" style=\"font-weight:bold;\">President</div>");

if ($president->account_id > 0) {

	print("<p><table border=\"0\" class=\"standard\" cellspacing=\"0\" align=\"center\" width=\"75%\">");
	print("<tr>");
	print("<th>Name</th>");
	print("<th>Race</th>");
	print("<th>Alliance</th>");
	print("<th>Experience</th>");
	print("</tr>");
	print("<tr>");

	print("<td valign=\"top\">President ");
	$container = array();
	$container["url"]		= "skeleton.php";
	$container["body"]		= "trader_search_result.php";
	$container["player_id"]	= $president->player_id;
	print_link($container, $president->get_colored_name());
	print("</td>");

	print("<td align=\"center\">");
	$container = array();
	$container["url"] = "skeleton.php";
	$container["body"] = "council_send_message.php";
	$container["race_id"] = $president->race_id;
	print_link($container, $president->get_colored_race($president->race_id));
	print("</td>");

	print("<td>");
	if ($president->alliance_id > 0) {

		$container = array();
		$container["url"] 			= "skeleton.php";
		$container["body"] 			= "alliance_roster.php";
		$container["alliance_id"]	= $president->alliance_id;
		print_link($container, "$president->alliance_name");
	} else
		print("(none)");
	print("</td>");
	$president->get_display_xp_lvl();
	print("<td align=\"right\">$president->display_experience</td>");

	print("</tr>");
	print("</table></p>");

} else
	print("<div align=\"center\">This council doesn't have a president!</div>");

print("<br><br><div align=\"center\" style=\"font-weight:bold;\">Member</div>");

$db->query("SELECT * FROM player " .
		   "WHERE game_id = $player->game_id AND " .
				 "race_id = $race_id " .
		   "ORDER by experience DESC " .
		   "LIMIT 20");
		   
if ($db->nf() > 0) {
	
	$list = "(0";
	while ($db->next_record()) $list .= "," . $db->f("account_id");
	$list .= ")";
	
}
$db->query("SELECT * FROM player_cache WHERE account_id IN $list AND game_id = $player->game_id ORDER BY experience DESC");

if ($db->nf() > 0) {

	print("<p><table border=\"0\" class=\"standard\" cellspacing=\"0\" align=\"center\" width=\"85%\">");
	print("<tr>");
	print("<th>&nbsp;</th>");
	print("<th>Name</th>");
	print("<th>Race</th>");
	print("<th>Alliance</th>");
	print("<th>Experience</th>");
	print("</tr>");

	$count = 0;
	while ($db->next_record()) {

		$council = new SMR_PLAYER($db->f("account_id"), $player->game_id);
		$count++;

		print("<tr>");

		print("<td align=\"center\"");
		if ($council->account_id == $player->account_id)
			print(" style=\"font-weight:bold;\"");
		print(">$count.</td>");

		print("<td valign=\"middle\"");
		if ($council->account_id == $player->account_id)
			print(" style=\"font-weight:bold;\"");
		print(">$council->display_level_name ");
		$container = array();
		$container["url"]		= "skeleton.php";
		$container["body"]		= "trader_search_result.php";
		$container["player_id"]	= $council->player_id;
		print_link($container, $council->get_colored_name());
		print("</td>");

		print("<td align=\"center\"");
		if ($council->account_id == $player->account_id)
			print(" style=\"font-weight:bold;\"");
		print(">");
		$container = array();
		$container["url"] = "skeleton.php";
		$container["body"] = "council_send_message.php";
		$container["race_id"] = $council->race_id;
		print_link($container, $council->get_colored_race($council->race_id));
		print("</td>");

		print("<td");
		if ($council->account_id == $player->account_id)
			print(" style=\"font-weight:bold;\"");
		print(">");
		if ($council->alliance_id > 0) {

			$container = array();
			$container["url"] 			= "skeleton.php";
			$container["body"] 			= "alliance_roster.php";
			$container["alliance_id"]	= $council->alliance_id;
			print_link($container, "$council->alliance_name");
		} else
			print("(none)");
		print("</td>");

		print("<td align=\"right\"");
		if ($council->account_id == $player->account_id)
			print(" style=\"font-weight:bold;\"");
		$council->get_display_xp_lvl();
		print(">$council->display_experience</td>");

		print("</tr>");

	}

	print("</table></p>");


} else
	print("<div align=\"center\">This council doesn't have any members!</div>");

print("<p>&nbsp;</p>");

print("<b>View Council</b><br>");
$db->query("SELECT * FROM race WHERE race_id > 1");
while($db->next_record()) {

	$race_id	= $db->f("race_id");
	$race_name	= $db->f("race_name");

	$container = array();
	$container["url"]		= "skeleton.php";
	$container["body"]		= "council_list.php";
	$container["race_id"]	= $race_id;

	print_link($container, "<span style=\"font-size:75%;\">$race_name</span>");
	print("<br>");

}

?>