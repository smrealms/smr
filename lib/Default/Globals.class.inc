<?php
require_once('SmrMySqlDatabase.class.inc');

class Globals
{
	protected static $PLANET_BUILDINGS = null;
	protected static $LEVEL_REQUIREMENTS = null;
	protected static $RACES = null;
	protected static $GOODS = null;
	protected static $GAMES = array();
	protected static $RACE_RELATIONS = array();
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
			$PLANET_BUILDINGS = array();
			$result = self::$db->query('SELECT * FROM planet_buildings');
			while(self::$db->next_record($result))
			{
				$row = self::$db->getRow();
				$PLANET_BUILDINGS[$row['building_id']]['Name'] = $row['name'];
				$PLANET_BUILDINGS[$row['building_id']]['Area'] = array($row['area'],$row['metals'],$row['energy']);
				$PLANET_BUILDINGS[$row['building_id']]['Max Amount'] = $row['max_amount'];
				$PLANET_BUILDINGS[$row['building_id']]['Build Time'] = $row['build_time'];
				$PLANET_BUILDINGS[$row['building_id']]['Credit Cost'] = $row['cost'];
				
				
				$PLANET_BUILDINGS[$row['building_id']]['Goods'] = array();
				$goodsResult = query('SELECT * FROM planet_building_goods_required WHERE building_id=' . $row['building_id']);
				while($goodsRow = next_record($goodsResult))
				{
					$PLANET_BUILDINGS[$row['building_id']]['Goods'][$goodsRow['good_id']] = $goodsRow['amount'];
				}
				
				global $PLANET_RESEARCH;
				$PLANET_BUILDINGS[$row['building_id']]['Research'] = array();
				$researchResult = query('SELECT * FROM planet_building_research_required WHERE building_id=' . $row['building_id']);
				while($researchRow = next_record($researchResult))
				{
					$PLANET_BUILDINGS[$row['building_id']]['Research'][$PLANET_RESEARCH[$researchRow['research_id']]['Name']] = $researchRow['amount'];
				}
				
				$PLANET_BUILDINGS[$row['building_id']]['Unlocks'] = array();
				$unlocksResult = query('SELECT * FROM planet_building_unlocks WHERE building_id=' . $row['building_id']);
				while($unlocksRow = next_record($unlocksResult))
				{
					$PLANET_BUILDINGS[$row['building_id']]['Unlocks'][$unlocksRow['unlock_id']] = $unlocksRow['amount'];
				}
				
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
				$LEVEL_REQUIREMENTS[self::$db->getField('level_id')] = array();
				$LEVEL_REQUIREMENTS[self::$db->getField('level_id')]['ID'] = (int)self::$db->getField('level_id');
				$LEVEL_REQUIREMENTS[self::$db->getField('level_id')]['Name'] = self::$db->getField('level_name');
				$LEVEL_REQUIREMENTS[self::$db->getField('level_id')]['Requirement'] = (int)self::$db->getField('requirement');
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
			self::$db->query('SELECT race_id,race_name FROM race');
			while(self::$db->nextRecord())
			{
				$RACES[self::$db->getField('race_id')] = array();
				$RACES[self::$db->getField('race_id')]['Race ID'] = (int)self::$db->getField('race_id');
				$RACES[self::$db->getField('race_id')]['Race Name'] = self::$db->getField('race_name');
			}
			self::$RACES =& $RACES;
		}
		return self::$RACES;
	}
	
	public static function &getGoods()
	{
		if(self::$GOODS==null) //use cached if available
		{
			self::initialiseDatabase();
			$GOODS = array();
			
			// determine user level
			self::$db->query('SELECT * FROM good');
			while(self::$db->nextRecord())
			{
				$GOODS[self::$db->getField('good_id')] = array();
				$GOODS[self::$db->getField('good_id')]['ID'] = (int)self::$db->getField('good_id');
				$GOODS[self::$db->getField('good_id')]['Name'] = self::$db->getField('good_name');
				$GOODS[self::$db->getField('good_id')]['Max'] = (int)self::$db->getField('max_amount');
				$GOODS[self::$db->getField('good_id')]['BasePrice'] = (int)self::$db->getField('base_price');
				$GOODS[self::$db->getField('good_id')]['Class'] = (int)self::$db->getField('good_class');
				$GOODS[self::$db->getField('good_id')]['ImageLink'] = 'images/port/' . self::$db->getField('good_id') . '.gif';
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
	
	public static function &getGameInfo($gameID)
	{
		if(!isset(self::$GAMES[$gameID]))
		{
			self::initialiseDatabase();
			self::$db->query('SELECT * FROM game WHERE game_id = '.$gameID.' LIMIT 1');
			self::$db->nextRecord();
			self::$GAMES[$gameID] = array();
			self::$GAMES[$gameID]['Speed'] = (int)self::$db->getField('game_speed');
			self::$GAMES[$gameID]['GameType'] = (string)self::$db->getField('game_type');
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
	
	public static function &getRaceRelations($gameID,$raceID)
	{
		if(!isset(self::$RACE_RELATIONS[$gameID]))
		{
			self::$RACE_RELATIONS[$gameID] = array();
		}
		
		if(!isset(self::$RACE_RELATIONS[$gameID][$raceID]))
		{
			//get relations
			$RACES = Globals::getRaces();
			self::$RACE_RELATIONS[$gameID][$raceID] = array();
			foreach ($RACES as $otherRaceID => $race_name)
			{
				self::$RACE_RELATIONS[$gameID][$raceID][$otherRaceID] = 0;
			}
			$r_sql = self::$db->query('SELECT race_id_2,relation FROM race_has_relation WHERE race_id_1='.$raceID.' LIMIT '.sizeof($RACES));
			while (self::$db->nextRecord($r_sql))
			{
				self::$RACE_RELATIONS[$gameID][$raceID][(int)self::$db->getField('race_id_2')] = (int)self::$db->getField('relation');
			}
		}
		return self::$RACE_RELATIONS[$gameID][$raceID];
	}
	
	
	protected static $CURRENT_SECTOR_HREF;
	protected static $LOCAL_MAP_HREF;
	protected static $POD_SCREEN_HREF;
	protected static $BETA_FUNCTIONS_HREF;
	
	public static function &getCurrentSectorHREF()
	{
		if(!isset(self::$CURRENT_SECTOR_HREF))
		{
			$container = array();
			$container['url']		= 'skeleton.php';
			$container['body']		= 'current_sector.php';
			self::$CURRENT_SECTOR_HREF = SmrSession::get_new_href($container);
		}
		return self::$CURRENT_SECTOR_HREF;
	}
	
	public static function &getLocalMapHREF()
	{
		if(!isset(self::$LOCAL_MAP_HREF))
		{
			$container = array();
			$container['url']		= 'skeleton.php';
			$container['body']		= 'map_local.php';
			self::$LOCAL_MAP_HREF = SmrSession::get_new_href($container);
		}
		return self::$LOCAL_MAP_HREF;
	}
	
	public static function &getPodScreenHREF()
	{
		if(!isset(self::$POD_SCREEN_HREF))
		{
			$container = array();
			$container['url']		= 'skeleton.php';
			$container['body']		= 'death.php';
			self::$POD_SCREEN_HREF = SmrSession::get_new_href($container);
		}
		return self::$POD_SCREEN_HREF;
	}
	
	public static function &getBetaFunctionsHREF() //BETA
	{
		if(!isset(self::$BETA_FUNCTIONS_HREF))
		{
			$container = array();
			$container['url']		= 'skeleton.php';
			$container['body']		= 'beta_functions.php';
			self::$BETA_FUNCTIONS_HREF = SmrSession::get_new_href($container);
		}
		return self::$BETA_FUNCTIONS_HREF;
	}
}


$smarty->assign('Globals',new Globals()); //Allow access to static globals in smarty
?>