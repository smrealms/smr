<?php
require_once('SmrSession.class.inc');
require_once('SmrMySqlDatabase.class.inc');
class Globals
{
	protected static $HIDDEN_PLAYERS = null;
	protected static $PLANET_BUILDINGS = null;
	protected static $LEVEL_REQUIREMENTS = null;
	protected static $RACES = null;
	protected static $GOODS = null;
	protected static $HARDWARE_TYPES = null;
	protected static $GAMES = array();
	protected static $BETA_OPEN = null;
	protected static $FEATURE_REQUEST_OPEN = null;
	protected static $RACE_RELATIONS = array();
	protected static $USER_RANKINGS = null;
	protected static $db = null;
	
	private static $compatibilityDatabases;
	
	private function __construct()
	{
	}
	
	protected static function initialiseDatabase()
	{
		if(self::$db==null)
			self::$db = new SmrMySqlDatabase();
	}
	
	public static function &getHiddenPlayers()
	{
		if(self::$HIDDEN_PLAYERS==null)
		{
			self::initialiseDatabase();
			$db->query('SELECT account_id FROM hidden_players');
			self::$HIDDEN_PLAYERS = array(0);//stop errors
			while ($db->nextRecord())
				self::$HIDDEN_PLAYERS[] = $db->getField('account_id');
		}
		return self::$HIDDEN_PLAYERS;
	}

	public static function &getPlanetBuildings()
	{
		if(self::$PLANET_BUILDINGS==null) //use cached if available
		{
			self::initialiseDatabase();
			$db2 = new SmrMySqlDatabase();
			$PLANET_BUILDINGS = array();
			self::$db->query('SELECT * FROM planet_construction');
			while(self::$db->nextRecord())
			{
				$buildingID = self::$db->getField('construction_id');
				$PLANET_BUILDINGS[$buildingID]['ConstructionID'] = $buildingID;
				$PLANET_BUILDINGS[$buildingID]['Name'] = self::$db->getField('construction_name');
				$PLANET_BUILDINGS[$buildingID]['Description'] = self::$db->getField('construction_description');
//				$PLANET_BUILDINGS[$buildingID]['Area'] = array(self::$db->getField('area'),self::$db->getField('metals'),self::$db->getField('energy'));
				$PLANET_BUILDINGS[$buildingID]['Max Amount'] = self::$db->getField('max_construction');
				
				$PLANET_BUILDINGS[$buildingID]['Build Time'] = 0;
				$db2->query('SELECT * FROM planet_cost_time WHERE construction_id=' . $buildingID);
				while($db2->nextRecord())
				{
					$PLANET_BUILDINGS[$buildingID]['Build Time'] += (int)$db2->getField('amount');
				}
				
				$PLANET_BUILDINGS[$buildingID]['Credit Cost'] = 0;
				$db2->query('SELECT * FROM planet_cost_credits WHERE construction_id=' . $buildingID);
				while($db2->nextRecord())
				{
					$PLANET_BUILDINGS[$buildingID]['Credit Cost'] += (int)$db2->getField('amount');
				}
				
				$PLANET_BUILDINGS[$buildingID]['Goods'] = array();
				$db2->query('SELECT * FROM planet_cost_good WHERE construction_id=' . $buildingID);
				while($db2->nextRecord())
				{
					$PLANET_BUILDINGS[$buildingID]['Goods'][$db2->getField('good_id')] = (int)$db2->getField('amount');
				}
				
//				global $PLANET_RESEARCH;
//				$PLANET_BUILDINGS[$buildingID]['Research'] = array();
//				$researchResult = query('SELECT * FROM planet_building_research_required WHERE construction_id=' . $buildingID);
//				while($researchRow = next_record($researchResult))
//				{
//					$PLANET_BUILDINGS[$buildingID]['Research'][$PLANET_RESEARCH[$researchRow['research_id']]['Name']] = $researchRow['amount'];
//				}
				
//				$PLANET_BUILDINGS[$buildingID]['Unlocks'] = array();
//				$unlocksResult = query('SELECT * FROM planet_building_unlocks WHERE construction_id=' . $buildingID);
//				while($unlocksRow = next_record($unlocksResult))
//				{
//					$PLANET_BUILDINGS[$buildingID]['Unlocks'][$unlocksRow['unlock_id']] = $unlocksRow['amount'];
//				}
				
			}
			self::$PLANET_BUILDINGS =& $PLANET_BUILDINGS;
		}
		return self::$PLANET_BUILDINGS;
	}
	
