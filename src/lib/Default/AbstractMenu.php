<?php declare(strict_types=1);

/**
 * Creates menu navigation bars.
 */
class AbstractMenu {

	public static function headquarters(int $locationTypeID): void {
		$links = [];
		$location = SmrLocation::getLocation($locationTypeID);
		if ($location->isHQ()) {
			$links[] = ['government.php', 'Government'];
			$links[] = ['military_payment_claim.php', 'Claim Military Payment'];
		} elseif ($location->isUG()) {
			$links[] = ['underground.php', 'Underground'];
		} else {
			throw new Exception('Location is not HQ or UG: ' . $location->getName());
		}
		$links[] = ['bounty_claim.php', 'Claim Bounty'];
		$links[] = ['bounty_place.php', 'Place Bounty'];

		$menuItems = [];
		$container = Page::create('skeleton.php');
		$container['LocationID'] = $locationTypeID;
		foreach ($links as $link) {
			$container['body'] = $link[0];
			$menuItems[] = [
				'Link' => $container->href(),
				'Text' => $link[1],
			];
		}
		$template = Smr\Template::getInstance();
		$template->assign('MenuItems', $menuItems);
	}

	public static function planet_list(int $alliance_id, int $selected_index): void {
		$menuItems = [];
		$menuItems[] = ['Link' => Globals::getPlanetListHREF($alliance_id), 'Text' => 'Defense'];
		$menuItems[] = ['Link' => Globals::getPlanetListFinancialHREF($alliance_id), 'Text' => 'Financial'];
		// make the selected index bold
		$boldItem =& $menuItems[$selected_index]['Text'];
		$boldItem = '<span class="bold">' . $boldItem . '</span>';

		$template = Smr\Template::getInstance();
		$template->assign('MenuItems', $menuItems);
	}

