<?php
require_once('AbstractSmrShip.class.inc');

require_once('SmrPlayer.class.inc');
require_once('SmrWeapon.class.inc');
require_once('SmrCombatDrones.class.inc');
class SmrShip extends AbstractSmrShip
{
	protected static $CACHE_SHIPS = array();
	
	protected $db;
	
	public static function refreshCache()
	{
		foreach(self::$CACHE_SHIPS as &$gameShips)
		{
			foreach($gameShips as &$ship)
			{
				$ship = self::getShip($ship->getPlayer(),true);
			}
		}
	}
	
	public static function clearCache()
	{
		self::$CACHE_SHIPS = array();
	}
	
	public static function saveShips()
	{
		foreach(self::$CACHE_SHIPS as &$gameShips)
		{
			foreach($gameShips as &$ship)
			{
				$ship->update_hardware();
				$ship->update_weapon();
				$ship->update_cargo();
				$ship->update();
			}
		}
	}

	public static function &getShip(AbstractSmrPlayer &$player,$forceUpdate = false)
	{
		if($forceUpdate || !isset(self::$CACHE_SHIPS[$player->getGameID()][$player->getAccountID()]))
		{
			$s = new SmrShip($player);
			return self::$CACHE_SHIPS[$player->getGameID()][$s->getAccountID()] =& $s;
		}
		return self::$CACHE_SHIPS[$player->getGameID()][$player->getAccountID()];
	}
	
	protected function __construct(AbstractSmrPlayer &$player)
	{
		parent::__construct($player);
		$this->db = new SmrMySqlDatabase();
		
		$this->load_hardware();
		$this->load_weapon();
		$this->load_cargo();
	}
	
	protected function load_weapon()
	{
		// determine weapon
		$this->db->query('SELECT order_id,weapon_type_id FROM ship_has_weapon WHERE ' .
			'account_id = '.$this->getAccountID().' AND ' .
			'game_id = '.$this->getGameID().
		' ORDER BY order_id LIMIT ' . $this->getHardpoints());
		
		$this->weapons = array();
		// generate list of weapon names the user transports
		while ($this->db->nextRecord())
		{
			$this->weapons[$this->db->getField('order_id')] =& SmrWeapon::getWeapon($this->gameID,$this->db->getField('weapon_type_id'));
		}
		$this->checkForExcessWeapons();
	}
	
	protected function load_cargo()
	{
		if(!isset($this->cargo))
		{
			// initialize cargo array
			$this->cargo = array();
			
			// get cargo from db
			$this->db->query('SELECT * FROM ship_has_cargo WHERE account_id = '.$this->getAccountID().' AND ' .
				'game_id = '.$this->getGameID());
			while ($this->db->nextRecord())
			{
				// adding cargo and amount to array
				$this->cargo[$this->db->getField('good_id')] = $this->db->getField('amount');
			}
		}
		$this->checkForExcessCargo();
	}
	
	protected function load_hardware()
	{
		$this->hardware = array();
		$this->oldHardware = array();
		
		// get currently hardware from db
		$this->db->query('SELECT * FROM ship_has_hardware, hardware_type ' .
			'WHERE account_id = '.$this->getAccountID().' AND ' .
			'game_id = '.$this->getGameID().' AND ' .
			'ship_has_hardware.hardware_type_id = hardware_type.hardware_type_id');
		
		while ($this->db->nextRecord())
		{
			$hardware_type_id = $this->db->getField('hardware_type_id');
			
			// adding hardware to array
			$this->hardware[$hardware_type_id]		= $this->db->getField('amount');
			$this->oldHardware[$hardware_type_id] = $this->db->getField('old_amount');
		}
		$this->checkForExcessHardware();
	}
	
	public function getAccountID()
	{
		return $this->getPlayer()->getAccountID();
	}
	
	function update_cargo()
	{
		if($this->hasChangedCargo === true)
		{
			// write cargo info
			foreach ($this->getCargo() as $id => $amount)
			{
				if ($amount > 0)
					$this->db->query('REPLACE INTO ship_has_cargo (account_id, game_id, good_id, amount) VALUES('.$this->getAccountID().', '.$this->getGameID().', '.$id.', '.$amount.')');
				else
					$this->db->query('DELETE FROM ship_has_cargo WHERE account_id = '.$this->getAccountID().' AND game_id = '.$this->getGameID().' AND good_id = '.$id.' LIMIT 1');
			}
			$this->hasChangedCargo = false;
		}
	}
	
	function update_hardware()
	{
		if($this->hasChangedHardware === true)
		{
			$this->db->lockTable('ship_has_hardware');
			
			// write hardware info
			foreach ($this->hardware as $hardware_type_id => $amount)
			{
				if ($amount > 0)
				{
					$this->db->query('SELECT * FROM ship_has_hardware WHERE account_id = '.$this->getAccountID().' AND game_id = '.$this->getGameID().' and hardware_type_id = '.$hardware_type_id);
					if ($this->db->nextRecord())
					{
						$this->db->query('UPDATE ship_has_hardware SET amount = '.$amount.', old_amount = '.$this->getOldHardware($hardware_type_id).' WHERE account_id = '.$this->getAccountID().' AND game_id = '.$this->getGameID().' and hardware_type_id = '.$hardware_type_id);
					}
					else
					{
						$this->db->query('INSERT INTO ship_has_hardware (account_id, game_id, hardware_type_id, amount, old_amount) VALUES('.$this->getAccountID().', '.$this->getGameID().', '.$hardware_type_id.', '.$amount.', '.$this->getOldHardware($hardware_type_id).')');
					}
				}
				else
					$this->db->query('DELETE FROM ship_has_hardware WHERE account_id = '.$this->getAccountID().' AND game_id = '.$this->getGameID().' AND hardware_type_id = '.$hardware_type_id);
			}
			$this->db->unlock();
			$this->hasChangedHardware = false;
		}
	}
	