	public static function &getLevelRequirements()
	{
		if(self::$LEVEL_REQUIREMENTS==null) //use cached if available
		{
			self::initialiseDatabase();
			self::$LEVEL_REQUIREMENTS = array();
			
			// determine user level
			self::$db->query('SELECT * FROM level ORDER BY level_id ASC');
			while (self::$db->nextRecord())
			{
				self::$LEVEL_REQUIREMENTS[(int)self::$db->getField('level_id')] = array(
																				'ID' => (int)self::$db->getField('level_id'),
																				'Name' => self::$db->getField('level_name'),
																				'Requirement' => (int)self::$db->getField('requirement')
																				);
			}
		}
		return self::$LEVEL_REQUIREMENTS;
	}
	
	public static function &getRaces()
	{
		if(self::$RACES==null) //use cached if available
		{
			self::initialiseDatabase();
			self::$RACES = array();
			
			// determine user level
			self::$db->query('SELECT race_id,race_name,race_description FROM race ORDER BY race_id');
			while(self::$db->nextRecord())
			{
				self::$RACES[self::$db->getField('race_id')] = array(
																'Race ID' => (int)self::$db->getField('race_id'),
																'Race Name' => self::$db->getField('race_name'),
																'Description' => self::$db->getField('race_description'),
																);
			}
		}
		return self::$RACES;
	}
	
	public static function getRaceName($raceID)
	{
		$races =& Globals::getRaces();
		return $races[$raceID]['Race Name'];
	}
	
	public static function &getGoods()
	{
		if(self::$GOODS==null) //use cached if available
		{
			self::initialiseDatabase();
			self::$GOODS = array();
			
			// determine user level
			self::$db->query('SELECT * FROM good ORDER BY good_id');
			while(self::$db->nextRecord())
			{
				self::$GOODS[self::$db->getField('good_id')] = array(
																'Type' => 'Good',
																'ID' => (int)self::$db->getField('good_id'),
																'Name' => self::$db->getField('good_name'),
																'Max' => (int)self::$db->getField('max_amount'),
																'BasePrice' => (int)self::$db->getField('base_price'),
																'Class' => (int)self::$db->getField('good_class'),
																'ImageLink' => 'images/port/' . self::$db->getField('good_id') . '.png',
																'AlignRestriction' => (int)self::$db->getField('align_restriction')
															);
			}
		}
		return self::$GOODS;
	}
	public static function &getGood($goodID)
	{
		$goods =& self::getGoods();
		return $goods[$goodID];
	}
	public static function getGoodName($goodID)
	{
		if($goodID==GOOD_NOTHING)
			return 'Nothing';
		$goods =& self::getGoods();
		return $goods[$goodID]['Name'];
	}
	
	public static function &getHardwareTypes($hardwareTypeID=false)
	{
		if(self::$HARDWARE_TYPES==null) //use cached if available
		{
			self::initialiseDatabase();
			self::$HARDWARE_TYPES = array();
			
			// determine user level
			self::$db->query('SELECT * FROM hardware_type ORDER BY hardware_type_id');
			while(self::$db->nextRecord())
			{
				self::$HARDWARE_TYPES[self::$db->getField('hardware_type_id')] = array(
																			'Type' => 'Hardware',
																			'ID' => (int)self::$db->getField('hardware_type_id'),
																			'Name' => self::$db->getField('hardware_name'),
																			'Cost' => (int)self::$db->getField('cost')
																			);
			}
		}
		if($hardwareTypeID===false)
			return self::$HARDWARE_TYPES;
		return self::$HARDWARE_TYPES[$hardwareTypeID];
	}
	
	public static function getHardwareName($hardwareTypeID)
	{
		$hardware =& self::getHardwareTypes();
		return $hardware[$hardwareTypeID]['Name'];
	}
	
	public static function getHardwareCost($hardwareTypeID)
	{
		$hardware =& self::getHardwareTypes();
		return $hardware[$hardwareTypeID]['Cost'];
	}
	
