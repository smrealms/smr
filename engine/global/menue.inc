<?

function echo_alliance_menue() {
	echo create_alliance_menue();
}
function create_alliance_menue($alliance_id = FALSE, $alliance_leader_id = FALSE) {

	global $player, $var, $alliance, $HIDDEN_PLAYERS;

	$db2 = new SmrMySqlDatabase();
	if($alliance_id)
	{
		$in_alliance = ($alliance_id == $player->getAllianceID());
	}
	else
	{
		$in_alliance = $player->hasAlliance();
	}
	$db2->query('SELECT mb_read, mod_read, planet_land FROM alliance_treaties
					WHERE (alliance_id_1 = '.$alliance_id.' OR alliance_id_1 = '.$player->getAllianceID().')
					AND (alliance_id_2 = '.$alliance_id.' OR alliance_id_2 = '.$player->getAllianceID().')
					AND game_id = '.$player->getGameID().'
					AND (mb_read = 1 OR mod_read = 1 OR planet_land = 1) AND official = \'TRUE\'');
	if ($db2->nextRecord()) {
		if ($db2->getField('mb_read')) $mbRead = TRUE;
		else $mbRead = FALSE;
		if ($db2->getField('mod_read')) $modRead = TRUE;
		else $modRead = FALSE;
		if ($db2->getField('planet_land')) $planetLand = TRUE;
		else $planetLand = FALSE;
	} else {
		$mbRead = FALSE;
		$modRead = FALSE;
		$planetLand = FALSE;
	}
	if ($in_alliance || in_array($player->getAccountID(), $HIDDEN_PLAYERS) || $modRead) {

		$container = create_container('skeleton.php', 'alliance_mod.php');
		if (isset($var['alliance_id']))
			$container['alliance_id'] = $var['alliance_id'];
		else
			$container['alliance_id'] = $alliance_id;
		$menue_items[] = create_link($container, 'Message of Day');

	};

	$container = create_container('skeleton.php', 'alliance_roster.php');
	if (isset($var['alliance_id']))
		$container['alliance_id'] = $var['alliance_id'];
	$menue_items[] = create_link($container, 'Roster');
	$db2->query('SELECT * FROM player_has_alliance_role WHERE account_id = '.$player->getAccountID().' AND game_id = '.$player->getGameID());
	if ($db2->nextRecord()) $role_id = $db2->getField('role_id');
	else $role_id = 0;
	$db2->query('SELECT * FROM alliance_has_roles WHERE alliance_id = '.$player->getAllianceID().' AND game_id = '.$player->getGameID().' AND role_id = '.$role_id);
	$db2->nextRecord();
	$send = $db2->getField('send_alliance_msg') == 'TRUE';
	$container=array();
	$container['url']='skeleton.php';
	$container['alliance_id'] = $alliance_id;
//	if ($player->getAccountID() == $alliance_leader_id) {
		//$container['body']='alliance_treaties.php';
		//$menue_items[] = create_link($container,'Treaties');	
	//}
	if (($send && $in_alliance) || in_array($player->getAccountID(), $HIDDEN_PLAYERS)) {
		$container['body']='alliance_broadcast.php';
		$menue_items[] = create_link($container,'Send Message');
	} if ($in_alliance || in_array($player->getAccountID(), $HIDDEN_PLAYERS)){
		$container['body'] = 'alliance_message.php';
		$menue_items[] = create_link($container, 'Message Board');
		$container['body'] = 'alliance_planets.php';
		$menue_items[] = create_link($container, 'Planets');
		$container['body'] = 'alliance_forces.php';
		$menue_items[] = create_link($container, 'Forces');
		$container['body'] = 'alliance_option.php';
		$menue_items[] = create_link($container, 'Options');
	} if (!$in_alliance) {
		if ($mbRead) {
			$container['body'] = 'alliance_message.php';
			$menue_items[] = create_link($container, 'Message Board');
		} if ($planetLand) {
			$container['body'] = 'alliance_planets.php';
			$menue_items[] = create_link($container, 'Planets');
		}
	}

	$container = array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'alliance_list.php';
	$container['order'] = 'alliance_name';
	$menue_items[] = create_link($container, 'List Alliances');

	return create_menue($menue_items);

}

