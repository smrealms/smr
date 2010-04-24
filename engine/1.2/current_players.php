<?php

print_topic("CURRENT PLAYERS");
$db->query("DELETE FROM cpl_tag WHERE expires > 0 AND expires < " . time());
$db->query("SELECT * FROM active_session
			WHERE last_accessed >= " . (time() - 600) . " AND
				  game_id = ".SmrSession::$game_id);
$count_real_last_active = $db->nf();
if (empty($var["sort"])) $sort = "experience DESC, player_name";
else $sort = $var["sort"];
if (empty($var["seq"])) $seq = "DESC";
else $seq = $var["seq"];
$db->query("SELECT * FROM player " .
		   "WHERE last_active >= " . (time() - 600) . " AND " .
				 "game_id = ".SmrSession::$game_id." " .
		   "ORDER BY $sort $seq");
//print("$sort, $seq<br>");
$count_last_active = $db->nf();
$list = "(0";
while ($db->next_record()) $list .= "," . $db->f("account_id");
$list .= ")";
$db->query("SELECT * FROM player " .
		   "WHERE last_active >= " . (time() - 600) . " AND " .
				 "game_id = ".SmrSession::$game_id." " .
		   "ORDER BY $sort $seq");
if ($sort == "experience DESC, player_name" || $sort == "experience")
	$db->query("SELECT * FROM player_cache WHERE game_id = $player->game_id AND account_id IN $list ORDER BY experience $seq");

// fix it if some1 is using the logoff button
if ($count_real_last_active < $count_last_active)
	$count_real_last_active = $count_last_active;
$exp = array();
while ($db->next_record()) {


	$curr_player = new SMR_PLAYER($db->f("account_id"), SmrSession::$game_id);
	if ($curr_player->alliance_id == $player->alliance_id && $player->alliance_id != 0)
		$exp[$db->f("account_id")] = $curr_player->experience;
	else
		$exp[$db->f("account_id")] = $db->f("experience");

}
if ($sort == "experience DESC, player_name" || ($sort == "experience" && $seq == "DESC"))
	arsort($exp, SORT_NUMERIC);
elseif ($sort == "experience" && $seq == "ASC")
	asort($exp);
//foreach ($exp as $acc_id => $val) print("$acc_id, $val<br>");
print("<div align=\"center\">");
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

	print("<p><u>Note:</u> Experience values are updated every 2 minutes.</p>");

if ($count_last_active > 0) {

	print("<table cellspacing=\"0\" cellpadding=\"5\" border=\"0\" class=\"standard\" width=\"95%\">");
	print("<tr>");
	$container = array();
	$container["url"] = "skeleton.php";
	$container["body"] = "current_players.php";
	if ($seq == "DESC")
		$container["seq"] = "ASC";
	else
		$container["seq"] = "DESC";
	$container["sort"] = "player_name";
	print("<th>");
	print_link($container, "<font color=#80c870>Player</font>");
	print("</th>");
	$container["sort"] = "race_id";
	print("<th>");
	print_link($container, "<font color=#80c870>Race</font>");
	print("</th>");
	$container["sort"] = "alliance_id";
	print("<th>");
	print_link($container, "<font color=#80c870>Alliance</font>");
	print("</th>");
	$container["sort"] = "experience";
	print("<th>");
	print_link($container, "<font color=#80c870>Experience</font>");
	print("</th>");
	print("</tr>");

	//while ($db->next_record()) {
	foreach ($exp as $acc_id => $exp) {

		$curr_account = new SMR_ACCOUNT();
		$curr_account->get_by_id($acc_id);
		//reset style
		$style = "";
		$curr_player = new SMR_PLAYER($acc_id, SmrSession::$game_id);
		$curr_player->get_display_xp_lvl();
		if ($curr_account->veteran == "FALSE" && $curr_account->get_rank() < FLEDGLING)
			$style = "font-style:italic;";
		if ($curr_player->account_id == $player->account_id)
			$style .= "font-weight:bold;";
		$fullStyle='';
		if (!empty($style))
			$fullStyle = " style=\"$style\"";

		print("<tr>");
		print("<td valign=\"top\"$fullStyle>");
		$rank = $curr_player->display_level_name;
		//print("$curr_player->display_level_name ");
		$container = array();
		$container["url"]		= "skeleton.php";
		$container["body"]		= "trader_search_result.php";
		$container["player_id"]	= $curr_player->player_id;
		//$name = $curr_player->get_colored_name();
		$name = $rank . " " . $curr_player->get_colored_name();
		$db->query("SELECT * FROM cpl_tag WHERE account_id = $curr_player->account_id ORDER BY custom DESC");
		while ($db->next_record()) {
			if ($db->f("custom")) {
				$name = $db->f("tag") . " " . $curr_player->get_colored_name();
				if ($db->f("custom_rank")) $name .= " (" . $db->f("custom_rank") . ")";
				else $name .= " (" . $rank . ")";
			} else $name .= " " . $db->f("tag");
		}
		//$name .= " $add";
		print_link($container, $name);
		print("</td>");
		$container = array();
		$container["url"] = "skeleton.php";
		$container["body"] = "council_list.php";
		$container["race_id"] = $curr_player->race_id;
		print("<td style=\"text-align:center;$style\">");
		print_link($container, $player->get_colored_race($curr_player->race_id));
		print("</td>");
		print("<td$fullStyle>");
		if ($curr_player->alliance_id > 0) {


			$container = array();
			$container["url"] 			= "skeleton.php";
			$container["body"] 			= "alliance_roster.php";
			$container["alliance_id"]	= $curr_player->alliance_id;
			print_link($container, "$curr_player->alliance_name");
		} else
			print("(none)");
		print("</td><td style=\"text-align:right;$style\">");

		if($curr_player->experience > $curr_player->display_experience) {
			print('<img src="images/cpl_up.gif" style="float:left;height:16px" />');
		}
		else if($curr_player->experience < $curr_player->display_experience) {
			print('<img src="images/cpl_down.gif" style="float:left;height:16px" />');
		}
		else {
			print('<img src="images/cpl_horizontal.gif" style="float:left;height:16px" />');
		}

		if ($curr_player->alliance_id == $player->alliance_id && $player->alliance_id != 0)
		{
			if ($curr_player->account_id == 2) print("A lot");
			else print(number_format($curr_player->experience) . "</td>");
		}
		else {
			if ($curr_player->account_id == 2) print("A lot");
			else print(number_format($curr_player->display_experience) . "</td>");
		}

		
		print("</tr>");

	}

	print("	</table>");

}

print("</div>");

?>
