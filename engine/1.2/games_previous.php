<?php

require_once(get_file_loc("smr_history_db.inc"));
print("<div align=center>");

//topic
if (isset($var["game_name"])) $game_name = $var["game_name"];
if (isset($var["game_id"])) $game_id = $var["game_id"];
if (isset($game_name)) $topic = "Game $var[game_name]";
else $topic = "Games";
print_topic("Viewing Old SMR $topic");

if (!isset($game_name)) {

	//list old games
	$db2 = new SMR_HISTORY_DB();
	$db2->query("SELECT DATE_FORMAT(start_date, '%c/%e/%Y') as start_date, " .
				"DATE_FORMAT(end_date, '%c/%e/%Y') as end_date, game_name, speed, game_id " .
				"FROM game ORDER BY game_id");
	if ($db2->nf()) {

		print_table();
		print("<tr><th align=center>Game Name</th><th align=center>Start Date</th><th align=center>End Date</th><th align=center>Speed</th><th align=center colspan=3>Options</th></tr>");
		while ($db2->next_record()) {

			$id = $db2->f("game_id");
			$container = array();
			$container["url"] = "skeleton.php";
			$container["game_id"] = $db2->f("game_id");
			$container["game_name"] = $db2->f("game_name");
			$container["body"] = "games_previous.php";
			$name = $db2->f("game_name");
			print("<tr><td align=center>");
			print_link($container, "$name ($id)");
			print("</td>");
			print("<td align=center>" . $db2->f("start_date") . "</td>");
			print("<td align=center>" . $db2->f("end_date") . "</td>");
			print("<td align=center>" . $db2->f("speed") . "</td>");
			print("<td align=center>");
			$container = array();
			$container["url"] = "skeleton.php";
			$container["body"] = "hall_of_fame_new.php";
			$container["game_id"] = $db2->f("game_id");
			print_link($container, "Hall of Fame");
			print("</td>");
			print("<td align=center>");
			$container["body"] = "games_previous_news.php";
			$container["game_id"] = $db2->f("game_id");
			$container["game_name"] = $db2->f("game_name");
			print_link($container, "Game News");
			print("</td>");
			print("<td align=center>");
			$container["body"] = "games_previous_detail.php";
			$container["game_id"] = $db2->f("game_id");
			$container["game_name"] = $db2->f("game_name");
			print_link($container, "Game Stats");
			print("</td>");

		}
		print("</table>");

	}

} else {

	//code for the game goes in here

	$db2 = new SMR_HISTORY_DB();
	$db2->query("SELECT DATE_FORMAT(start_date, '%c/%e/%Y') as start_date, type, " .
				"DATE_FORMAT(end_date, '%c/%e/%Y') as end_date, game_name, speed, game_id " .
				"FROM game WHERE game_id = '$game_id'");
	print_table();
	$db2->next_record();
	$start = $db2->f("start_date");
	$end = $db2->f("end_date");
	$type = $db2->f("type");
	$speed = $db2->f("speed");
	print"<tr><td align=center>General Info</td><td align=center>Other Info</td></tr>
	<tr>
	<td valign=top align=center>
	<table>
	<tr><td align=right>Name</td>           <td>&nbsp;</td><td align=left>$game_name</td></tr>
	<tr><td align=right>Start Date</td>     <td>&nbsp;</td><td align=left>$start</td></tr>
	<tr><td align=right>End Date</td>       <td>&nbsp;</td><td align=left>$end</td></tr>
	<tr><td align=right>Game Type</td>      <td>&nbsp;</td><td align=left>$type</td></tr>
	<tr><td align=right>Game Speed</td>     <td>&nbsp;</td><td align=left>$speed</td></tr>
	</table>
	</td>";
	$db2->query("SELECT * FROM player WHERE game_id = $game_id ORDER BY experience DESC");
	if ($db2->next_record()) {

		$players = $db2->nf();
		$max_exp = $db2->f("experience");

	}
	$db2->query("SELECT * FROM player WHERE game_id = $game_id ORDER BY alignment DESC");
	if ($db2->next_record()) $align = $db2->f("alignment");
	$db2->query("SELECT * FROM player WHERE game_id = $game_id ORDER BY alignment ASC");
	if ($db2->next_record()) $align_low = $db2->f("alignment");
	$db2->query("SELECT * FROM player WHERE game_id = $game_id ORDER BY kills DESC");
	if ($db2->next_record()) $kills = $db2->f("kills");


	$db2->query("SELECT * FROM alliance WHERE game_id = $game_id");
	if ($db2->next_record()) $alliances = $db2->nf();
	print"
	<td valign=top align=center>
	<table>
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
	<td align=center>";
	$rank = 0;
	$db2->query("SELECT * FROM player WHERE game_id = $game_id ORDER BY experience DESC LIMIT 10");
	if ($db2->nf() > 0) {

		print("<table><tr><th align=center>Rank</th><th align=center>Player</th><th align=center>Experience</th></tr>");
		while ($db2->next_record()) {

			$exp = $db2->f("experience");
			$player_name = stripslashes($db2->f("player_name"));
			print("<tr><td align=center>" . ++$rank . "</td><td align=center>$player_name</td><td align=center>$exp</td></tr>");

		}
		print("</table>");

	}
	print"
	</td><td align=center>";
	$rank = 0;
	$db2->query("SELECT * FROM player WHERE game_id = $game_id ORDER BY kills DESC LIMIT 10");
	if ($db2->nf() > 0) {

		print("<table><tr><th align=center>Rank</th><th align=center>Player</th><th align=center>Kills</th></tr>");
		while ($db2->next_record()) {

			$kills = $db2->f("kills");
			$player_name = stripslashes($db2->f("player_name"));
			print("<tr><td align=center>" . ++$rank . "</td><td align=center>$player_name</td><td align=center>$kills</td></tr>");

		}
		print("</table>");

	}
	print"
	</td>
	</tr>
	</table><br>";
	print_table();
	print"<tr><td align=center>Top 10 Alliances in Experience</td><td align=center>Top 10 Alliances in Kills</td></tr>
	<tr>
	<td align=center>";
	$rank = 0;
	//now for the alliance stuff
	$db2->query("SELECT sum(experience) as exp, alliance_name, player.alliance_id FROM player, alliance WHERE player.game_id = $game_id AND alliance.game_id = $game_id AND player.alliance_id = alliance.alliance_id GROUP BY player.alliance_id ORDER BY exp DESC LIMIT 10");
	if ($db2->nf()) {

		print("<table><tr><th align=center>Rank</th><th align=center>Alliance</th><th align=center>Experience</th></tr>");
		$container = array();
		$container["url"] = "skeleton.php";
		$container["body"] = "alliance_detail_old.php";
		$container["game_id"] = $game_id;
		while ($db2->next_record()) {

			$exp = $db2->f("exp");
			$alliance = stripslashes($db2->f("alliance_name"));
			$id = $db2->f("alliance_id");
			$container["alliance_id"] = $id;
			print("<tr><td align=center>" . ++$rank . "</td><td align=center>");
			print_link($container, $alliance);
			print("</td><td align=center>$exp</td></tr>");

		}

		print("</table>");

	}
	print"
	</td>
	<td valign=top align=center>";
	$rank = 0;
	//now for the alliance stuff
	$db2->query("SELECT kills, alliance_name, alliance_id FROM alliance WHERE game_id = $game_id ORDER BY kills DESC LIMIT 10");
	if ($db2->nf()) {

		print("<table><tr><th align=center>Rank</th><th align=center>Alliance</th><th align=center>Kills</th></tr>");
		$container = array();
		$container["url"] = "skeleton.php";
		$container["body"] = "alliance_detail_old.php";
		$container["game_id"] = $game_id;
		while ($db2->next_record()) {

			$kill = $db2->f("kills");
			$alliance = stripslashes($db2->f("alliance_name"));
			$id = $db2->f("alliance_id");
			$container["alliance_id"] = $id;
			print("<tr><td align=center>" . ++$rank . "</td><td align=center>");
			print_link($container, $alliance);
			print("</td><td align=center>$kill</td></tr>");

		}

		print("</table>");

	}
	print"
	</td>
	</tr>
	</table><br>";

}
print("</div>");
//to stop errors on the following scripts
$db = new SmrMySqlDatabase();
?>