<?php declare(strict_types=1);

namespace Smr;

use Exception;
use Smr\Pages\Account\NewsReadAdvanced;
use Smr\Pages\Account\NewsReadArchives;
use Smr\Pages\Player\Bank\AllianceBank;
use Smr\Pages\Player\Bank\AnonBank;
use Smr\Pages\Player\Bank\PersonalBank;
use Smr\Pages\Player\Bar\BarMain;
use Smr\Pages\Player\Bar\LottoBuyTicket;
use Smr\Pages\Player\Bar\PlayBlackjackBet;
use Smr\Pages\Player\CombatLogList;
use Smr\Pages\Player\Council\Embassy;
use Smr\Pages\Player\Council\MessageCouncil;
use Smr\Pages\Player\Council\PoliticalStatus;
use Smr\Pages\Player\Council\ViewCouncil;
use Smr\Pages\Player\Council\VotingCenter;
use Smr\Pages\Player\GalacticPost\ArticleWrite;
use Smr\Pages\Player\GalacticPost\CurrentEditionProcessor;
use Smr\Pages\Player\GalacticPost\EditorOptions;
use Smr\Pages\Player\GalacticPost\PastEditionSelect;
use Smr\Pages\Player\Headquarters\BountyClaimProcessor;
use Smr\Pages\Player\Headquarters\BountyPlace;
use Smr\Pages\Player\Headquarters\Government;
use Smr\Pages\Player\Headquarters\MilitaryPaymentClaimProcessor;
use Smr\Pages\Player\Headquarters\Underground;
use Smr\Pages\Player\NewsReadCurrent;
use Smr\Pages\Player\Rankings\AllianceDeaths;
use Smr\Pages\Player\Rankings\AllianceExperience;
use Smr\Pages\Player\Rankings\AllianceKills;
use Smr\Pages\Player\Rankings\AllianceProfit;
use Smr\Pages\Player\Rankings\AllianceVsAlliance;
use Smr\Pages\Player\Rankings\PlayerAssists;
use Smr\Pages\Player\Rankings\PlayerDeaths;
use Smr\Pages\Player\Rankings\PlayerExperience;
use Smr\Pages\Player\Rankings\PlayerKills;
use Smr\Pages\Player\Rankings\PlayerNpcKills;
use Smr\Pages\Player\Rankings\PlayerProfit;
use Smr\Pages\Player\Rankings\RaceDeaths;
use Smr\Pages\Player\Rankings\RaceExperience;
use Smr\Pages\Player\Rankings\RaceKills;
use Smr\Pages\Player\Rankings\SectorKills;

/**
 * Creates menu navigation bars.
 */
class Menu {

	public static function headquarters(int $locationTypeID): void {
		$gameID = Session::getInstance()->getGameID();

		$links = [];
		$location = Location::getLocation($gameID, $locationTypeID);
		if ($location->isHQ()) {
			$links[] = [Government::class, 'Government'];
			$links[] = [MilitaryPaymentClaimProcessor::class, 'Claim Military Payment'];
		} elseif ($location->isUG()) {
			$links[] = [Underground::class, 'Underground'];
		} else {
			throw new Exception('Location is not HQ or UG: ' . $location->getName());
		}

		// No bounties in Semi Wars games
		if (!Game::getGame($gameID)->isGameType(Game::GAME_TYPE_SEMI_WARS)) {
			$links[] = [BountyClaimProcessor::class, 'Claim Bounty'];
			$links[] = [BountyPlace::class, 'Place Bounty'];
		}

		$menuItems = [];
		foreach ($links as [$class, $text]) {
			$container = new $class($locationTypeID);
			$menuItems[] = [
				'Link' => $container->href(),
				'Text' => $text,
			];
		}
		$template = Template::getInstance();
		$template->assign('MenuItems', $menuItems);
	}