function echo_galactic_post_menue() {
	echo create_galactic_post_menue();
}
function create_galactic_post_menue() {

	global $player;
	$menue_items[] = create_link(create_container('skeleton.php', 'galactic_post_read.php'),
												  'Current Edition');

	//find out the user's privligages
	$db = new SmrMySqlDatabase();
	$db->query('SELECT * FROM galactic_post_writer WHERE game_id = '.SmrSession::$game_id.' AND account_id = '.$player->getAccountID());
	if ($db->nextRecord()) {

		$position = $db->getField('position');
		if ($position == 'writer')
			$allowed_write = 'yes';
		else
			$allowed_edit = 'yes';

	}
	else {

		$allowed_write = 'no';
		$allowed_edit = 'no';

	}
	if ($allowed_write == 'no' && $allowed_edit == 'no')
		$menue_items[] = create_link(create_container('skeleton.php', 'galactic_post_application.php'), 'Apply as writer!');
	if ($allowed_edit == 'yes') {

		$menue_items[] = create_link(create_container('skeleton.php', 'galactic_post_view_applications.php'), 'View the applications');
		$menue_items[] = create_link(create_container('skeleton.php', 'galactic_post_view_article.php'), 'View the articles');
		$menue_items[] = create_link(create_container('skeleton.php', 'galactic_post.php'), 'Editor Options');
		$menue_items[] = create_link(create_container('skeleton.php', 'galactic_post_make_paper.php'), 'Make a paper');

	}
	if ($allowed_edit == 'yes' || $allowed_write == 'yes')
		$menue_items[] = create_link(create_container('skeleton.php', 'galactic_post_write_article.php'), 'Write an article');

	return create_menue($menue_items);

}

function echo_message_menue() {
	echo create_message_menue();
}
function create_message_menue() {

	global $var;
	global $player;

	$menue_items[] = create_link(create_container('skeleton.php', 'message_view.php'),
												  'View Messages');

	if (isset($var['folder_id']) && $var['folder_id'] == MSG_POLITICAL) {

		$container = array();
		$container['url'] = 'skeleton.php';
		$container['body'] = 'council_send_message.php';
		$container['race_id'] = $player->getRaceID();
		$container['folder_id'] = MSG_POLITICAL;
		$menue_items[] = create_link($container, 'Send Council Message');

	}

	$menue_items[] = create_link(create_container('skeleton.php', 'message_send.php'),
												  'Send Global Message');

	return create_menue($menue_items);

}

function echo_combat_log_menue() {
	echo create_combat_log_menue();
}
function create_combat_log_menue() {

	global $player;

	$container = array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'combat_log_viewer.php';
	$container['action'] = 0;
	$menue_items[] = create_link($container,'Personal');
	$container['action'] = 1;
	$menue_items[] = create_link($container,'Alliance');
	$container['action'] = 6;
	$menue_items[] = create_link($container,'Force');
	$container['action'] = 2;
	$menue_items[] = create_link($container,'Port');
	$container['action'] = 3;
	$menue_items[] = create_link($container,'Planet');
	$container['action'] = 4;
	$menue_items[] = create_link($container,'Saved');

	return create_menue($menue_items);

}

function echo_trader_menue() {
	echo create_trader_menue();
}
function create_trader_menue() {

	global $player;

	$menue_items[] = create_link(create_container('skeleton.php', 'trader_status.php'),
												  'Trader Status');
	$menue_items[] = create_link(create_container('skeleton.php', 'trader_planet.php'),
												  'Planet');
	$container = array();
	$container['url'] = 'skeleton.php';
	if ($player->getAllianceID() > 0) {
		$container['body'] = 'alliance_roster.php';
	} else {
		$container['body'] = 'alliance_list.php';
		$container['order'] = 'alliance_name';
	}
	$menue_items[] = create_link($container, 'Alliance');

	$menue_items[] = create_link(create_container('skeleton.php', 'council_list.php'),
												  'Politics');

	$menue_items[] = create_link(create_container('skeleton.php', 'trader_relations.php'),
												  'Relations');
	$menue_items[] = create_link(create_container('skeleton.php', 'trader_bounties.php'),
												  'Bounties');
	return create_menue($menue_items);

}

function echo_planet_menue() {
	echo create_planet_menue();
}
function create_planet_menue() {

	$menue_items[] = create_link(create_container('skeleton.php', 'planet_main.php'),
												  'Planet Main');

	$menue_items[] = create_link(create_container('skeleton.php', 'planet_construction.php'),
												  'Construction');

	$menue_items[] = create_link(create_container('skeleton.php', 'planet_defense.php'),
												  'Defenses');

	$menue_items[] = create_link(create_container('skeleton.php', 'planet_ownership.php'),
												  'Ownership');

	$menue_items[] = create_link(create_container('skeleton.php', 'planet_stockpile.php'),
												  'Stockpile');

	$menue_items[] = create_link(create_container('skeleton.php', 'planet_financial.php'),
												  'Financial');

	return create_menue($menue_items);

}

