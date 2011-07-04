<?php
class SmrLocation
{
	protected static $CACHE_ALL_LOCATIONS;
	protected static $CACHE_LOCATIONS = array();
	protected static $CACHE_SECTOR_LOCATIONS = array();
	
	protected $db;
	
	protected $typeID;
	protected $name;
	protected $processor;
	protected $image;
	
	protected $fed;
	protected $bank;
	protected $bar;
	protected $HQ;
	protected $UG;
	
	protected $hardwareSold;
	protected $shipsSold;
	protected $weaponsSold;
	
	public static function &getAllLocations($forceUpdate = false)
	{
		if($forceUpdate || !isset(self::$CACHE_ALL_LOCATIONS))
		{
			$db = new SmrMySqlDatabase();
			$db->query('SELECT location_type_id FROM location_type ORDER BY location_type_id');
			$locations = array();
			while($db->nextRecord())
			{
				$locations[$db->getField('location_type_id')] =& SmrLocation::getLocation($db->getField('location_type_id'));
			}
			self::$CACHE_ALL_LOCATIONS =& $locations;
		}
		return self::$CACHE_ALL_LOCATIONS;
	}
	
	public static function &getSectorLocations($gameID,$sectorID,$forceUpdate = false)
	{
		if($forceUpdate || !isset(self::$CACHE_SECTOR_LOCATIONS[$gameID][$sectorID]))
		{
			$db = new SmrMySqlDatabase();
			$db->query('SELECT location_type_id FROM location WHERE sector_id = ' . $sectorID . ' AND game_id=' . $gameID);
			$locations = array();
			$i=0;
			while($db->nextRecord())
			{
				$locations[$i] =& self::getLocation($db->getField('location_type_id'));
				$i++;
			}
			return self::$CACHE_SECTOR_LOCATIONS[$gameID][$sectorID] =& $locations;
		}
		return self::$CACHE_SECTOR_LOCATIONS[$gameID][$sectorID];
	}
	
	public static function &getLocation($locationTypeID,$forceUpdate = false)
	{
		if($forceUpdate || !isset(self::$CACHE_LOCATIONS[$locationTypeID]))
		{
			$p = new SmrLocation($locationTypeID);
			return self::$CACHE_LOCATIONS[$locationTypeID] =& $p;
		}
		return self::$CACHE_LOCATIONS[$locationTypeID];
	}
	
	protected function __construct($locationTypeID)
	{
		$this->db = new SmrMySqlDatabase();
		$this->db->query('SELECT location_type_id,location_name,location_processor,location_image FROM location_type WHERE location_type_id = ' . $locationTypeID . ' LIMIT 1');
		
		if($this->db->nextRecord())
		{
			$this->typeID = $this->db->getField('location_type_id');
			$this->name = $this->db->getField('location_name');
			$this->processor = $this->db->getField('location_processor');
			$this->image = $this->db->getField('location_image');
		}
		else
			throw new Exception('Cannot find location: '.$locationTypeID);
	}
	
	public function getTypeID()
	{
		return $this->typeID;
	}
	
	public function getRaceID()
	{
		if($this->isFed())
		{
			return $this->typeID - LOCATION_TYPE_FEDERAL_BEACON;
		}
		return RACE_NEUTRAL;
	}
	
	public function getName()
	{
		return $this->name;
	}
	
	public function setName($name)
	{
		$name = htmlentities($name,ENT_COMPAT,'utf-8');
		if($this->name==$name)
			return;
		$this->name = $name;
		$this->db->query('UPDATE location_type SET location_name='.$db->escapeString($this->name).' WHERE location_type_id=' . $this->getTypeID() . ' LIMIT 1');
	}
	
	public function hasAction()
	{
		return $this->processor!=null;
	}
	
	public function getAction()
	{
		return $this->processor;
	}
	
	public function getImage()
	{
		return $this->image;
	}
	
	public function isFed()
	{
		if(!isset($this->fed))
		{
			$this->db->query('SELECT * FROM location_is_fed WHERE location_type_id = ' . $this->getTypeID() . ' LIMIT 1');
			$this->fed = $this->db->nextRecord();
		}
		return $this->fed;
	}
	
