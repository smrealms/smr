<?php
require_once('AbstractSmrShip.class.inc');

class DummyShip extends AbstractSmrShip
{
    function __construct($gameID=0,$playerName='Dummy',$raceID=1,$experience=1000,$alignment=100,$allianceID=0,$shipTypeID=60)
    {
		parent::__construct();
		$this->gameID = $gameID;
    }
	
	public function __construct(AbstractSmrPlayer &$player)
	{
		parent::__construct($player);
		$this->player = $player;
		
		$this->load_hardware();
		$this->load_weapon();
		$this->load_cargo();
	}
	
	function load_weapon()
	{
		
		// determine weapon
		$this->db->query('SELECT order_id,weapon_type_id FROM ship_has_weapon WHERE ' .
			'account_id = '.$this->getAccountID().' AND ' .
			'game_id = '.$this->getGameID().
		' ORDER BY order_id LIMIT ' . $this->hardpoint);
		
		$this->weapons = array();
		
		// generate list of weapon names the user transports
		while ($this->db->nextRecord())
		{
			
			$order_id		= $this->db->getField('order_id');
			$this->weapons[$order_id] =& SmrWeapon::getWeapon($this->gameID,$this->db->getField('weapon_type_id'));
			
		}
	}
	
	function load_cargo() {
		
		// initialize cargo array
		$this->cargo = array();
		$this->cargo_left = $this->getCargoHolds();
		
		// get cargo from db
		$this->db->query('SELECT * FROM ship_has_cargo, good WHERE ship_has_cargo.good_id = good.good_id AND ' .
			'account_id = '.$this->getAccountID().' AND ' .
			'game_id = '.$this->getGameID());
		while ($this->db->nextRecord()) {
			
			$good_id	= $this->db->getField('good_id');
			$amount		= $this->db->getField('amount');
			
			// subtract the current amount from cargo_hold to get cargo left value
			$this->cargo_left -= $amount;
			
			// adding cargo and amount to array
			$this->cargo[$good_id] = $amount;
		}
		
		// initialize empty cargo
		$this->db->query('SELECT good_id FROM good');
		while ($this->db->nextRecord()) {
			
			$good_id = $this->db->getField('good_id');
			if (empty($this->cargo[$good_id])) $this->cargo[$good_id] = 0;
		}
	}
	
	function load_hardware() {
		
		$this->hardware = array();
		$this->maxHardware = array();
		
		// get currently hardware from db
		$this->db->query('SELECT * FROM ship_has_hardware, hardware_type ' .
			'WHERE account_id = '.$this->getAccountID().' AND ' .
			'game_id = '.$this->getGameID().' AND ' .
		'ship_has_hardware.hardware_type_id = hardware_type.hardware_type_id');
		
		while ($this->db->nextRecord()) {
			
			$hardware_type_id = $this->db->getField('hardware_type_id');
			
			// adding hardware to array
			$this->hardware[$hardware_type_id]		= $this->db->getField('amount');
			$this->oldHardware[$hardware_type_id] = $this->db->getField('old_amount');
			
		}
		// get supported hardware from db
		$this->db->query('SELECT hardware_type.hardware_type_id as hardware_type_id, max_amount FROM ship_type_support_hardware, hardware_type ' .
			'WHERE ship_type_support_hardware.hardware_type_id = hardware_type.hardware_type_id AND ' .
			'ship_type_id = '.$this->getShipTypeID());
		
		while ($this->db->nextRecord()) {
			
			$hardware_type_id	= $this->db->getField('hardware_type_id');
			
			// adding hardware to array
			$this->maxHardware[$hardware_type_id] = $this->db->getField('max_amount');
		}
		
		// initialize empty hardware
		$this->db->query('SELECT hardware_type_id FROM hardware_type');
		while ($this->db->nextRecord()) {
			
			$hardware_type_id = $this->db->getField('hardware_type_id');
			
			// initialize empty hardware
			if (empty($this->hardware[$hardware_type_id])) $this->hardware[$hardware_type_id] = 0;
			if (empty($this->maxHardware[$hardware_type_id])) $this->maxHardware[$hardware_type_id] = 0;
		}
		
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