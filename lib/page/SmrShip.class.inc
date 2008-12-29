<?
require_once('SmrMySqlDatabase.class.inc');

require_once('SmrPlayer.class.inc');
require_once('SmrWeapon.class.inc');
require_once('SmrCombatDrones.class.inc');
class SmrShip
{
	protected static $CACHE_SHIPS = array();
	
	protected $db;
	
	protected $accountID;
	protected $gameID;
	protected $shipName;
	protected $shipTypeID;
	
	protected $hardware;
	protected $maxHardware;
	protected $oldHardware;
	
	protected $cargo;
	protected $cargo_left;
	
	protected $speed;
	protected $cost;
	
	protected $hardpoint;
	protected $weapon;
	
	protected $player_exp;
	protected $player_level;
	
	protected $illusionShip;
	
	protected $upgradeShipHREF;
	
	public static function saveShips()
	{
		foreach(self::$CACHE_SHIPS as &$ship)
		{
			$ship->update_hardware();
			$ship->update_weapon();
			$ship->update_cargo();
			$ship->update();
		}
	}

	public static function &getShip($gameID,$accountID,$forceUpdate = false)
	{
		if($forceUpdate || !isset(self::$CACHE_SHIPS[$gameID . ':' . $accountID]))
		{
			$s = new SmrShip($gameID,$accountID);
			return self::$CACHE_SHIPS[$gameID . ':' . $s->getAccountID()] =& $s;
		}
		return self::$CACHE_SHIPS[$gameID. ':' .$accountID];
	}
	
	protected function __construct($gameID, $accountID)
	{
		$this->db = new SmrMySqlDatabase();
		$this->accountID = $accountID;
		$this->gameID = $gameID;
		
		$this->load_ship();
		$this->load_hardware();
		$this->load_weapon();
		$this->load_cargo();
	}
	
	function load_ship()
	{
		// determine ship
		$this->db->query('SELECT * FROM player, ship_type WHERE player.ship_type_id = ship_type.ship_type_id AND ' .
			'account_id = '.$this->getAccountID().' AND ' .
			'game_id = '.$this->getGameID().' LIMIT 1');
		$this->db->nextRecord();
		$this->shipName			= stripslashes($this->db->getField('ship_name'));
		$this->shipTypeID			= $this->db->getField('ship_type_id');
		$this->hardpoint			= $this->db->getField('hardpoint');
		$this->speed				= $this->db->getField('speed');
		$this->cost					= $this->db->getField('cost');
		$this->player_exp			= $this->db->getField('experience');
		$this->db->query('SELECT * FROM level WHERE requirement <= '.$this->player_exp.' ORDER BY level_id DESC LIMIT 1');
		if ($this->db->nextRecord())
			$this->player_level		= $this->db->getField('level_id');
		else
			$this->player_level		= 1;
		
	}
	
	function load_weapon() {
		
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
	
	function attack_rating()
	{
		return $this->getAttackRating();
	}
	
	public function hasIllegalGoods()
	{
		return $ship->hasCargo(5) || $ship->hasCargo(9) || $ship->hasCargo(12);
	}
	
	public function getAttackRating()
	{
		// for rating
		$this->db->query('SELECT sum(shield_damage), sum(armor_damage) FROM ship_has_weapon, weapon_type ' .
			'WHERE ship_has_weapon.weapon_type_id = weapon_type.weapon_type_id AND ' .
			'account_id = '.$this->getAccountID().' AND ' .
			'game_id = '.$this->getGameID());
		$this->db->nextRecord();
		$sum_shield_damage = $this->db->getField('sum(shield_damage)');
		$sum_armor_damage = $this->db->getField('sum(armor_damage)');
		$maxDronesPercent = (35 + $this->player_level * .6 + ($this->player_level - 1) * .4 + 15) * .01;
		$maxDrones = $maxDronesPercent * $this->getCDs();
		return round((($sum_shield_damage + $sum_armor_damage + $maxDrones * 2) / 40));
	}
	
	function defense_rating()
	{
		return $this->getDefenseRating();
	}
	
	public function getDefenseRating()
	{
		return round((($this->getShields() + $this->getArmour()) / 100) + (($this->getCDs() * 3) / 100));
	}
	
	function update_cargo() {
		
		$this->cargo_left = $this->getCargoHolds();
		
		// write cargo info
		foreach ($this->cargo as $id => $amount) {
			
			$this->cargo_left -= $amount;
			
			if ($amount != 0)
				$this->db->query('REPLACE INTO ship_has_cargo (account_id, game_id, good_id, amount) VALUES('.$this->getAccountID().', '.$this->getGameID().', '.$id.', '.$amount.')');
			else
				$this->db->query('DELETE FROM ship_has_cargo WHERE account_id = '.$this->getAccountID().' AND game_id = '.$this->getGameID().' AND good_id = '.$id.' LIMIT 1');
		}
	}
	
	function update_hardware()
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
	}
	