	function update_weapon()
	{
		if($this->hasChangedWeapons === true)
		{
			// write weapon info
			$this->db->query('DELETE FROM ship_has_weapon ' .
				'WHERE account_id = '.$this->getAccountID().' AND ' .
				'game_id = '.$this->getGameID());
			foreach ($this->weapons as $order_id => &$weapon)
			{
				$this->db->query('INSERT INTO ship_has_weapon (account_id, game_id, order_id, weapon_type_id) ' .
					'VALUES('.$this->getAccountID().', '.$this->getGameID().', '.$order_id.', '.$weapon->getWeaponTypeID().')');
			}
			$this->hasChangedWeapons = false;
		}
	}
	
	function update() {
		
		// write ship infos
		$this->db->query('UPDATE player SET ship_type_id = '.$this->getShipTypeID().' ' .
			'WHERE account_id = '.$this->getAccountID().' AND ' .
			'game_id = '.$this->getGameID());
		
	}
	
	function check_power_level($power_level) {
		
		$this->db->query('SELECT ship_has_weapon.weapon_type_id FROM ship_has_weapon, weapon_type ' .
			'WHERE ship_has_weapon.weapon_type_id = weapon_type.weapon_type_id AND ' .
			'account_id = '.$this->getAccountID().' AND ' .
			'game_id = '.$this->getGameID().' AND ' .
			'power_level = '.$power_level);
		
		switch ($power_level) {
		
			case 5: if ($this->db->getNumRows() < 1) return 1; else return 0;
			break;
			case 4: if ($this->db->getNumRows() < 2) return 1; else return 0;
			break;
			case 3: if ($this->db->getNumRows() < 3) return 1; else return 0;
			break;
			case 2: if ($this->db->getNumRows() < 4) return 1; else return 0;
			break;
			case 1: return 1;
			break;
		}
		return 1;
	}
	
	public function isCloaked()
	{
		if(!$this->hasCloak())
			return false;
		if(!isset($this->isCloaked))
		{
			$this->db->query('SELECT * FROM ship_is_cloaked ' .
				'WHERE account_id = '.$this->getAccountID().' AND ' .
				'game_id = '.$this->getGameID().' LIMIT 1');
			$this->isCloaked = $this->db->getNumRows()>0;
		}
		return $this->isCloaked;
	}
	
	function decloak()
	{
		$this->isCloaked = false;
		$this->db->query('DELETE FROM ship_is_cloaked WHERE account_id = '.$this->getAccountID().' AND ' .
			'game_id = '.$this->getGameID() .' LIMIT 1');
	}
	
	function enableCloak()
	{
		$this->isCloaked = true;
		$this->db->query('REPLACE INTO ship_is_cloaked VALUES('.$this->getAccountID().', '.$this->getGameID().')');
		
	}
	
	function cloak_overload()
	{
		// 1 in 25 chance of cloak being destroyed if active
		if($this->isCloaked() && mt_rand(0,99) < 5)
		{
			$this->db->query(
				'DELETE FROM ship_has_hardware ' .
				'WHERE account_id=' . $this->getAccountID() . ' ' .
				'AND game_id=' . $this->getGameID() . ' ' .
				'AND hardware_type_id=8 ' .
				'limit 1'
			);
			$this->decloak();
			$this->setHardware(HARDWARE_CLOAK,0);
			return true;
		}
		
		return false;
	}
	
	function setIllusion($ship_id, $attack, $defense)
	{
		$this->db->query('REPLACE INTO ship_has_illusion VALUES('.$this->getAccountID().', '.$this->getGameID().', '.$ship_id.', '.$attack.', '.$defense.')');
	}
	
	function disableIllusion()
	{
		$this->db->query('DELETE FROM ship_has_illusion WHERE account_id = '.$this->getAccountID().' AND game_id = '.$this->getGameID().' LIMIT 1');
	}
	
	public function getIllusionShip()
	{
		if(!isset($this->illusionShip))
		{
			$this->illusionShip=false;
			$this->db->query('SELECT ship_has_illusion.*,ship_type.ship_name FROM ship_has_illusion JOIN ship_type USING(ship_type_id)' .
							' WHERE account_id = '.$this->getAccountID().' AND game_id = '.$this->getGameID().' LIMIT 1');
			if($this->db->nextRecord())
			{
				$this->illusionShip=array();
				$this->illusionShip['ID'] = $this->db->getField('ship_type_id');
				$this->illusionShip['Attack'] = $this->db->getField('attack');
				$this->illusionShip['Defense'] = $this->db->getField('defense');
				$this->illusionShip['Name'] = $this->db->getField('ship_name');
			}
		}
		return $this->illusionShip;
		
	}
	
	public function getUpgradeShipHREF()
	{
		$container = array();
		$container['url']		= 'skeleton.php';
		$container['body']		= 'ship_upgrade.php';
		return SmrSession::get_new_href($container);
	}
}

?>