<?php declare(strict_types=1);

class SmrWeapon extends AbstractSmrCombatWeapon {
	protected static $CACHE_WEAPONS = array();
	
	protected static $db;
	
	protected $weaponTypeID;
	protected $cost;
	protected $powerLevel;
	protected $buyerRestriction;

	
	protected static function initialiseDatabase() {
		if (self::$db == null) {
			self::$db = new SmrMySqlDatabase();
		}
	}

	public static function getWeapon($weaponTypeID, $forceUpdate = false, $db = null) {
		if ($forceUpdate || !isset(self::$CACHE_WEAPONS[$weaponTypeID])) {
			$w = new SmrWeapon($weaponTypeID, $db);
			if ($w->exists()) {
				self::$CACHE_WEAPONS[$weaponTypeID] = $w;
			} else {
				self::$CACHE_WEAPONS[$weaponTypeID] = false;
			}
		}
		return self::$CACHE_WEAPONS[$weaponTypeID];
	}

	public static function getAllWeapons($forceUpdate = false) {
		$db = new SmrMySqlDatabase();
		$db->query('SELECT * FROM weapon_type');
		$weapons = array();
		while ($db->nextRecord()) {
			$weapons[] = self::getWeapon($db->getInt('weapon_type_id'), $forceUpdate, $db);
		}
		return $weapons;
	}
	
	protected function __construct($weaponTypeID, $db = null) {
		
		$this->weaponTypeID = $weaponTypeID;
		
		if (isset($db)) {
			$weaponExists = true;
		} else {
			self::initialiseDatabase();
			$db = self::$db;
			self::$db->query('SELECT * FROM weapon_type WHERE weapon_type_id = ' . $db->escapeNumber($weaponTypeID) . ' LIMIT 1');
			$weaponExists = $db->nextRecord();
		}

		if ($weaponExists) {
			$this->name = $db->getField('weapon_name');
			$this->raceID = $db->getInt('race_id');
			$this->cost = $db->getInt('cost');
			$this->shieldDamage = $db->getInt('shield_damage');
			$this->armourDamage = $db->getInt('armour_damage');
			$this->accuracy = $db->getInt('accuracy');
			$this->powerLevel = $db->getInt('power_level');
			$this->buyerRestriction = $db->getInt('buyer_restriction');
			$this->damageRollover = false;
			$this->raidWeapon = false;
			$this->maxDamage = max($this->shieldDamage, $this->armourDamage);
		}
	}

	public function exists() {
		return !empty($this->name);
	}

	public function getBuyHREF(SmrLocation $location) {
		$container = create_container('shop_weapon_processing.php');
		$container['LocationID'] = $location->getTypeID();
		$container['WeaponTypeID'] = $this->getWeaponTypeID();
		return SmrSession::getNewHREF($container);
	}

	public function getSellHREF(SmrLocation $location, $orderID) {
		$container = create_container('shop_weapon_processing.php');
		$container['LocationID'] = $location->getTypeID();
		$container['WeaponTypeID'] = $this->getWeaponTypeID();
		$container['OrderID'] = $orderID;
		return SmrSession::getNewHREF($container);
	}
	
	public function getWeaponTypeID() {
		return $this->weaponTypeID;
	}
	
	public function getCost() {
		return $this->cost;
	}
	
	public function getPowerLevel() {
		return $this->powerLevel;
	}
	
	public function getBuyerRestriction() {
		return $this->buyerRestriction;
	}
	
	protected function getWeightedRandomForPlayer(AbstractSmrPlayer $player) {
		return WeightedRandom::getWeightedRandomForPlayer($player, 'Weapon', $this->getWeaponTypeID());
	}

	/**
	 * Given $weaponAccuracy as a percent, decide if the weapon hits.
	 */
	protected function checkHit(AbstractSmrPlayer $player, $weaponAccuracy) : bool {
		// Skip weighting factor for absolute hits/misses.
		if ($weaponAccuracy >= 100) {
			return true;
		} elseif ($weaponAccuracy <= 0) {
			return false;
		}
		return $this->getWeightedRandomForPlayer($player)->flipWeightedCoin($weaponAccuracy);
	}
	
	public static function getPlayerLevelAccuracyMod(AbstractSmrPlayer $player) {
		return ($player->getLevelID() * $player->getLevelID() / 60 + $player->getLevelID() / 2 + 2) / 100;
	}
	
	public function getModifiedAccuracy(AbstractSmrPlayer $weaponPlayer) {
		$modifiedAccuracy = $this->getBaseAccuracy();
		$modifiedAccuracy += $this->getBaseAccuracy() * self::getPlayerLevelAccuracyMod($weaponPlayer);
		return $modifiedAccuracy;
	}
	
	public function getModifiedAccuracyAgainstForces(AbstractSmrPlayer $weaponPlayer, SmrForce $forces) {
		$modifiedAccuracy = $this->getModifiedAccuracy($weaponPlayer);
		return $modifiedAccuracy;
	}
	
