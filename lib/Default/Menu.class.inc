<?php

/**
 * Creates menu navigation bars.
 */
class Menu {

	static function planet_list($alliance_id, $selected_index) {
		global $template;

		$menuItems = array();
		$menuItems[] = array('Link'=>Globals::getPlanetListHREF($alliance_id), 'Text'=>'Defense');
		$menuItems[] = array('Link'=>Globals::getPlanetListFinancialHREF($alliance_id), 'Text'=>'Financial');
		// make the selected index bold
		$boldItem =& $menuItems[$selected_index]['Text'];
		$boldItem = '<span class="bold">' . $boldItem . '</span>';
		$template->assign('MenuItems', $menuItems);
	}

	static function alliance($alliance_id = null, $alliance_leader_id = FALSE) {
		global $player, $template, $db;

		if ($alliance_id) {
			$in_alliance = ($alliance_id == $player->getAllianceID());
		}
		else {
			$in_alliance = $player->hasAlliance();
		}
		if (!$in_alliance) {
			$db->query('SELECT mb_read, mod_read, planet_land FROM alliance_treaties
							WHERE (alliance_id_1 = ' . $db->escapeNumber($alliance_id) . ' OR alliance_id_1 = ' . $db->escapeNumber($player->getAllianceID()) . ')
							AND (alliance_id_2 = ' . $db->escapeNumber($alliance_id) . ' OR alliance_id_2 = ' . $db->escapeNumber($player->getAllianceID()) . ')
							AND game_id = ' . $db->escapeNumber($player->getGameID()) . '
							AND (mb_read = 1 OR mod_read = 1 OR planet_land = 1) AND official = \'TRUE\'');
			if ($db->nextRecord()) {
				$mbRead = $db->getBoolean('mb_read');
				$modRead = $db->getBoolean('mod_read');
				$planetLand = $db->getBoolean('planet_land');
			}
			else {
				$mbRead = FALSE;
				$modRead = FALSE;
				$planetLand = FALSE;
			}
		}

		$role_id = $player->getAllianceRole();
		$db->query('SELECT send_alliance_msg FROM alliance_has_roles WHERE alliance_id = ' . $db->escapeNumber($player->getAllianceID()) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND role_id = ' . $db->escapeNumber($role_id));
		$db->nextRecord();
		$send = $db->getBoolean('send_alliance_msg');
		//if ($player->getAccountID() == $alliance_leader_id) {
		//	$container['body']='alliance_treaties.php';
		//	$menu_items[] = create_link($container,'Treaties','nav');
		//}
		$menuItems = array();
		if ($in_alliance || in_array($player->getAccountID(), Globals::getHiddenPlayers()) || $modRead) {
			$menuItems[] = array('Link'=>Globals::getAllianceMotdHREF($alliance_id), 'Text'=>'Message of the Day');
		}
		$menuItems[] = array('Link'=>Globals::getAllianceRosterHREF($alliance_id), 'Text'=>'Roster');
		if (($send && $in_alliance) || in_array($player->getAccountID(), Globals::getHiddenPlayers())) {
			$menuItems[] = array('Link'=>Globals::getAllianceMessageHREF($alliance_id), 'Text'=>'Send Message');
		}
		if ($in_alliance || in_array($player->getAccountID(), Globals::getHiddenPlayers()) || $mbRead) {
			$menuItems[] = array('Link'=>Globals::getAllianceMessageBoardHREF($alliance_id), 'Text'=>'Message Board');
		}
		if ($in_alliance || in_array($player->getAccountID(), Globals::getHiddenPlayers()) || $planetLand) {
			$menuItems[] = array('Link'=>Globals::getPlanetListHREF($alliance_id), 'Text'=>'Planets');
		}
		if ($in_alliance || in_array($player->getAccountID(), Globals::getHiddenPlayers())) {
			$menuItems[] = array('Link'=>Globals::getAllianceForcesHREF($alliance_id), 'Text'=>'Forces');
			$menuItems[] = array('Link'=>Globals::getAllianceOptionsHREF($alliance_id), 'Text'=>'Options');
		}
		$menuItems[] = array('Link'=>Globals::getAllianceListHREF(), 'Text'=>'List Alliances');
		$menuItems[] = array('Link'=>Globals::getAllianceNewsHREF($alliance_id ? $alliance_id : $player->getAllianceID()), 'Text'=>'View News');

		$template->assign('MenuItems', $menuItems);
	}

	static function galactic_post() {
		global $template, $player;
		$menuItems = array();
		$menuItems[] = array('Link'=>SmrSession::getNewHREF(create_container('galactic_post_current.php')), 'Text'=>'Current Edition');
		$menuItems[] = array('Link'=>SmrSession::getNewHREF(create_container('skeleton.php', 'galactic_post_past.php')), 'Text'=>'Past Editions');
		$menuItems[] = array('Link'=>SmrSession::getNewHREF(create_container('skeleton.php', 'galactic_post_write_article.php')), 'Text'=>'Write an article');
		if ($player->isGPEditor()) {
			$menuItems[] = array('Link'=>SmrSession::getNewHREF(create_container('skeleton.php', 'galactic_post.php')), 'Text'=>'Editor Options');
		}
		$template->assign('MenuItems', $menuItems);
	}

	static function history_games($selected_index) {
		global $template, $var;
		$menuItems = [];
		$container = create_container('skeleton.php', 'history_games.php');
		$container['HistoryDatabase'] = $var['HistoryDatabase'];
		$container['view_game_id'] = $var['view_game_id'];
		$container['game_name'] = $var['game_name'];
		$menuItems[] = ['Link' => SmrSession::getNewHREF($container),
		                'Text' => 'Game Details'];
		$container['body'] = 'history_games_detail.php';
		$menuItems[] = ['Link' => SmrSession::getNewHREF($container),
		                'Text' => 'Extended Stats'];
		$container['body'] = 'history_games_hof.php';
		$menuItems[] = ['Link' => SmrSession::getNewHREF($container),
		                'Text' => 'Hall of Fame'];
		$container['body'] = 'history_games_news.php';
		$menuItems[] = ['Link' => SmrSession::getNewHREF($container),
		                'Text' => 'Game News'];
		// make the selected index bold
		$boldItem =& $menuItems[$selected_index]['Text'];
		$boldItem = '<b>' . $boldItem . '</b>';
		$template->assign('MenuItems', $menuItems);
	}

	static function messages() {
		global $player, $template;
		$menuItems = array();
		$menuItems[] = array('Link'=>Globals::getViewMessagesHREF(), 'Text'=>'View Messages');
		$menuItems[] = array('Link'=>Globals::getSendGlobalMessageHREF(), 'Text'=>'Send Global Message');
		if ($player->isOnCouncil()) {
			$menuItems[] = array('Link'=>Globals::getSendCouncilMessageHREF($player->getRaceID()), 'Text'=>'Send Council Message');
		}
		$menuItems[] = array('Link'=>Globals::getManageBlacklistHREF(), 'Text'=>'Manage Blacklist');

		$template->assign('MenuItems', $menuItems);
	}

	static function combat_log() {
		global $template;

		$container = create_container('skeleton.php', 'combat_log_list.php');
		$menuItems = array();

		$container['action'] = COMBAT_LOG_PERSONAL;
		$menuItems[] = array('Link'=>SmrSession::getNewHREF($container), 'Text'=>'Personal');
		$container['action'] = COMBAT_LOG_ALLIANCE;
		$menuItems[] = array('Link'=>SmrSession::getNewHREF($container), 'Text'=>'Alliance');
		$container['action'] = COMBAT_LOG_FORCE;
		$menuItems[] = array('Link'=>SmrSession::getNewHREF($container), 'Text'=>'Force');
		$container['action'] = COMBAT_LOG_PORT;
		$menuItems[] = array('Link'=>SmrSession::getNewHREF($container), 'Text'=>'Port');
		$container['action'] = COMBAT_LOG_PLANET;
		$menuItems[] = array('Link'=>SmrSession::getNewHREF($container), 'Text'=>'Planet');
		$container['action'] = COMBAT_LOG_SAVED;
		$menuItems[] = array('Link'=>SmrSession::getNewHREF($container), 'Text'=>'Saved');

		$template->assign('MenuItems', $menuItems);
	}

	static function trader() {
		global $player, $template;
		$template->assign('MenuItems', array(
						array('Link'=>Globals::getTraderStatusHREF(), 'Text'=>'Trader Status'),
						array('Link'=>Globals::getPlanetListHREF($player->getAllianceID()), 'Text'=>'Planets'),
						array('Link'=>Globals::getAllianceHREF($player->getAllianceID()), 'Text'=>'Alliance'),
						array('Link'=>Globals::getCouncilHREF(), 'Text'=>'Politics'),
						array('Link'=>Globals::getTraderRelationsHREF(), 'Text'=>'Relations'),
						array('Link'=>Globals::getTraderBountiesHREF(), 'Text'=>'Bounties')));
	}

	static function planet($planet) {
		global $template;

		$menu_array = array();
		$menu_array[] = array('Link'=>Globals::getPlanetMainHREF(), 'Text'=>'Planet Main');
		if ($planet->hasMenuOption('CONSTRUCTION')) {
			$menu_array[] = array('Link'=>Globals::getPlanetConstructionHREF(), 'Text'=>'Construction');
		}
		if ($planet->hasMenuOption('DEFENSE')) {
			$menu_array[] = array('Link'=>Globals::getPlanetDefensesHREF(), 'Text'=>'Defense');
		}
		if ($planet->hasMenuOption('OWNERSHIP')) {
			$menu_array[] = array('Link'=>Globals::getPlanetOwnershipHREF(), 'Text'=>'Ownership');
		}
		if ($planet->hasMenuOption('STOCKPILE')) {
			$menu_array[] = array('Link'=>Globals::getPlanetStockpileHREF(), 'Text'=>'Stockpile');
		}
		if ($planet->hasMenuOption('FINANCE')) {
			$menu_array[] = array('Link'=>Globals::getPlanetFinancesHREF(), 'Text'=>'Financial');
		}

		$template->assign('MenuItems', $menu_array);
	}

	/*
	 * $active_level1 - the id of the active menu on the first level
	 * $active_level1 - the id of the active menu on the second level
	 */
	static function rankings($active_level1 = 0, $active_level2 = 0) {

		$menu = array();

		// player rankings
		$menu_item = array();
		$menu_item['entry'] = create_link(create_container('skeleton.php', 'rankings_player_experience.php'), 'Player Rankings', 'nav');

		$menu_subitem = array();
		$menu_subitem[] = create_link(create_container('skeleton.php', 'rankings_player_experience.php'), 'Experience', 'nav');
		$menu_subitem[] = create_link(create_container('skeleton.php', 'rankings_player_profit.php'), 'Profit', 'nav');
		$menu_subitem[] = create_link(create_container('skeleton.php', 'rankings_player_kills.php'), 'Kills', 'nav');
		$menu_subitem[] = create_link(create_container('skeleton.php', 'rankings_player_death.php'), 'Deaths', 'nav');
		$menu_subitem[] = create_link(create_container('skeleton.php', 'rankings_player_assists.php'), 'Assists', 'nav');

		$menu_item['submenu'] = $menu_subitem;

		$menu[] = $menu_item;

		// alliance rankings
		$menu_item = array();
		$menu_item['entry'] = create_link(create_container('skeleton.php', 'rankings_alliance_experience.php'), 'Alliance Rankings', 'nav');

		$menu_subitem = array();
		$menu_subitem[] = create_link(create_container('skeleton.php', 'rankings_alliance_experience.php'), 'Experience', 'nav');
		$menu_subitem[] = create_link(create_container('skeleton.php', 'rankings_alliance_profit.php'), 'Profit', 'nav');
		$menu_subitem[] = create_link(create_container('skeleton.php', 'rankings_alliance_kills.php'), 'Kills', 'nav');
		$menu_subitem[] = create_link(create_container('skeleton.php', 'rankings_alliance_death.php'), 'Deaths', 'nav');
		$menu_subitem[] = create_link(create_container('skeleton.php', 'rankings_alliance_vs_alliance.php'), 'Versus', 'nav');

		$menu_item['submenu'] = $menu_subitem;

		$menu[] = $menu_item;

		// racial rankings
		$menu_item = array();
		$menu_item['entry'] = create_link(create_container('skeleton.php', 'rankings_race.php'), 'Racial Standings', 'nav');

		$menu_subitem = array();
		$menu_subitem[] = create_link(create_container('skeleton.php', 'rankings_race.php'), 'Experience', 'nav');
		$menu_subitem[] = create_link(create_container('skeleton.php', 'rankings_race_kills.php'), 'Kills', 'nav');
		$menu_subitem[] = create_link(create_container('skeleton.php', 'rankings_race_death.php'), 'Deaths', 'nav');

		$menu_item['submenu'] = $menu_subitem;

		$menu[] = $menu_item;

		// sector rankings
		$menu_item = array();
		$menu_item['entry'] = create_link(create_container('skeleton.php', 'rankings_sector_kill.php'), 'Sector Kills', 'nav');
		$menu[] = $menu_item;

		create_sub_menu($menu, $active_level1, $active_level2);
	}

	static function bank() {
		global $player;

		$menu_items[] = create_link(create_container('skeleton.php', 'bank_personal.php'),
														'Personal Account', 'nav');

		if ($player->hasAlliance())
			$menu_items[] = create_link(create_container('skeleton.php', 'bank_alliance.php'),
															'Alliance Account', 'nav');

		$menu_items[] = create_link(create_container('skeleton.php', 'bank_anon.php'),
														'Anonymous Account', 'nav');
		create_menu($menu_items);
	}

	static function council($race_id) {
		global $player;

		$menu_items[] = create_link(create_container('skeleton.php', 'council_list.php'),
														'View Council', 'nav');

		$container = create_container('skeleton.php');
		$container['body'] = 'council_politics.php';
		$container['race_id'] = $race_id;
		$menu_items[] = create_link($container, 'Political Status', 'nav');

		$container['body'] = 'council_send_message.php';
		$container['race_id'] = $race_id;
		$menu_items[] = create_link($container, 'Send Message', 'nav');

		if ($player->getRaceID() == $race_id) {
			if ($player->isOnCouncil())
				$menu_items[] = create_link(create_container('skeleton.php', 'council_vote.php'),
																'Voting Center', 'nav');
			if ($player->isPresident())
				$menu_items[] = create_link(create_container('skeleton.php', 'council_embassy.php'),
																'Embassy', 'nav');
		}

		create_menu($menu_items);
	}

	static function bar() {
		global $template;
		$template->assign('MenuItems', array(
					array('Link'=>Globals::getBarMainHREF(), 'Text'=>'Bar Main'),
					array('Link'=>Globals::getBarLottoPlayHREF(), 'Text'=>'Lotto'),
					array('Link'=>Globals::getBarBlackjackHREF(), 'Text'=>'BlackJack')));
	}

	static function news(&$template) {
		global $var;
		$menuItems = array();
		if (SmrSession::getGameID() == $var['GameID']) {
			$menuItems[] = array('Link'=>SmrSession::getNewHREF(create_container('skeleton.php', 'news_read_current.php', array('GameID'=>$var['GameID']))), 'Text'=>'Read Current News');
		}
		$menuItems[] = array('Link'=>SmrSession::getNewHREF(create_container('skeleton.php', 'news_read.php', array('GameID'=>$var['GameID']))), 'Text'=>'Read Latest News');
		$menuItems[] = array('Link'=>SmrSession::getNewHREF(create_container('skeleton.php', 'news_read_advanced.php', array('GameID'=>$var['GameID']))), 'Text'=>'Advanced News');

		$template->assign('MenuItems', $menuItems);
	}

	static function navigation(&$template, &$player) {
		$menuItems = array();
		$menuItems[] = array('Link'=>Globals::getPlotCourseHREF(), 'Text'=>'Plot A Course');
		if (!$player->isLandedOnPlanet())
			$menuItems[] = array('Link'=>Globals::getLocalMapHREF(), 'Text'=>'Local Map');
		$menuItems[] = array('Link'=>'map_galaxy.php" target="gal_map', 'Text'=>'Galaxy Map');
		$template->assign('MenuItems', $menuItems);
	}

}


function create_sub_menu($menu, $active_level1, $active_level2) {
	global $template;
	$return = ('<table class="bar1">');
	$return .= ('<tr>');
	$return .= ('<td>');
	$return .= ('<table class="fullwidth">');
	$return .= ('<tr class="bar1">');
	$return .= ('<td>');

	$return .= ('<table class="center">');
	$return .= ('<tr>');
	foreach ($menu as $number => $entry) {
		// insert spacer
		if ($number > 0)
			$return .= ('<td>&nbsp;|&nbsp;</td>');

		// if this is the active entry we mark it
		if ($number == $active_level1)
			$active = ' class="bold"';
		else
			$active = '';

		// echo entry itself
		$return .= ('<td ' . $active . '> ' . $entry['entry'] . '</td>');

	}
	$return .= ('</tr>');

	$return .= ('<tr>');
	foreach ($menu as $number => $entry) {
		// if this entry has a submenu and is the active one
		if (isset($entry['submenu']) && $number == $active_level1) {
			$return .= ('<td><small>');
			foreach ($entry['submenu'] as $sub_number => $sub_entry) {
				if ($sub_number > 0)
					$return .= (' | ');

				if ($sub_number == $active_level2)
					$return .= ('<span class="bold">' . $sub_entry . '</span>');
				else
					$return .= ($sub_entry);
			}
			$return .= ('</small></td>');
		}
		else {
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
	$template->unassign('MenuItems');
	$template->unassign('MenuBar');
	$template->assign('SubMenuBar', $return);
}
