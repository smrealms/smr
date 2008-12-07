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
	
	protected $HREF;
	
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
}
?>