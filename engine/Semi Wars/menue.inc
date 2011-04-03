<?php

function create_alliance_menue($alliance_id = null, $alliance_leader_id = FALSE)
{
	global $player, $template;

	$db2 = new SmrMySqlDatabase();
	if($alliance_id)
	{
		$in_alliance = ($alliance_id == $player->getAllianceID());
	}
	else
	{
		$in_alliance = $player->hasAlliance();
	}
	if(!$in_alliance)
	{
		$db2->query('SELECT mb_read, mod_read, planet_land FROM alliance_treaties
						WHERE (alliance_id_1 = '.$alliance_id.' OR alliance_id_1 = '.$player->getAllianceID().')
						AND (alliance_id_2 = '.$alliance_id.' OR alliance_id_2 = '.$player->getAllianceID().')
						AND game_id = '.$player->getGameID().'
						AND (mb_read = 1 OR mod_read = 1 OR planet_land = 1) AND official = \'TRUE\'');
		if ($db2->nextRecord())
		{
			if ($db2->getField('mb_read')) $mbRead = TRUE;
			else $mbRead = FALSE;
			if ($db2->getField('mod_read')) $modRead = TRUE;
			else $modRead = FALSE;
			if ($db2->getField('planet_land')) $planetLand = TRUE;
			else $planetLand = FALSE;
		}
		else
		{
			$mbRead = FALSE;
			$modRead = FALSE;
			$planetLand = FALSE;
		}
	}

	$db2->query('SELECT * FROM player_has_alliance_role WHERE account_id = '.$player->getAccountID().' AND game_id = '.$player->getGameID().' AND alliance_id='.$player->getAllianceID());
	if ($db2->nextRecord()) $role_id = $db2->getField('role_id');
	else $role_id = 0;
	$db2->query('SELECT * FROM alliance_has_roles WHERE alliance_id = '.$player->getAllianceID().' AND game_id = '.$player->getGameID().' AND role_id = '.$role_id);
	$db2->nextRecord();
	$send = $db2->getField('send_alliance_msg') == 'TRUE';
//	if ($player->getAccountID() == $alliance_leader_id)
//	{
		//$container['body']='alliance_treaties.php';
		//$menue_items[] = create_link($container,'Treaties','nav');	
	//}
	$menuItems = array();
	if ($in_alliance || in_array($player->getAccountID(), Globals::getHiddenPlayers()) || $modRead)
	{
		$menuItems[] = array('Link'=>Globals::getAllianceMotdHREF($alliance_id),'Text'=>'Message of the Day');
	}
	$menuItems[] = array('Link'=>Globals::getAllianceRosterHREF($alliance_id),'Text'=>'Roster');
	if (($send && $in_alliance) || in_array($player->getAccountID(), Globals::getHiddenPlayers()))
	{
		$menuItems[] = array('Link'=>Globals::getAllianceMessageHREF($alliance_id),'Text'=>'Send Message');
	}
	if ($in_alliance || in_array($player->getAccountID(), Globals::getHiddenPlayers()) || $mbRead)
	{
		$menuItems[] = array('Link'=>Globals::getAllianceMessageBoardHREF($alliance_id),'Text'=>'Message Board');
	}
	if ($in_alliance || in_array($player->getAccountID(), Globals::getHiddenPlayers()) || $planetLand)
	{
		$menuItems[] = array('Link'=>Globals::getAlliancePlanetsHREF($alliance_id),'Text'=>'Planets');
	}
	if ($in_alliance || in_array($player->getAccountID(), Globals::getHiddenPlayers()))
	{
		$menuItems[] = array('Link'=>Globals::getAllianceForcesHREF($alliance_id),'Text'=>'Forces');
		$menuItems[] = array('Link'=>Globals::getAllianceOptionsHREF($alliance_id),'Text'=>'Options');
	}
	$menuItems[] = array('Link'=>Globals::getAllianceListHREF(),'Text'=>'List Alliances');
	$menuItems[] = array('Link'=>Globals::getAllianceNewsHREF($alliance_id?$alliance_id:$player->getAllianceID()),'Text'=>'View News');

	$template->assign('MenuItems',$menuItems);
}