/*
 * $active_level1 - the id of the active menu on the first level
 * $active_level1 - the id of the active menu on the second level
 */
function echo_ranking_menue() {
	echo create_ranking_menue();
}
function create_ranking_menue($active_level1 = 0, $active_level2 = 0) {

	$menu = array();

	// player rankings
	$menu_item = array();
	$menu_item['entry'] = create_link(create_container('skeleton.php', 'rankings_player_experience.php'), 'Player Rankings');

	$menu_subitem = array();
	$menu_subitem[] = create_link(create_container('skeleton.php', 'rankings_player_experience.php'), 'Experience');
	$menu_subitem[] = create_link(create_container('skeleton.php', 'rankings_player_kills.php'), 'Kills');
	$menu_subitem[] = create_link(create_container('skeleton.php', 'rankings_player_death.php'), 'Death');

	$menu_item['submenu'] = $menu_subitem;

	$menu[] = $menu_item;


	$menu_item = array();
	$menu_item['entry'] = create_link(create_container('skeleton.php', 'rankings_alliance_experience.php'), 'Alliance Rankings');

	$menu_subitem = array();
	$menu_subitem[] = create_link(create_container('skeleton.php', 'rankings_alliance_experience.php'), 'Experience');
	$menu_subitem[] = create_link(create_container('skeleton.php', 'rankings_alliance_kills.php'), 'Kills');
	$menu_subitem[] = create_link(create_container('skeleton.php', 'rankings_alliance_death.php'), 'Death');
	$menu_subitem[] = create_link(create_container('skeleton.php', 'rankings_alliance_vs_alliance.php'), 'Versus');

	$menu_item['submenu'] = $menu_subitem;

	$menu[] = $menu_item;


	$menu_item = array();
	$menu_item['entry'] = create_link(create_container('skeleton.php', 'rankings_race.php'), 'Racial Standings');
	
	$menu_subitem = array();
	$menu_subitem[] = create_link(create_container('skeleton.php', 'rankings_race.php'), 'Experience');
	$menu_subitem[] = create_link(create_container('skeleton.php', 'rankings_race_kills.php'), 'Kills');
	$menu_subitem[] = create_link(create_container('skeleton.php', 'rankings_race_death.php'), 'Death');

	$menu_item['submenu'] = $menu_subitem;

	$menu[] = $menu_item;
	
	$menu_item = array();
	$menu_item['entry'] = create_link(create_container('skeleton.php', 'rankings_sector_kill.php'), 'Sector Kills');
	$menu[] = $menu_item;

	return create_sub_menu($menu, $active_level1, $active_level2);

}

function echo_sub_menu() {
	echo create_sub_menu();
}
function create_sub_menu($menu, $active_level1, $active_level2) {
	$return = ('<p>');
	$return .= ('<table border="0" cellpadding="0" cellspacing="1" width="100%">');
	$return .= ('<tr>');
	$return .= ('<td bgcolor="#0B8D35">');
	$return .= ('<table border="0" cellpadding="3" cellspacing="1" width="100%">');
	$return .= ('<tr bgcolor="#0B2121">');
	$return .= ('<td>');
	$return .= ('<table border="0" cellpadding="3" cellspacing="2" width="100%">');
	$return .= ('<tr bgcolor="#0B8D35">');
	$return .= ('<td align="center">');

	$return .= ('<table cellpadding="0" cellspacing="0">');
	$return .= ('<tr>');
	foreach ($menu as $number => $entry) {

		// insert spacer
		if ($number > 0)
			$return .= ('<td>&nbsp;|&nbsp;</td>');

		// if this is the active entry we mark it
		if ($number == $active_level1)
			$active = ' style="font-weight:bold;"';
		else
			$active = '';

		// echo entry itself
		$return .= ('<td align="center"'.$active.'> ' . $entry['entry'] . '</td>');

	}
	$return .= ('</tr>');

	$return .= ('<tr>');
	foreach ($menu as $number => $entry) {

		// if this entry has a submenu and is the active one
		if ($entry['submenu'] && $number == $active_level1) {

			$return .= ('<td><small>');
			foreach ($entry['submenu'] as $sub_number => $sub_entry) {

				if ($sub_number > 0)
					$return .= (' | ');

				if ($sub_number == $active_level2)
					$return .= ('<b>'.$sub_entry.'</b>');
				else
					$return .= ($sub_entry);

			}
			$return .= ('</small></td>');

		} else {

			// if it's not the first entry we have to put
			// additional empty cell for the spacer
			//if ($number > 0)
				//echo ('<td>&nbsp;<td>');

			// emppty cell (no submenu)
			$return .= ('<td>&nbsp;<td>');

		}


	}
	$return .= ('</tr>');

	$return .= ('</table>');

	$return .= ('</td>');
	$return .= ('</tr>');
	$return .= ('</table>');
	$return .= ('</td>');
	$return .= ('</tr>');
	$return .= ('</table>');
	$return .= ('</td>');
	$return .= ('</tr>');
	$return .= ('</table>');
	$return .= ('</p>');
	return $return;

}

