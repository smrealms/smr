<?php

print_topic("Racial Standings");

include(get_file_loc('menue.inc'));
print_ranking_menue(2, 0);

print("<div align=center>");
print("<p>Here are the rankings of the races by their experience</p>");
print("<table cellspacing=\"0\" cellpadding=\"5\" border=\"0\" class=\"standard\" width=\"95%\">");
print("<tr>");
print("<th>Rank</th>");
print("<th>Race</th>");
print("<th>Total Experience</th>");
print("<th>Average Experience</th>");
print("<th>Total Traders</th>");
print("</tr>");

$rank = 0;
$db2 = new SmrMySqlDatabase();
$db->query("SELECT player.race_id as race_id, race_name, sum(player_cache.experience) as experience_sum, count(player_cache.account_id) as members FROM player,player_cache NATURAL JOIN race WHERE race.race_id = player.race_id AND (player_cache.account_id = player.account_id AND player_cache.game_id = $player->game_id) AND player.game_id = $player->game_id GROUP BY player.race_id ORDER BY experience_sum DESC");
while ($db->next_record()) {

	$rank++;
	$race_id = $db->f("race_id");
	$db2->query("SELECT * FROM player WHERE race_id = $race_id AND game_id = $player->game_id AND out_of_game = 'TRUE'");
	if ($player->race_id == $race_id) $style = " style=\"font-weight:bold;\"";
	elseif ($db2->next_record()) $style = " style=\"color:red;\"";
	else $style = "";
	
	if ($db2->next_record()) $style .= 
	print("<tr>");
	print("<td align=\"center\"$style>$rank</td>");
	print("<td align=\"center\"$style>" . $db->f("race_name") . "</td>");
	print("<td align=\"center\"$style>" . $db->f("experience_sum") . "</td>");
	print("<td align=\"center\"$style>" . round($db->f("experience_sum") / $db->f("members")) . "</td>");
	print("<td align=\"center\"$style>" . $db->f("members") . "</td>");
	print("</tr>");

}

print("</table>");
print("</div>");

?>
