<?php

//get game id
$game_id = $var["game_id"];
//get name
$db->query("SELECT game_description, credits_needed, game_name, game_speed, max_players, " . 
			"game_type, DATE_FORMAT(start_date, '%c/%e/%Y') as format_start_date, " . 
			"DATE_FORMAT(end_date, '%c/%e/%Y') as format_end_date FROM game " . 
			"WHERE game_id = $game_id");
$db->next_record();
$game_name = $db->f("game_name");
$game_desc = $db->f("game_description");
$start = $db->f("format_start_date");
$end = $db->f("format_end_date");
$speed = $db->f("game_speed");
$max = $db->f("max_players");
$type = $db->f("game_type");
$creds = $db->f("credits_needed");

$db->query("SELECT * FROM player " .
			"WHERE last_active >= " . (time() - 600) . " AND " .
				  "game_id = $game_id");
$current = $db->nf();
print("<div align=center>");
print_topic("Game Stats for $game_name");
print_table();
print"<tr><td align=center>General Info</td><td align=center>Other Info</td></tr>
<tr>
<td valign=top align=center>
<table class=\"nobord\">
<tr><td align=right>Name</td>           <td>&nbsp;</td><td align=left>$game_name</td></tr>
<tr><td align=right>Description</td>    <td>&nbsp;</td><td align=left>$game_desc</td></tr>
<tr><td align=right>Start Date</td>     <td>&nbsp;</td><td align=left>$start</td></tr>
<tr><td align=right>End Date</td>       <td>&nbsp;</td><td align=left>$end</td></tr>
<tr><td align=right>Current Players</td><td>&nbsp;</td><td align=left>$current</td></tr>
<tr><td align=right>Max Players</td>    <td>&nbsp;</td><td align=left>$max</td></tr>
<tr><td align=right>Game Type</td>      <td>&nbsp;</td><td align=left>$type</td></tr>
<tr><td align=right>Game Speed</td>     <td>&nbsp;</td><td align=left>$speed</td></tr>
<tr><td align=right>Credits Needed</td> <td>&nbsp;</td><td align=left>$creds</td></tr>
</table>
</td>";
$db->query("SELECT * FROM player WHERE game_id = $game_id ORDER BY experience DESC");
if ($db->next_record()) {
	
	$players = $db->nf();
	$max_exp = $db->f("experience");
	
}
$db->query("SELECT * FROM player WHERE game_id = $game_id ORDER BY alignment DESC");
if ($db->next_record()) $align = $db->f("alignment");
$db->query("SELECT * FROM player WHERE game_id = $game_id ORDER BY alignment ASC");
if ($db->next_record()) $align_low = $db->f("alignment");
$db->query("SELECT * FROM player WHERE game_id = $game_id ORDER BY kills DESC");
if ($db->next_record()) $kills = $db->f("kills");

	
$db->query("SELECT * FROM alliance WHERE game_id = $game_id");
if ($db->next_record()) $alliances = $db->nf();
print"
<td valign=top align=center>
<table class=\"nobord\">
<tr><td align=right>Players</td>           <td>&nbsp;</td><td align=left>$players</td></tr>
<tr><td align=right>Alliances</td>          <td>&nbsp;</td><td align=left>$alliances</td></tr>
<tr><td align=right>Highest Experience</td><td>&nbsp;</td><td align=left>$max_exp</td></tr>
<tr><td align=right>Highest Alignment</td> <td>&nbsp;</td><td align=left>$align</td></tr>
<tr><td align=right>Lowest Alignment</td><td>&nbsp;</td><td align=left>$align_low</td></tr>
<tr><td align=right>Highest Kills</td>     <td>&nbsp;</td><td align=left>$kills</td></tr>
</table>
</td>
</tr>
</table><br>";
print_table();
print"
<tr>
<td align=center>Top 10 Players in Experience</td>
<td align=center>Top 10 Players in Kills</td>
</tr>
<tr>
<td align=center style=\"border:none\">";
$rank = 0;
$db->query("SELECT * FROM player WHERE game_id = $game_id ORDER BY experience DESC LIMIT 10");
if ($db->nf() > 0) {
	print("<table class=\"nobord\"><tr><th align=center>Rank</th><th align=center>Player</th><th align=center>Experience</th></tr>");
	while ($db->next_record()) {
		
		$exp = $db->f("experience");
		$this_player = new SMR_PLAYER($db->f("account_id"), $game_id);
		print("<tr><td align=center>" . ++$rank . "</td><td align=center>$this_player->player_name</td><td align=center>$exp</td></tr>");
		
	}
	print("</table>");
	
}
print"
</td><td align=center>";
$rank = 0;
$db->query("SELECT * FROM player WHERE game_id = $game_id ORDER BY kills DESC LIMIT 10");
if ($db->nf() > 0) {
	print("<table class=\"nobord\"><tr><th align=center>Rank</th><th align=center>Player</th><th align=center>Kills</th></tr>");
	while ($db->next_record()) {
		
		$kills = $db->f("kills");
		$this_player = new SMR_PLAYER($db->f("account_id"), $game_id);
		print("<tr><td align=center>" . ++$rank . "</td><td align=center>$this_player->player_name</td><td align=center>$kills</td></tr>");
		
	}
	print("</table>");
	
}
print"
</td>
</tr>
</table>";