	public static function alliance(int $alliance_id): void {
		$db = Smr\Database::getInstance();
		$player = Smr\Session::getInstance()->getPlayer();

		$in_alliance = ($alliance_id == $player->getAllianceID() || in_array($player->getAccountID(), Globals::getHiddenPlayers()));

		// Some pages are visible to all alliance members
		$canReadMb = $in_alliance;
		$canReadMotd = $in_alliance;
		$canSeePlanetList = $in_alliance;

		// Check if player has permissions through an alliance treaty
		if (!$in_alliance) {
			$dbResult = $db->read('SELECT mb_read, mod_read, planet_land FROM alliance_treaties
							WHERE (alliance_id_1 = ' . $db->escapeNumber($alliance_id) . ' OR alliance_id_1 = ' . $db->escapeNumber($player->getAllianceID()) . ')
							AND (alliance_id_2 = ' . $db->escapeNumber($alliance_id) . ' OR alliance_id_2 = ' . $db->escapeNumber($player->getAllianceID()) . ')
							AND game_id = ' . $db->escapeNumber($player->getGameID()) . '
							AND (mb_read = 1 OR mod_read = 1 OR planet_land = 1) AND official = \'TRUE\'');
			if ($dbResult->hasRecord()) {
				$dbRecord = $dbResult->record();
				$canReadMb = $dbRecord->getBoolean('mb_read');
				$canReadMotd = $dbRecord->getBoolean('mod_read');
				$canSeePlanetList = $dbRecord->getBoolean('planet_land');
			}
		}

		$role_id = $player->getAllianceRole($alliance_id);
		$dbResult = $db->read('SELECT send_alliance_msg FROM alliance_has_roles WHERE alliance_id = ' . $db->escapeNumber($alliance_id) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND role_id = ' . $db->escapeNumber($role_id));
		if ($dbResult->hasRecord()) {
			$send_alliance_msg = $dbResult->record()->getBoolean('send_alliance_msg');
		} else {
			$send_alliance_msg = false;
		}

		$menuItems = [];
		if ($canReadMotd) {
			$menuItems[] = ['Link' => Globals::getAllianceMotdHREF($alliance_id), 'Text' => 'Message of the Day'];
		}
		$menuItems[] = ['Link' => Globals::getAllianceRosterHREF($alliance_id), 'Text' => 'Roster'];
		if ($send_alliance_msg) {
			$menuItems[] = ['Link' => Globals::getAllianceMessageHREF($alliance_id), 'Text' => 'Send Message'];
		}
		if ($canReadMb) {
			$menuItems[] = ['Link' => Globals::getAllianceMessageBoardHREF($alliance_id), 'Text' => 'Message Board'];
		}
		if ($canSeePlanetList) {
			$menuItems[] = ['Link' => Globals::getPlanetListHREF($alliance_id), 'Text' => 'Planets'];
		}
		if ($in_alliance) {
			$menuItems[] = ['Link' => Globals::getAllianceForcesHREF($alliance_id), 'Text' => 'Forces'];
			$menuItems[] = ['Link' => Globals::getAllianceOptionsHREF($alliance_id), 'Text' => 'Options'];
		}
		$menuItems[] = ['Link' => Globals::getAllianceListHREF(), 'Text' => 'List Alliances'];
		$menuItems[] = ['Link' => Globals::getAllianceNewsHREF($alliance_id), 'Text' => 'View News'];

		$template = Smr\Template::getInstance();
		$template->assign('MenuItems', $menuItems);
	}

	public static function galactic_post(): void {
		$player = Smr\Session::getInstance()->getPlayer();

		$menuItems = [];
		$menuItems[] = ['Link' => Page::create('galactic_post_current.php')->href(), 'Text' => 'Current Edition'];
		$menuItems[] = ['Link' => Page::create('skeleton.php', 'galactic_post_past.php')->href(), 'Text' => 'Past Editions'];
		$menuItems[] = ['Link' => Page::create('skeleton.php', 'galactic_post_write_article.php')->href(), 'Text' => 'Write an article'];
		if ($player->isGPEditor()) {
			$menuItems[] = ['Link' => Page::create('skeleton.php', 'galactic_post.php')->href(), 'Text' => 'Editor Options'];
		}

		$template = Smr\Template::getInstance();
		$template->assign('MenuItems', $menuItems);
	}

	public static function history_games(int $selected_index): void {
		$menuItems = [];
		$container = Page::create('skeleton.php', 'history_games.php');
		$container->addVar('HistoryDatabase');
		$container->addVar('view_game_id');
		$container->addVar('game_name');
		$menuItems[] = [
			'Link' => $container->href(),
			'Text' => 'Game Details',
		];
		$container['body'] = 'history_games_detail.php';
		$menuItems[] = [
			'Link' => $container->href(),
			'Text' => 'Extended Stats',
		];
		$container['body'] = 'history_games_hof.php';
		$menuItems[] = [
			'Link' => $container->href(),
			'Text' => 'Hall of Fame',
		];
		$container['body'] = 'history_games_news.php';
		$menuItems[] = [
			'Link' => $container->href(),
			'Text' => 'Game News',
		];
		// make the selected index bold
		$boldItem =& $menuItems[$selected_index]['Text'];
		$boldItem = '<b>' . $boldItem . '</b>';

		$template = Smr\Template::getInstance();
		$template->assign('MenuItems', $menuItems);
	}

	public static function messages(): void {
		$player = Smr\Session::getInstance()->getPlayer();

		$menuItems = [];
		$menuItems[] = ['Link' => Globals::getViewMessageBoxesHREF(), 'Text' => 'View Messages'];
		$menuItems[] = ['Link' => Globals::getSendGlobalMessageHREF(), 'Text' => 'Send Global Message'];
		if ($player->isOnCouncil()) {
			$menuItems[] = ['Link' => Globals::getSendCouncilMessageHREF($player->getRaceID()), 'Text' => 'Send Council Message'];
		}
		$menuItems[] = ['Link' => Globals::getManageBlacklistHREF(), 'Text' => 'Manage Blacklist'];

		$template = Smr\Template::getInstance();
		$template->assign('MenuItems', $menuItems);
	}

	public static function combat_log(): void {
		$container = Page::create('skeleton.php', 'combat_log_list.php');
		$menuItems = [];

		$container['action'] = COMBAT_LOG_PERSONAL;
		$menuItems[] = ['Link' => $container->href(), 'Text' => 'Personal'];
		$container['action'] = COMBAT_LOG_ALLIANCE;
		$menuItems[] = ['Link' => $container->href(), 'Text' => 'Alliance'];
		$container['action'] = COMBAT_LOG_FORCE;
		$menuItems[] = ['Link' => $container->href(), 'Text' => 'Force'];
		$container['action'] = COMBAT_LOG_PORT;
		$menuItems[] = ['Link' => $container->href(), 'Text' => 'Port'];
		$container['action'] = COMBAT_LOG_PLANET;
		$menuItems[] = ['Link' => $container->href(), 'Text' => 'Planet'];
		$container['action'] = COMBAT_LOG_SAVED;
		$menuItems[] = ['Link' => $container->href(), 'Text' => 'Saved'];

		$template = Smr\Template::getInstance();
		$template->assign('MenuItems', $menuItems);
	}

	public static function trader(): void {
		$player = Smr\Session::getInstance()->getPlayer();

		$template = Smr\Template::getInstance();
		$template->assign('MenuItems', [
						['Link' => Globals::getTraderStatusHREF(), 'Text' => 'Trader Status'],
						['Link' => Globals::getPlanetListHREF($player->getAllianceID()), 'Text' => 'Planets'],
						['Link' => Globals::getAllianceHREF($player->getAllianceID()), 'Text' => 'Alliance'],
						['Link' => Globals::getCouncilHREF(), 'Text' => 'Politics'],
						['Link' => Globals::getTraderRelationsHREF(), 'Text' => 'Relations'],
						['Link' => Globals::getTraderBountiesHREF(), 'Text' => 'Bounties']]);
	}

	public static function planet(SmrPlanet $planet): void {
		$menu_array = [];
		$menu_array[] = ['Link' => Globals::getPlanetMainHREF(), 'Text' => 'Planet Main'];
		if ($planet->hasMenuOption('CONSTRUCTION')) {
			$menu_array[] = ['Link' => Globals::getPlanetConstructionHREF(), 'Text' => 'Construction'];
		}
		if ($planet->hasMenuOption('DEFENSE')) {
			$menu_array[] = ['Link' => Globals::getPlanetDefensesHREF(), 'Text' => 'Defense'];
		}
		if ($planet->hasMenuOption('OWNERSHIP')) {
			$menu_array[] = ['Link' => Globals::getPlanetOwnershipHREF(), 'Text' => 'Ownership'];
		}
		if ($planet->hasMenuOption('STOCKPILE')) {
			$menu_array[] = ['Link' => Globals::getPlanetStockpileHREF(), 'Text' => 'Stockpile'];
		}
		if ($planet->hasMenuOption('FINANCE')) {
			$menu_array[] = ['Link' => Globals::getPlanetFinancesHREF(), 'Text' => 'Financial'];
		}

		$template = Smr\Template::getInstance();
		$template->assign('MenuItems', $menu_array);
	}

	/*
	 * $active_level1 - the id of the active menu on the first level
	 * $active_level1 - the id of the active menu on the second level
	 */
	public static function rankings(int $active_level1 = 0, int $active_level2 = 0): void {

		$menu = [];

		// player rankings
		$menu_item = [];
		$menu_item['entry'] = create_link(Page::create('skeleton.php', 'rankings_player_experience.php'), 'Player Rankings', 'nav');

		$menu_subitem = [];
		$menu_subitem[] = create_link(Page::create('skeleton.php', 'rankings_player_experience.php'), 'Experience', 'nav');
		$menu_subitem[] = create_link(Page::create('skeleton.php', 'rankings_player_profit.php'), 'Profit', 'nav');
		$menu_subitem[] = create_link(Page::create('skeleton.php', 'rankings_player_kills.php'), 'Kills', 'nav');
		$menu_subitem[] = create_link(Page::create('skeleton.php', 'rankings_player_death.php'), 'Deaths', 'nav');
		$menu_subitem[] = create_link(Page::create('skeleton.php', 'rankings_player_assists.php'), 'Assists', 'nav');
		$menu_subitem[] = create_link(Page::create('skeleton.php', 'rankings_player_npc_kills.php'), 'NPC Kills', 'nav');

		$menu_item['submenu'] = $menu_subitem;

		$menu[] = $menu_item;

		// alliance rankings
		$menu_item = [];
		$menu_item['entry'] = create_link(Page::create('skeleton.php', 'rankings_alliance_experience.php'), 'Alliance Rankings', 'nav');

		$menu_subitem = [];
		$menu_subitem[] = create_link(Page::create('skeleton.php', 'rankings_alliance_experience.php'), 'Experience', 'nav');
		$menu_subitem[] = create_link(Page::create('skeleton.php', 'rankings_alliance_profit.php'), 'Profit', 'nav');
		$menu_subitem[] = create_link(Page::create('skeleton.php', 'rankings_alliance_kills.php'), 'Kills', 'nav');
		$menu_subitem[] = create_link(Page::create('skeleton.php', 'rankings_alliance_death.php'), 'Deaths', 'nav');
		$menu_subitem[] = create_link(Page::create('skeleton.php', 'rankings_alliance_vs_alliance.php'), 'Versus', 'nav');

		$menu_item['submenu'] = $menu_subitem;

		$menu[] = $menu_item;

		// racial rankings
		$menu_item = [];
		$menu_item['entry'] = create_link(Page::create('skeleton.php', 'rankings_race.php'), 'Racial Standings', 'nav');

		$menu_subitem = [];
		$menu_subitem[] = create_link(Page::create('skeleton.php', 'rankings_race.php'), 'Experience', 'nav');
		$menu_subitem[] = create_link(Page::create('skeleton.php', 'rankings_race_kills.php'), 'Kills', 'nav');
		$menu_subitem[] = create_link(Page::create('skeleton.php', 'rankings_race_death.php'), 'Deaths', 'nav');

		$menu_item['submenu'] = $menu_subitem;

		$menu[] = $menu_item;

		// sector rankings
		$menu_item = [];
		$menu_item['entry'] = create_link(Page::create('skeleton.php', 'rankings_sector_kill.php'), 'Sector Kills', 'nav');
		$menu[] = $menu_item;

		create_sub_menu($menu, $active_level1, $active_level2);
	}

	public static function bank(): void {
		$player = Smr\Session::getInstance()->getPlayer();

		$links = [];
		$links[] = ['bank_personal.php', 'Personal Account'];
		if ($player->hasAlliance()) {
			$links[] = ['bank_alliance.php', 'Alliance Account'];
		}
		$links[] = ['bank_anon.php', 'Anonymous Account'];

		$menuItems = [];
		$container = Page::create('skeleton.php');
		foreach ($links as $link) {
			$container['body'] = $link[0];
			$menuItems[] = [
				'Link' => $container->href(),
				'Text' => $link[1],
			];
		}

		$template = Smr\Template::getInstance();
		$template->assign('MenuItems', $menuItems);
	}

	public static function council(int $race_id): void {
		$player = Smr\Session::getInstance()->getPlayer();

		$container = Page::create('skeleton.php', 'council_list.php');
		$container['race_id'] = $race_id;
		$menu_items = [];
		$menu_items[] = [
			'Link' => $container->href(),
			'Text' => 'View Council',
		];

		$container['body'] = 'council_politics.php';
		$menu_items[] = [
			'Link' => $container->href(),
			'Text' => 'Political Status',
		];

		$container['body'] = 'council_send_message.php';
		$menu_items[] = [
			'Link' => $container->href(),
			'Text' => 'Send Message',
		];

		if ($player->getRaceID() == $race_id) {
			if ($player->isOnCouncil()) {
				$container = Page::create('skeleton.php', 'council_vote.php');
				$menu_items[] = [
					'Link' => $container->href(),
					'Text' => 'Voting Center',
				];
			}
			if ($player->isPresident()) {
				$container = Page::create('skeleton.php', 'council_embassy.php');
				$menu_items[] = [
					'Link' => $container->href(),
					'Text' => 'Embassy',
				];
			}
		}

		$template = Smr\Template::getInstance();
		$template->assign('MenuItems', $menu_items);
	}

	public static function bar(): void {
		$template = Smr\Template::getInstance();
		$template->assign('MenuItems', [
					['Link' => Globals::getBarMainHREF(), 'Text' => 'Bar Main'],
					['Link' => Globals::getBarLottoPlayHREF(), 'Text' => 'Lotto'],
					['Link' => Globals::getBarBlackjackHREF(), 'Text' => 'BlackJack']]);
	}

	public static function news(int $gameID): void {
		$session = Smr\Session::getInstance();

		$menuItems = [];
		if ($session->getGameID() == $gameID) {
			$menuItems[] = [
				'Link' => Page::create('skeleton.php', 'news_read_current.php', ['GameID' => $gameID])->href(),
				'Text' => 'Read Current News',
			];
		}
		$menuItems[] = [
			'Link' => Page::create('skeleton.php', 'news_read.php', ['GameID' => $gameID])->href(),
			'Text' => 'Read Latest News',
		];
		$menuItems[] = [
			'Link' => Page::create('skeleton.php', 'news_read_advanced.php', ['GameID' => $gameID])->href(),
			'Text' => 'Advanced News',
		];

		$template = Smr\Template::getInstance();
		$template->assign('MenuItems', $menuItems);
	}

	public static function navigation(AbstractSmrPlayer $player): void {
		$menuItems = [];
		$menuItems[] = ['Link' => Globals::getPlotCourseHREF(), 'Text' => 'Plot A Course'];
		if (!$player->isLandedOnPlanet()) {
			$menuItems[] = ['Link' => Globals::getLocalMapHREF(), 'Text' => 'Local Map'];
		}
		$menuItems[] = ['Link' => 'map_galaxy.php" target="gal_map', 'Text' => 'Galaxy Map'];

		$template = Smr\Template::getInstance();
		$template->assign('MenuItems', $menuItems);
	}

}

function create_sub_menu(array $menu, int $active_level1, int $active_level2): void {
	$return = ('<table class="fullwidth center">');
	$return .= ('<tr>');
	foreach ($menu as $number => $entry) {
		// insert spacer
		if ($number > 0) {
			$return .= ('<td>&nbsp;|&nbsp;</td>');
		}

		// if this is the active entry we mark it
		if ($number == $active_level1) {
			$active = ' class="bold"';
		} else {
			$active = '';
		}

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
				if ($sub_number > 0) {
					$return .= (' | ');
				}

				if ($sub_number == $active_level2) {
					$return .= ('<span class="bold">' . $sub_entry . '</span>');
				} else {
					$return .= ($sub_entry);
				}
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

	$template = Smr\Template::getInstance();
	$template->unassign('MenuItems');
	$template->assign('SubMenuBar', $return);
}