	public function getModifiedAccuracyAgainstPort(AbstractSmrPlayer $weaponPlayer, SmrPort $port) {
		$modifiedAccuracy = $this->getModifiedAccuracy($weaponPlayer);
		$modifiedAccuracy -= $this->getBaseAccuracy() * $port->getLevel() / 50;
		return $modifiedAccuracy;
	}
	
	public function getModifiedAccuracyAgainstPlanet(AbstractSmrPlayer $weaponPlayer, SmrPlanet $planet) {
		$modifiedAccuracy = $this->getModifiedAccuracy($weaponPlayer);
		$modifiedAccuracy -= $this->getBaseAccuracy() * $planet->getLevel() / 350;
		return $modifiedAccuracy;
	}
	
	public function getModifiedAccuracyAgainstPlayer(AbstractSmrPlayer $weaponPlayer, AbstractSmrPlayer $targetPlayer) {
		$modifiedAccuracy = $this->getModifiedAccuracy($weaponPlayer);
		$modifiedAccuracy -= $this->getBaseAccuracy() * self::getPlayerLevelAccuracyMod($targetPlayer) / 2;
		
		$weaponShip = $weaponPlayer->getShip();
		$targetShip = $targetPlayer->getShip();
		$mrDiff = $targetShip->getMR() - $weaponShip->getMR();
		if ($mrDiff > 0) {
			$modifiedAccuracy -= $this->getBaseAccuracy() * ($mrDiff / MR_FACTOR) / 100;
		}
	
		return $modifiedAccuracy;
	}
	
	public function getModifiedPortAccuracy(SmrPort $port) {
		$modifiedAccuracy = $this->getBaseAccuracy();
		return $modifiedAccuracy;
	}
	
	public function getModifiedPortAccuracyAgainstPlayer(SmrPort $port, AbstractSmrPlayer $targetPlayer) {
		$modifiedAccuracy = $this->getModifiedPortAccuracy($port);
		$modifiedAccuracy -= $this->getBaseAccuracy() * self::getPlayerLevelAccuracyMod($targetPlayer);
		return $modifiedAccuracy;
	}
	
	public function getModifiedPlanetAccuracy(SmrPlanet $planet) {
		$modifiedAccuracy = $this->getBaseAccuracy();
		if ($this->getWeaponTypeID() == WEAPON_PLANET_TURRET) {
			$modifiedAccuracy += $planet->getLevel() / 2;
		} else {
			$modifiedAccuracy += $planet->getAccuracyBonus();
		}
		return $modifiedAccuracy;
	}
	
	public function getModifiedPlanetAccuracyAgainstPlayer(SmrPlanet $planet, AbstractSmrPlayer $targetPlayer) {
		$modifiedAccuracy = $this->getModifiedPlanetAccuracy($planet);
		$modifiedAccuracy -= $this->getBaseAccuracy() * self::getPlayerLevelAccuracyMod($targetPlayer);
		return $modifiedAccuracy;
	}
	
	public function &getModifiedDamage() {
		$damage = $this->getDamage();
		return $damage;
	}
	
	public function &getModifiedDamageAgainstForces(AbstractSmrPlayer $weaponPlayer, SmrForce $forces) {
		if (!$this->canShootForces()) // If we can't shoot forces then just return a damageless array and don't waste resources calculated any damage mods.
			return array('MaxDamage' => 0, 'Shield' => 0, 'Armour' => 0, 'Rollover' => $this->isDamageRollover());
		$damage =& $this->getModifiedDamage();
		return $damage;
	}
	
	public function &getModifiedDamageAgainstPort(AbstractSmrPlayer $weaponPlayer, SmrPort $port) {
		if (!$this->canShootPorts()) // If we can't shoot forces then just return a damageless array and don't waste resources calculated any damage mods.
			return array('MaxDamage' => 0, 'Shield' => 0, 'Armour' => 0, 'Rollover' => $this->isDamageRollover());
		$damage =& $this->getModifiedDamage();
		return $damage;
	}
	
	public function &getModifiedDamageAgainstPlanet(AbstractSmrPlayer $weaponPlayer, SmrPlanet $planet) {
		if (!$this->canShootPlanets()) // If we can't shoot forces then just return a damageless array and don't waste resources calculated any damage mods.
			return array('MaxDamage' => 0, 'Shield' => 0, 'Armour' => 0, 'Rollover' => $this->isDamageRollover());
		$damage =& $this->getModifiedDamage();
		
		$planetMod = self::PLANET_DAMAGE_MOD;
		$damage['MaxDamage'] = ICeil($damage['MaxDamage'] * $planetMod);
		$damage['Shield'] = ICeil($damage['Shield'] * $planetMod);
		$damage['Armour'] = ICeil($damage['Armour'] * $planetMod);
		
		return $damage;
	}
	
	public function &getModifiedForceDamageAgainstPlayer(SmrForce $forces, AbstractSmrPlayer $targetPlayer) {
		$return = array('MaxDamage' => 0, 'Shield' => 0, 'Armour' => 0, 'Rollover' => $this->isDamageRollover());
		return $return;
	}
	