	public function setFed($bool)
	{
		if($this->fed===$bool)
			return;
		if($bool===true)
		{
			$this->db->query('INSERT IGNORE INTO location_is_fed (location_type_id) values (' . $this->getTypeID() . ')');
			$this->fed = true;
		}
		else if($bool===false)
		{
			$this->db->query('DELETE FROM location_is_fed WHERE location_type_id  = ' . $this->getTypeID() . ' LIMIT 1');
			$this->fed = false;
		}
	}
	
	public function isBank()
	{
		if(!isset($this->bank))
		{
			$this->db->query('SELECT * FROM location_is_bank WHERE location_type_id = ' . $this->getTypeID() . ' LIMIT 1');
			$this->bank = $this->db->nextRecord();
		}
		return $this->bank;
	}
	
	public function setBank($bool)
	{
		if($this->bank===$bool)
			return;
		if($bool===true)
		{
			$this->db->query('INSERT INTO location_is_bank (location_type_id) values (' . $this->getTypeID() . ')');
			$this->bank = true;
		}
		else if($bool===false)
		{
			$this->db->query('DELETE FROM location_is_bank WHERE location_type_id  = ' . $this->getTypeID() . ' LIMIT 1');
			$this->bank = false;
		}
	}
	
	public function isBar()
	{
		if(!isset($this->bar))
		{
			$this->db->query('SELECT * FROM location_is_bar WHERE location_type_id = ' . $this->getTypeID() . ' LIMIT 1');
			$this->bar = $this->db->nextRecord();
		}
		return $this->bar;
	}
	
	public function setBar($bool)
	{
		if($this->bar===$bool)
			return;
		if($bool===true)
		{
			$this->db->query('INSERT IGNORE INTO location_is_bar (location_type_id) values (' . $this->getTypeID() . ')');
			$this->bar = true;
		}
		else if($bool===false)
		{
			$this->db->query('DELETE FROM location_is_bar WHERE location_type_id  = ' . $this->getTypeID() . ' LIMIT 1');
			$this->bar = false;
		}
	}
	
	public function isHQ()
	{
		if(!isset($this->HQ))
		{
			$this->db->query('SELECT * FROM location_is_hq WHERE location_type_id = ' . $this->getTypeID() . ' LIMIT 1');
			$this->HQ = $this->db->nextRecord();
		}
		return $this->HQ;
	}
	
	public function setHQ($bool)
	{
		if($this->HQ===$bool)
			return;
		if($bool===true)
		{
			$this->db->query('INSERT IGNORE INTO location_is_hq (location_type_id) values (' . $this->getTypeID() . ')');
			$this->HQ = true;
		}
		else if($bool===false)
		{
			$this->db->query('DELETE FROM location_is_hq WHERE location_type_id  = ' . $this->getTypeID() . ' LIMIT 1');
			$this->HQ = false;
		}
	}
	
	public function isUG()
	{
		if(!isset($this->UG))
		{
			$this->db->query('SELECT * FROM location_is_ug WHERE location_type_id = ' . $this->getTypeID() . ' LIMIT 1');
			$this->UG = $this->db->nextRecord();
		}
		return $this->UG;
	}
	
	public function setUG($bool)
	{
		if($this->UG===$bool)
			return;
		if($bool===true)
		{
			$this->db->query('INSERT INTO location_is_ug (location_type_id) values (' . $this->getTypeID() . ')');
			$this->UG = true;
		}
		else if($bool===false)
		{
			$this->db->query('DELETE FROM location_is_ug WHERE location_type_id  = ' . $this->getTypeID() . ' LIMIT 1');
			$this->UG = false;
		}
	}
	
	public function &getHardwareSold()
	{
		if(!isset($this->hardwareSold))
		{
			$this->hardwareSold = array();
			$this->db->query('SELECT * FROM location_sells_hardware NATURAL JOIN hardware_type WHERE location_type_id = ' . $this->getTypeID());
			while($this->db->nextRecord())
				$this->hardwareSold[$this->db->getField('hardware_type_id')] = $this->db->getField('hardware_name');
		}
		return $this->hardwareSold;
	}
	
