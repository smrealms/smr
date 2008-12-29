<?php
require_once('AbstractSmrShip.class.inc');

class DummyShip extends AbstractSmrShip
{
	public function __construct(AbstractSmrPlayer &$player)
	{
		parent::__construct($player);
		$this->player = $player;
		
		$this->hardware = array();
		$this->oldHardware = array();
		foreach($this->maxHardware as $hardwareTypeID => $max)
		{
			$this->hardware[$hardwareTypeID] = $max;
			$this->oldHardware[$hardwareTypeID] = $max;
		}
		
		$this->weapons = array();
		
		$this->cargo = array();
		$this->cargo_left = $this->getCargoHolds();
	}
	
	function decloak()
	{
	}
	
	function enableCloak()
	{
	}
	
	function setIllusion($ship_id, $attack, $defense)
	{
	}
	
	function disableIllusion()
	{
	}
	
	public function getIllusionShip()
	{
		if(!isset($this->illusionShip))
		{
			$this->illusionShip=false;
		}
		return $this->illusionShip;
	}
    
    public function cacheDummyPlayer()
    {
		$cache = serialize($this);
		$this->db->query('REPLACE INTO cached_dummys ' .
					'(type, id, info) ' .
					'VALUES (\'DummyPlayer\', '.$this->getPlayerName().', '.$this->db->escapeString($cache).')');	
    }
    
    public static function &getCachedDummyShip($name)
    {
		$db = new SmrMySqlDatabase();
		$db->query('SELECT info FROM cached_dummys ' .
						  'WHERE type = \'DummyShip\' AND ' .
						  		'id = '.$db->escapeString($name).' LIMIT 1');
		if($db->nextRecord())
		{
			$return = unserialize($db->getField('info'));
			return $return;
		}
		else
		{
			$return = new DummyPlayer();
			return $return;
		}
    }
    
    public static function getDummyShipNames()
    {
		$db = new SmrMySqlDatabase();
		$db->query('SELECT id FROM cached_dummys ' .
						  'WHERE type = \'DummyShip\'');
		$dummyNames = array();
		while($db->nextRecord())
		{
			$dummyNames[] = $db->getField('id');
		}
		return $dummyNames;
    }
}
?>