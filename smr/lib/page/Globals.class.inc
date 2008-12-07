<?php

require_once('SmrMySqlDatabase.class.inc');

class Globals
{
	private static $PLANET_BUILDINGS = null;
	private static $LEVEL_REQUIREMENTS = null;
	private static $RACES = null;
	private static $GOODS = null;
	private static $WEAPONS = array();
	private static $GAME_SPEEDS = array();
	private static $RACE_RELATIONS = array();
	private static $db = null;
	
	private function __construct()
	{
	}
	
	private static function initialiseDatabase()
	{
		if(self::$db==null)
			self::$db = new SmrMySqlDatabase();
	}

	public static function getPlanetBuildings()
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
	
	public static function getLevelRequirements()
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
	
	public static function getRaces()
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
	
	public static function getGoods()
	{
		if(self::$GOODS==null) //use cached if available
		{
			self::initialiseDatabase();
			$GOODS = array();
			
			// determine user level
			self::$db->query('SELECT good_id,good_name,max_amount,base_price,good_class,restriction FROM good');
			while(self::$db->nextRecord())
			{
				$GOODS[self::$db->getField('good_id')] = array();
				$GOODS[self::$db->getField('good_id')]['ID'] = (int)self::$db->getField('good_id');
				$GOODS[self::$db->getField('good_id')]['Name'] = self::$db->getField('good_name');
				$GOODS[self::$db->getField('good_id')]['Max'] = (int)self::$db->getField('max_amount');
				$GOODS[self::$db->getField('good_id')]['BasePrice'] = (int)self::$db->getField('base_price');
				$GOODS[self::$db->getField('good_id')]['Class'] = (int)self::$db->getField('good_class');
				$GOODS[self::$db->getField('good_id')]['ImageLink'] = 'images/port/' . self::$db->getField('good_id') . '.gif';
				$GOODS[self::$db->getField('good_id')]['Restriction'] = (int)self::$db->getField('restriction');				
			}
			self::$GOODS =& $GOODS;
		}
		return self::$GOODS;
	}
	
	public static function getWeapon($weaponID)
	{
		if(!isset(self::$WEAPONS[$weaponID])) //use cached if available
		{
			self::initialiseDatabase();
			self::$WEAPONS[$weaponID] = array();
			
				
			self::$db->query('SELECT weapon_name,race_id,cost,shield_damage,armor_damage,accuracy,power_level,buyer_restriction FROM weapon_type WHERE weapon_type_id = ' . $weaponID . ' LIMIT 1');
			
			if(self::$db->nextRecord())
			{
				self::$WEAPONS[$weaponID] = array(
												'Name' => self::$db->getField('weapon_name'),
												'RaceID' => (int)self::$db->getField('race_id'),
												'Cost' => (int)self::$db->getField('cost'),
												'ShieldDamage' => (int)self::$db->getField('shield_damage'),
												'ArmourDamage' => (int)self::$db->getField('armor_damage'),
												'Accuracy' => (int)self::$db->getField('accuracy'),
												'PowerLevel' => (int)self::$db->getField('power_level'),
												'BuyerRestriction' => (int)self::$db->getField('buyer_restriction')
											);
			}
		}
		return self::$WEAPONS[$weaponID];
	}
	
	public static function getGameSpeed($gameID)
	{
		if(!isset(self::$GAME_SPEEDS[$gameID]))
		{
			self::initialiseDatabase();
			self::$db->query('SELECT * FROM game WHERE game_id = '.$gameID.' LIMIT 1');
			self::$db->nextRecord();
			self::$GAME_SPEEDS[$gameID] = (int)self::$db->getField('game_speed');
		}
		return self::$GAME_SPEEDS[$gameID];
	}
	
	public static function getRaceRelations($gameID,$raceID)
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
}
?>