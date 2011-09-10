<?php
require_once('SmrSession.class.inc');
require_once('SmrMySqlDatabase.class.inc');
require_once('SmrGame.class.inc');
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
	
	public static function canAccessPage($pageName, AbstractSmrPlayer &$player, array $extraInfo)
	{
		switch($pageName)
		{
			case 'AllianceMOTD':
				if($player->getAllianceID() != $extraInfo['AllianceID'])
				{
					logException(new Exception('Tried to access page without permission.'));
					create_error('You cannot access this page.');
				}
			break;
		}
	}
	
	public static function &getHiddenPlayers()
	{
		if(self::$HIDDEN_PLAYERS==null)
		{
			self::initialiseDatabase();
			self::$db->query('SELECT account_id FROM hidden_players');
			self::$HIDDEN_PLAYERS = array(0);//stop errors
			while (self::$db->nextRecord())
				self::$HIDDEN_PLAYERS[] = self::$db->getField('account_id');
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
				$PLANET_BUILDINGS[$buildingID]['ExpGain'] = self::$db->getField('exp_gain');
				
				$PLANET_BUILDINGS[$buildingID]['Build Time'] = 0;
				$db2->query('SELECT * FROM planet_cost_time WHERE construction_id=' . $buildingID);
				while($db2->nextRecord())
				{
					$PLANET_BUILDINGS[$buildingID]['Build Time'] += $db2->getInt('amount');
				}
				
				$PLANET_BUILDINGS[$buildingID]['Credit Cost'] = 0;
				$db2->query('SELECT * FROM planet_cost_credits WHERE construction_id=' . $buildingID);
				while($db2->nextRecord())
				{
					$PLANET_BUILDINGS[$buildingID]['Credit Cost'] += $db2->getInt('amount');
				}
				
				$PLANET_BUILDINGS[$buildingID]['Goods'] = array();
				$db2->query('SELECT * FROM planet_cost_good WHERE construction_id=' . $buildingID);
				while($db2->nextRecord())
				{
					$PLANET_BUILDINGS[$buildingID]['Goods'][$db2->getInt('good_id')] = $db2->getInt('amount');
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
				self::$LEVEL_REQUIREMENTS[self::$db->getInt('level_id')] = array(
																				'ID' => self::$db->getInt('level_id'),
																				'Name' => self::$db->getField('level_name'),
																				'Requirement' => self::$db->getInt('requirement')
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
				self::$RACES[self::$db->getInt('race_id')] = array(
																'Race ID' => self::$db->getInt('race_id'),
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
																'ID' => self::$db->getInt('good_id'),
																'Name' => self::$db->getField('good_name'),
																'Max' => self::$db->getInt('max_amount'),
																'BasePrice' => self::$db->getInt('base_price'),
																'Class' => self::$db->getInt('good_class'),
																'ImageLink' => 'images/port/' . self::$db->getField('good_id') . '.png',
																'AlignRestriction' => self::$db->getInt('align_restriction')
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
																			'ID' => self::$db->getInt('hardware_type_id'),
																			'Name' => self::$db->getField('hardware_name'),
																			'Cost' => self::$db->getInt('cost')
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
	
	public static function isValidGame($gameID)
	{
		return self::getGameInfo($gameID)!==false;
	}
	
	public static function &getGameInfo($gameID = false)
	{
		if($gameID===false)
		{
			self::initialiseDatabase();
			self::$db->query('SELECT game_id FROM game');
			while(self::$db->nextRecord())
			{
				$allGames[self::$db->getInt('game_id')] = self::getGameInfo(self::$db->getInt('game_id'));
			}
			return $allGames;
		}
		try
		{
			$gameID = (int)$gameID;
			$game =& SmrGame::getGame($gameID);
			$gameInfo = array(
					'ID' => $game->getGameID(),
					'Speed' => $game->getGameSpeed(),
					'GameType' => $game->getGameType(),
					'GameName' => $game->getName(),
					'GameDescription' => $game->getDescription(),
					'StartDate' => $game->getStartDate(),
					'StartTurnsDate' => $game->getStartTurnsDate(),
					'EndDate' => $game->getEndDate(),
					'MaxTurns' => $game->getMaxTurns(),
					'StartTurnHours' => $game->getStartTurnHours(),
					'GameMaxPlayers' => $game->getMaxPlayers(),
					'GameCreditsRequired' => $game->getCreditsNeeded(),
					'AllianceMaxPlayers' => $game->getAllianceMaxPlayers(),
					'AllianceMaxVets' => $game->getAllianceMaxVets(),
					'IgnoreStats' => $game->isIgnoreStats(),
					'StartingCredits' => $game->getStartingCredits()
					);
		}
		catch (Exception $e)
		{
			if(stripos($e->getMessage(),'No such game')!==false)
			{
				$gameInfo = false;
			}
			else
			{
				throw $e;
			}
		}
		return $gameInfo;
	}
	
	public static function getGameDescription($gameID)
	{
		return SmrGame::getGame($gameID)->getDescription();
	}
	
	public static function getGameSpeed($gameID)
	{
		return SmrGame::getGame($gameID)->getGameSpeed();
	}
	
	public static function getGameType($gameID)
	{
		if(self::isValidGame($gameID))
		{
			return SmrGame::getGame($gameID)->getGameType();
		}
		return 0;
	}
	
	public static function getGameName($gameID)
	{
		return SmrGame::getGame($gameID)->getName();
	}
	
	public static function getGameStartDate($gameID)
	{
		return SmrGame::getGame($gameID)->getStartDate();
	}
	
	public static function getGameEndDate($gameID)
	{
		return SmrGame::getGame($gameID)->getEndDate();
	}
	
	public static function getGameMaxTurns($gameID)
	{
		return SmrGame::getGame($gameID)->getMaxTurns();
	}
	
	public static function getGameMaxPlayers($gameID)
	{
		return SmrGame::getGame($gameID)->getMaxPlayers();
	}
	
	public static function getGameCreditsRequired($gameID)
	{
		return SmrGame::getGame($gameID)->getCreditsNeeded();
	}
	
	public static function getGameIgnoreStats($gameID)
	{
		return SmrGame::getGame($gameID)->isIgnoreStats();
	}
	
	public static function getAllianceMaxPlayers($gameID)
	{
		return SmrGame::getGame($gameID)->getAllianceMaxPlayers();
	}
	
	public static function getAllianceMaxVets($gameID)
	{
		return SmrGame::getGame($gameID)->getAllianceMaxVets();
	}
	
	public static function getStartingCredits($gameID)
	{
		return SmrGame::getGame($gameID)->getStartingCredits();
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
				self::$RACE_RELATIONS[$gameID][$raceID][self::$db->getInt('race_id_2')] = self::$db->getInt('relation');
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
				self::$USER_RANKINGS[self::$db->getInt('rank')] = self::$db->getField('rank_name');
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
		return SmrSession::get_new_href(create_container('skeleton.php','current_sector.php'));
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
		return SmrSession::get_new_href(create_container('skeleton.php','beta_functions.php'));
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
	
	public static function getAllianceRosterHREF($allianceID = null, $sortKey = 'getExperience', $sortDesc = true)
	{
		$container = create_container('skeleton.php', 'alliance_roster.php');
		if ($allianceID!=null)
		{
			$container['alliance_id'] = $allianceID;
		}
		$container['SortKey'] = $sortKey;
		$container['SortDesc'] = $sortDesc;
		return SmrSession::get_new_href($container);
	}
	
	public static function getAllianceListHREF()
	{
		return SmrSession::get_new_href(create_container('skeleton.php', 'alliance_list.php', array('order'=>'alliance_name')));
	}
	
	public static function getAllianceNewsHREF($allianceID)
	{
		return SmrSession::get_new_href(create_container('skeleton.php', 'news_read_advanced.php', array('AllianceID'=>$allianceID, 'submit' => 'Search For Alliance')));
	}
	
	public static function getAllianceMotdHREF($allianceID)
	{
		return SmrSession::get_new_href(create_container('skeleton.php', 'alliance_mod.php', array('alliance_id'=>$allianceID)));
	}
	
	public static function getAllianceMessageHREF($allianceID)
	{
		return SmrSession::get_new_href(create_container('skeleton.php', 'alliance_broadcast.php', array('alliance_id'=>$allianceID)));
	}
	
	public static function getAllianceMessageBoardHREF($allianceID)
	{
		return SmrSession::get_new_href(create_container('skeleton.php', 'alliance_message.php', array('alliance_id'=>$allianceID)));
	}
	
	public static function getAlliancePlanetsHREF($allianceID)
	{
		return SmrSession::get_new_href(create_container('skeleton.php', 'alliance_planets.php', array('alliance_id'=>$allianceID)));
	}
	
	public static function getAllianceForcesHREF($allianceID)
	{
		return SmrSession::get_new_href(create_container('skeleton.php', 'alliance_forces.php', array('alliance_id'=>$allianceID)));
	}
	
	public static function getAllianceOptionsHREF($allianceID)
	{
		return SmrSession::get_new_href(create_container('skeleton.php', 'alliance_option.php', array('alliance_id'=>$allianceID)));
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
	
	public static function getPoliticsHREF()
	{
		return SmrSession::get_new_href(create_container('skeleton.php', 'council_list.php'));
	}
	
	public static function getBarMainHREF()
	{
		$container = create_container('skeleton.php', 'bar_main.php');
		$container['script'] = 'bar_opening.php';
		return SmrSession::get_new_href($container);
	}
	
	public static function getBarBuyDrinkHREF()
	{
		$container = create_container('skeleton.php', 'bar_main.php');
		$container['script'] = 'bar_buy_drink_processing.php';
		$container['action'] = 'drink';
		return SmrSession::get_new_href($container);
	}
	
	public static function getBarBuyWaterHREF()
	{
		$container = create_container('skeleton.php', 'bar_main.php');
		$container['script'] = 'bar_buy_drink_processing.php';
		$container['action'] = 'water';
		return SmrSession::get_new_href($container);
	}
	
	public static function getBarLottoClaimHREF()
	{
		$container = create_container('skeleton.php', 'bar_main.php');
		$container['script'] = 'bar_lotto_claim.php';
		$container['action'] = 'lotto_claim';
		return SmrSession::get_new_href($container);
	}
	
	public static function getBarLottoPlayHREF()
	{
		$container = create_container('skeleton.php', 'bar_main.php');
		$container['script'] = 'bar_gambling_lotto.php';
		$container['action'] = 'lotto';
		return SmrSession::get_new_href($container);
	}
	
	public static function getBarBlackjackHREF()
	{
		$container = create_container('skeleton.php', 'bar_main.php');
		$container['script'] = 'bar_gambling_bet.php';
		$container['action'] = 'blackjack';
		return SmrSession::get_new_href($container);
	}
	
	public static function getBarTickerHREF()
	{
		$container = create_container('skeleton.php', 'bar_main.php');
		$container['script'] = 'bar_ticker_buy.php';
		$container['action'] = 'system';
		return SmrSession::get_new_href($container);
	}
	
	public static function getBuyTickerHREF()
	{
		$container = create_container('skeleton.php', 'bar_ticker_buy.php');
		$container['action'] = 'system';
		return SmrSession::get_new_href($container);
	}
	
	public static function getBarGalMapHREF()
	{
		$container = create_container('skeleton.php', 'bar_main.php');
		$container['script'] = 'bar_galmap_buy.php';
		$container['action'] = 'map';
		return SmrSession::get_new_href($container);
	}
	
	public static function getBuyGalMapHREF()
	{
		$container = create_container('skeleton.php', 'bar_galmap_buy.php');
		$container['action'] = 'map';
		return SmrSession::get_new_href($container);
	}
	
	public static function getBuyMessageNotificationsHREF()
	{
		return SmrSession::get_new_href(create_container('skeleton.php', 'buy_message_notifications.php'));
	}
	
	public static function getBuyShipNameHREF()
	{
		return SmrSession::get_new_href(create_container('skeleton.php', 'buy_ship_name.php'));
	}
	
	public static function getAcceptMissionHREF(&$mission)
	{
		return SmrSession::get_new_href(create_container('skeleton.php', 'mission_accept.php', array('MissionID'=>$mission['ID'])));
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
	global $overrideGameID;
	static $stored_dir;
	if(isset($stored_dir))
	{
		$game_dir = $stored_dir;
	}
	else
	{
		if ($overrideGameID > 0)
		{
			$stored_dir = Globals::getGameType($overrideGameID) . '/';
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