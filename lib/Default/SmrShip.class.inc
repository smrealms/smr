<?php
require_once('AbstractSmrShip.class.inc');

require_once('SmrPlayer.class.inc');
require_once('SmrWeapon.class.inc');
require_once('SmrCombatDrones.class.inc');
class SmrShip extends AbstractSmrShip {
	protected static $CACHE_SHIPS = array();
	
	protected $db;
	
	public static function refreshCache() {
		foreach(self::$CACHE_SHIPS as &$gameShips) {
			foreach($gameShips as &$ship) {
				$ship = self::getShip($ship->getPlayer(),true);
			}
		}
	}
	
	public static function clearCache() {
		self::$CACHE_SHIPS = array();
	}
	
	public static function saveShips() {
		foreach(self::$CACHE_SHIPS as $gameShips) {
			foreach($gameShips as $ship) {
				$ship->updateHardware();
				$ship->updateWeapon();
				$ship->updateCargo();
				$ship->update();
			}
		}
	}

	public static function &getShip(AbstractSmrPlayer &$player,$forceUpdate = false) {
		if($forceUpdate || !isset(self::$CACHE_SHIPS[$player->getGameID()][$player->getAccountID()])) {
			$s = new SmrShip($player);
			self::$CACHE_SHIPS[$player->getGameID()][$player->getAccountID()] = $s;
		}
		return self::$CACHE_SHIPS[$player->getGameID()][$player->getAccountID()];
	}
	
	protected function __construct(AbstractSmrPlayer &$player) {
		parent::__construct($player);
		$this->db = new SmrMySqlDatabase();
		
		$this->loadHardware();
		$this->loadWeapon();
		$this->loadCargo();
	}
	
