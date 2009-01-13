<?
require_once(get_file_loc("smr_history_db.inc"));
print("<h1>PLAY GAME</h1>");
if (isset($var["msg"]))
	print("$var[msg]<br>");

print_link(create_container("skeleton.php", "rankings_view.php"),
		   "<b style=\"color:yellow;\">Rankings</b>");

print("<br>You are ranked as a");
if ($rank == 3 || $rank == 4 || $rank == 5)
	print("n");
print(" <span style=\"font-size:125%;color:greenyellow;\">" . $account->get_rank_name() . "</span> player.<p>");

$db->query("SELECT DATE_FORMAT(end_date, '%c/%e/%Y') as format_end_date, end_date, game.game_id as game_id, game_name, game_speed FROM game, player " .
					"WHERE game.game_id = player.game_id AND " .
						  "account_id = SmrSession::$account_id AND " .
						  "end_date >= '" . date("Y-m-d") . "'");
if ($db->nf() > 0) {

	print("<p>");
	print_table();
	print("<tr>");
	print("<th>&nbsp;</th>");
	print("<th>Game</th>");
	print("<th>Turns</th>");
	print("<th>Playing</th>");
	print("<th>End Date</th>");
	print("</tr>");

	while ($db->next_record()) {

		$game_id = $db->f("game_id");
		$game_name = $db->f("game_name");
		$end_date = $db->f("format_end_date");
		$game_speed = $db->f("game_speed");

		// creates a new player object
		$curr_player = new SMR_PLAYER(SmrSession::$account_id, $game_id);
		$curr_ship = new SMR_SHIP(SmrSession::$account_id, $game_id);

		// update turns for this game
		$curr_player->update_turns($curr_ship->speed);

		// generate list of game_id that this player is joined
		if ($game_id_list) $game_id_list .= ",";
		$game_id_list .= $game_id;

		$db2 = new SmrMySqlDatabase();
		$db2->query("SELECT * FROM player " .
					"WHERE last_active >= " . (time() - 600) . " AND " .
						  "game_id = $game_id");
		$current_player = $db2->nf();

		// create a container that will hold next url and additional variables.
		$container = array();
		$container["game_id"] = $game_id;
		$container["url"] = "game_play_processing.php";


		print("<tr>");
		print("<td>");
		print_button($container,"Play Game");
		print("</form>");
		print("</td>");
		print("<td>");
		$container_game = array();
		$container_game["url"] = "skeleton.php";
		$container_game["body"] = "game_stats.php";
		$container_game["game_id"] = $game_id;
		print_link($container_game, "$game_name ($game_id)");
		print("</td>");
		print("<td align=\"center\">$curr_player->turns</td>");
		print("<td align=\"center\">$current_player</td>");
		print("<td align=\"center\">$end_date</td>");
		print("</tr>");

	}

	print("</table>");
	print("</p>");

}

// put parenthesis around the list
$game_id_list = "(".$game_id_list.")";

if ($game_id_list == "()")
	$db->query("SELECT DATE_FORMAT(start_date, '%c/%e/%Y') as start_date, game.game_id as game_id, game_name, max_players, game_type, credits_needed, game_speed " .
					"FROM game WHERE end_date >= '" . date("Y-m-d") . "' AND enabled = 'TRUE'");
else
	$db->query("SELECT DATE_FORMAT(start_date, '%c/%e/%Y') as start_date, game.game_id as game_id, game_name, max_players, game_type, credits_needed, game_speed " .
					"FROM game WHERE game_id NOT IN $game_id_list AND " .
									"end_date >= '" . date("Y-m-d") . "' AND enabled = 'TRUE'");

// ***************************************
// ** Join Games
// ***************************************
print("<br><br>");
print("<h4 style=\"color:#80c870;\">Join Game</h4>");