	public static function &getGameInfo($gameID = false)
	{
		if($gameID===false)
		{
			self::initialiseDatabase();
			self::$db->query('SELECT * FROM game');
			while(self::$db->nextRecord())
			{
				self::$GAMES[(int)self::$db->getField('game_id')] = array(
											'ID' => (int)self::$db->getField('game_id'),
											'Speed' => (float)self::$db->getField('game_speed'),
											'GameType' => (string)self::$db->getField('game_type'),
											'GameName' => (string)self::$db->getField('game_name'),
											'GameDescription' => (string)self::$db->getField('game_description'),
											'StartDate' => (string)self::$db->getField('start_date'),
											'EndDate' => (string)self::$db->getField('end_date'),
											'GameMaxPlayers' => (int)self::$db->getField('max_players'),
											'GameCreditsRequired' => (int)self::$db->getField('credits_needed'),
											'AllianceMaxPlayers' => (int)self::$db->getField('alliance_max_players'),
											'AllianceMaxVets' => (int)self::$db->getField('alliance_max_vets'),
											'IgnoreStats' => self::$db->getBoolean('ignore_stats'),
											'StartingCredits' => (int)self::$db->getField('starting_credits')
											);
			}
			return self::$GAMES;
		}
		if(!isset(self::$GAMES[$gameID]))
		{
			self::initialiseDatabase();
			$gameID = (int)$gameID;
			self::$db->query('SELECT * FROM game WHERE game_id = '.$gameID.' LIMIT 1');
			if(self::$db->nextRecord())
			{
				self::$GAMES[$gameID] = array(
											'ID' => (int)self::$db->getField('game_id'),
											'Speed' => (float)self::$db->getField('game_speed'),
											'GameType' => (string)self::$db->getField('game_type'),
											'GameName' => (string)self::$db->getField('game_name'),
											'GameDescription' => (string)self::$db->getField('game_description'),
											'StartDate' => (string)self::$db->getField('start_date'),
											'EndDate' => (string)self::$db->getField('end_date'),
											'GameMaxPlayers' => (int)self::$db->getField('max_players'),
											'GameCreditsRequired' => (int)self::$db->getField('credits_needed'),
											'AllianceMaxPlayers' => (int)self::$db->getField('alliance_max_players'),
											'AllianceMaxVets' => (int)self::$db->getField('alliance_max_vets'),
											'IgnoreStats' => self::$db->getBoolean('ignore_stats'),
											'StartingCredits' => (int)self::$db->getField('starting_credits')
											);
			}
			else
				self::$GAMES[$gameID] = false;
		}
		return self::$GAMES[$gameID];
	}
	
	public static function getGameDescription($gameID)
	{
		$gameInfo =& self::getGameInfo($gameID);
		return $gameInfo['GameDescription'];
	}
	
	public static function &getGameSpeed($gameID)
	{
		$gameInfo =& self::getGameInfo($gameID);
		return $gameInfo['Speed'];
	}
	
	public static function &getGameType($gameID)
	{
		$gameInfo =& self::getGameInfo($gameID);
		return $gameInfo['GameType'];
	}
	
	public static function &getGameName($gameID)
	{
		$gameInfo =& self::getGameInfo($gameID);
		return $gameInfo['GameName'];
	}
	
	public static function &getGameStartDate($gameID)
	{
		$gameInfo =& self::getGameInfo($gameID);
		return $gameInfo['StartDate'];
	}
	
	public static function &getGameEndDate($gameID)
	{
		$gameInfo =& self::getGameInfo($gameID);
		return $gameInfo['EndDate'];
	}
	
	public static function &getGameMaxPlayers($gameID)
	{
		$gameInfo =& self::getGameInfo($gameID);
		return $gameInfo['GameMaxPlayers'];
	}
	
	public static function &getGameCreditsRequired($gameID)
	{
		$gameInfo =& self::getGameInfo($gameID);
		return $gameInfo['GameCreditsRequired'];
	}
	
	public static function &getGameIgnoreStats($gameID)
	{
		$gameInfo =& self::getGameInfo($gameID);
		return $gameInfo['IgnoreStats'];
	}
	
	public static function &getAllianceMaxPlayers($gameID)
	{
		$gameInfo =& self::getGameInfo($gameID);
		return $gameInfo['AllianceMaxPlayers'];
	}
	
