<?php

function print_alliance_menue($alliance_id = FALSE, $alliance_leader_id = FALSE) {

	global $player, $var, $alliance, $HIDDEN_PLAYERS;

	$db2 = new SmrMySqlDatabase();
	if($alliance_id) {
		$in_alliance = ($alliance_id == $player->alliance_id);
	}
	else{
		$in_alliance = ($alliance->alliance_id == $player->alliance_id);
	}
	$db2->query("SELECT mb_read, mod_read, planet_land FROM alliance_treaties
					WHERE (alliance_id_1 = $alliance_id OR alliance_id_1 = $player->alliance_id)
					AND (alliance_id_2 = $alliance_id OR alliance_id_2 = $player->alliance_id)
					AND game_id = $player->game_id
					AND (mb_read = 1 OR mod_read = 1 OR planet_land = 1) AND official = 'TRUE'");
	if ($db2->next_record()) {
		if ($db2->f("mb_read")) $mbRead = TRUE;
		else $mbRead = FALSE;
		if ($db2->f("mod_read")) $modRead = TRUE;
		else $modRead = FALSE;
		if ($db2->f("planet_land")) $planetLand = TRUE;
		else $planetLand = FALSE;
	} else {
		$mbRead = FALSE;
		$modRead = FALSE;
		$planetLand = FALSE;
	}
	if ($in_alliance || in_array($player->account_id, $HIDDEN_PLAYERS) || $modRead) {

		$container = create_container("skeleton.php", "alliance_mod.php");
		if (isset($var["alliance_id"]))
			$container["alliance_id"] = $var["alliance_id"];
		else
			$container['alliance_id'] = $alliance_id;
		$menue_items[] = create_link($container, "Message of Day");

	};

	$container = create_container("skeleton.php", "alliance_roster.php");
	if (isset($var["alliance_id"]))
		$container["alliance_id"] = $var["alliance_id"];
	$menue_items[] = create_link($container, "Roster");
	$db2->query("SELECT * FROM player_has_alliance_role WHERE account_id = $player->account_id AND game_id = $player->game_id AND alliance_id=$player->alliance_id");
	if ($db2->next_record()) $role_id = $db2->f("role_id");
	else $role_id = 0;
	$db2->query("SELECT * FROM alliance_has_roles WHERE alliance_id = $player->alliance_id AND game_id = $player->game_id AND role_id = $role_id");
	$db2->next_record();
	$send = $db2->f("send_alliance_msg");
	$container=array();
	$container['url']='skeleton.php';
	$container['alliance_id'] = $alliance_id;
//	if ($player->account_id == $alliance_leader_id) {
		//$container['body']='alliance_treaties.php';
		//$menue_items[] = create_link($container,"Treaties");	
	//}
	if (($send && $in_alliance) || in_array($player->account_id, $HIDDEN_PLAYERS)) {
		$container['body']='alliance_broadcast.php';
		$menue_items[] = create_link($container,"Send Message");
	} if ($in_alliance || in_array($player->account_id, $HIDDEN_PLAYERS)){
		$container['body'] = 'alliance_message.php';
		$menue_items[] = create_link($container, "Message Board");
		$container['body'] = 'alliance_planets.php';
		$menue_items[] = create_link($container, "Planets");
		$container['body'] = 'alliance_forces.php';
		$menue_items[] = create_link($container, "Forces");
		$container['body'] = 'alliance_option.php';
		$menue_items[] = create_link($container, "Options");
	} if (!$in_alliance) {
		if ($mbRead) {
			$container['body'] = 'alliance_message.php';
			$menue_items[] = create_link($container, "Message Board");
		} if ($planetLand) {
			$container['body'] = 'alliance_planets.php';
			$menue_items[] = create_link($container, "Planets");
		}
	}

	$container = array();
	$container["url"] = "skeleton.php";
	$container["body"] = "alliance_list.php";
	$container["order"] = "alliance_name";
	$menue_items[] = create_link($container, "List Alliances");

	print_menue($menue_items);

}

function print_galactic_post_menue() {

	global $session, $player;
	$menue_items[] = create_link(create_container("skeleton.php", "galactic_post_read.php"),
												  "Current Edition");

	//find out the user's privligages
	$db = new SmrMySqlDatabase();
	$db->query("SELECT * FROM galactic_post_writer WHERE game_id = $session->game_id AND account_id = $player->account_id");
	if ($db->next_record()) {

		$position = $db->f("position");
		if ($position == "writer")
			$allowed_write = "yes";
		else
			$allowed_edit = "yes";

	}
	else {

		$allowed_write = "no";
		$allowed_edit = "no";

	}
	if ($allowed_write == "no" && $allowed_edit == "no")
		$menue_items[] = create_link(create_container("skeleton.php", "galactic_post_application.php"), "Apply as writer!");
	if ($allowed_edit == "yes") {

		$menue_items[] = create_link(create_container("skeleton.php", "galactic_post_view_applications.php"), "View the applications");
		$menue_items[] = create_link(create_container("skeleton.php", "galactic_post_view_article.php"), "View the articles");
		$menue_items[] = create_link(create_container("skeleton.php", "galactic_post.php"), "Editor Options");
		$menue_items[] = create_link(create_container("skeleton.php", "galactic_post_make_paper.php"), "Make a paper");

	}
	if ($allowed_edit == "yes" || $allowed_write == "yes")
		$menue_items[] = create_link(create_container("skeleton.php", "galactic_post_write_article.php"), "Write an article");

	print_menue($menue_items);

}

function print_message_menue() {

	global $var;
	global $POLITICALMSG;
	global $player;

	$menue_items[] = create_link(create_container("skeleton.php", "message_view.php"),
												  "View Messages");

	if (isset($var["folder_id"]) && $var["folder_id"] == $POLITICALMSG) {

		$container = array();
		$container["url"] = "skeleton.php";
		$container["body"] = "council_send_message.php";
		$container["race_id"] = $player->race_id;
		$container["folder_id"] = $POLITICALMSG;
		$menue_items[] = create_link($container, "Send Council Message");

	}

	$menue_items[] = create_link(create_container("skeleton.php", "message_send.php"),
												  "Send Global Message");

	print_menue($menue_items);

}

function print_combat_log_menue() {

	global $player,$session;

	$container = array();
	$container["url"] = "skeleton.php";
	$container["body"] = "combat_log_viewer.php";
	$container["action"] = 0;
	$menue_items[] = create_link($container,"Personal");
	$container["action"] = 1;
	$menue_items[] = create_link($container,"Alliance");
	$container["action"] = 2;
	$menue_items[] = create_link($container,"Port");
	$container["action"] = 3;
	$menue_items[] = create_link($container,"Planet");
	$container["action"] = 4;
	$menue_items[] = create_link($container,"Saved");

	print_menue($menue_items);

}

function print_trader_menue() {

	global $player;

	$menue_items[] = create_link(create_container("skeleton.php", "trader_status.php"),
												  "Trader Status");
	$menue_items[] = create_link(create_container("skeleton.php", "trader_planet.php"),
												  "Planet");
	$container = array();
	$container["url"] = "skeleton.php";
	if ($player->alliance_id > 0) {
		$container["body"] = "alliance_roster.php";
	} else {
		$container["body"] = "alliance_list.php";
		$container["order"] = "alliance_name";
	}
	$menue_items[] = create_link($container, "Alliance");

	$menue_items[] = create_link(create_container("skeleton.php", "council_list.php"),
												  "Politics");

	$menue_items[] = create_link(create_container("skeleton.php", "trader_relations.php"),
												  "Relations");
	$menue_items[] = create_link(create_container("skeleton.php", "trader_bounties.php"),
												  "Bounties");
	print_menue($menue_items);

}

function print_planet_menue() {

	$menue_items[] = create_link(create_container("skeleton.php", "planet_main.php"),
												  "Planet Main");

	$menue_items[] = create_link(create_container("skeleton.php", "planet_construction.php"),
												  "Construction");

	$menue_items[] = create_link(create_container("skeleton.php", "planet_defense.php"),
												  "Defenses");

	$menue_items[] = create_link(create_container("skeleton.php", "planet_ownership.php"),
												  "Ownership");

	$menue_items[] = create_link(create_container("skeleton.php", "planet_stockpile.php"),
												  "Stockpile");

	$menue_items[] = create_link(create_container("skeleton.php", "planet_financial.php"),
												  "Financial");

	print_menue($menue_items);

}

/*
 * $active_level1 - the id of the active menu on the first level
 * $active_level1 - the id of the active menu on the second level
 */
function print_ranking_menue($active_level1 = 0, $active_level2 = 0) {

	$menu = array();

	// player rankings
	$menu_item = array();
	$menu_item["entry"] = create_link(create_container("skeleton.php", "rankings_player_experience.php"), "Player Rankings");

	$menu_subitem = array();
	$menu_subitem[] = create_link(create_container("skeleton.php", "rankings_player_experience.php"), "Experience");
	$menu_subitem[] = create_link(create_container("skeleton.php", "rankings_player_kills.php"), "Kills");
	$menu_subitem[] = create_link(create_container("skeleton.php", "rankings_player_death.php"), "Death");

	$menu_item["submenu"] = $menu_subitem;

	$menu[] = $menu_item;


	$menu_item = array();
	$menu_item["entry"] = create_link(create_container("skeleton.php", "rankings_alliance_experience.php"), "Alliance Rankings");

	$menu_subitem = array();
	$menu_subitem[] = create_link(create_container("skeleton.php", "rankings_alliance_experience.php"), "Experience");
	$menu_subitem[] = create_link(create_container("skeleton.php", "rankings_alliance_kills.php"), "Kills");
	$menu_subitem[] = create_link(create_container("skeleton.php", "rankings_alliance_death.php"), "Death");
	$menu_subitem[] = create_link(create_container("skeleton.php", "rankings_alliance_vs_alliance.php"), "Versus");

	$menu_item["submenu"] = $menu_subitem;

	$menu[] = $menu_item;


	$menu_item = array();
	$menu_item["entry"] = create_link(create_container("skeleton.php", "rankings_race.php"), "Racial Standings");
	
	$menu_subitem = array();
	$menu_subitem[] = create_link(create_container("skeleton.php", "rankings_race.php"), "Experience");
	$menu_subitem[] = create_link(create_container("skeleton.php", "rankings_race_kills.php"), "Kills");
	$menu_subitem[] = create_link(create_container("skeleton.php", "rankings_race_death.php"), "Death");

	$menu_item["submenu"] = $menu_subitem;

	$menu[] = $menu_item;
	
	$menu_item = array();
	$menu_item["entry"] = create_link(create_container("skeleton.php", "rankings_sector_kill.php"), "Sector Kills");
	$menu[] = $menu_item;

	print_sub_menu($menu, $active_level1, $active_level2);

}

function print_sub_menu($menu, $active_level1, $active_level2) {

	print("<p>");
	print("<table border=\"0\" cellpadding=\"0\" cellspacing=\"1\" width=\"100%\">");
	print("<tr>");
	print("<td bgcolor=\"#0B8D35\">");
	print("<table border=\"0\" cellpadding=\"3\" cellspacing=\"1\" width=\"100%\">");
	print("<tr bgcolor=\"#0B2121\">");
	print("<td>");
	print("<table border=\"0\" cellpadding=\"3\" cellspacing=\"2\" width=\"100%\">");
	print("<tr bgcolor=\"#0B8D35\">");
	print("<td align=\"center\">");

	print("<table cellpadding=\"0\" cellspacing=\"0\">");
	print("<tr>");
	foreach ($menu as $number => $entry) {

		// insert spacer
		if ($number > 0)
			print("<td>&nbsp;|&nbsp;</td>");

		// if this is the active entry we mark it
		if ($number == $active_level1)
			$active = " style=\"font-weight:bold;\"";
		else
			$active = "";

		// print entry itself
		print("<td align=\"center\"$active> " . $entry["entry"] . "</td>");

	}
	print("</tr>");

	print("<tr>");
	foreach ($menu as $number => $entry) {

		// if this entry has a submenu and is the active one
		if ($entry["submenu"] && $number == $active_level1) {

			print("<td><small>");
			foreach ($entry["submenu"] as $sub_number => $sub_entry) {

				if ($sub_number > 0)
					print(" | ");

				if ($sub_number == $active_level2)
					print("<b>$sub_entry</b>");
				else
					print("$sub_entry");

			}
			print("</small></td>");

		} else {

			// if it's not the first entry we have to put
			// additional empty cell for the spacer
			//if ($number > 0)
				//print("<td>&nbsp;<td>");

			// emppty cell (no submenu)
			print("<td>&nbsp;<td>");

		}


	}
	print("</tr>");

	print("</table>");

	print("</td>");
	print("</tr>");
	print("</table>");
	print("</td>");
	print("</tr>");
	print("</table>");
	print("</td>");
	print("</tr>");
	print("</table>");
	print("</p>");


}

function print_bank_menue() {

	global $player;

	$menue_items[] = create_link(create_container("skeleton.php", "bank_personal.php"),
												  "Personal Account");

	if ($player->alliance_id != 0)
		$menue_items[] = create_link(create_container("skeleton.php", "bank_alliance.php"),
													  "Alliance Account");

	$menue_items[] = create_link(create_container("skeleton.php", "bank_anon.php"),
												  "Anonymous Account");
	print_menue($menue_items);

}

function print_hq_menue() {

	$menue_items[] = create_link(create_container("skeleton.php", "government.php"),
												  "Government");

	$menue_items[] = create_link(create_container("skeleton.php", "military_payment_claim.php"),
												  "Claim Military Payment");

	$menue_items[] = create_link(create_container("skeleton.php", "bounty_claim.php"),
												  "Claim Bounty");

	$menue_items[] = create_link(create_container("skeleton.php", "bounty_place.php"),
												  "Place a Bounty");

	print_menue($menue_items);

}

function print_ug_menue() {

	$menue_items[] = create_link(create_container("skeleton.php", "underground.php"),
												  "Underground");

	$menue_items[] = create_link(create_container("skeleton.php", "bounty_claim.php"),
												  "Claim Bounty");

	$menue_items[] = create_link(create_container("skeleton.php", "bounty_place.php"),
												  "Place a Bounty");

	print_menue($menue_items);
}

function print_council_menue($race_id, $president) {

	global $player;

	$menue_items[] = create_link(create_container("skeleton.php", "council_list.php"),
												  "View Council");

	$container = array();
	$container["url"] = "skeleton.php";
	$container["body"] = "council_politics.php";
	$container["race_id"] = $race_id;
	$menue_items[] = create_link($container, "Political Status");

	$container = array();
	$container["url"] = "skeleton.php";
	$container["body"] = "council_send_message.php";
	$container["race_id"] = $race_id;
	$menue_items[] = create_link($container, "Send Message");

	include(get_file_loc('council_menue.inc'));

	print_menue($menue_items);

}

function print_bar_menue() {

	$container = array();
	$container["url"] = "skeleton.php";
	$container["body"] = "bar_main.php";
	$container["script"] = "bar_opening.php";
	$menue_items[] = create_link($container, "Bar Main");

	$container["script"] = "bar_gambling.php";
	$container["action"] = "lotto";
	$menue_items[] = create_link($container, "Lotto");
	
	$container["script"] = "bar_gambling_bet.php";
	$container["action"] = "blackjack";
	$menue_items[] = create_link($container, "BlackJack");

	print_menue($menue_items);

}

function print_news_menue() {

	$menue_items[] = create_link(create_container("skeleton.php", "news_read_current.php"),
													"Read current news");
	$menue_items[] = create_link(create_container("skeleton.php", "news_read.php"),
													"Read Latest News");

	print_menue($menue_items);

}

function print_hof_menue() {

	$menue_items[] = create_link(create_container("skeleton.php", "hall_of_fame.php"),
													"Hall of Fame Main");

	$container = array();
	$container["url"] = "skeleton.php";
	$container["body"] = "hall_of_fame.php";

	$container["type"] = "Newbie";
	$menue_items[] = create_link($container, "Newbie Stats");
	$container["type"] = "Veteran";
	$menue_items[] = create_link($container, "Veteran Stats");
	$container["type"] = "Semi War";
	$menue_items[] = create_link($container, "Special Game Stats");

	print_menue($menue_items);

}
?>