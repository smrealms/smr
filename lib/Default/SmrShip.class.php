<?php declare(strict_types=1);

class SmrShip extends AbstractSmrShip {
	protected static $CACHE_SHIPS = array();
	
	protected $db;
	protected $SQL;
	
	public static function refreshCache() {
		foreach (self::$CACHE_SHIPS as &$gameShips) {
			foreach ($gameShips as &$ship) {
				$ship = self::getShip($ship->getPlayer(), true);
			}
		}
	}
	
	public static function clearCache() {
		self::$CACHE_SHIPS = array();
	}
	
	public static function saveShips() {
		foreach (self::$CACHE_SHIPS as $gameShips) {
			foreach ($gameShips as $ship) {
				$ship->update();
			}
		}
	}

	public static function getShip(AbstractSmrPlayer $player, $forceUpdate = false) {
		if ($forceUpdate || !isset(self::$CACHE_SHIPS[$player->getGameID()][$player->getAccountID()])) {
			$s = new SmrShip($player);
			self::$CACHE_SHIPS[$player->getGameID()][$player->getAccountID()] = $s;
		}
		return self::$CACHE_SHIPS[$player->getGameID()][$player->getAccountID()];
	}
	
	protected function __construct(AbstractSmrPlayer $player) {
		parent::__construct($player);
		$this->db = new SmrMySqlDatabase();
		$this->SQL = 'account_id=' . $this->db->escapeNumber($this->getAccountID()) . ' AND game_id=' . $this->db->escapeNumber($this->getGameID());
		
		$this->loadHardware();
		$this->loadWeapons();
		$this->loadCargo();
	}
	