	public static function &getAllianceMaxVets($gameID)
	{
		$gameInfo =& self::getGameInfo($gameID);
		return $gameInfo['AllianceMaxVets'];
	}
	
	public static function &getStartingCredits($gameID)
	{
		$gameInfo =& self::getGameInfo($gameID);
		return $gameInfo['StartingCredits'];
	}
	
	public static function isBetaOpen()
	{
		if(self::$BETA_OPEN == null)
		{
			self::initialiseDatabase();
			self::$db->query('SELECT * FROM open_forms WHERE type=\'BETA\'');
			self::$db->nextRecord();
			
			self::$BETA_OPEN = self::$db->getField('open') == 'TRUE';
		}
		return self::$BETA_OPEN;
	}
	
	public static function isFeatureRequestOpen()
	{
		if(self::$FEATURE_REQUEST_OPEN == null)
		{
			self::initialiseDatabase();
			self::$db->query('SELECT * FROM open_forms WHERE type=\'FEATURE\'');
			self::$db->nextRecord();
			
			self::$FEATURE_REQUEST_OPEN = self::$db->getField('open') == 'TRUE';
		}
		return self::$FEATURE_REQUEST_OPEN;
	}
	
	public static function &getRaceRelations($gameID,$raceID)
	{
		if(!isset(self::$RACE_RELATIONS[$gameID]))
		{
			self::$RACE_RELATIONS[$gameID] = array();
		}
		
		if(!isset(self::$RACE_RELATIONS[$gameID][$raceID]))
		{
			self::initialiseDatabase();
			//get relations
			$RACES =& Globals::getRaces();
			self::$RACE_RELATIONS[$gameID][$raceID] = array();
			foreach ($RACES as $otherRaceID => $raceArray)
			{
				self::$RACE_RELATIONS[$gameID][$raceID][$otherRaceID] = 0;
			}
			self::$db->query('SELECT race_id_2,relation FROM race_has_relation WHERE race_id_1='.$raceID.' AND game_id='.$gameID.' LIMIT '.count($RACES));
			while (self::$db->nextRecord())
			{
				self::$RACE_RELATIONS[$gameID][$raceID][(int)self::$db->getField('race_id_2')] = (int)self::$db->getField('relation');
			}
		}
		return self::$RACE_RELATIONS[$gameID][$raceID];
	}
	
	public static function getUserRanking()
	{
		if(!isset(self::$USER_RANKINGS))
		{
			self::initialiseDatabase();
			self::$USER_RANKINGS = array();
			self::$db->query('SELECT * FROM user_rankings ORDER BY rank');
			while (self::$db->nextRecord())
			{
				self::$USER_RANKINGS[(int)self::$db->getField('rank')] = self::$db->getField('rank_name');
			}
		}
		return self::$USER_RANKINGS;
	}
	
	public static function getBetaApplyHREF()
	{
		$container = array();
		$container['url']		= 'skeleton.php';
		$container['body']		= 'beta_apply.php';
		return SmrSession::get_new_href($container);
	}
	
	public static function getFeatureRequestHREF()
	{
		$container = array();
		$container['url']		= 'skeleton.php';
		$container['body']		= 'feature_request.php';
		return SmrSession::get_new_href($container);
	}
	
	public static function getCurrentSectorHREF()
	{
		$container = array();
		$container['url']		= 'skeleton.php';
		$container['body']		= 'current_sector.php';
		return SmrSession::get_new_href($container);
	}
	
	public static function getLocalMapHREF()
	{
		return SmrSession::get_new_href(create_container('skeleton.php','map_local.php'));
	}
	
	public static function getPodScreenHREF()
	{
		return SmrSession::get_new_href(create_container('skeleton.php','death.php'));
	}
	
	public static function getBetaFunctionsHREF() //BETA
	{
		$container = array();
		$container['url']		= 'skeleton.php';
		$container['body']		= 'beta_functions.php';
		return SmrSession::get_new_href($container);
	}
	
	public static function getWeaponReorderHREF($weaponOrderID, $direction)
	{
		$container = create_container('skeleton.php','weapon_reorder.php');
		$container[$direction]	= $weaponOrderID;
		return SmrSession::get_new_href($container);
	}
	