// are there any results?
if ($db->nf() > 0) {

	print("<p>");
	print_table();
	print("<tr>");
	print("<th>&nbsp;</th>");
	print("<th width=\"150\">Game</th>");
	print("<th>Start Date</th>");
	print("<th>Max Players</th>");
	print("<th>Type</th>");
	print("<th>Game Speed</th>");
	print("<th>Credits Needed</th>");
	print("</tr>");

	// iterate over the resultset
	while ($db->next_record()) {

		$game_id = $db->f("game_id");
		$game_name = $db->f("game_name");
		$start_date = $db->f("start_date");
		$max_players = $db->f("max_players");
		$game_type = $db->f("game_type");
		$credits = $db->f("credits_needed");
		$game_speed = $db->f("game_speed");

		// create a container that will hold next url and additional variables.
		$container = array();
		$container["game_id"] = $game_id;
		$container["url"] = "skeleton.php";
		$container["body"] = "game_join.php";

		print("<tr>");

		print("<td>");
		print_form($container);
		print_submit("Join Game");
		print("</form>");
		print("</td>");
		print("<td>$game_name ($game_id)</td>");
		print("<td align=\"center\">$start_date</td>");
		print("<td align=\"center\">$max_players</td>");
		print("<td align=\"center\">$game_type</td>");
		print("<td align=\"center\">$game_speed</td>");
		print("<td align=\"center\">$credits</td>");
		print("</form>");
		print("</tr>");
	}

	print("</table>");
	print("</p>");

} else print("<p>You joined all open games.</p>");

// ***************************************
// ** Previous Games
// ***************************************
print("<br><br>");
print("<h4 style=\"color:#80c870;\">Previous Games</h4>");

$db = new SMR_HISTORY_DB();
$db->query("SELECT DATE_FORMAT(start_date, '%c/%e/%Y') as start_date, " .
		   "DATE_FORMAT(end_date, '%c/%e/%Y') as end_date, game_name, speed, game_id " .
		   "FROM game ORDER BY game_id");
if ($db->nf()) {

	print("<p>");
	print_table();
	print("<tr><th align=center>Game Name</th><th align=center>Start Date</th><th align=center>End Date</th><th align=center>Speed</th><th align=center colspan=3>Options</th></tr>");
	while ($db->next_record()) {

		$id = $db->f("game_id");
		$container = array();
		$container["url"] = "skeleton.php";
		$container["game_id"] = $db->f("game_id");
		$container["game_name"] = $db->f("game_name");
		$container["body"] = "games_previous.php";
		$name = $db->f("game_name");
		print("<tr><td>");
		print_link($container, "$name ($id)");
		print("</td>");
		print("<td align=center>" . $db->f("start_date") . "</td>");
		print("<td align=center>" . $db->f("end_date") . "</td>");
		print("<td align=center>" . $db->f("speed") . "</td>");
		print("<td align=center>");
		$container = array();
		$container["url"] = "skeleton.php";
		$container["body"] = "hall_of_fame_new.php";
		$container["game_id"] = $db->f("game_id");
		print_link($container, "Hall of Fame");
		print("</td>");
		print("<td align=center>");
		$container["body"] = "games_previous_news.php";
		$container["game_id"] = $db->f("game_id");
		$container["game_name"] = $db->f("game_name");
		print_link($container, "Game News");
		print("</td>");
		print("<td align=center>");
		$container["body"] = "games_previous_detail.php";
		$container["game_id"] = $db->f("game_id");
		$container["game_name"] = $db->f("game_name");
		print_link($container, "Game Stats");
		print("</td>");

	}

	print("</table>");
	print("</p>");

}

// restore old database
$db = new SmrMySqlDatabase();


// ***************************************
// ** Donation Link
// ***************************************
print("<br /><br />");
print("<h4 style=\"color:#80c870;\">Donate Money</h4>");

print("<p>");
print_link(create_container("skeleton.php", "donation.php"),
		   "<img src=\"images/donation.jpg\" border=\"0\"></a>");
print("</p>");

// ***************************************
// ** Announcements View
// ***************************************
print("<p>");
$container = array();
$container["url"] = "skeleton.php";
$container["body"] = "announcements.php";
$container["view_all"] = "yes";
print_link($container, "<h4 style=\"color:#80c870;\">View Old Announcements</h4>");
print("</p>");

// ***************************************
// ** Admin Functions
// ***************************************
$db->query("SELECT * FROM account_has_permission NATURAL JOIN permission WHERE account_id = $account->account_id");

if ($db->nf()) {

	print("<br><br>");
	print("<h4 style=\"color:#80c870;\">Admin functions</h4>");
	print("<p>");
	print("<ul>");

	while ($db->next_record()) {

		print("<li>");
		print_link(create_container("skeleton.php", $db->f("link_to")), $db->f("permission_name"));
		print("</li>");

	}
	print("</ul>");
	print("</p>");

}

?>