	/**
	 * Initialize the weapons onboard this ship.
	 */
	protected function loadWeapons() {
		// determine weapon
		$this->db->query('SELECT weapon_type.*, order_id FROM ship_has_weapon JOIN weapon_type USING (weapon_type_id)
							WHERE ' . $this->SQL . '
							ORDER BY order_id LIMIT ' . $this->db->escapeNumber($this->getHardpoints()));
		
		$this->weapons = array();
		// generate list of weapon names the user transports
		while ($this->db->nextRecord()) {
			$this->weapons[$this->db->getInt('order_id')] = SmrWeapon::getWeapon($this->db->getInt('weapon_type_id'), false, $this->db);
		}
		$this->checkForExcessWeapons();
	}
	
	protected function loadCargo() {
		if (!isset($this->cargo)) {
			// initialize cargo array
			$this->cargo = array();
			
			// get cargo from db
			$this->db->query('SELECT * FROM ship_has_cargo WHERE ' . $this->SQL);
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
							WHERE ' . $this->SQL);
		
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
	
	public function updateCargo() {
		if ($this->hasChangedCargo === true) {
			// write cargo info
			foreach ($this->getCargo() as $id => $amount) {
				if ($amount > 0) {
					$this->db->query('REPLACE INTO ship_has_cargo (account_id, game_id, good_id, amount) VALUES(' . $this->db->escapeNumber($this->getAccountID()) . ', ' . $this->db->escapeNumber($this->getGameID()) . ', ' . $this->db->escapeNumber($id) . ', ' . $this->db->escapeNumber($amount) . ')');
				} else {
					$this->db->query('DELETE FROM ship_has_cargo WHERE ' . $this->SQL . ' AND good_id = ' . $this->db->escapeNumber($id) . ' LIMIT 1');
					// Unset now to omit displaying this good with 0 amount
					// before the next page is loaded.
					unset($this->cargo[$id]);
				}
			}
			$this->hasChangedCargo = false;
		}
	}
	
	public function updateHardware() {
		// write hardware info only for hardware that has changed
		foreach ($this->hasChangedHardware as $hardwareTypeID => $hasChanged) {
			if (!$hasChanged) {
				continue;
			}
			$amount = $this->getHardware($hardwareTypeID);
			if ($amount > 0) {
				$this->db->query('REPLACE INTO ship_has_hardware (account_id, game_id, hardware_type_id, amount, old_amount) VALUES(' . $this->db->escapeNumber($this->getAccountID()) . ', ' . $this->db->escapeNumber($this->getGameID()) . ', ' . $this->db->escapeNumber($hardwareTypeID) . ', ' . $this->db->escapeNumber($amount) . ', ' . $this->db->escapeNumber($this->getOldHardware($hardwareTypeID)) . ')');
			} else {
				$this->db->query('DELETE FROM ship_has_hardware WHERE ' . $this->SQL . ' AND hardware_type_id = ' . $this->db->escapeNumber($hardwareTypeID));
			}
		}
		$this->hasChangedHardware = array();
	}
	
	private function updateWeapon() {
		if ($this->hasChangedWeapons === true) {
			// write weapon info
			$this->db->query('DELETE FROM ship_has_weapon WHERE ' . $this->SQL);
			foreach ($this->weapons as $orderID => $weapon) {
				$this->db->query('INSERT INTO ship_has_weapon (account_id, game_id, order_id, weapon_type_id)
								VALUES(' . $this->db->escapeNumber($this->getAccountID()) . ', ' . $this->db->escapeNumber($this->getGameID()) . ', ' . $this->db->escapeNumber($orderID) . ', ' . $this->db->escapeNumber($weapon->getWeaponTypeID()) . ')');
			}
			$this->hasChangedWeapons = false;
		}
	}
	
	public function update() {
		$this->updateHardware();
		$this->updateWeapon();
		$this->updateCargo();
		// note: SmrShip::setShipTypeID updates the SmrPlayer only
		$this->getPlayer()->update();
	}
	
	/**
	 * given power level of new weapon, return whether there is enough power available to install it on this ship
	 */
	public function checkPowerAvailable($powerLevel) {
		return $this->getRemainingPower() >= $powerLevel;
	}
	
	public function isCloaked() {
		if (!$this->hasCloak()) {
			return false;
		}
		if (!isset($this->isCloaked)) {
			$this->db->query('SELECT 1 FROM ship_is_cloaked WHERE ' . $this->SQL . ' LIMIT 1');
			$this->isCloaked = $this->db->getNumRows() > 0;
		}
		return $this->isCloaked;
	}
	
	public function decloak() {
		$this->isCloaked = false;
		$this->db->query('DELETE FROM ship_is_cloaked WHERE ' . $this->SQL . ' LIMIT 1');
	}
	
	public function enableCloak() {
		$this->isCloaked = true;
		$this->db->query('REPLACE INTO ship_is_cloaked VALUES(' . $this->db->escapeNumber($this->getAccountID()) . ', ' . $this->db->escapeNumber($this->getGameID()) . ')');		
	}
	
	public function cloakOverload() {
		// 1 in 25 chance of cloak being destroyed if active
		if ($this->isCloaked() && mt_rand(0, 99) < 5) {
			$this->db->query('DELETE FROM ship_has_hardware
								WHERE ' . $this->SQL . '
								AND hardware_type_id = 8
								LIMIT 1');
			$this->decloak();
			$this->setHardware(HARDWARE_CLOAK, 0);
			return true;
		}
		
		return false;
	}
	
	public function setIllusion($shipID, $attack, $defense) {
		$this->db->query('REPLACE INTO ship_has_illusion VALUES(' . $this->db->escapeNumber($this->getAccountID()) . ', ' . $this->db->escapeNumber($this->getGameID()) . ', ' . $this->db->escapeNumber($shipID) . ', ' . $this->db->escapeNumber($attack) . ', ' . $this->db->escapeNumber($defense) . ')');
	}
	
	public function disableIllusion() {
		$this->db->query('DELETE FROM ship_has_illusion WHERE ' . $this->SQL . ' LIMIT 1');
	}
	
	public function getIllusionShip() {
		if (!isset($this->illusionShip)) {
			$this->illusionShip = false;
			$this->db->query('SELECT ship_has_illusion.*,ship_type.ship_name
							FROM ship_has_illusion
							JOIN ship_type USING(ship_type_id)
							WHERE ' . $this->SQL . ' LIMIT 1');
			if ($this->db->nextRecord()) {
				$this->illusionShip = array(
										'ID' => $this->db->getInt('ship_type_id'),
										'Attack' => $this->db->getInt('attack'),
										'Defense' => $this->db->getInt('defense'),
										'Name' => $this->db->getField('ship_name'));
			}
		}
		return $this->illusionShip;
		
	}

}