	public static function getSmrFileCreateHREF($adminCreateGameID = false)
	{
		$container = create_container('skeleton.php','smr_file_create.php');
		$container['AdminCreateGameID'] = $adminCreateGameID;
		return SmrSession::get_new_href($container);
	}
	
	public static function getPlotCourseHREF()
	{
		return SmrSession::get_new_href(create_container('skeleton.php','course_plot.php'));
	}
	
	public static function getPlanetMainHREF()
	{
		return SmrSession::get_new_href(create_container('skeleton.php','planet_main.php'));
	}
	
	public static function getPlanetConstructionHREF()
	{
		return SmrSession::get_new_href(create_container('skeleton.php','planet_construction.php'));
	}
	
	public static function getPlanetDefensesHREF()
	{
		return SmrSession::get_new_href(create_container('skeleton.php','planet_defense.php'));
	}
	
	public static function getPlanetOwnershipHREF()
	{
		return SmrSession::get_new_href(create_container('skeleton.php','planet_ownership.php'));
	}
	
	public static function getPlanetStockpileHREF()
	{
		return SmrSession::get_new_href(create_container('skeleton.php','planet_stockpile.php'));
	}
	
	public static function getPlanetFinancesHREF()
	{
		return SmrSession::get_new_href(create_container('skeleton.php','planet_financial.php'));
	}
	
	public static function getAllianceHREF($allianceID = null)
	{
		if ($allianceID > 0)
			return self::getAllianceMotdHREF($allianceID);
		else
			return self::getAllianceListHREF();
	}
	
	public static function getAllianceRosterHREF($allianceID = null)
	{
		$container = create_container('skeleton.php', 'alliance_roster.php');
		if ($allianceID!=null)
			$container['alliance_id'] = $allianceID;
		return SmrSession::get_new_href($container);
	}
	
	public static function getAllianceListHREF()
	{
		$container = create_container('skeleton.php','alliance_list.php');
		$container['order'] = 'alliance_name';
		return SmrSession::get_new_href($container);
	}
	
	public static function getAllianceNewsHREF($allianceID)
	{
		$container = create_container('skeleton.php','news_read_advanced.php');
		$container['submit'] = 'Search For Alliance';
		$container['AllianceID'] = $allianceID;
		return SmrSession::get_new_href($container);
	}
	
	public static function getAllianceMotdHREF($allianceID)
	{
		$container = create_container('skeleton.php', 'alliance_mod.php');
		$container['alliance_id'] = $allianceID;
		return SmrSession::get_new_href($container);
	}
	
	public static function getAllianceMessageHREF($allianceID)
	{
		$container = create_container('skeleton.php', 'alliance_broadcast.php');
		$container['alliance_id'] = $allianceID;
		return SmrSession::get_new_href($container);
	}
	
	public static function getAllianceMessageBoardHREF($allianceID)
	{
		$container = create_container('skeleton.php', 'alliance_message.php');
		$container['alliance_id'] = $allianceID;
		return SmrSession::get_new_href($container);
	}
	
	public static function getAlliancePlanetsHREF($allianceID)
	{
		$container = create_container('skeleton.php', 'alliance_planets.php');
		$container['alliance_id'] = $allianceID;
		return SmrSession::get_new_href($container);
	}
	
	public static function getAllianceForcesHREF($allianceID)
	{
		$container = create_container('skeleton.php', 'alliance_forces.php');
		$container['alliance_id'] = $allianceID;
		return SmrSession::get_new_href($container);
	}
	
	public static function getAllianceOptionsHREF($allianceID)
	{
		$container = create_container('skeleton.php', 'alliance_option.php');
		$container['alliance_id'] = $allianceID;
		return SmrSession::get_new_href($container);
	}
	
	public static function getViewMessagesHREF()
	{
		return SmrSession::get_new_href(create_container('skeleton.php', 'message_view.php'));
	}
	
	public static function getSendGlobalMessageHREF()
	{
		return SmrSession::get_new_href(create_container('skeleton.php', 'message_send.php'));
	}
	
	public static function getManageBlacklistHREF()
	{
		return SmrSession::get_new_href(create_container('skeleton.php', 'message_blacklist.php'));
	}
	
	public static function getSendCouncilMessageHREF($raceID)
	{
		$container = create_container('skeleton.php', 'council_send_message.php');
		$container['race_id'] = $raceID;
		$container['folder_id'] = MSG_POLITICAL;
		return SmrSession::get_new_href($container);
	}
	