function create_galactic_post_menue()
{
	global $template, $player;
	$menuItems = array();
	$menuItems[] = array('Link'=>SmrSession::get_new_href(create_container('skeleton.php', 'galactic_post_read.php')),'Text'=>'Current Edition');

	$allowedWrite = true; //Lets always allow people to write for the GP? The less hoops to jump through the more writers we'll have surely? 
	$allowedEdit = false;
	//find out the user's privligages
	$db = new SmrMySqlDatabase();
	$db->query('SELECT * FROM galactic_post_writer WHERE game_id = '.SmrSession::$game_id.' AND account_id = '.$player->getAccountID());
	if ($db->nextRecord())
	{
		$allowedEdit = $db->getField('position') == 'editor';
	}
	
	if ($allowedWrite === false)
	{
		$menuItems[] = array('Link'=>SmrSession::get_new_href(create_container('skeleton.php', 'galactic_post_application.php')),'Text'=>'Apply as writer!');
	}
	if ($allowedEdit === true)
	{
		$menuItems[] = array('Link'=>SmrSession::get_new_href(create_container('skeleton.php', 'galactic_post_view_applications.php')),'Text'=>'View the applications');
		$menuItems[] = array('Link'=>SmrSession::get_new_href(create_container('skeleton.php', 'galactic_post_view_article.php')),'Text'=>'View the articles');
		$menuItems[] = array('Link'=>SmrSession::get_new_href(create_container('skeleton.php', 'galactic_post.php')),'Text'=>'Editor Options');
		$menuItems[] = array('Link'=>SmrSession::get_new_href(create_container('skeleton.php', 'galactic_post_make_paper.php')),'Text'=>'Make a paper');
	}
	if ($allowedWrite === true)
	{
		$menuItems[] = array('Link'=>SmrSession::get_new_href(create_container('skeleton.php', 'galactic_post_write_article.php')),'Text'=>'Write an article');
	}
	$template->assign('MenuItems', $menuItems);
}

function create_message_menue()
{
	global $player, $template;
	$menuItems = array();
	$menuItems[] = array('Link'=>Globals::getViewMessagesHREF(),'Text'=>'View Messages');
	if ($player->isOnCouncil())
	{
		$menuItems[] = array('Link'=>Globals::getSendCouncilMessageHREF($player->getRaceID()),'Text'=>'Send Council Message');
	}
	$menuItems[] = array('Link'=>Globals::getSendGlobalMessageHREF(),'Text'=>'Send Global Message');
	$menuItems[] = array('Link'=>Globals::getManageBlacklistHREF(),'Text'=>'Manage Blacklist');

	$template->assign('MenuItems',$menuItems);
}

function create_combat_log_menue()
{
	global $template;

	$container = create_container('skeleton.php','combat_log_viewer.php');
	$menuItems = array();
	
	$container['action'] = 0;
	$menuItems[] = array('Link'=>SmrSession::get_new_href($container),'Text'=>'Personal');
	$container['action'] = 1;
	$menuItems[] = array('Link'=>SmrSession::get_new_href($container),'Text'=>'Alliance');
	$container['action'] = 6;
	$menuItems[] = array('Link'=>SmrSession::get_new_href($container),'Text'=>'Force');
	$container['action'] = 2;
	$menuItems[] = array('Link'=>SmrSession::get_new_href($container),'Text'=>'Port');
	$container['action'] = 3;
	$menuItems[] = array('Link'=>SmrSession::get_new_href($container),'Text'=>'Planet');
	$container['action'] = 4;
	$menuItems[] = array('Link'=>SmrSession::get_new_href($container),'Text'=>'Saved');

	$template->assign('MenuItems',$menuItems);
}

function create_trader_menue()
{
	global $player, $template;
	$template->assign('MenuItems', array(
						array('Link'=>Globals::getTraderStatusHREF(),'Text'=>'Trader Status'),
						array('Link'=>Globals::getTraderPlanetHREF(),'Text'=>'Planet'),
						array('Link'=>Globals::getAllianceHREF($player->getAllianceID()),'Text'=>'Alliance'),
						array('Link'=>Globals::getCouncilHREF(),'Text'=>'Politics'),
						array('Link'=>Globals::getTraderRelationsHREF(),'Text'=>'Relations'),
						array('Link'=>Globals::getTraderBountiesHREF(),'Text'=>'Bounties')));
}

