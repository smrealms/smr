<?php

include(get_file_loc("council.inc"));
include(get_file_loc('menue.inc'));

// print topic
$race_id = $var["race_id"];
if (empty($race_id))
	$race_id = $player->race_id;

$db->query("SELECT * FROM race " .
		   "WHERE race_id = $race_id");
if ($db->next_record())
	print_topic("RULING COUNCIL OF " . $db->f("race_name"));

// get president and print menue
$president = getPresident($race_id);
print_council_menue($race_id, $president);

print("<div align=\"center\">");
print("<p>We are at War/Peace<br>with the following races:</p>");

print("<table>");
print("<tr>");
print("<th width=\"150\">Peace</th>");
print("<th width=\"150\">War</th>");
print("</tr>");

print("<tr>");

// peace
print("<td align=\"center\" valign=\"top\">");
print("<table>");
$db->query("SELECT race_name, race.race_id as race_id, relation FROM race_has_relation, race " .
		   "WHERE race_has_relation.race_id_2 = race.race_id AND " .
				 "race_has_relation.race_id_1 = $race_id AND " .
				 "race_has_relation.race_id_1 != race_has_relation.race_id_2 AND " .
				 "race_has_relation.relation >= 300 AND " .
				 "race_has_relation.game_id = $player->game_id");
while ($db->next_record()) {

	$container = array();
	$container["url"] = "skeleton.php";
	$container["body"] = "council_send_message.php";
	$container["race_id"] = $db->f("race_id");
	print("<tr><td align=\"center\">");
	print_link($container, get_colored_text($db->f("relation"), $db->f("race_name")));
	print("</td></tr>");

}

print("</table>");
print("</td>");

// war
print("<td align=\"center\" valign=\"top\">");
print("<table>");
$db->query("SELECT race_name, race.race_id as race_id, relation FROM race_has_relation, race " .
		   "WHERE race_has_relation.race_id_2 = race.race_id AND " .
				 "race_has_relation.race_id_1 = $race_id AND " .
				 "race_has_relation.race_id_1 != race_has_relation.race_id_2 AND " .
				 "race_has_relation.relation <= -300 AND " .
				 "race_has_relation.game_id = $player->game_id");
while ($db->next_record()) {

	$container = array();
	$container["url"] = "skeleton.php";
	$container["body"] = "council_send_message.php";
	$container["race_id"] = $db->f("race_id");
	print("<tr><td align=\"center\">");
	print_link($container, get_colored_text($db->f("relation"), $db->f("race_name")));
	print("</td></tr>");

}
print("</table>");
print("</td>");

print("</tr>");

print("</table>");
print("</div>");

?>