	public function isHardwareSold($hardwareTypeID=false)
	{
		$hardware = $this->getHardwareSold();
		if($hardwareTypeID===false)
			return count($hardware)!=0;
		return isset($hardware[$hardwareTypeID]);
	}
	
	public function addHardwareSold($hardwareTypeID)
	{
		if($this->isHardwareSold($hardwareTypeID))
			return;
		$this->db->query('SELECT * FROM hardware_type WHERE hardware_type_id = ' . $hardwareTypeID . ' LIMIT 1');
		if(!$this->db->nextRecord())
			throw new Exception('Invalid hardware type id given');
		$this->db->query('INSERT INTO location_sells_hardware (location_type_id,hardware_type_id) values (' . $this->getTypeID(). ',' . $hardwareTypeID . ')');
		$this->hardwareSold[$hardwareTypeID] = $this->db->getField('hardware_name');
	}
	
	public function removeHardwareSold($hardwareTypeID)
	{
		if(!$this->isHardwareSold($hardwareTypeID))
			return;
		$this->db->query('DELETE FROM location_sells_hardware WHERE location_type_id = ' . $this->getTypeID() . ' AND hardware_type_id = ' . $hardwareTypeID . ' LIMIT 1');
		unset($this->hardwareSold[$hardwareTypeID]);
	}
	
	public function &getShipsSold()
	{
		if(!isset($this->shipsSold))
		{
			require_once(get_file_loc('AbstractSmrShip.class.inc'));
			$this->shipsSold = array();
			$this->db->query('SELECT * FROM location_sells_ships WHERE location_type_id = ' . $this->getTypeID());
			while($this->db->nextRecord())
				$this->shipsSold[$this->db->getField('ship_type_id')] =& AbstractSmrShip::getBaseShip(Globals::getGameType(SmrSession::$game_id),$this->db->getField('ship_type_id'));
		}
		return $this->shipsSold;
	}
	
	public function isShipSold($shipTypeID=false)
	{
		$ships = $this->getShipsSold();
		if($shipTypeID===false)
			return count($ships)!=0;
		return isset($ships[$shipTypeID]);
	}
	
	public function addShipSold($shipTypeID)
	{
		if($this->isShipSold($shipTypeID))
			return;
		require_once(get_file_loc('AbstractSmrShip.class.inc'));
		$ship =& AbstractSmrShip::getBaseShip(Globals::getGameType(SmrSession::$game_id),$shipTypeID);
		if($ship===false)
			throw new Exception('Invalid ship type id given');
		$this->db->query('INSERT INTO location_sells_ships (location_type_id,ship_type_id) values (' . $this->getTypeID(). ',' . $shipTypeID . ')');
		$this->shipsSold[$shipTypeID] =& $ship;
	}
	
	public function removeShipSold($shipTypeID)
	{
		if(!$this->isShipSold($shipTypeID))
			return;
		$this->db->query('DELETE FROM location_sells_ships WHERE location_type_id = ' . $this->getTypeID() . ' AND ship_type_id = ' . $shipTypeID . ' LIMIT 1');
		unset($this->shipsSold[$shipTypeID]);
	}
	
	public function &getWeaponsSold()
	{
		if(!isset($this->weaponsSold))
		{
			require_once(get_file_loc('SmrWeapon.class.inc'));
			$this->weaponsSold = array();
			$this->db->query('SELECT * FROM location_sells_weapons WHERE location_type_id = ' . $this->getTypeID());
			while($this->db->nextRecord())
				$this->weaponsSold[$this->db->getField('weapon_type_id')] =& SmrWeapon::getWeapon(Globals::getGameType(SmrSession::$game_id),$this->db->getField('weapon_type_id'));
		}
		return $this->weaponsSold;
	}
	
