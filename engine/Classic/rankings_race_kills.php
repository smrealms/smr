<?php

print_topic("Racial Standings");

include(get_file_loc('menue.inc'));
print_ranking_menue(2, 1);

print("<div align=center>");
print("<p>Here are the rankings of the races by their kills</p>");
print("<table cellspacing=\"0\" cellpadding=\"5\" border=\"0\" class=\"standard\" width=\"95%\">");
print("<tr>");
print("<th>Rank</th>");
print("<th>Race</th>");
print("<th>Kills</th>");
print("</tr>");

$rank = 0;
$db2 = new SmrMySqlDatabase();
$db->query("SELECT race.race_id as race_id, race_name, sum(kills) as kill_sum, count(account_id) FROM player NATURAL JOIN race WHERE game_id = $player->game_id GROUP BY player.race_id ORDER BY kill_sum DESC");
while ($db->next_record()) {

	$rank++;
	$race_id = $db->f("race_id");
	$db2->query("SELECT * FROM player WHERE race_id = $race_id AND game_id = $player->game_id AND out_of_game = 'TRUE'");
	if ($player->race_id == $race_id) $style = " style=\"font-weight:bold;\"";
	elseif ($db2->next_record()) $style = " style=\"color:red;\"";
	else $style = "";

	print("<tr>");
	print("<td align=\"center\"$style>$rank</td>");
	print("<td align=\"center\"$style>" . $db->f("race_name") . "</td>");
	print("<td align=\"center\"$style>" . $db->f("kill_sum") . "</td>");
	print("</tr>");

}

print("</table>");
print("</div>");

?>