	function update_weapon() {
		
		// write weapon info
		$this->db->query('DELETE FROM ship_has_weapon ' .
			'WHERE account_id = '.$this->getAccountID().' AND ' .
			'game_id = '.$this->getGameID());
		$count = 1;
		foreach ($this->weapons as $order_id => &$weapon)
		{
			$this->db->query('INSERT INTO ship_has_weapon (account_id, game_id, order_id, weapon_type_id) ' .
				'VALUES('.$this->getAccountID().', '.$this->getGameID().', '.$count.', '.$weapon->getWeaponTypeID().')');
			$count++;
		}
	}
	
	function update() {
		
		// write ship infos
		$this->db->query('UPDATE player SET ship_type_id = '.$this->getShipTypeID().' ' .
			'WHERE account_id = '.$this->getAccountID().' AND ' .
			'game_id = '.$this->getGameID());
		
	}
	
	function shield_low() { return floor($this->getShields() / 100) * 100; }
	function shield_high() { return $this->shield_low() + 100; }
	function armor_low() { return floor($this->getArmour() / 100) * 100; }
	function armor_high() { return $this->armor_low() + 100; }
	function combat_drones_low() { return floor($this->getCDs() / 100) * 100; }
	function combat_drones_high() { return $this->combat_drones_low() + 100; }
	
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
	
	function &addWeapon($weaponTypeID)
	{
		$weapon =& SmrWeapon::getWeapon($this->getGameID(),$weaponTypeID);
		$this->weapons[] = $weapon;
		$this->update_weapon();
		return $weapon;
	}
	
	function moveWeaponUp($orderID)
	{
		$replacement = $orderID - 1;
		if($replacement < 1)
		{
			// Shift everything up by one and put the selected weapon at the bottom
			$temp =& $this->weapon[$orderID];
			for($i=1;$i<count($this->weapon);++$i)
			{
				$ship->weapon[$i] =& $this->weapon[$i+1];
			}
			$ship->weapon[count($this->weapon)] =& $temp;
		}
		else
		{
			$temp =& $this->weapon[$replacement];
			$this->weapon[$replacement] =& $this->weapon[$orderID];
			$this->weapon[$orderID] =& $temp;
		}
	}
	
	function moveWeaponDown($orderID)
	{
		$replacement = $orderID + 1;
		if($replacement > count($this->weapon))
		{
			// Shift everything down by one and put the selected weapon at the top
			$temp =& $this->weapon[count($this->weapon)];
			for($i=count($this->weapon);$i>1;--$i) {
				$this->weapon[$i] =& $this->weapon[$i-1];
			}	
			$this->weapon[1] =& $temp;
		}
		else {
			$temp =& $ship->weapon[$replacement];
			$this->weapon[$replacement] =& $this->weapon[$orderID];
			$this->weapon[$orderID] =& $temp;
		}
	}
	
	function remove_weapon($weapon_type_id) {
		
		$this->db->query('DELETE FROM ship_has_weapon WHERE account_id = '.$this->getAccountID().' AND ' .
			'game_id = '.$this->getGameID().' AND ' .
			'weapon_type_id = '.$weapon_type_id);
		
	}
	
