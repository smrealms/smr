<?php
require_once('AbstractSmrShip.class.inc');

class DummyShip extends AbstractSmrShip
{
	public function __construct(AbstractSmrPlayer &$player)
	{
		parent::__construct($player);
		$this->player = $player;
		
		$this->hardware = array();
		$this->maxHardware = array();
		$this->oldHardware = array();
		
		$this->weapons = array();
		
		$this->cargo = array();
		$this->cargo_left = $this->getCargoHolds();
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