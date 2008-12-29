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
		$this->doFullUNO();
		
		$this->weapons = array();
		
		$this->cargo = array();
		$this->cargo_left = $this->getCargoHolds();
	}
	protected function doFullUNO()
	{
		foreach($this->maxHardware as $hardwareTypeID => $max)
		{
			$this->hardware[$hardwareTypeID] = $max;
			$this->oldHardware[$hardwareTypeID] = $max;
		}
	}
	
	protected function regenerate(AbstractSmrPlayer &$player)
	{
		$this->player = $player;
		$this->regenerateBaseShip();
		$this->doFullUNO();
		for($i=1;$i<=$this->hardpoint;++$i)
		{
			if(!isset($this->weapons[$i]))
				$this->weapons[$i] = SmrWeapon::getWeapon($player->getGameID(),1);
		}
		$this->checkForExcessWeapons();
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
    
    public function cacheDummyShip()
    {
		$cache = serialize($this);
		$db = new SmrMySqlDatabase();
		$db->query('REPLACE INTO cached_dummys ' .
					'(type, id, info) ' .
					'VALUES (\'DummyShip\', '.$db->escapeString($this->getPlayer()->getName()).', '.$db->escapeString($cache).')');	
    }
    
    public static function &getCachedDummyShip(AbstractSmrPlayer &$player)
    {
		$db = new SmrMySqlDatabase();
		$db->query('SELECT info FROM cached_dummys ' .
						  'WHERE type = \'DummyShip\' AND ' .
						  		'id = '.$db->escapeString($player->getPlayerName()).' LIMIT 1');
		if($db->nextRecord($player))
		{
			$return = unserialize($db->getField('info'));
			$return->regenerate();
			return $return;
		}
		else
		{
			$return = new DummyShip($player);
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