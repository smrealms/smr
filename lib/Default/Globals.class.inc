<?php
require_once('SmrMySqlDatabase.class.inc');

class Globals
{
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
	
	public function __construct() //required public for smarty
	{
	}
	
	protected static function initialiseDatabase()
	{
		if(self::$db==null)
			self::$db = new SmrMySqlDatabase();
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
					$PLANET_BUILDINGS[$buildingID]['Build Time'] += $db2->getField('amount');
				}
				
				$PLANET_BUILDINGS[$buildingID]['Credit Cost'] = 0;
				$db2->query('SELECT * FROM planet_cost_credits WHERE construction_id=' . $buildingID);
				while($db2->nextRecord())
				{
					$PLANET_BUILDINGS[$buildingID]['Credit Cost'] += $db2->getField('amount');
				}
				
				$PLANET_BUILDINGS[$buildingID]['Goods'] = array();
				$db2->query('SELECT * FROM planet_cost_good WHERE construction_id=' . $buildingID);
				while($db2->nextRecord())
				{
					$PLANET_BUILDINGS[$buildingID]['Goods'][$db2->getField('good_id')] = $db2->getField('amount');
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
			$LEVEL_REQUIREMENTS = array();
			
			// determine user level
			self::$db->query('SELECT * FROM level ORDER BY level_id ASC');
			while (self::$db->nextRecord())
			{
				$LEVEL_REQUIREMENTS[(int)self::$db->getField('level_id')] = array();
				$LEVEL_REQUIREMENTS[(int)self::$db->getField('level_id')]['ID'] = (int)self::$db->getField('level_id');
				$LEVEL_REQUIREMENTS[(int)self::$db->getField('level_id')]['Name'] = self::$db->getField('level_name');
				$LEVEL_REQUIREMENTS[(int)self::$db->getField('level_id')]['Requirement'] = (int)self::$db->getField('requirement');
			}
			self::$LEVEL_REQUIREMENTS =& $LEVEL_REQUIREMENTS;
		}
		return self::$LEVEL_REQUIREMENTS;
	}
	
	public static function &getRaces()
	{
		if(self::$RACES==null) //use cached if available
		{
			self::initialiseDatabase();
			$RACES = array();
			
			// determine user level
			self::$db->query('SELECT race_id,race_name,race_description FROM race ORDER BY race_id');
			while(self::$db->nextRecord())
			{
				$RACES[self::$db->getField('race_id')] = array();
				$RACES[self::$db->getField('race_id')]['Race ID'] = (int)self::$db->getField('race_id');
				$RACES[self::$db->getField('race_id')]['Race Name'] = self::$db->getField('race_name');
				$RACES[self::$db->getField('race_id')]['Description'] = self::$db->getField('race_description');
			}
			self::$RACES =& $RACES;
		}
		return self::$RACES;
	}
	
	public static function &getRaceName($raceID)
	{
		$races = Globals::getRaces();
		return $races[$raceID]['Race Name'];
	}
	
	public static function &getGoods()
	{
		if(self::$GOODS==null) //use cached if available
		{
			self::initialiseDatabase();
			$GOODS = array();
			
			// determine user level
			self::$db->query('SELECT * FROM good ORDER BY good_id');
			while(self::$db->nextRecord())
			{
				$GOODS[self::$db->getField('good_id')] = array();
				$GOODS[self::$db->getField('good_id')]['Type'] = 'Good';
				$GOODS[self::$db->getField('good_id')]['ID'] = (int)self::$db->getField('good_id');
				$GOODS[self::$db->getField('good_id')]['Name'] = self::$db->getField('good_name');
				$GOODS[self::$db->getField('good_id')]['Max'] = (int)self::$db->getField('max_amount');
				$GOODS[self::$db->getField('good_id')]['BasePrice'] = (int)self::$db->getField('base_price');
				$GOODS[self::$db->getField('good_id')]['Class'] = (int)self::$db->getField('good_class');
				$GOODS[self::$db->getField('good_id')]['ImageLink'] = 'images/port/' . self::$db->getField('good_id') . '.png';
				$GOODS[self::$db->getField('good_id')]['AlignRestriction'] = (int)self::$db->getField('align_restriction');
			}
			self::$GOODS =& $GOODS;
		}
		return self::$GOODS;
	}
	public static function &getGood($goodID)
	{
		$goods =& self::getGoods();
		return $goods[$goodID];
	}
	
	public static function &getHardwareTypes($hardwareTypeID=false)
	{
		if(self::$HARDWARE_TYPES==null) //use cached if available
		{
			self::initialiseDatabase();
			$HARDWARE_TYPES = array();
			
			// determine user level
			self::$db->query('SELECT * FROM hardware_type ORDER BY hardware_type_id');
			while(self::$db->nextRecord())
			{
				$HARDWARE_TYPES[self::$db->getField('hardware_type_id')] = array();
				$HARDWARE_TYPES[self::$db->getField('hardware_type_id')]['Type'] = 'Hardware';
				$HARDWARE_TYPES[self::$db->getField('hardware_type_id')]['ID'] = (int)self::$db->getField('hardware_type_id');
				$HARDWARE_TYPES[self::$db->getField('hardware_type_id')]['Name'] = self::$db->getField('hardware_name');
				$HARDWARE_TYPES[self::$db->getField('hardware_type_id')]['Cost'] = (int)self::$db->getField('cost');
			}
			self::$HARDWARE_TYPES =& $HARDWARE_TYPES;
		}
		if($hardwareTypeID===false)
			return self::$HARDWARE_TYPES;
		return self::$HARDWARE_TYPES[$hardwareTypeID];
	}
	
	public static function &getGameInfo($gameID = false)
	{
		if($gameID===false)
		{
			self::initialiseDatabase();
			self::$db->query('SELECT * FROM game');
			while(self::$db->nextRecord())
			{
				$gameID = (int)self::$db->getField('game_id');
				self::$GAMES[$gameID] = array();
				self::$GAMES[$gameID]['ID'] = $gameID;
				self::$GAMES[$gameID]['Speed'] = (float)self::$db->getField('game_speed');
				self::$GAMES[$gameID]['GameType'] = (string)self::$db->getField('game_type');
				self::$GAMES[$gameID]['GameName'] = (string)self::$db->getField('game_name');
				self::$GAMES[$gameID]['StartDate'] = (string)self::$db->getField('start_date');
			}
			return self::$GAMES;
		}
		if(!isset(self::$GAMES[$gameID]))
		{
			self::initialiseDatabase();
			self::$db->query('SELECT * FROM game WHERE game_id = '.$gameID.' LIMIT 1');
			if(self::$db->nextRecord())
			{
				$gameID = (int)$gameID;
				self::$GAMES[$gameID] = array();
				self::$GAMES[$gameID]['ID'] = $gameID;
				self::$GAMES[$gameID]['Speed'] = (float)self::$db->getField('game_speed');
				self::$GAMES[$gameID]['GameType'] = (string)self::$db->getField('game_type');
				self::$GAMES[$gameID]['GameName'] = (string)self::$db->getField('game_name');
				self::$GAMES[$gameID]['StartDate'] = (string)self::$db->getField('start_date');
			}
			else
				self::$GAMES[$gameID] = false;
		}
		return self::$GAMES[$gameID];
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
		$container = array();
		$container['url']		= 'skeleton.php';
		$container['body']		= 'map_local.php';
		return SmrSession::get_new_href($container);
	}
	
	public static function getPodScreenHREF()
	{
		$container = array();
		$container['url']		= 'skeleton.php';
		$container['body']		= 'death.php';
		return SmrSession::get_new_href($container);
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
	
	public static function getSmrFileCreateHREF()
	{
		$container = array();
		$container['url']		= 'skeleton.php';
		$container['body']		= 'smr_file_create.php';
		return SmrSession::get_new_href($container);
	}
}
?>