	public static function getTraderStatusHREF()
	{
		return SmrSession::get_new_href(create_container('skeleton.php', 'trader_status.php'));
	}
	
	public static function getTraderPlanetHREF()
	{
		return SmrSession::get_new_href(create_container('skeleton.php', 'trader_planet.php'));
	}
	
	public static function getCouncilHREF()
	{
		return SmrSession::get_new_href(create_container('skeleton.php', 'council_list.php'));
	}
	
	public static function getTraderRelationsHREF()
	{
		return SmrSession::get_new_href(create_container('skeleton.php', 'trader_relations.php'));
	}
	
	public static function getTraderBountiesHREF()
	{
		return SmrSession::get_new_href(create_container('skeleton.php', 'trader_bounties.php'));
	}
	
	public static function getBarMainHREF()
	{
		$container = create_container('skeleton.php', 'bar_main.php');
		$container['script'] = 'bar_opening.php';
		return SmrSession::get_new_href($container);
	}
	
	public static function getLottoHREF()
	{
		$container = create_container('skeleton.php', 'bar_main.php');
		$container['script'] = 'bar_gambling_lotto.php';
		$container['action'] = 'lotto';
		return SmrSession::get_new_href($container);
	}
	
	public static function getBlackjackHREF()
	{
		$container = create_container('skeleton.php', 'bar_main.php');
		$container['script'] = 'bar_gambling_bet.php';
		$container['action'] = 'blackjack';
		return SmrSession::get_new_href($container);
	}
	
	public static function getAvailableTemplates()
	{
		return array('Default' => array('Default'),
			'Freon22' => array('Default','Blue','ClassicGreen','None'));
	}
	
	public static function getAvailableColourSchemes($templateName)
	{
		$templates = self::getAvailableTemplates();
		return $templates[$templateName];
	}
	
	public static function getCompatibilityDatabases($dbType = false)
	{
		if(!isset(self::$compatibilityDatabases))
		{
			global $COMPATIBILITY_DATABASES;
			self::$compatibilityDatabases = $COMPATIBILITY_DATABASES;
		}
		if($dbType===false)
			return self::$compatibilityDatabases;
		return self::$compatibilityDatabases[$dbType];
	}
}


function get_file_loc($file_name, $exists = 1, $overrideGameDir = false)
{
	global $g_id;
	static $stored_dir;
	if(isset($stored_dir))
	{
		$game_dir = $stored_dir;
	}
	else
	{
		if (SmrSession::$game_id > 0 || $g_id > 0)
		{
			if ($g_id > 0) $game_id = $g_id;
			else $g_id = SmrSession::$game_id;
			$stored_dir = Globals::getGameType($g_id) . '/';
			$game_dir = $stored_dir;
		} else $game_dir = 'Default/';
	}
	if($game_dir=='1.2/')
		$game_dir='Default/';
	if ($overrideGameDir !== false)
	{
		if($overrideGameDir[strlen($overrideGameDir)]!='/') $overrideGameDir .= '/';
		if(file_exists(ENGINE . $overrideGameDir . $file_name)) return ENGINE . $overrideGameDir . $file_name;
		if(file_exists(LIB . $overrideGameDir . $file_name)) return LIB . $overrideGameDir . $file_name;
		if(file_exists(ADMIN . $overrideGameDir . $file_name)) return ADMIN . $overrideGameDir . $file_name;
	}
	if (file_exists(LIB . $game_dir . $file_name)) return LIB . $game_dir . $file_name;
	if (file_exists(ENGINE . $game_dir . $file_name)) return ENGINE . $game_dir . $file_name;
	if (file_exists(ADMIN . $game_dir . $file_name)) return ADMIN . $game_dir . $file_name;
	
	if (file_exists(LIB . 'Default/' . $file_name)) return LIB . 'Default/' . $file_name;
	if (file_exists(ENGINE . 'Default/' . $file_name)) return ENGINE . 'Default/' . $file_name;
	if (file_exists(ADMIN . 'Default/' . $file_name)) return ADMIN . 'Default/' . $file_name;
	
	if (file_exists(WWW . $file_name)) return WWW . $file_name;
	if ($exists == 1) return ENGINE . 'empty.php';
	return $file_name;
}
?>