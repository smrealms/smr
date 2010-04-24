<?php
require_once(get_file_loc("smr_history_db.inc"));
//get vars
$action = $_REQUEST["action"];
$row = $var["row"];
$rank = 1;
$cat = $var["category"];
$mod = $_REQUEST["mod"];
if (isset($var["game_id"])) $game_id = $var["game_id"];
//do we need to mod stat?
if (is_array($mod))
	foreach($mod as $mod1) {

		if (!stristr($mod1,$action)) continue;
		list($one, $two) = explode(",", $mod1);
		$row .= $two;
		break;
	}

//for future when we have curr game stats
if (isset($game_id)) {

	$table = "player_has_stats_cache WHERE game_id = $game_id AND";

	$db2 = new SMR_HISTORY_DB();
	$db2->query("SELECT * FROM game WHERE game_id = $game_id");
	//if next record we have an old game so we query the hist db
	if ($db2->next_record()) {

		$db = new SMR_HISTORY_DB();
		$past = "Yes";
		$table = "player_has_stats WHERE game_id = $game_id AND";

	} else $db = new SmrMySqlDatabase();

}
else $table = "account_has_stats_cache WHERE";
print("<div align=center>");
print_topic("Hall of Fame - $cat $action");
$container = array();
$container["url"] = "skeleton.php";
$container["body"] = "hall_of_fame_new.php";
if (isset($game_id))
	$container["game_id"] = $game_id;
print_link($container, "<b>&lt;&lt;Back</b>");
print("<br>");
print("Here are the ranks of players by $cat $action<br><br>");
print_table();
print("<tr><th align=center>Rank</th><th align=center>Player</th><th align=center>$cat $action</th></tr>");
if ($cat == "<b>Money Donated to SMR</b>")
	$db->query("SELECT account_id, sum(amount) as amount FROM account_donated " .
			"GROUP BY account_id ORDER BY amount DESC LIMIT 25");
else
	$db->query("SELECT account_id, $row as amount FROM $table $row > 0 ORDER BY amount DESC LIMIT 25");

while ($db->next_record()) {

	$this_acc = new SMR_ACCOUNT();
	$this_acc->get_by_id($db->f("account_id"));
	if ($db->f("account_id") == ".SmrSession::$old_account_id.") $bold = " style=\"font-weight:bold;\"";
	else $bold = "";
	print("<tr>");
	print("<td align=center$bold>" . $rank++ . "</td>");
	//link to stat page
	$container = array();
	$container["url"] = "skeleton.php";
	$container["body"] = "hall_of_fame_player_detail.php";
	$container["acc_id"] = $db->f("account_id");
	
	if (isset($game_id)) {
		$container["game_id"] = $game_id;
		$container["sending_page"] = 'current_hof';
	} else {
		$container["game_id"] = $player->game_id;
		$container["sending_page"] = 'hof';
	}
	print("<td align=center$bold>");
	$hof_name = stripslashes($this_acc->HoF_name);
	print_link($container, "$hof_name");
	print("</td>");
	if($cat == "Turns Since Last Death") print("<td align=center$bold>" . $db->f("amount") . "</td>");
	else print("<td align=center$bold>" . $db->f("amount") . "</td>");
	print("</tr>");

}

//our rank goes here if we aren't shown...first get our value
if (isset($past)) $db = new SMR_HISTORY_DB();
if ($cat == "<b>Money Donated to SMR</b>")
	$db->query("SELECT account_id, sum(amount) as amount FROM account_donated " .
			"WHERE account_id = ".SmrSession::$old_account_id." GROUP BY account_id");
else
	$db->query("SELECT account_id, $row as amount FROM $table " .
			"$row > 0 AND account_id = ".SmrSession::$old_account_id." ORDER BY amount DESC");
if ($db->next_record()) {

	$my_stat = $db->f("amount");
	if ($cat == "<b>Money Donated to SMR</b>")
		$db->query("SELECT account_id, sum(amount) as amount FROM account_donated " .
				"WHERE amount > $my_stat GROUP BY account_id ORDER BY amount DESC");
	else
		$db->query("SELECT account_id, $row as amount FROM $table " .
				"$row > $my_stat ORDER BY amount DESC");

	$better = $db->nf();

} else {

	$my_stat = 0;
	if ($cat == "<b>Money Donated to SMR</b>")
		$db->query("SELECT account_id, sum(amount) as amount FROM account_donated " .
				"GROUP BY account_id ORDER BY amount DESC");
	else
		$db->query("SELECT account_id, $row as amount FROM $table $row > 0 ORDER BY amount DESC");

	$better = $db->nf();

}
if ($better >= 25) {

	if (isset($past)) $sql = "game_id = $game_id ";
	else $sql = "";
	if(isset($past)) {
		$db->query("SELECT * FROM player_has_stats WHERE $sqlAND account_id = $account->account_id");
	}
	else {
		$db->query("SELECT * FROM player_has_stats_cache WHERE $sqlAND account_id = $account->account_id");
	}
	if ($db->next_record()) {

		print("<tr>");
		print("<td align=center style=\"font-weight:bold;\">" . ++$better . "</td>");
		print("<td align=center style=\"font-weight:bold;\">" . stripslashes($account->HoF_name) . "</td>");
		print("<td align=center style=\"font-weight:bold;\">$my_stat</td>");
		print("</tr>");

	}

}
print("</table>");

print("</div>");
$db = new SmrMySqlDatabase();
?>