	protected function loadWeapon() {
		// determine weapon
		$this->db->query('SELECT order_id,weapon_type_id FROM ship_has_weapon
							WHERE account_id = ' . $this->db->escapeNumber($this->getAccountID()) . '
							AND game_id = ' . $this->db->escapeNumber($this->getGameID()) . '
							ORDER BY order_id LIMIT ' . $this->db->escapeNumber($this->getHardpoints()));
		
		$this->weapons = array();
		// generate list of weapon names the user transports
		while ($this->db->nextRecord()) {
			$this->weapons[$this->db->getInt('order_id')] = SmrWeapon::getWeapon($this->gameID,$this->db->getInt('weapon_type_id'));
		}
		$this->checkForExcessWeapons();
	}
	
	protected function loadCargo() {
		if(!isset($this->cargo)) {
			// initialize cargo array
			$this->cargo = array();
			
			// get cargo from db
			$this->db->query('SELECT * FROM ship_has_cargo
								WHERE account_id = ' . $this->db->escapeNumber($this->getAccountID()) . '
								AND game_id = ' . $this->db->escapeNumber($this->getGameID()));
			while ($this->db->nextRecord()) {
				// adding cargo and amount to array
				$this->cargo[$this->db->getInt('good_id')] = $this->db->getInt('amount');
			}
		}
		$this->checkForExcessCargo();
	}
	
	protected function loadHardware() {
		$this->hardware = array();
		$this->oldHardware = array();
		
		// get currently hardware from db
		$this->db->query('SELECT *
							FROM ship_has_hardware
							JOIN hardware_type USING(hardware_type_id)
							WHERE account_id = ' . $this->db->escapeNumber($this->getAccountID()) . '
							AND game_id = ' . $this->db->escapeNumber($this->getGameID()));
		
		while ($this->db->nextRecord()) {
			$hardwareTypeID = $this->db->getInt('hardware_type_id');
			
			// adding hardware to array
			$this->hardware[$hardwareTypeID] = $this->db->getInt('amount');
			$this->oldHardware[$hardwareTypeID] = $this->db->getInt('old_amount');
		}
		$this->checkForExcessHardware();
	}
	
	public function getAccountID() {
		return $this->getPlayer()->getAccountID();
	}
	
	function updateCargo() {
		if($this->hasChangedCargo === true) {
			// write cargo info
			foreach ($this->getCargo() as $id => $amount) {
				if ($amount > 0) {
					$this->db->query('REPLACE INTO ship_has_cargo (account_id, game_id, good_id, amount) VALUES(' . $this->db->escapeNumber($this->getAccountID()) . ', ' . $this->db->escapeNumber($this->getGameID()) . ', ' . $this->db->escapeNumber($id) . ', ' . $this->db->escapeNumber($amount) . ')');
				} else {
					$this->db->query('DELETE FROM ship_has_cargo WHERE account_id = ' . $this->db->escapeNumber($this->getAccountID()) . ' AND game_id = ' . $this->db->escapeNumber($this->getGameID()) . ' AND good_id = ' . $this->db->escapeNumber($id) . ' LIMIT 1');
					// Unset now to omit displaying this good with 0 amount
					// before the next page is loaded.
					unset($this->cargo[$id]);
				}
			}
			$this->hasChangedCargo = false;
		}
	}
	
	function updateHardware() {
		// write hardware info only for hardware that has changed
		foreach ($this->hasChangedHardware as $hardwareTypeID => $hasChanged) {
			if (!$hasChanged) {
				continue;
			}
			$amount = $this->getHardware($hardwareTypeID);
			if ($amount > 0) {
				$this->db->query('REPLACE INTO ship_has_hardware (account_id, game_id, hardware_type_id, amount, old_amount) VALUES(' . $this->db->escapeNumber($this->getAccountID()) . ', ' . $this->db->escapeNumber($this->getGameID()) . ', ' . $this->db->escapeNumber($hardwareTypeID) . ', ' . $this->db->escapeNumber($amount) . ', ' . $this->db->escapeNumber($this->getOldHardware($hardwareTypeID)) . ')');
			}
			else {
				$this->db->query('DELETE FROM ship_has_hardware WHERE account_id = ' . $this->db->escapeNumber($this->getAccountID()) . ' AND game_id = ' . $this->db->escapeNumber($this->getGameID()) . ' AND hardware_type_id = ' . $this->db->escapeNumber($hardwareTypeID));
			}
		}
		$this->hasChangedHardware = array();
	}
	
	function updateWeapon() {
		if($this->hasChangedWeapons === true) {
			// write weapon info
			$this->db->query('DELETE FROM ship_has_weapon
								WHERE account_id = ' . $this->db->escapeNumber($this->getAccountID()) . '
								AND game_id = ' . $this->db->escapeNumber($this->getGameID()));
			foreach ($this->weapons as $orderID => $weapon) {
				$this->db->query('INSERT INTO ship_has_weapon (account_id, game_id, order_id, weapon_type_id)
								VALUES(' . $this->db->escapeNumber($this->getAccountID()) . ', ' . $this->db->escapeNumber($this->getGameID()) . ', ' . $this->db->escapeNumber($orderID) . ', ' . $this->db->escapeNumber($weapon->getWeaponTypeID()) . ')');
			}
			$this->hasChangedWeapons = false;
		}
	}
	
	function update() {
		
		// write ship infos
		$this->db->query('UPDATE player SET ship_type_id = ' . $this->db->escapeNumber($this->getShipTypeID()) . '
						WHERE account_id = ' . $this->db->escapeNumber($this->getAccountID()) . '
							AND game_id = ' . $this->db->escapeNumber($this->getGameID()));
		
	}
	
	function checkPowerLevel($powerLevel) {
		$this->db->query('SELECT weapon_type_id
							FROM ship_has_weapon
							JOIN weapon_type USING(weapon_type_id)
							WHERE account_id = ' . $this->db->escapeNumber($this->getAccountID()) . '
								AND game_id = ' . $this->db->escapeNumber($this->getGameID()) . '
								AND power_level = ' . $this->db->escapeNumber($powerLevel));
		
		switch ($powerLevel) {
			case 5:
				return $this->db->getNumRows() < 1;
			break;
			case 4:
				return $this->db->getNumRows() < 2;
			break;
			case 3:
				return $this->db->getNumRows() < 3;
			break;
			case 2:
				return $this->db->getNumRows() < 4;
			break;
			case 1:
				return true;
			break;
		}
		return true;
	}
	
	public function isCloaked() {
		if(!$this->hasCloak())
			return false;
		if(!isset($this->isCloaked)) {
			$this->db->query('SELECT 1 FROM ship_is_cloaked
								WHERE account_id = ' . $this->db->escapeNumber($this->getAccountID()) . '
								AND game_id = ' . $this->db->escapeNumber($this->getGameID()) . ' LIMIT 1');
			$this->isCloaked = $this->db->getNumRows()>0;
		}
		return $this->isCloaked;
	}
	
	function decloak() {
		$this->isCloaked = false;
		$this->db->query('DELETE FROM ship_is_cloaked
							WHERE account_id = ' . $this->db->escapeNumber($this->getAccountID()) . '
							AND game_id = ' . $this->db->escapeNumber($this->getGameID()) .' LIMIT 1');
	}
	
	function enableCloak() {
		$this->isCloaked = true;
		$this->db->query('REPLACE INTO ship_is_cloaked VALUES(' . $this->db->escapeNumber($this->getAccountID()) . ', ' . $this->db->escapeNumber($this->getGameID()) . ')');		
	}
	
	function cloakOverload() {
		// 1 in 25 chance of cloak being destroyed if active
		if($this->isCloaked() && mt_rand(0,99) < 5) {
			$this->db->query('DELETE FROM ship_has_hardware
								WHERE account_id=' . $this->db->escapeNumber($this->getAccountID()) . '
								AND game_id=' . $this->db->escapeNumber($this->getGameID()) . '
								AND hardware_type_id = 8
								LIMIT 1');
			$this->decloak();
			$this->setHardware(HARDWARE_CLOAK,0);
			return true;
		}
		
		return false;
	}
	
	function setIllusion($shipID, $attack, $defense) {
		$this->db->query('REPLACE INTO ship_has_illusion VALUES(' . $this->db->escapeNumber($this->getAccountID()) . ', ' . $this->db->escapeNumber($this->getGameID()) . ', ' . $this->db->escapeNumber($shipID) . ', ' . $this->db->escapeNumber($attack) . ', ' . $this->db->escapeNumber($defense) . ')');
	}
	
	function disableIllusion() {
		$this->db->query('DELETE FROM ship_has_illusion WHERE account_id = ' . $this->db->escapeNumber($this->getAccountID()) . ' AND game_id = ' . $this->db->escapeNumber($this->getGameID()) . ' LIMIT 1');
	}
	
	public function getIllusionShip() {
		if(!isset($this->illusionShip)) {
			$this->illusionShip=false;
			$this->db->query('SELECT ship_has_illusion.*,ship_type.ship_name
							FROM ship_has_illusion
							JOIN ship_type USING(ship_type_id)
							WHERE account_id = ' . $this->db->escapeNumber($this->getAccountID()) . ' AND game_id = ' . $this->db->escapeNumber($this->getGameID()) . ' LIMIT 1');
			if($this->db->nextRecord()) {
				$this->illusionShip=array(
										'ID' => $this->db->getInt('ship_type_id'),
										'Attack' => $this->db->getInt('attack'),
										'Defense' => $this->db->getInt('defense'),
										'Name' => $this->db->getField('ship_name'));
			}
		}
		return $this->illusionShip;
		
	}
	
	public function getUpgradeShipHREF() {
		return SmrSession::getNewHREF(create_container('skeleton.php', 'ship_upgrade.php'));
	}
}

?>