	function remove_all_weapon() {
		
		$this->weapons = array();
		$this->db->query('DELETE FROM ship_has_weapon WHERE account_id = '.$this->getAccountID().' AND ' .
			'game_id = '.$this->getGameID());
		
	}
	
	function remove_all_cargo() {
		
		$this->cargo = array();
		$this->db->query('DELETE FROM ship_has_cargo WHERE account_id = '.$this->getAccountID().' AND ' .
			'game_id = '.$this->getGameID());
		
	}
	
	function remove_all_hardware() {
		
		$this->hardware = array();
		$this->db->query('DELETE FROM ship_has_hardware WHERE account_id = '.$this->getAccountID().' AND ' .
			'game_id = '.$this->getGameID());
		$this->db->query('DELETE FROM ship_has_illusion WHERE account_id = '.$this->getAccountID().' AND ' .
			'game_id = '.$this->getGameID());
		$this->db->query('DELETE FROM ship_is_cloaked WHERE account_id = '.$this->getAccountID().' AND ' .
			'game_id = '.$this->getGameID());
		
	}
	
	function get_pod()
	{
		$this->remove_all_weapon();
		$this->remove_all_cargo();
		$this->remove_all_hardware();
		
		$this->hardware[1] = 50;
		$this->hardware[2] = 50;
		$this->hardware[3] = 5;
		$this->update_hardware();
		
		$this->shipTypeID = 69;
		$this->update();
		
		$this->mark_seen();
		
	}
	
	function mark_seen() {
		foreach ($this->hardware as $hardware_type_id => $amount) {
			$this->db->query('UPDATE ship_has_hardware SET old_amount = '.$amount.' ' .
				'WHERE account_id = '.$this->getAccountID().' AND ' .
				'game_id = '.$this->getGameID().' AND ' .
				'hardware_type_id = '.$hardware_type_id.' LIMIT 1');
		}
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
	
	public function hasCloak()
	{
		return $this->getHardware(HARDWARE_CLOAK);
	}
	
	public function canHaveCloak()
	{
		return $this->getMaxHardware(HARDWARE_CLOAK);
	}
	
	
	public function hasActiveIllusion()
	{
		if(!$this->hasIllusion())
			return false;
		return $this->getIllusionShip()!==false;
		
	}
	
	public function hasIllusion()
	{
		return $this->getHardware(HARDWARE_ILLUSION);
	}
	
	public function canHaveIllusion()
	{
		return $this->getMaxHardware(HARDWARE_ILLUSION);
	}
	
	public function hasJump()
	{
		return $this->getHardware(HARDWARE_JUMP);
	}
	
	public function canHaveJump()
	{
		return $this->getMaxHardware(HARDWARE_JUMP);
	}
	
	public function hasDCS()
	{
		return $this->getHardware(HARDWARE_DCS);
	}
	
	public function canHaveDCS()
	{
		return $this->getMaxHardware(HARDWARE_DCS);
	}
	
	public function hasScanner()
	{
		return $this->getHardware(HARDWARE_SCANNER);
	}
	
	public function canHaveScanner()
	{
		return $this->getMaxHardware(HARDWARE_SCANNER);
	}
	
	function decloak()
	{
		$this->isCloaked = false;
		$this->db->query('DELETE FROM ship_is_cloaked WHERE account_id = '.$this->getAccountID().' AND ' .
			'game_id = '.$this->getGameID() .' LIMIT 1');
	}
	
	function enable_cloak()
	{
		$this->isCloaked = true;
		$this->db->query('REPLACE INTO ship_is_cloaked VALUES('.$this->getAccountID().', '.$this->getGameID().')');
		
	}
	
	function cloak_overload() {
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
			$this->hardware[8] = 0;
			return true;
		}	
		
		return false;
		
	}
	
	function set_illusion($ship_id, $attack, $defense) {
		
		$this->db->query('REPLACE INTO ship_has_illusion VALUES('.$this->getAccountID().', '.$this->getGameID().', '.$ship_id.', '.$attack.', '.$defense.')');
		
	}
	