	public function &getModifiedDamageAgainstPlayer(AbstractSmrPlayer $weaponPlayer, AbstractSmrPlayer $targetPlayer) {
		if (!$this->canShootTraders()) { // If we can't shoot traders then just return a damageless array and don't waste resources calculating any damage mods.
			$return = array('MaxDamage' => 0, 'Shield' => 0, 'Armour' => 0, 'Rollover' => $this->isDamageRollover());
			return $return;
		}
		$damage =& $this->getModifiedDamage();
		return $damage;
	}
	
	public function &getModifiedPortDamageAgainstPlayer(SmrPort $port, AbstractSmrPlayer $targetPlayer) {
		if (!$this->canShootTraders()) { // If we can't shoot traders then just return a damageless array and don't waste resources calculating any damage mods.
			$return = array('MaxDamage' => 0, 'Shield' => 0, 'Armour' => 0, 'Rollover' => $this->isDamageRollover());
			return $return;
		}
		$damage = $this->getDamage();
		return $damage;
	}
	
	public function &getModifiedPlanetDamageAgainstPlayer(SmrPlanet $planet, AbstractSmrPlayer $targetPlayer) {
		if (!$this->canShootTraders()) { // If we can't shoot traders then just return a damageless array and don't waste resources calculating any damage mods.
			$return = array('MaxDamage' => 0, 'Shield' => 0, 'Armour' => 0, 'Rollover' => $this->isDamageRollover());
			return $return;
		}
		$damage = $this->getDamage();
		return $damage;
	}
	
	public function &shootForces(AbstractSmrPlayer $weaponPlayer, SmrForce $forces) {
		$return = array('Weapon' => $this, 'TargetForces' => $forces, 'Hit' => false);
		$modifiedAccuracy = $this->getModifiedAccuracyAgainstForces($weaponPlayer, $forces);
		if ($this->checkHit($weaponPlayer, $modifiedAccuracy)) {
			$return['Hit'] = true;
			return $this->doPlayerDamageToForce($return, $weaponPlayer, $forces);
		}
		return $return;
	}
	
	public function &shootPort(AbstractSmrPlayer $weaponPlayer, SmrPort $port) {
		$return = array('Weapon' => $this, 'TargetPort' => $port, 'Hit' => false);
		$modifiedAccuracy = $this->getModifiedAccuracyAgainstPort($weaponPlayer, $port);
		if ($this->checkHit($weaponPlayer, $modifiedAccuracy)) {
			$return['Hit'] = true;
			return $this->doPlayerDamageToPort($return, $weaponPlayer, $port);
		}
		return $return;
	}
	
	public function &shootPlanet(AbstractSmrPlayer $weaponPlayer, SmrPlanet $planet, $delayed) {
		$return = array('Weapon' => $this, 'TargetPlanet' => $planet, 'Hit' => false);
		$modifiedAccuracy = $this->getModifiedAccuracyAgainstPlanet($weaponPlayer, $planet);
		if ($this->checkHit($weaponPlayer, $modifiedAccuracy)) {
			$return['Hit'] = true;
			return $this->doPlayerDamageToPlanet($return, $weaponPlayer, $planet, $delayed);
		}
		return $return;
	}
	
	public function &shootPlayer(AbstractSmrPlayer $weaponPlayer, AbstractSmrPlayer $targetPlayer) {
		$return = array('Weapon' => $this, 'TargetPlayer' => $targetPlayer, 'Hit' => false);
		$modifiedAccuracy = $this->getModifiedAccuracyAgainstPlayer($weaponPlayer, $targetPlayer);
		if ($this->checkHit($weaponPlayer, $modifiedAccuracy)) {
			$return['Hit'] = true;
			return $this->doPlayerDamageToPlayer($return, $weaponPlayer, $targetPlayer);
		}
		return $return;
	}
	
	public function &shootPlayerAsForce(SmrForce $forces, AbstractSmrPlayer $targetPlayer) {
		$return = array('Weapon' => $this, 'TargetPlayer' => $targetPlayer, 'Hit' => false);
		return $return;
	}
	
	public function &shootPlayerAsPort(SmrPort $port, AbstractSmrPlayer $targetPlayer) {
		$return = array('Weapon' => $this, 'TargetPlayer' => $targetPlayer, 'Hit' => false);
		$modifiedAccuracy = $this->getModifiedPortAccuracyAgainstPlayer($port, $targetPlayer);
		if ($this->checkHit($targetPlayer, $modifiedAccuracy)) {
			$return['Hit'] = true;
			return $this->doPortDamageToPlayer($return, $port, $targetPlayer);
		}
		return $return;
	}
	
	public function &shootPlayerAsPlanet(SmrPlanet $planet, AbstractSmrPlayer $targetPlayer) {
		$return = array('Weapon' => $this, 'TargetPlayer' => $targetPlayer, 'Hit' => false);
		$modifiedAccuracy = $this->getModifiedPlanetAccuracyAgainstPlayer($planet, $targetPlayer);
		if ($this->checkHit($targetPlayer, $modifiedAccuracy)) {
			$return['Hit'] = true;
			return $this->doPlanetDamageToPlayer($return, $planet, $targetPlayer);
		}
		return $return;
	}
	
}
