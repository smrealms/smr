<?php declare(strict_types=1);

/**
 * Defines a concrete realization of a weapon type for ships/planets.
 */
class SmrWeapon extends AbstractSmrCombatWeapon {
	use Traits\RaceID;

	const BONUS_DAMAGE = 1.05; // multiplicative bonus
	const BONUS_ACCURACY = 3; // additive bonus

	protected int $weaponTypeID;
	protected SmrWeaponType $weaponType;
	protected bool $bonusAccuracy = false; // default
	protected bool $bonusDamage = false; // default
	protected $damageRollover = false; // fixed for all SmrWeapons

	public static function getWeapon(int $weaponTypeID, SmrMySqlDatabase $db = null) : SmrWeapon {
		return new SmrWeapon($weaponTypeID, $db);
	}

	protected function __construct(int $weaponTypeID, SmrMySqlDatabase $db = null) {
		$this->weaponType = SmrWeaponType::getWeaponType($weaponTypeID, $db);
		$this->weaponTypeID = $weaponTypeID;
		$this->name = $this->weaponType->getName();
		$this->raceID = $this->weaponType->getRaceID();
	}

	public function hasBonusAccuracy() : bool {
		return $this->bonusAccuracy;
	}

	public function setBonusAccuracy(bool $bonusAccuracy) {
		$this->bonusAccuracy = $bonusAccuracy;
	}

	public function hasBonusDamage() : bool {
		return $this->bonusDamage;
	}

	public function setBonusDamage(bool $bonusDamage) {
		$this->bonusDamage = $bonusDamage;
	}

	private function hasEnhancements() : bool {
		return $this->getNumberOfEnhancements() > 0;
	}

	private function getNumberOfEnhancements() : int {
		return (int)$this->bonusAccuracy + (int)$this->bonusDamage;
	}

	/**
	 * (Override) Return weapon name suitable for HTML display.
	 * The name is displayed in green with pluses if enhancements are present.
	 */
	public function getName() : string {
		if ($this->hasEnhancements()) {
			return '<span class="green">' . $this->name . str_repeat('+', $this->getNumberOfEnhancements()) . '</span>';
		}
		return $this->name;
	}

	/**
	 * (Override) Return the weapon base accuracy.
	 */
	public function getBaseAccuracy() : int {
		if ($this->bonusAccuracy) {
			return $this->weaponType->getAccuracy() + self::BONUS_ACCURACY;
		}
		return $this->weaponType->getAccuracy();
	}

	/**
	 * (Override) Return the weapon shield damage.
	 */
	public function getShieldDamage() : int {
		if ($this->bonusDamage) {
			return IFloor($this->weaponType->getShieldDamage() * self::BONUS_DAMAGE);
		}
		return $this->weaponType->getShieldDamage();
	}

	/**
	 * (Override) Return the weapon armour damage.
	 */
	public function getArmourDamage() : int {
		if ($this->bonusDamage) {
			return IFloor($this->weaponType->getArmourDamage() * self::BONUS_DAMAGE);
		}
		return $this->weaponType->getArmourDamage();
	}

	/**
	 * (Override) Return the max weapon damage possible in a single round.
	 */
	public function getMaxDamage() : int {
		return max($this->getShieldDamage(), $this->getArmourDamage());
	}

	public function getBuyHREF(SmrLocation $location) {
		$container = create_container('shop_weapon_processing.php');
		$container['LocationID'] = $location->getTypeID();
		$container['Weapon'] = $this;
		return SmrSession::getNewHREF($container);
	}

	public function getSellHREF(SmrLocation $location, $orderID) {
		$container = create_container('shop_weapon_processing.php');
		$container['LocationID'] = $location->getTypeID();
		$container['Weapon'] = $this;
		$container['OrderID'] = $orderID;
		return SmrSession::getNewHREF($container);
	}
	
	public function getWeaponTypeID() {
		return $this->weaponTypeID;
	}

	/**
	 * Weapon cost is increased by 100% for each enhancement present
	 */
	public function getCost() {
		return $this->weaponType->getCost() * (1 + $this->getNumberOfEnhancements());
	}
	
	public function getPowerLevel() {
		return $this->weaponType->getPowerLevel();
	}
	
	public function getBuyerRestriction() {
		return $this->weaponType->getBuyerRestriction();
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
		if (!$this->canShootForces()) {
			// If we can't shoot forces then just return a damageless array and don't waste resources calculated any damage mods.
			return array('MaxDamage' => 0, 'Shield' => 0, 'Armour' => 0, 'Rollover' => $this->isDamageRollover());
		}
		$damage =& $this->getModifiedDamage();
		return $damage;
	}
	
	public function &getModifiedDamageAgainstPort(AbstractSmrPlayer $weaponPlayer, SmrPort $port) {
		if (!$this->canShootPorts()) {
			// If we can't shoot forces then just return a damageless array and don't waste resources calculated any damage mods.
			return array('MaxDamage' => 0, 'Shield' => 0, 'Armour' => 0, 'Rollover' => $this->isDamageRollover());
		}
		$damage =& $this->getModifiedDamage();
		return $damage;
	}
	
	public function &getModifiedDamageAgainstPlanet(AbstractSmrPlayer $weaponPlayer, SmrPlanet $planet) {
		if (!$this->canShootPlanets()) {
			// If we can't shoot forces then just return a damageless array and don't waste resources calculated any damage mods.
			return array('MaxDamage' => 0, 'Shield' => 0, 'Armour' => 0, 'Rollover' => $this->isDamageRollover());
		}
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
