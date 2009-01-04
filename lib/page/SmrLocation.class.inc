<?php
require_once('SmrMySqlDatabase.class.inc');

class SmrLocation
{
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
	
	protected $HREF;
	protected $editHREF;
	
	public static function &getSectorLocations($gameID,$sectorID,$forceUpdate = false)
	{
		if($forceUpdate || !isset(self::$CACHE_SECTOR_LOCATIONS[$gameID . ':' . $sectorID]))
		{
			$db = new SmrMySqlDatabase();
			$db->query('SELECT location_type_id FROM location WHERE sector_id = ' . $sectorID . ' AND game_id=' . $gameID);
			$locations = array();
			while($db->nextRecord())
			{
				$locations[$db->getField('location_type_id')] =& self::getLocation($db->getField('location_type_id'));
			}
			return self::$CACHE_SECTOR_LOCATIONS[$gameID . ':' . $sectorID] =& $locations;
		}
		return self::$CACHE_SECTOR_LOCATIONS[$gameID . ':' . $sectorID];
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
		
		while($this->db->nextRecord())
		{
			$this->typeID = $this->db->getField('location_type_id');
			$this->name = $this->db->getField('location_name');
			$this->processor = $this->db->getField('location_processor');
			$this->image = $this->db->getField('location_image');
		}
	}
	
	public function getTypeID()
	{
		return $this->typeID;
	}
	
	public function getName()
	{
		return $this->name;
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
			if($this->db->nextRecord())
				$this->fed = true;
			else
				$this->fed = false;
		}
		return $this->fed;
	}
	
	public function isBank()
	{
		if(!isset($this->bank))
		{
			$this->db->query('SELECT * FROM location_is_bank WHERE location_type_id = ' . $this->getTypeID() . ' LIMIT 1');
			if($this->db->nextRecord())
				$this->bank = true;
			else
				$this->bank = false;
		}
		return $this->bank;
	}
	
	public function isBar()
	{
		if(!isset($this->bar))
		{
			$this->db->query('SELECT * FROM location_is_bar WHERE location_type_id = ' . $this->getTypeID() . ' LIMIT 1');
			if($this->db->nextRecord())
				$this->bar = true;
			else
				$this->bar = false;
		}
		return $this->bar;
	}
	
	public function isHQ()
	{
		if(!isset($this->HQ))
		{
			$this->db->query('SELECT * FROM location_is_hq WHERE location_type_id = ' . $this->getTypeID() . ' LIMIT 1');
			if($this->db->nextRecord())
				$this->HQ = true;
			else
				$this->HQ = false;
		}
		return $this->HQ;
	}
	
	public function isUG()
	{
		if(!isset($this->UG))
		{
			$this->db->query('SELECT * FROM location_is_ug WHERE location_type_id = ' . $this->getTypeID() . ' LIMIT 1');
			if($this->db->nextRecord())
				$this->UG = true;
			else
				$this->UG = false;
		}
		return $this->UG;
	}
	
	public function getHardwareSold()
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
	
	public function isHardwareSold($hardwareTypeID)
	{
		$hardware = $this->getHardwareSold();
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
	
	public function getShipsSold()
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
	
	public function isShipSold($shipTypeID)
	{
		$ships = $this->getShipsSold();
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
	
	public function getWeaponsSold()
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
	
	public function isWeaponSold($weaponTypeID)
	{
		$weapons = $this->getWeaponsSold();
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
	
	public function getExamineHREF()
	{
		if(!isset($this->HREF))
		{
			$container = array();
			$container['url'] = 'skeleton.php';
			$container['body'] = $this->getAction();
			$this->HREF = SmrSession::get_new_href($container);
		}
		return $this->HREF;
	}
	
	public function getEditHREF()
	{
		if(!isset($this->editHREF))
		{
			$container = array();
			$container['url'] = 'skeleton.php';
			$container['body'] = 'location_edit.php';
			$container['game_type_id'] = 0; //TODO add game type id
			$container['location_type_id'] = $this->getTypeID();
			$this->editHREF = SmrSession::get_new_href($container);
		}
		return $this->editHREF;
	}
}
?>