	public static function planetList(int $alliance_id, int $selected_index): void {
		$menuItems = [];
		$menuItems[] = ['Link' => Globals::getPlanetListHREF($alliance_id), 'Text' => 'Defense'];
		$menuItems[] = ['Link' => Globals::getPlanetListFinancialHREF($alliance_id), 'Text' => 'Financial'];
		// make the selected index bold
		$boldItem =& $menuItems[$selected_index]['Text'];
		$boldItem = '<span class="bold">' . $boldItem . '</span>';

		$template = Template::getInstance();
		$template->assign('MenuItems', $menuItems);
	}

	public static function alliance(int $alliance_id): void {
		$db = Database::getInstance();
		$player = Session::getInstance()->getPlayer();

		$in_alliance = ($alliance_id == $player->getAllianceID() || in_array($player->getAccountID(), Globals::getHiddenPlayers()));

		// Some pages are visible to all alliance members
		$canReadMb = $in_alliance;
		$canReadMotd = $in_alliance;
		$canSeePlanetList = $in_alliance;

		// Check if player has permissions through an alliance treaty
		if (!$in_alliance) {
			$dbResult = $db->read('SELECT mb_read, mod_read, planet_land FROM alliance_treaties
							WHERE (alliance_id_1 = :alliance_id OR alliance_id_1 = :player_alliance_id)
							AND (alliance_id_2 = :alliance_id OR alliance_id_2 = :player_alliance_id)
							AND game_id = :game_id
							AND (mb_read = 1 OR mod_read = 1 OR planet_land = 1) AND official = \'TRUE\'', [
				'alliance_id' => $db->escapeNumber($alliance_id),
				'player_alliance_id' => $db->escapeNumber($player->getAllianceID()),
				'game_id' => $db->escapeNumber($player->getGameID()),
			]);
			if ($dbResult->hasRecord()) {
				$dbRecord = $dbResult->record();
				$canReadMb = $dbRecord->getBoolean('mb_read');
				$canReadMotd = $dbRecord->getBoolean('mod_read');
				$canSeePlanetList = $dbRecord->getBoolean('planet_land');
			}
		}

		$role_id = $player->getAllianceRole($alliance_id);
		$dbResult = $db->read('SELECT send_alliance_msg FROM alliance_has_roles WHERE alliance_id = :alliance_id AND game_id = :game_id AND role_id = :role_id', [
			'alliance_id' => $db->escapeNumber($alliance_id),
			'game_id' => $db->escapeNumber($player->getGameID()),
			'role_id' => $db->escapeNumber($role_id),
		]);
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
			$menuItems[] = ['Link' => Globals::getAllianceOptionsHREF(), 'Text' => 'Options'];
		}
		$menuItems[] = ['Link' => Globals::getAllianceListHREF(), 'Text' => 'List Alliances'];
		$menuItems[] = ['Link' => Globals::getAllianceNewsHREF($player->getGameID(), $alliance_id), 'Text' => 'View News'];