print_topic("CURRENT PLAYERS");

$db->query("SELECT * FROM active_session
			WHERE last_accessed >= " . (time() - 600) . " AND
				  game_id = $game_id");
$count_real_last_active = $db->nf();

$db->query("SELECT * FROM player " .
		   "WHERE last_active >= " . (time() - 600) . " AND " .
				 "game_id = $game_id " .
		   "ORDER BY experience DESC, player_name");
$count_last_active = $db->nf();

// fix it if some1 is using the logoff button
if ($count_real_last_active < $count_last_active)
	$count_real_last_active = $count_last_active;

print("<p>There ");
if ($count_real_last_active != 1)
	print("are $count_real_last_active players who have ");
else
	print("is 1 player who has ");
print("accessed the server in the last 10 minutes.<br>");

if ($count_last_active == 0)
	print("Noone was moving so your ship computer can't intercept any transmissions.<br>");
else {

	if ($count_last_active == $count_real_last_active)
		print("All of them ");
	else
		print("A few of them ");

	print("were moving so your ship computer was able to intercept $count_last_active transmission");

	if ($count_last_active > 1)
		print("s.<br>");
	else
		print(".<br>");
}
	print("The traders listed in <span style=\"font-style:italic;\">italics</span> are still ranked as Newbie or Beginner.</p>");

$player = new SMR_PLAYER($account->account_id, $game_id);
if ($count_last_active > 0) {

	print("<table cellspacing=\"0\" cellpadding=\"5\" border=\"0\" class=\"standard\" width=\"95%\">");
	print("<tr>");
	print("<th>Player</th>");
	print("<th>Race</th>");
	print("<th>Alliance</th>");
	print("<th>Experience</th>");
	print("</tr>");

	while ($db->next_record()) {

		$curr_account = new SMR_ACCOUNT();
		$curr_account->get_by_id($db->f("account_id"));
		//reset style
		$style = "";
		$curr_player = new SMR_PLAYER($db->f("account_id"), $game_id);

		if ($curr_account->veteran == "FALSE" && $curr_account->get_rank() < FLEDGLING)
			$style = "font-style:italic;";
		if ($curr_player->account_id == $account->account_id)
			$style .= "font-weight:bold;";

		if (!empty($style))
			$style = " style=\"$style\"";

		print("<tr>");
		print("<td valign=\"top\"$style>$curr_player->display_level_name ");
		$name = $curr_player->get_colored_name();
		print("$name");
		print("</td>");
		print("<td align=\"center\"$style>");
		$race = $player->get_colored_race($curr_player->race_id);
		print("$race");
		print("</td>");
		print("<td$style>");
		if ($curr_player->alliance_id > 0) print("$curr_player->alliance_name");
		else print("(none)");
		print("</td>");
		$curr_player->get_display_xp_lvl();
		print("<td align=\"right\"$style>" . number_format($curr_player->display_experience) . "</td>");
		print("</tr>");

	}

	print("	</table>");

}

print("</div>");

?>