<?
require_once('AbstractSmrShip.class.inc');
require_once('SmrMySqlDatabase.class.inc');

require_once('SmrPlayer.class.inc');
require_once('SmrWeapon.class.inc');
require_once('SmrCombatDrones.class.inc');
class SmrShip extends AbstractSmrShip
{
	protected static $CACHE_SHIPS = array();
	protected static $CACHE_BASE_SHIPS = array();
	
	protected $db;
	
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

	public static function &getShip(AbstractSmrPlayer &$player,$forceUpdate = false)
	{
		if($forceUpdate || !isset(self::$CACHE_SHIPS[$player->getGameID() . ':' . $player->getAccountID()]))
		{
			$s = new SmrShip($player);
			return self::$CACHE_SHIPS[$player->getGameID() . ':' . $s->getAccountID()] =& $s;
		}
		return self::$CACHE_SHIPS[$player->getGameID(). ':' .$player->getAccountID()];
	}
	
	protected function __construct(AbstractSmrPlayer &$player)
	{
		parent::__construct($player);
		$this->db = new SmrMySqlDatabase();
		
		$this->load_hardware();
		$this->load_weapon();
		$this->load_cargo();
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
	
	public function getAccountID()
	{
		return $this->getPlayer()->getAccountID();
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
		foreach ($this->weapons as $order_id => &$weapon)
		{
			$this->db->query('INSERT INTO ship_has_weapon (account_id, game_id, order_id, weapon_type_id) ' .
				'VALUES('.$this->getAccountID().', '.$this->getGameID().', '.$order_id.', '.$weapon->getWeaponTypeID().')');
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
	
	public function &addWeapon($weaponTypeID)
	{
		$weapon =& SmrWeapon::getWeapon($this->getGameID(),$weaponTypeID);
		$this->weapons[] = $weapon;
		$this->update_weapon();
		return $weapon;
	}
	
	public function moveWeaponUp($orderID)
	{
		$replacement = $orderID - 1;
		if($replacement < 1)
		{
			// Shift everything up by one and put the selected weapon at the bottom
			$temp =& $this->weapons[$orderID];
			for($i=1;$i<count($this->weapons);++$i)
			{
				$ship->weapons[$i] =& $this->weapons[$i+1];
			}
			$ship->weapon[count($this->weapons)] =& $temp;
		}
		else
		{
			$temp =& $this->weapons[$replacement];
			$this->weapons[$replacement] =& $this->weapons[$orderID];
			$this->weapons[$orderID] =& $temp;
		}
	}
	
	public function moveWeaponDown($orderID)
	{
		$replacement = $orderID + 1;
		if($replacement > count($this->weapons))
		{
			// Shift everything down by one and put the selected weapon at the top
			$temp =& $this->weapons[count($this->weapons)];
			for($i=count($this->weapons);$i>1;--$i) {
				$this->weapons[$i] =& $this->weapons[$i-1];
			}	
			$this->weapons[1] =& $temp;
		}
		else
		{
			$temp =& $this->weapons[$replacement];
			$this->weapons[$replacement] =& $this->weapons[$orderID];
			$this->weapons[$orderID] =& $temp;
		}
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
		$this->removeAllWeapons();
		$this->removeAllCargo();
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
			$this->hardware[8] = 0;
			return true;
		}	
		
		return false;
	}
	
	function set_illusion($ship_id, $attack, $defense)
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
	
	public function &getPlayer()
	{
		return $this->player;
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