function echo_bank_menue()
{
	echo create_bank_menue();
}

function create_bank_menue() {

	global $player;

	$menue_items[] = create_link(create_container('skeleton.php', 'bank_personal.php'),
												  'Personal Account');

	if ($player->getAllianceID() != 0)
		$menue_items[] = create_link(create_container('skeleton.php', 'bank_alliance.php'),
													  'Alliance Account');

	$menue_items[] = create_link(create_container('skeleton.php', 'bank_anon.php'),
												  'Anonymous Account');
	return create_menue($menue_items);

}

function echo_hq_menue() {
	echo create_hq_menue();
}
function create_hq_menue() {

	$menue_items[] = create_link(create_container('skeleton.php', 'government.php'),
												  'Government');

	$menue_items[] = create_link(create_container('skeleton.php', 'military_payment_claim.php'),
												  'Claim Military Payment');

	$menue_items[] = create_link(create_container('skeleton.php', 'bounty_claim.php'),
												  'Claim Bounty');

	$menue_items[] = create_link(create_container('skeleton.php', 'bounty_place.php'),
												  'Place a Bounty');

	return create_menue($menue_items);

}

function echo_ug_menue() {
	echo create_ug_menue();
}
function create_ug_menue() {

	$menue_items[] = create_link(create_container('skeleton.php', 'underground.php'),
												  'Underground');

	$menue_items[] = create_link(create_container('skeleton.php', 'bounty_claim.php'),
												  'Claim Bounty');

	$menue_items[] = create_link(create_container('skeleton.php', 'bounty_place.php'),
												  'Place a Bounty');

	return create_menue($menue_items);
}

function echo_council_menue() {
	echo create_council_menue();
}
function create_council_menue($race_id, $president) {

	global $player;

	$menue_items[] = create_link(create_container('skeleton.php', 'council_list.php'),
												  'View Council');

	$container = array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'council_politics.php';
	$container['race_id'] = $race_id;
	$menue_items[] = create_link($container, 'Political Status');

	$container = array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'council_send_message.php';
	$container['race_id'] = $race_id;
	$menue_items[] = create_link($container, 'Send Message');

	include(get_file_loc('council_menue.inc'));

	return create_menue($menue_items);

}

function echo_bar_menue() {
	echo create_bar_menue();
}
function create_bar_menue() {

	$container = array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'bar_main.php';
	$container['script'] = 'bar_opening.php';
	$menue_items[] = create_link($container, 'Bar Main');

	$container['script'] = 'bar_gambling.php';
	$container['action'] = 'lotto';
	$menue_items[] = create_link($container, 'Lotto');
	
	$container['script'] = 'bar_gambling_bet.php';
	$container['action'] = 'blackjack';
	$menue_items[] = create_link($container, 'BlackJack');

	return create_menue($menue_items);

}

function echo_news_menue() {
	echo create_news_menue();
}
function create_news_menue() {

	$menue_items[] = create_link(create_container('skeleton.php', 'news_read_current.php'),
													'Read current news');
	$menue_items[] = create_link(create_container('skeleton.php', 'news_read.php'),
													'Read Latest News');

	return create_menue($menue_items);

}

function echo_hof_menue() {
	echo create_hof_menue();
}
function create_hof_menue() {

	$menue_items[] = create_link(create_container('skeleton.php', 'hall_of_fame.php'),
													'Hall of Fame Main');

	$container = array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'hall_of_fame.php';

	$container['type'] = 'Newbie';
	$menue_items[] = create_link($container, 'Newbie Stats');
	$container['type'] = 'Veteran';
	$menue_items[] = create_link($container, 'Veteran Stats');
	$container['type'] = 'Semi War';
	$menue_items[] = create_link($container, 'Special Game Stats');

	return create_menue($menue_items);

}
?>