	function disable_illusion() {
		
		$this->db->query('DELETE FROM ship_has_illusion WHERE account_id = '.$this->getAccountID().' AND game_id = '.$this->getGameID().' LIMIT 1');
		
	}
	
	function get_illusion()
	{
		return $this->getIllusionShipID();
	}
	
	function getIllusionShipID()
	{
		$this->getIllusionShip();
		return $this->illusionShip['ID'];
	}
	
	function getIllusionShipName()
	{
		$this->getIllusionShip();
		return $this->illusionShip['Name'];
	}
	
	public function getIllusionShip()
	{
		if(!isset($this->illusionShip))
		{
			$this->illusionShip=false;
			$this->db->query('SELECT ship_has_illusion.*,ship_type.ship_name FROM ship_has_illusion NATURAL JOIN ship_type WHERE account_id = '.$this->getAccountID().' AND ' .
				'game_id = '.$this->getGameID().' LIMIT 1');
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
	
	function get_illusion_attack()
	{
		return $this->getIllusionAttack();
		
	}
	
	function getIllusionAttack()
	{
		$this->getIllusionShip();
		return $this->illusionShip['Attack'];
		
	}
	
	function get_illusion_defense()
	{
		return $this->getIllusionDefense();
	}
	
	function getIllusionDefense()
	{
		$this->getIllusionShip();
		return $this->illusionShip['Defense'];
	}
	
	function under_attack($hardware_type_id) {
		
		$this->db->query('SELECT * FROM ship_has_hardware WHERE account_id = '.$this->getAccountID().' AND game_id = '.$this->getGameID().' AND hardware_type_id = '.$hardware_type_id.' AND amount != old_amount LIMIT 1');
		return $this->db->getNumRows();
		
	}
	
	public function &getPlayer()
	{
		return SmrPlayer::getPlayer($this->getAccountID(),$this->getGameID());
	}
	
	public function getAccountID()
	{
		return $this->accountID;
	}
	
	public function getGameID()
	{
		return $this->gameID;
	}
	
	public function getShipTypeID()
	{
		return $this->shipTypeID;
	}
	
	public function setShipTypeID($shipTypeID)
	{
		$this->shipTypeID=$shipTypeID;
	}
	
	public function getName()
	{
		return $this->shipName;
	}
	
	public function getCost()
	{
		return $this->cost;
	}
	
	public function getSpeed()
	{
		return $this->speed;
	}
	
	public function getHardware($hardwareTypeID)
	{
		return isset($this->hardware[$hardwareTypeID]) ? $this->hardware[$hardwareTypeID] : 0;
	}
	
	public function setHardware($hardwareTypeID,$amount)
	{
		$this->hardware[$hardwareTypeID] = $amount;
	}
	
	public function increaseHardware($hardwareTypeID,$amount)
	{
		$this->setHardware($hardwareTypeID,$this->getHardware($hardwareTypeID)+$amount);
	}
	
	public function getOldHardware($hardwareTypeID)
	{
		return isset($this->oldHardware[$hardwareTypeID]) ? $this->oldHardware[$hardwareTypeID] : 0;
	}
	
	public function setOldHardware($hardwareTypeID,$amount)
	{
		$this->oldHardware[$hardwareTypeID] = $amount;
	}
	
	public function getMaxHardware($hardwareTypeID)
	{
		return $this->maxHardware[$hardwareTypeID];
	}
	
	public function getShields()
	{
		return $this->getHardware(HARDWARE_SHIELDS);
	}
	
	public function setShields($amount,$updateOldAmount=false)
	{
		if($updateOldAmount && !$this->hasLostShields())
			$this->setOldHardware(HARDWARE_SHIELDS,$amount);
		$this->setHardware(HARDWARE_SHIELDS,$amount);
	}
	
	public function decreaseShields($amount)
	{
		$this->setShields($this->getShields()-$amount);
	}
	
	public function increaseShields($amount)
	{
		$this->setShields($this->getShields()+$amount);
	}
	
	public function getOldShields()
	{
		return $this->getOldHardware(HARDWARE_SHIELDS);
	}
	
	public function setOldShields($amount)
	{
		$this->setOldHardware(HARDWARE_SHIELDS,$amount);
	}
	
	public function hasShields()
	{
		return $this->getShields()>0;
	}
	
	public function hasLostShields()
	{
		return $this->getShields()<$this->getOldShields();
	}
	
	public function getMaxShields()
	{
		return $this->getMaxHardware(HARDWARE_SHIELDS);
	}
	
	public function getArmour()
	{
		return $this->getHardware(HARDWARE_ARMOUR);
	}
	
	public function setArmour($amount,$updateOldAmount=false)
	{
		if($updateOldAmount && !$this->hasLostArmour())
			$this->setOldHardware(HARDWARE_ARMOUR,$amount);
		$this->setHardware(HARDWARE_ARMOUR,$amount);
	}
	
	public function decreaseArmour($amount)
	{
		$this->setArmour($this->getArmour()-$amount);
	}
	
	public function increaseArmour($amount)
	{
		$this->setArmour($this->getArmour()+$amount);
	}
	
	public function getOldArmour()
	{
		return $this->getOldHardware(HARDWARE_ARMOUR);
	}
	
	public function setOldArmour($amount)
	{
		$this->setOldHardware(HARDWARE_ARMOUR,$amount);
	}
	
	public function hasArmour()
	{
		return $this->getArmour()>0;
	}
	
	public function hasLostArmour()
	{
		return $this->getArmour()<$this->getOldArmour();
	}
	
	public function getMaxArmour()
	{
		return $this->getMaxHardware(HARDWARE_ARMOUR);
	}
	
	public function isDead()
	{
		return !$this->hasArmour()&&!$this->hasShields();
	}
	
	public function canAcceptCDs()
	{
		return $this->getCDs()<$this->getMaxCDs();
	}
	
	public function canAcceptSDs()
	{
		return $this->getSDs()<$this->getMaxSDs();
	}
	
	public function canAcceptMines()
	{
		return $this->getMines()<$this->getMaxMines();
	}
	
	public function hasCDs()
	{
		return $this->getCDs()>0;
	}
	
	public function hasSDs()
	{
		return $this->getSDs()>0;
	}
	
	public function hasMines()
	{
		return $this->getMines()>0;
	}
	
	public function getCDs()
	{
		return $this->getHardware(HARDWARE_COMBAT);
	}
	
	public function setCDs($amount,$updateOldAmount=false)
	{
		if($updateOldAmount && !$this->hasLostCDs())
			$this->setOldHardware(HARDWARE_COMBAT,$amount);
		$this->setHardware(HARDWARE_COMBAT,$amount);
	}
	
	public function decreaseCDs($amount,$updateOldAmount=false)
	{
		$this->setCDs($this->getCDs()-$amount,$updateOldAmount);
	}
	
	public function increaseCDs($amount)
	{
		$this->setCDs($this->getCDs()+$amount);
	}
	
	public function getOldCDs()
	{
		return $this->getOldHardware(HARDWARE_COMBAT);
	}
	
	public function setOldCDs($amount)
	{
		$this->setOldHardware(HARDWARE_COMBAT,$amount);
	}
	
	public function hasLostCDs()
	{
		return $this->getCDs()<$this->getOldCDs();
	}
	
	public function getMaxCDs()
	{
		return $this->getMaxHardware(HARDWARE_COMBAT);
	}
	
	public function getSDs()
	{
		return $this->getHardware(HARDWARE_SCOUT);
	}
	
	public function setSDs($amount)
	{
		$this->setHardware(HARDWARE_SCOUT,$amount);
	}
	
	public function decreaseSDs($amount)
	{
		$this->setSDs($this->getSDs()-$amount);
	}
	
	public function increaseSDs($amount)
	{
		$this->setSDs($this->getSDs()+$amount);
	}
	
	public function getMaxSDs()
	{
		return $this->getMaxHardware(HARDWARE_SCOUT);
	}
	
	public function getMines()
	{
		return $this->getHardware(HARDWARE_MINE);
	}
	
	public function setMines($amount)
	{
		$this->setHardware(HARDWARE_MINE,$amount);
	}
	
	public function decreaseMines($amount)
	{
		$this->setMines($this->getMines()-$amount);
	}
	
	public function increaseMines($amount)
	{
		$this->setMines($this->getMines()+$amount);
	}
	
	public function getMaxMines()
	{
		return $this->getMaxHardware(HARDWARE_MINE);
	}
	
	public function getCargoHolds()
	{
		return $this->getHardware(HARDWARE_CARGO);
	}
	
	public function getCargo($goodID=false)
	{
		if($goodID!==false)
			return $this->cargo[$goodID];
		return $this->cargo;
	}
	
	public function hasCargo($goodID=false)
	{
		if($goodID!==false)
			return $this->getCargo($goodID)>0;
		return array_sum($this->getCargo())>0;
	}
	
	public function setCargo($goodID,$amount)
	{
		$this->cargo[$goodID] = $amount;
	}
	
	public function decreaseCargo($goodID,$amount)
	{
		$this->setCargo($goodID,$this->getCargo($goodID)-$amount);
	}
	
	public function increaseCargo($goodID,$amount)
	{
		$this->setCargo($goodID,$this->getCargo($goodID)+$amount);
	}
	
	public function getEmptyHolds()
	{
		return $this->cargo_left;
	}
	
	
	public function getMaxCargoHolds()
	{
		return $this->getMaxHardware(HARDWARE_CARGO);
	}
	
	public function isUnderAttack()
	{
		return $this->hasLostShields() || $this->hasLostArmour() || $this->hasLostCDs();
	}
	
	public function removeUnderAttack()
	{
		$underAttack = $this->isUnderAttack();
		$this->setOldShields($this->getShields());
		$this->setOldCDs($this->getCDs());
		$this->setOldArmour($this->getArmour());
		return $underAttack;
	}
	
	public function hasWeapons()
	{
		return $this->getNumWeapons()>0;
	}
	
	public function &getWeapons()
	{
		return $this->weapons;
	}
	
	public function removeWeapon($orderID)
	{
		unset($this->weapons[$orderID]);
	}
	
	public function getNumWeapons()
	{
		return count($this->getWeapons());
	}
	
	public function getOpenWeaponSlots()
	{
		return $this->getHardpoints-$this->getNumWeapons();
	}
	
	public function getHardpoints()
	{
		return $this->hardpoint;
	}
	
	public function getTotalShieldDamage()
	{
		$weapons = $this->getWeapons();
		$shieldDamage = 0;
		foreach($weapons as &$weapon)
		{
			$shieldDamage+=$weapon->getShieldDamage();
		}
		return $shieldDamage;
	}
	
	public function getTotalArmourDamage()
	{
		$weapons = $this->getWeapons();
		$armourDamage = 0;
		foreach($weapons as &$weapon)
		{
			$armourDamage+=$weapon->getArmourDamage();
		}
		return $armourDamage;
	}
	
	public function getTotalUpgradeSpace()
	{
		//TODO
		return 0;
	}
	
	public function getUsedUpgradeSpace()
	{
		//TODO
		return 0;
	}
	
	public function getFreeUpgradeSpace()
	{
		return $this->getTotalUpgradeSpace()-$this->getUsedUpgradeSpace();
	}
	
	public function getLastDowngrade()
	{
		return 0;
	}
	
	public function canDowngrade()
	{
		return $this->getRemainingDowngradeWait() <= 0;
	}
	
	public function getRemainingDowngradeWait()
	{
		$downgradeWait = 24 * 3600;
		return $this->getLastDowngrade() - (TIME - $downgradeWait);
	}
	
	public function &shootPlayer(AbstractSmrPlayer &$targetPlayer)
	{
		return $this->shootPlayers(array(&$targetPlayer));
	}
	
	public function &shootPlayers(array &$targetPlayers)
	{
		$thisPlayer =& $this->getPlayer();
		$results = array('Player' => &$thisPlayer, 'TotalDamage' => 0);
		if($thisPlayer->isDead())
		{
			$results['DeadBeforeShot'] = false;
			return $results;
		}
		$results['DeadBeforeShot'] = false;
		foreach($this->weapons as $orderID => &$weapon)
		{
			$results['Weapons'][$orderID] =& $weapon->shootPlayer($thisPlayer, $targetPlayers[array_rand($targetPlayers)]);
			if($results['Weapons'][$orderID]['Hit'])
				$results['TotalDamage'] += $results['Weapons'][$orderID]['ActualDamage']['TotalDamage'];
		}
		if($this->hasCDs())
		{
			$thisCDs = new SmrCombatDrones($this->getGameID(),$this->getCDs());
			$results['Drones'] =& $thisCDs->shootPlayer($thisPlayer, $targetPlayers[array_rand($targetPlayers)]);
			$results['TotalDamage'] += $results['Drones']['ActualDamage']['TotalDamage'];
		}
		$thisPlayer->increaseExperience($results['TotalDamage']/4); // 1/4 weapon damage to exp.
		$thisPlayer->increaseHOF($results['TotalDamage'],'player_damage');
		return $results;
	}
	
	public function &doWeaponDamage(array $damage)
	{
		$alreadyDead = $this->getPlayer()->isDead();
		$armourDamage = 0;
		$cdDamage = 0;
		$shieldDamage = 0;
		if(!$alreadyDead)
		{
			$shieldDamage = $this->doShieldDamage(min($damage['MaxDamage'],$damage['Shield']));
			$damage['MaxDamage'] -= $shieldDamage;
			if(!$this->hasShields() && ($shieldDamage == 0 || $damage['Rollover']))
			{
				$cdDamage = $this->doCDDamage(min($damage['MaxDamage'],$damage['Armour']));
				$damage['Armour'] -= $cdDamage;
				$damage['MaxDamage'] -= $cdDamage;
				if(!$this->hasCDs() && ($cdDamage == 0 || $damage['Rollover']))
					$armourDamage = $this->doArmourDamage(min($damage['MaxDamage'],$damage['Armour']));
			}
		}
		$return = array(
						'KillingShot' => $alreadyDead != $this->isDead(),
						'TargetAlreadyDead' => $alreadyDead,
						'Shield' => $shieldDamage,
						'CDs' => $cdDamage,
						'NumCDs' => $cdDamage/CD_ARMOUR,
						'Armour' => $armourDamage,
						'HasCDs' => $this->hasCDs(),
						'TotalDamage' => $shieldDamage+$cdDamage+$armourDamage
		);
		return $return;
	}
	
	protected function doShieldDamage($damage)
	{
		$actualDamage = min($this->getShields(),$damage);
		$this->decreaseShields($actualDamage);
		return $actualDamage;
	}
	
	protected function doCDDamage($damage)
	{
		$actualDamage = min($this->getCDs(),$damage/CD_ARMOUR);
		$this->decreaseCDs($actualDamage);
		return $actualDamage*CD_ARMOUR;
	}
	
	protected function doArmourDamage($damage)
	{
		$actualDamage = min($this->getArmour(),$damage);
		$this->decreaseArmour($actualDamage);
		return $actualDamage;
	}
	
	public function getMR()
	{
		return round(
						700 - 
						(
							(
								$this->getShields()
								+$this->getArmour()
								+$this->getCDs()*3
							)/25
							+(
								$this->getCargoHolds()/100
								-$this->getSpeed()*5
								+($this->getHardpoints()/*+$ship['Increases']['Ship Power']*/)*5
								/*+(
									$ship['Increases']['Mines']
									+$ship['Increases']['Scout Drones']
								)/12*/
								+$this->getCDs()/5
							)
						)
//						+ $player->getStat('Maneuverability') * $MANEUV_MOD
					);
	}
	
	public function getUpgradeShipHREF()
	{
		if(!isset($this->upgradeShipHREF))
		{
			$container = array();
			$container['url']		= 'skeleton.php';
			$container['body']		= 'ship_upgrade.php';
			$this->upgradeShipHREF = SmrSession::get_new_href($container);
		}
		return $this->upgradeShipHREF;
	}
}

?>