function create_planet_menue()
{
	global $template;
	$template->assign('MenuItems', array(
						array('Link'=>Globals::getPlanetMainHREF(),'Text'=>'Planet Main'),
						array('Link'=>Globals::getPlanetConstructionHREF(),'Text'=>'Construction'),
						array('Link'=>Globals::getPlanetDefensesHREF(),'Text'=>'Defenses'),
						array('Link'=>Globals::getPlanetOwnershipHREF(),'Text'=>'Ownership'),
						array('Link'=>Globals::getPlanetStockpileHREF(),'Text'=>'Stockpile'),
						array('Link'=>Globals::getPlanetFinancesHREF(),'Text'=>'Financial')));
}

/*
 * $active_level1 - the id of the active menu on the first level
 * $active_level1 - the id of the active menu on the second level
 */
function create_ranking_menue($active_level1 = 0, $active_level2 = 0) {

	$menu = array();

	// player rankings
	$menu_item = array();
	$menu_item['entry'] = create_link(create_container('skeleton.php', 'rankings_player_experience.php'), 'Player Rankings','nav');

	$menu_subitem = array();
	$menu_subitem[] = create_link(create_container('skeleton.php', 'rankings_player_experience.php'), 'Experience','nav');
	$menu_subitem[] = create_link(create_container('skeleton.php', 'rankings_player_kills.php'), 'Kills','nav');
	$menu_subitem[] = create_link(create_container('skeleton.php', 'rankings_player_death.php'), 'Death','nav');

	$menu_item['submenu'] = $menu_subitem;

	$menu[] = $menu_item;


	$menu_item = array();
	$menu_item['entry'] = create_link(create_container('skeleton.php', 'rankings_alliance_experience.php'), 'Alliance Rankings','nav');

	$menu_subitem = array();
	$menu_subitem[] = create_link(create_container('skeleton.php', 'rankings_alliance_experience.php'), 'Experience','nav');
	$menu_subitem[] = create_link(create_container('skeleton.php', 'rankings_alliance_kills.php'), 'Kills','nav');
	$menu_subitem[] = create_link(create_container('skeleton.php', 'rankings_alliance_death.php'), 'Death','nav');
	$menu_subitem[] = create_link(create_container('skeleton.php', 'rankings_alliance_vs_alliance.php'), 'Versus','nav');

	$menu_item['submenu'] = $menu_subitem;

	$menu[] = $menu_item;


	$menu_item = array();
	$menu_item['entry'] = create_link(create_container('skeleton.php', 'rankings_race.php'), 'Racial Standings','nav');
	
	$menu_subitem = array();
	$menu_subitem[] = create_link(create_container('skeleton.php', 'rankings_race.php'), 'Experience','nav');
	$menu_subitem[] = create_link(create_container('skeleton.php', 'rankings_race_kills.php'), 'Kills','nav');
	$menu_subitem[] = create_link(create_container('skeleton.php', 'rankings_race_death.php'), 'Death','nav');

	$menu_item['submenu'] = $menu_subitem;

	$menu[] = $menu_item;
	
	$menu_item = array();
	$menu_item['entry'] = create_link(create_container('skeleton.php', 'rankings_sector_kill.php'), 'Sector Kills','nav');
	$menu[] = $menu_item;

	create_sub_menu($menu, $active_level1, $active_level2);

}

function create_sub_menu($menu, $active_level1, $active_level2) {
	global $template;
	$return = ('<p>');
	$return .= ('<table class="bar1">');
	$return .= ('<tr>');
	$return .= ('<td>');
	$return .= ('<table class="fullwidth">');
	$return .= ('<tr class="bar1">');
	$return .= ('<td align="center">');

	$return .= ('<table>');
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
					$return .= ('<span class="bold">'.$sub_entry.'</span>');
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
	$return .= ('</p>');
	$template->unassign('MenuItems');
	$template->unassign('MenuBar');
	$template->assign('SubMenuBar',$return);
}