		$template = Template::getInstance();
		$template->assign('MenuItems', $menuItems);
	}

	public static function galacticPost(): void {
		$player = Session::getInstance()->getPlayer();

		$menuItems = [];
		$menuItems[] = ['Link' => (new CurrentEditionProcessor())->href(), 'Text' => 'Current Edition'];
		$menuItems[] = ['Link' => (new PastEditionSelect($player->getGameID()))->href(), 'Text' => 'Past Editions'];
		$menuItems[] = ['Link' => (new ArticleWrite())->href(), 'Text' => 'Write an article'];
		if ($player->isGPEditor()) {
			$menuItems[] = ['Link' => (new EditorOptions())->href(), 'Text' => 'Editor Options'];
		}

		$template = Template::getInstance();
		$template->assign('MenuItems', $menuItems);
	}

	public static function messages(): void {
		$player = Session::getInstance()->getPlayer();

		$menuItems = [];
		$menuItems[] = ['Link' => Globals::getViewMessageBoxesHREF(), 'Text' => 'View Messages'];
		$menuItems[] = ['Link' => Globals::getSendGlobalMessageHREF(), 'Text' => 'Send Global Message'];
		if ($player->isOnCouncil()) {
			$menuItems[] = ['Link' => Globals::getSendCouncilMessageHREF($player->getRaceID()), 'Text' => 'Send Council Message'];
		}
		$menuItems[] = ['Link' => Globals::getManageBlacklistHREF(), 'Text' => 'Manage Blacklist'];

		$template = Template::getInstance();
		$template->assign('MenuItems', $menuItems);
	}

	public static function combatLog(): void {
		$menuItems = [];

		foreach (CombatLogType::cases() as $type) {
			$container = new CombatLogList($type);
			$menuItems[] = ['Link' => $container->href(), 'Text' => $type->name];
		}

		$template = Template::getInstance();
		$template->assign('MenuItems', $menuItems);
	}

	public static function trader(): void {
		$player = Session::getInstance()->getPlayer();

		$template = Template::getInstance();
		$template->assign('MenuItems', [
						['Link' => Globals::getTraderStatusHREF(), 'Text' => 'Trader Status'],
						['Link' => Globals::getPlanetListHREF($player->getAllianceID()), 'Text' => 'Planets'],
						['Link' => Globals::getAllianceHREF($player->getAllianceID()), 'Text' => 'Alliance'],
						['Link' => Globals::getCouncilHREF($player->getRaceID()), 'Text' => 'Politics'],
						['Link' => Globals::getTraderRelationsHREF(), 'Text' => 'Relations'],
						['Link' => Globals::getTraderBountiesHREF(), 'Text' => 'Bounties']]);
	}

	/*
	 * $active_level1 - the id of the active menu on the first level
	 * $active_level1 - the id of the active menu on the second level
	 */
	public static function rankings(int $active_level1 = 0, int $active_level2 = 0): void {

		$menu = [];

		// player rankings
		$menu_item = [];
		$menu_item['entry'] = create_link(new PlayerExperience(), 'Player Rankings', 'nav');

		$menu_subitem = [];
		$menu_subitem[] = create_link(new PlayerExperience(), 'Experience', 'nav');
		$menu_subitem[] = create_link(new PlayerProfit(), 'Profit', 'nav');
		$menu_subitem[] = create_link(new PlayerKills(), 'Kills', 'nav');
		$menu_subitem[] = create_link(new PlayerDeaths(), 'Deaths', 'nav');
		$menu_subitem[] = create_link(new PlayerAssists(), 'Assists', 'nav');
		$menu_subitem[] = create_link(new PlayerNpcKills(), 'NPC Kills', 'nav');

		$menu_item['submenu'] = $menu_subitem;

		$menu[] = $menu_item;

		// alliance rankings
		$menu_item = [];
		$menu_item['entry'] = create_link(new AllianceExperience(), 'Alliance Rankings', 'nav');

		$menu_subitem = [];
		$menu_subitem[] = create_link(new AllianceExperience(), 'Experience', 'nav');
		$menu_subitem[] = create_link(new AllianceProfit(), 'Profit', 'nav');
		$menu_subitem[] = create_link(new AllianceKills(), 'Kills', 'nav');
		$menu_subitem[] = create_link(new AllianceDeaths(), 'Deaths', 'nav');
		$menu_subitem[] = create_link(new AllianceVsAlliance(), 'Versus', 'nav');

		$menu_item['submenu'] = $menu_subitem;

		$menu[] = $menu_item;

		// racial rankings
		$menu_item = [];
		$menu_item['entry'] = create_link(new RaceExperience(), 'Racial Standings', 'nav');

		$menu_subitem = [];
		$menu_subitem[] = create_link(new RaceExperience(), 'Experience', 'nav');
		$menu_subitem[] = create_link(new RaceKills(), 'Kills', 'nav');
		$menu_subitem[] = create_link(new RaceDeaths(), 'Deaths', 'nav');

		$menu_item['submenu'] = $menu_subitem;

		$menu[] = $menu_item;

		// sector rankings
		$menu_item = [];
		$menu_item['entry'] = create_link(new SectorKills(), 'Sector Kills', 'nav');
		$menu[] = $menu_item;

		create_sub_menu($menu, $active_level1, $active_level2);
	}

	public static function bank(): void {
		$player = Session::getInstance()->getPlayer();

		$links = [];
		$links[] = [new PersonalBank(), 'Personal Account'];
		if ($player->hasAlliance()) {
			$links[] = [new AllianceBank($player->getAllianceID()), 'Alliance Account'];
		}
		$links[] = [new AnonBank(), 'Anonymous Account'];

		$menuItems = [];
		foreach ($links as [$container, $label]) {
			$menuItems[] = [
				'Link' => $container->href(),
				'Text' => $label,
			];
		}

		$template = Template::getInstance();
		$template->assign('MenuItems', $menuItems);
	}

	public static function council(int $race_id): void {
		$player = Session::getInstance()->getPlayer();

		$container = new ViewCouncil($race_id);
		$menu_items = [];
		$menu_items[] = [
			'Link' => $container->href(),
			'Text' => 'View Council',
		];

		$container = new PoliticalStatus($race_id);
		$menu_items[] = [
			'Link' => $container->href(),
			'Text' => 'Political Status',
		];

		$container = new MessageCouncil($race_id);
		$menu_items[] = [
			'Link' => $container->href(),
			'Text' => 'Send Message',
		];

		if ($player->getRaceID() == $race_id) {
			if ($player->isOnCouncil()) {
				$container = new VotingCenter();
				$menu_items[] = [
					'Link' => $container->href(),
					'Text' => 'Voting Center',
				];
			}
			if ($player->isPresident()) {
				$container = new Embassy();
				$menu_items[] = [
					'Link' => $container->href(),
					'Text' => 'Embassy',
				];
			}
		}

		$template = Template::getInstance();
		$template->assign('MenuItems', $menu_items);
	}

	public static function bar(int $locationID): void {
		$template = Template::getInstance();
		$template->assign('MenuItems', [
			['Link' => (new BarMain($locationID))->href(), 'Text' => 'Bar Main'],
			['Link' => (new LottoBuyTicket($locationID))->href(), 'Text' => 'Lotto'],
			['Link' => (new PlayBlackjackBet($locationID))->href(), 'Text' => 'BlackJack'],
		]);
	}

	public static function news(int $gameID): void {
		$session = Session::getInstance();

		$menuItems = [];
		if ($session->getGameID() == $gameID) {
			$menuItems[] = [
				'Link' => (new NewsReadCurrent())->href(),
				'Text' => 'Read Current News',
			];
		}
		$menuItems[] = [
			'Link' => (new NewsReadArchives($gameID))->href(),
			'Text' => 'Read Latest News',
		];
		$menuItems[] = [
			'Link' => (new NewsReadAdvanced($gameID))->href(),
			'Text' => 'Advanced News',
		];

		$template = Template::getInstance();
		$template->assign('MenuItems', $menuItems);
	}

	public static function navigation(AbstractPlayer $player): void {
		$menuItems = [];
		$menuItems[] = ['Link' => Globals::getPlotCourseHREF(), 'Text' => 'Plot A Course'];
		if (!$player->isLandedOnPlanet()) {
			$menuItems[] = ['Link' => Globals::getLocalMapHREF(), 'Text' => 'Local Map'];
		}
		$menuItems[] = ['Link' => 'map_galaxy.php" target="gal_map', 'Text' => 'Galaxy Map'];

		$template = Template::getInstance();
		$template->assign('MenuItems', $menuItems);
	}

}

/**
 * @param array<array<string, mixed>> $menu
 */
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

	$template = Template::getInstance();
	$template->unassign('MenuItems');
	$template->assign('SubMenuBar', $return);
}
