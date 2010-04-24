<?php

$db2 = new SmrMySqlDatabase();
$player_id = $_REQUEST['player_id'];
$player_name = $_REQUEST['player_name'];
if (!is_numeric($player_id) && !empty($player_id)) {

	print_error("Please enter only numbers!");
	return;

}
$count = 0;
print_topic("SEARCH TRADER RESULTS");

if (isset($var["player_id"]))
	$player_id = $var["player_id"];

if (!empty($player_id))
	$db->query("SELECT * FROM player " .
			   "WHERE game_id = $player->game_id AND " .
			   "player_id = $player_id LIMIT 5");

else {

	if (empty($player_name))
		$player_name = "%";

	$db->query("SELECT * FROM player " .
			   "WHERE game_id = $player->game_id AND " .
					 "player_name = " . format_string($player_name, true) . " " .
			   "ORDER BY player_name LIMIT 5");

}

if ($db->nf() > 0) {

	print("<table border=\"0\" class=\"standard\" cellspacing=\"0\" cellpadding=\"3\" width=\"75%\">");
	print("<tr>");
	print("<th>Name</th>");
	print("<th>Alliance</th>");
	print("<th>Race</th>");
	print("<th>Experience</th>");
	print("<th>Online</th>");
	if (in_array($player->account_id, $HIDDEN_PLAYERS)) print("<th>Sector</th>");
	print("<th>Option</th>");
	print("</tr>");

	while ($db->next_record()) {

		$curr_player = new SMR_PLAYER($db->f("account_id"), $player->game_id);
		$curr_player->get_display_xp_lvl();
		print("<tr>");

		$container = array();
		$container["url"]		= "skeleton.php";
		$container["body"]		= "trader_search_result.php";
		$container["player_id"]	= $curr_player->player_id;

		print("<td>");
		print_link($container, $curr_player->get_colored_name());
		print("<br>");
		$db2->query("SELECT * FROM ship_has_name WHERE game_id = $player->game_id AND " .
				"account_id = $curr_player->account_id");
		if ($db2->next_record()) {
			
			//they have a name so we print it
			$named_ship = stripslashes($db2->f("ship_name"));
			print("$named_ship");
			
		}
		print("</td>");

		print("<td>");
		if ($curr_player->alliance_id > 0) {

			$container = array();
			$container["url"]			= "skeleton.php";
			$container["body"]			= "alliance_roster.php";
			$container["alliance_id"]	= $curr_player->alliance_id;
			print_link($container, "$curr_player->alliance_name");
		} else
			print("(none)");
		print("</td>");
		$container = array();
		$container["url"] = "skeleton.php";
		$container["body"] = "council_list.php";
		$container["race_id"] = $curr_player->race_id;
		$container["race_name"] = $curr_player->race_name;
		print("<td align=\"center\" valign=\"middle\">");
		print_link($container, $player->get_colored_race($curr_player->race_id));
		print("</td>");
		print("<td align=\"center\" valign=\"middle\">$curr_player->display_experience</td>");
		if ($curr_player->last_active > time() - 600)
			print("<td width=\"10%\" align=\"center\" valign=\"middle\" style=\"color:green;\">YES</td>");
		else
			print("<td width=\"10%\" align=\"center\" valign=\"middle\" style=\"color:red;\">NO</td>");
		if (in_array($player->account_id, $HIDDEN_PLAYERS)) print("<td align=\"center\" valign=\"middle\">$curr_player->sector_id</td>");
		print("<td style=\"font-size:75%;\" width=\"10%\" align=\"center\">");
		$container = array();
		$container["url"]		= "skeleton.php";
		$container["body"]		= "message_send.php";
		$container["receiver"]	= $curr_player->account_id;
		print_link($container, "<span style=\"color:yellow;\">Send Message</span>");
		$container = array();
		$container["url"] = "skeleton.php";
		$container["body"] = "bounty_view.php";
		$container["id"] = $curr_player->account_id;
		print_link($container, "<br><font color=yellow>View Bounty</font><br>");
		$container = array();
		$container["url"] = "skeleton.php";
		$container["body"] = "hall_of_fame_player_detail.php";
		$container["acc_id"] = $curr_player->account_id;
		$container["game_id"] = $player->game_id;
		$container["sending_page"] = 'search';
		print_link($container, "<font color=yellow>View Stats</font><br>");
		if (in_array($player->account_id, $HIDDEN_PLAYERS)) {
			$container=array();
			$container['url'] = 'sector_jump_processing.php';
			$container['to'] = $curr_player->sector_id;
			print_link($container, "<span class=\"yellow\">Jump to Sector</span>");
		}
		print("</td></tr>");

	}

	print("</table>");
	$count++;

} 
if (empty($player_id)) {
	$real = $player_name;
	if (!empty($player_name))
		$player_name = "%" . $player_name . "%";
	else
		$player_name = "%";
	
	$db->query("SELECT * FROM player " .
			   "WHERE game_id = $player->game_id AND " .
					 "player_name LIKE " . format_string($player_name, true) . " AND player_name != " . format_string($real, true) . " " .
			   "ORDER BY player_name LIMIT 5");
			   
	if ($db->nf() > 0) {
	
		print("<table border=\"0\" class=\"standard\" cellspacing=\"0\" cellpadding=\"3\" width=\"75%\">");
		print("<tr>");
		print("<th>Name</th>");
		print("<th>Alliance</th>");
		print("<th>Race</th>");
		print("<th>Experience</th>");
		print("<th>Online</th>");
		if (in_array($player->account_id, $HIDDEN_PLAYERS)) print("<th>Sector</th>");
		print("<th>Option</th>");
		print("</tr>");
	
		while ($db->next_record()) {
	
			$curr_player = new SMR_PLAYER($db->f("account_id"), $player->game_id);
			$curr_player->get_display_xp_lvl();

			print("<tr>");
	
			$container = array();
			$container["url"]		= "skeleton.php";
			$container["body"]		= "trader_search_result.php";
			$container["player_id"]	= $curr_player->player_id;
	
			print("<td>");
			print_link($container, $curr_player->get_colored_name());
			print("<br>");
			$db2->query("SELECT * FROM ship_has_name WHERE game_id = $player->game_id AND " .
					"account_id = $curr_player->account_id");
			if ($db2->next_record()) {
				
				//they have a name so we print it
				$named_ship = stripslashes($db2->f("ship_name"));
				print("$named_ship");
				
			}
			print("</td>");
	
			print("<td>");
			if ($curr_player->alliance_id > 0) {
	
				$container = array();
				$container["url"]			= "skeleton.php";
				$container["body"]			= "alliance_roster.php";
				$container["alliance_id"]	= $curr_player->alliance_id;
				print_link($container, "$curr_player->alliance_name");
			} else
				print("(none)");
			print("</td>");
			$container = array();
			$container["url"] = "skeleton.php";
			$container["body"] = "council_send_message.php";
			$container["race_id"] = $curr_player->race_id;
			$container["race_name"] = $curr_player->race_name;
			print("<td align=\"center\" valign=\"middle\">");
			print_link($container, $player->get_colored_race($curr_player->race_id));
			print("</td>");
			print("<td align=\"center\" valign=\"middle\">$curr_player->display_experience</td>");
			if ($curr_player->last_active > time() - 600)
				print("<td width=\"10%\" align=\"center\" valign=\"middle\" style=\"color:green;\">YES</td>");
			else
				print("<td width=\"10%\" align=\"center\" valign=\"middle\" style=\"color:red;\">NO</td>");
			if (in_array($player->account_id, $HIDDEN_PLAYERS)) print("<td align=\"center\" valign=\"middle\">$curr_player->sector_id</td>");
			print("<td style=\"font-size:75%;\" width=\"10%\" align=\"center\">");
			$container = array();
			$container["url"]		= "skeleton.php";
			$container["body"]		= "message_send.php";
			$container["receiver"]	= $curr_player->account_id;
			print_link($container, "<span style=\"color:yellow;\">Send Message</span>");
			$container = array();
			$container["url"] = "skeleton.php";
			$container["body"] = "bounty_view.php";
			$container["id"] = $curr_player->account_id;
			print_link($container, "<br><font color=yellow>View Bounty</font><br>");
			$container = array();
			$container["url"] = "skeleton.php";
			$container["body"] = "hall_of_fame_player_detail.php";
			$container["acc_id"] = $curr_player->account_id;
			$container["game_id"] = $player->game_id;
			$container["sending_page"] = 'search';
			print_link($container, "<font color=yellow>View Stats</font><br>");
			if (in_array($player->account_id, $HIDDEN_PLAYERS)) {
				$container=array();
				$container['url'] = 'sector_jump_processing.php';
				$container['to'] = $curr_player->sector_id;
				print_link($container, "<span class=\"yellow\">Jump to Sector</span>");
			}
			print("</td></tr>");
	
		}
	
		print("</table>");
		$count++;
	
	}
}
if ($count == 0)
	print("No Trader found!");

?>