	public function isWeaponSold($weaponTypeID=false)
	{
		$weapons = $this->getWeaponsSold();
		if($weaponTypeID===false)
			return count($weapons)!=0;
		return isset($weapons[$weaponTypeID]);
	}
	
	public function addWeaponSold($weaponTypeID)
	{
		if($this->isWeaponSold($weaponTypeID))
			return;
		require_once(get_file_loc('SmrWeapon.class.inc'));
		$weapon =& SmrWeapon::getWeapon(Globals::getGameType(SmrSession::$game_id),$weaponTypeID);
		if($weapon===false)
			throw new Exception('Invalid weapon type id given');
		$this->db->query('INSERT INTO location_sells_weapons (location_type_id,weapon_type_id) values (' . $this->getTypeID(). ',' . $weaponTypeID . ')');
		$this->weaponsSold[$weaponTypeID] =& $weapon;
	}
	
	public function removeWeaponSold($weaponTypeID)
	{
		if(!$this->isWeaponSold($weaponTypeID))
			return;
		$this->db->query('DELETE FROM location_sells_weapons WHERE location_type_id = ' . $this->getTypeID() . ' AND weapon_type_id = ' . $weaponTypeID . ' LIMIT 1');
		unset($this->weaponsSold[$weaponTypeID]);
	}
	
	public function &getLinkedLocations()
	{
		$linkedLocations = array();
		if($this->isHQ())
		{
			if($this->getTypeID()==LOCATION_TYPE_FEDERAL_HQ)
			{
				$linkedLocations[] =& SmrLocation::getLocation(LOCATION_TYPE_FEDERAL_BEACON);
				$linkedLocations[] =& SmrLocation::getLocation(LOCATION_TYPE_FEDERAL_MINT);
			}
			else
			{
				$relativeID = $this->getTypeID()%100-2;
				$linkedLocations[] =& SmrLocation::getLocation(LOCATION_GROUP_RACIAL_BEACONS+$relativeID);
				$linkedLocations[] =& SmrLocation::getLocation(LOCATION_GROUP_RACIAL_SHIPS+$relativeID);
				$linkedLocations[] =& SmrLocation::getLocation(LOCATION_GROUP_RACIAL_SHOPS+$relativeID);
			}
		}
		return $linkedLocations;
	}
	
	public function getExamineHREF()
	{
		$container = array();
		$container['url'] = 'skeleton.php';
		$container['body'] = $this->getAction();
		$container['LocationID'] = $this->getTypeID();
		return SmrSession::get_new_href($container);
	}
	
	public function getEditHREF()
	{
		$container = create_container('skeleton.php', 'location_edit.php');
		$container['game_type_id'] = 0; //TODO add game type id
		$container['location_type_id'] = $this->getTypeID();
		return SmrSession::get_new_href($container);
	}
	
	public function equals(SmrLocation &$otherLocation)
	{
		return $this->getTypeID()==$otherLocation->getTypeID();
	}

	public function hasX(/*Object*/ &$x)
	{
		if ($x instanceof SmrWeapon)
		{
			return $this->isWeaponSold($x->getWeaponTypeID());
		}
		if (is_array($x))
		{
			if($x['Type']=='Ship')// instanceof Ship)
			{
				return $this->isShipSold($x['ShipTypeID']);
			}
			if ($x['Type']=='Hardware')// instanceof ShipEquipment)
			{
				return $this->isHardwareSold($x['ID']);
			}
		}
		if(is_string($x))
		{
			if ($x=='Bank')
			{
				return $this->isBank();
			}
			if ($x=='Bar')
			{
				return $this->isBar();
			}
			if ($x=='Fed')
			{
				return $this->isFed();
			}
			if ($x=='HQ')
			{
				return $this->isHQ();
			}
			if ($x=='UG')
			{
				return $this->isUG();
			}
			if ($x=='Hardware')
			{
				return $this->isHardwareSold();
			}
			if ($x=='Ship')
			{
				return $this->isShipSold();
			}
			if ($x=='Weapon')
			{
				return $this->isWeaponSold();
			}
		}
		return false;
	}
}
?>