function create_bank_menue() {

	global $player;

	$menue_items[] = create_link(create_container('skeleton.php', 'bank_personal.php'),
													'Personal Account','nav');

	if ($player->hasAlliance())
		$menue_items[] = create_link(create_container('skeleton.php', 'bank_alliance.php'),
														'Alliance Account','nav');

	$menue_items[] = create_link(create_container('skeleton.php', 'bank_anon.php'),
													'Anonymous Account','nav');
	create_menue($menue_items);

}

function create_hq_menue()
{
	global $var;
	$container = create_container('skeleton.php');
	$container['LocationID'] = $var['LocationID'];
	
	$container['body'] = 'government.php';
	$menue_items[] = create_link($container, 'Government','nav');

	$container['body'] = 'military_payment_claim.php';
	$menue_items[] = create_link($container, 'Claim Military Payment','nav');

	create_menue($menue_items);
}

function create_ug_menue() {

	$menue_items[] = create_link(create_container('skeleton.php', 'underground.php'),
													'Underground','nav');

	create_menue($menue_items);
}

function create_council_menue($race_id)
{
	global $player;

	$menue_items[] = create_link(create_container('skeleton.php', 'council_list.php'),
													'View Council','nav');

	$container = array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'council_politics.php';
	$container['race_id'] = $race_id;
	$menue_items[] = create_link($container, 'Political Status','nav');

	$container = array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'council_send_message.php';
	$container['race_id'] = $race_id;
	$menue_items[] = create_link($container, 'Send Message','nav');

	if($player->getRaceID()==$race_id)
	{
		if ($player->isOnCouncil())
			$menue_items[] = create_link(create_container('skeleton.php', 'council_vote.php'),
															'Voting Center','nav');
		if ($player->isPresident())
			$menue_items[] = create_link(create_container('skeleton.php', 'council_embassy.php'),
															'Embassy','nav');
	}

	create_menue($menue_items);
}

function create_bar_menue()
{
	global $template;
	$template->assign('MenuItems', array(
					array('Link'=>Globals::getBarMainHREF(),'Text'=>'Bar Main'),
					array('Link'=>Globals::getLottoHREF(),'Text'=>'Lotto'),
					array('Link'=>Globals::getBlackjackHREF(),'Text'=>'BlackJack')));
}

function create_news_menue(&$template)
{
	global $var;
	$menuItems = array();
	if(SmrSession::$game_id==$var['GameID'])
		$menuItems[] = array('Link'=>SmrSession::get_new_href(create_container('skeleton.php', 'news_read_current.php',array('GameID'=>$var['GameID']))),'Text'=>'Read Current News');
	$menuItems[] = array('Link'=>SmrSession::get_new_href(create_container('skeleton.php', 'news_read.php',array('GameID'=>$var['GameID']))),'Text'=>'Read Latest News');
	$menuItems[] = array('Link'=>SmrSession::get_new_href(create_container('skeleton.php', 'news_read_advanced.php',array('GameID'=>$var['GameID']))),'Text'=>'Advanced News');
					
	$template->assign('MenuItems', $menuItems);
}

function create_nav_menue(&$template, &$player)
{
	$menuItems = array();
	$menuItems[] = array('Link'=>Globals::getPlotCourseHREF(),'Text'=>'Plot A Course');
	if(!$player->isLandedOnPlanet())
		$menuItems[] = array('Link'=>Globals::getLocalMapHREF(),'Text'=>'Local Map');
	$menuItems[] = array('Link'=>'map_galaxy.php" target="gal_map','Text'=>'Galaxy Map');
	$template->assign('MenuItems', $menuItems);
}

function create_hof_menue() {

	$menue_items[] = create_link(create_container('skeleton.php', 'hall_of_fame.php'),
													'Hall of Fame Main','nav');

	$container = array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'hall_of_fame.php';

	$container['type'] = 'Newbie';
	$menue_items[] = create_link($container, 'Newbie Stats','nav');
	$container['type'] = 'Veteran';
	$menue_items[] = create_link($container, 'Veteran Stats','nav');
	$container['type'] = 'Semi War';
	$menue_items[] = create_link($container, 'Special Game Stats','nav');

	create_menue($menue_items);

}
?>