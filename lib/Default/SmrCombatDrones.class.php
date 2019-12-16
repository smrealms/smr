<?php declare(strict_types=1);

class SmrCombatDrones extends AbstractSmrCombatWeapon {
	const MAX_CDS_RAND = 54;
	protected $numberOfCDs;
	
	public function __construct($gameTypeID, $numberOfCDs, $portPlanetDrones = false) {
		$this->gameTypeID = $gameTypeID;
		$this->numberOfCDs = $numberOfCDs;
		$this->name = 'Combat Drones';
		$this->raceID = 0;
		if ($portPlanetDrones === false) {
			$this->maxDamage = 2;
			$this->shieldDamage = 2;
			$this->armourDamage = 2;
		}
		else {
			$this->maxDamage = 1;
			$this->shieldDamage = 1;
			$this->armourDamage = 1;
		}
		$this->accuracy = 3;
		$this->damageRollover = true;
		$this->raidWeapon = false;
	}
	
	public function getNumberOfCDs() {
		return $this->numberOfCDs;
	}
	
	public function getModifiedAccuracy() {
		$modifiedAccuracy = $this->getBaseAccuracy();
		return $modifiedAccuracy;
	}
	
	protected function getModifiedAccuracyAgainstForcesUsingRandom(AbstractSmrPlayer $weaponPlayer, SmrForce $forces, $random) {
		$modifiedAccuracy = $this->getModifiedAccuracy();
		$modifiedAccuracy += $random;
	
		return max(0, min(100, $modifiedAccuracy));
	}
	public function getMaxModifiedAccuracyAgainstForces(AbstractSmrPlayer $weaponPlayer, SmrForce $forces) {
		return $this->getModifiedAccuracyAgainstForcesUsingRandom($weaponPlayer, $forces, self::MAX_CDS_RAND);
	}
	public function getModifiedAccuracyAgainstForces(AbstractSmrPlayer $weaponPlayer, SmrForce $forces) {
		return $this->getModifiedAccuracyAgainstForcesUsingRandom($weaponPlayer, $forces, mt_rand(3, self::MAX_CDS_RAND));
	}
	
	protected function getModifiedAccuracyAgainstPortUsingRandom(AbstractSmrPlayer $weaponPlayer, SmrPort $port, $random) {
		$modifiedAccuracy = $this->getModifiedAccuracy();
		$modifiedAccuracy += $random;
	
		return max(0, min(100, $modifiedAccuracy));
	}
	public function getMaxModifiedAccuracyAgainstPort(AbstractSmrPlayer $weaponPlayer, SmrPort $port) {
		return $this->getModifiedAccuracyAgainstPortUsingRandom($weaponPlayer, $port, self::MAX_CDS_RAND);
	}
	public function getModifiedAccuracyAgainstPort(AbstractSmrPlayer $weaponPlayer, SmrPort $port) {
		return $this->getModifiedAccuracyAgainstPortUsingRandom($weaponPlayer, $port, mt_rand(3, self::MAX_CDS_RAND));
	}
	
	protected function getModifiedAccuracyAgainstPlanetUsingRandom(AbstractSmrPlayer $weaponPlayer, SmrPlanet $planet, $random) {
		$modifiedAccuracy = $this->getModifiedAccuracy();
		$modifiedAccuracy += $random;
	
		return max(0, min(100, $modifiedAccuracy));
	}
	public function getMaxModifiedAccuracyAgainstPlanet(AbstractSmrPlayer $weaponPlayer, SmrPlanet $planet) {
		return $this->getModifiedAccuracyAgainstPlanetUsingRandom($weaponPlayer, $planet, self::MAX_CDS_RAND);
	}
	public function getModifiedAccuracyAgainstPlanet(AbstractSmrPlayer $weaponPlayer, SmrPlanet $planet) {
		return $this->getModifiedAccuracyAgainstPlanetUsingRandom($weaponPlayer, $planet, mt_rand(3, self::MAX_CDS_RAND));
	}
	
	
	public function getModifiedAccuracyAgainstPlayer(AbstractSmrPlayer $weaponPlayer, AbstractSmrPlayer $targetPlayer) {
		return $this->getModifiedAccuracyAgainstPlayerUsingRandom($weaponPlayer, $targetPlayer, mt_rand(3, self::MAX_CDS_RAND));
	}
	
	protected function getModifiedAccuracyAgainstPlayerUsingRandom(AbstractSmrPlayer $weaponPlayer, AbstractSmrPlayer $targetPlayer, $random) {
		$modifiedAccuracy = $this->getModifiedAccuracy();
		$modifiedAccuracy += ($random + mt_rand($weaponPlayer->getLevelID() / 2, $weaponPlayer->getLevelID()) - ($targetPlayer->getLevelID() - $weaponPlayer->getLevelID()) / 3) / 1.5;

		$weaponShip = $weaponPlayer->getShip();
		$targetShip = $targetPlayer->getShip();
		$mrDiff = $targetShip->getMR() - $weaponShip->getMR();
		if ($mrDiff > 0)
			$modifiedAccuracy -= $this->getBaseAccuracy() * ($mrDiff / MR_FACTOR) / 100;
	
		return max(0, min(100, $modifiedAccuracy));
	}
	
	public function getMaxModifiedAccuracyAgainstPlayer(AbstractSmrPlayer $weaponPlayer, AbstractSmrPlayer $targetPlayer) {
		return $this->getModifiedAccuracyAgainstPlayerUsingRandom($weaponPlayer, $targetPlayer, self::MAX_CDS_RAND);
	}
	
	public function getModifiedForceAccuracyAgainstPlayer(SmrForce $forces, AbstractSmrPlayer $targetPlayer) {
		return $this->getModifiedForceAccuracyAgainstPlayerUsingRandom($forces, $targetPlayer, mt_rand(3, self::MAX_CDS_RAND));
	}
	
	protected function getModifiedForceAccuracyAgainstPlayerUsingRandom(SmrForce $forces, AbstractSmrPlayer $targetPlayer, $random) {
		$modifiedAccuracy = $this->getModifiedAccuracy();
		$modifiedAccuracy += $random;
	
		return max(0, min(100, $modifiedAccuracy));
	}
	
	protected function getModifiedPortAccuracyAgainstPlayer(SmrPort $port, AbstractSmrPlayer $targetPlayer) {
		return 100;
	}
	
	protected function getModifiedPlanetAccuracyAgainstPlayer(SmrPlanet $planet, AbstractSmrPlayer $targetPlayer) {
		return 100;
	}
	
	public function getMaxModifiedForceAccuracyAgainstPlayer(SmrForce $forces, AbstractSmrPlayer $targetPlayer) {
		return $this->getModifiedForceAccuracyAgainstPlayerUsingRandom($forces, $targetPlayer, self::MAX_CDS_RAND);
	}
	
	public function getMaxModifiedPortAccuracyAgainstPlayer(SmrPort $forces, AbstractSmrPlayer $targetPlayer) {
		return 100;
	}
	
	public function &getModifiedDamage() {
		$damage = $this->getDamage();
		return $damage;
	}
	
	public function &getModifiedDamageAgainstForces(AbstractSmrPlayer $weaponPlayer, SmrForce $forces) {
		if (!$this->canShootForces()) // If we can't shoot forces then just return a damageless array and don't waste resources calculated any damage mods.
			return array('MaxDamage' => 0, 'Shield' => 0, 'Armour' => 0, 'Rollover' => $this->isDamageRollover());
		$damage =& $this->getModifiedDamage();
		$damage['Launched'] = ICeil($this->getNumberOfCDs() * $this->getModifiedAccuracyAgainstForces($weaponPlayer, $forces) / 100);
		$damage['Kamikaze'] = 0;
		if ($weaponPlayer->isCombatDronesKamikazeOnMines()) { // If kamikaze then damage is same as MINE_ARMOUR
			$damage['Kamikaze'] = min($damage['Launched'], $forces->getMines());
			$damage['Launched'] -= $damage['Kamikaze'];
		}
		$damage['MaxDamage'] = ICeil($damage['Launched'] * $damage['MaxDamage']);
		$damage['Shield'] = ICeil($damage['Launched'] * $damage['Shield']);
		$damage['Armour'] = ICeil($damage['Launched'] * $damage['Armour']);
		
		$damage['Launched'] += $damage['Kamikaze'];
		$damage['MaxDamage'] += $damage['Kamikaze'] * MINE_ARMOUR;
		$damage['Shield'] += $damage['Kamikaze'] * MINE_ARMOUR;
		$damage['Armour'] += $damage['Kamikaze'] * MINE_ARMOUR;
			
		return $damage;
	}
	
	public function &getModifiedDamageAgainstPort(AbstractSmrPlayer $weaponPlayer, SmrPort $port) {
		if (!$this->canShootPorts()) // If we can't shoot forces then just return a damageless array and don't waste resources calculated any damage mods.
			return array('MaxDamage' => 0, 'Shield' => 0, 'Armour' => 0, 'Rollover' => $this->isDamageRollover());
		$damage =& $this->getModifiedDamage();
		$damage['Launched'] = ICeil($this->getNumberOfCDs() * $this->getModifiedAccuracyAgainstPort($weaponPlayer, $port) / 100);
		$damage['MaxDamage'] = ICeil($damage['Launched'] * $damage['MaxDamage']);
		$damage['Shield'] = ICeil($damage['Launched'] * $damage['Shield']);
		$damage['Armour'] = ICeil($damage['Launched'] * $damage['Armour']);
		
		return $damage;
	}
	
	public function &getModifiedDamageAgainstPlanet(AbstractSmrPlayer $weaponPlayer, SmrPlanet $planet) {
		if (!$this->canShootPlanets()) // If we can't shoot forces then just return a damageless array and don't waste resources calculated any damage mods.
			return array('MaxDamage' => 0, 'Shield' => 0, 'Armour' => 0, 'Rollover' => $this->isDamageRollover());
		$damage =& $this->getModifiedDamage();
		$damage['Launched'] = ICeil($this->getNumberOfCDs() * $this->getModifiedAccuracyAgainstPlanet($weaponPlayer, $planet) / 100);
		$planetMod = self::PLANET_DAMAGE_MOD;
		$damage['MaxDamage'] = ICeil($damage['Launched'] * $damage['MaxDamage'] * $planetMod);
		$damage['Shield'] = ICeil($damage['Launched'] * $damage['Shield'] * $planetMod);
		$damage['Armour'] = ICeil($damage['Launched'] * $damage['Armour'] * $planetMod);
		
		return $damage;
	}
	
	public function &getModifiedDamageAgainstPlayer(AbstractSmrPlayer $weaponPlayer, AbstractSmrPlayer $targetPlayer) {
		if (!$this->canShootTraders()) { // If we can't shoot traders then just return a damageless array and don't waste resources calculated any damage mods.
			$return = array('MaxDamage' => 0, 'Shield' => 0, 'Armour' => 0, 'Rollover' => $this->isDamageRollover());
			return $return;
		}
		$damage =& $this->getModifiedDamage();
		if ($targetPlayer->getShip()->hasDCS()) {
			$damage['MaxDamage'] *= DCS_PLAYER_DAMAGE_DECIMAL_PERCENT;
			$damage['Shield'] *= DCS_PLAYER_DAMAGE_DECIMAL_PERCENT;
			$damage['Armour'] *= DCS_PLAYER_DAMAGE_DECIMAL_PERCENT;
		}
		$damage['Launched'] = ICeil($this->getNumberOfCDs() * $this->getModifiedAccuracyAgainstPlayer($weaponPlayer, $targetPlayer) / 100);
		$damage['MaxDamage'] = ICeil($damage['Launched'] * $damage['MaxDamage']);
		$damage['Shield'] = ICeil($damage['Launched'] * $damage['Shield']);
		$damage['Armour'] = ICeil($damage['Launched'] * $damage['Armour']);
		return $damage;
	}
	
	public function &getModifiedForceDamageAgainstPlayer(SmrForce $forces, AbstractSmrPlayer $targetPlayer) {
		if (!$this->canShootTraders()) { // If we can't shoot traders then just return a damageless array and don't waste resources calculated any damage mods.
			$return = array('MaxDamage' => 0, 'Shield' => 0, 'Armour' => 0, 'Rollover' => $this->isDamageRollover());
			return $return;
		}
		$damage =& $this->getModifiedDamage();
		
		if ($targetPlayer->getShip()->hasDCS()) {
			$damage['MaxDamage'] *= DCS_FORCE_DAMAGE_DECIMAL_PERCENT;
			$damage['Shield'] *= DCS_FORCE_DAMAGE_DECIMAL_PERCENT;
			$damage['Armour'] *= DCS_FORCE_DAMAGE_DECIMAL_PERCENT;
		}
		
		$damage['Launched'] = ICeil($this->getNumberOfCDs() * $this->getModifiedForceAccuracyAgainstPlayer($forces, $targetPlayer) / 100);
		$damage['MaxDamage'] = ICeil($damage['Launched'] * $damage['MaxDamage']);
		$damage['Shield'] = ICeil($damage['Launched'] * $damage['Shield']);
		$damage['Armour'] = ICeil($damage['Launched'] * $damage['Armour']);
		return $damage;
	}
	
	public function &getModifiedPortDamageAgainstPlayer(SmrPort $port, AbstractSmrPlayer $targetPlayer) {
		if (!$this->canShootTraders()) { // If we can't shoot traders then just return a damageless array and don't waste resources calculated any damage mods.
			$return = array('MaxDamage' => 0, 'Shield' => 0, 'Armour' => 0, 'Rollover' => $this->isDamageRollover());
			return $return;
		}
		$damage =& $this->getModifiedDamage();
		
		if ($targetPlayer->getShip()->hasDCS()) {
			$damage['MaxDamage'] *= DCS_PORT_DAMAGE_DECIMAL_PERCENT;
			$damage['Shield'] *= DCS_PORT_DAMAGE_DECIMAL_PERCENT;
			$damage['Armour'] *= DCS_PORT_DAMAGE_DECIMAL_PERCENT;
		}
		$damage['Launched'] = ICeil($this->getNumberOfCDs() * $this->getModifiedPortAccuracyAgainstPlayer($port, $targetPlayer) / 100);
		$damage['MaxDamage'] = ICeil($damage['Launched'] * $damage['MaxDamage']);
		$damage['Shield'] = ICeil($damage['Launched'] * $damage['Shield']);
		$damage['Armour'] = ICeil($damage['Launched'] * $damage['Armour']);
		return $damage;
	}
	
	public function &getModifiedPlanetDamageAgainstPlayer(SmrPlanet $planet, AbstractSmrPlayer $targetPlayer) {
		if (!$this->canShootTraders()) { // If we can't shoot traders then just return a damageless array and don't waste resources calculated any damage mods.
			$return = array('MaxDamage' => 0, 'Shield' => 0, 'Armour' => 0, 'Rollover' => $this->isDamageRollover());
			return $return;
		}
		$damage =& $this->getModifiedDamage();
		
		if ($targetPlayer->getShip()->hasDCS()) {
			$damage['MaxDamage'] *= DCS_PLANET_DAMAGE_DECIMAL_PERCENT;
			$damage['Shield'] *= DCS_PLANET_DAMAGE_DECIMAL_PERCENT;
			$damage['Armour'] *= DCS_PLANET_DAMAGE_DECIMAL_PERCENT;
		}
		$damage['Launched'] = ICeil($this->getNumberOfCDs() * $this->getModifiedPlanetAccuracyAgainstPlayer($planet, $targetPlayer) / 100);
		$damage['MaxDamage'] = ICeil($damage['Launched'] * $damage['MaxDamage']);
		$damage['Shield'] = ICeil($damage['Launched'] * $damage['Shield']);
		$damage['Armour'] = ICeil($damage['Launched'] * $damage['Armour']);
		return $damage;
	}
	
	public function &shootForces(AbstractSmrPlayer $weaponPlayer, SmrForce $forces) {
		$return = array('Weapon' => $this, 'TargetForces' => $forces, 'Hit' => true);
		$this->doPlayerDamageToForce($return, $weaponPlayer, $forces);
		if ($return['WeaponDamage']['Kamikaze'] > 0) {
			$weaponPlayer->getShip()->decreaseCDs($return['WeaponDamage']['Kamikaze']);
		}
		return $return;
	}
	
	public function &shootPort(AbstractSmrPlayer $weaponPlayer, SmrPort $port) {
		$return = array('Weapon' => $this, 'TargetPort' => $port, 'Hit' => true);
		return $this->doPlayerDamageToPort($return, $weaponPlayer, $port);
	}
	
	public function &shootPlanet(AbstractSmrPlayer $weaponPlayer, SmrPlanet $planet, $delayed) {
		$return = array('Weapon' => $this, 'TargetPlanet' => $planet, 'Hit' => true);
		return $this->doPlayerDamageToPlanet($return, $weaponPlayer, $planet, $delayed);
	}
	
	public function &shootPlayer(AbstractSmrPlayer $weaponPlayer, AbstractSmrPlayer $targetPlayer) {
		$return = array('Weapon' => $this, 'TargetPlayer' => $targetPlayer, 'Hit' => true);
		return $this->doPlayerDamageToPlayer($return, $weaponPlayer, $targetPlayer);
	}
	
	public function &shootPlayerAsForce(SmrForce $forces, AbstractSmrPlayer $targetPlayer) {
		$return = array('Weapon' => $this, 'TargetPlayer' => $targetPlayer, 'Hit' => true);
		return $this->doForceDamageToPlayer($return, $forces, $targetPlayer);
	}
	
	public function &shootPlayerAsPort(SmrPort $forces, AbstractSmrPlayer $targetPlayer) {
		$return = array('Weapon' => $this, 'TargetPlayer' => $targetPlayer, 'Hit' => true);
		return $this->doPortDamageToPlayer($return, $forces, $targetPlayer);
	}
	
	public function &shootPlayerAsPlanet(SmrPlanet $forces, AbstractSmrPlayer $targetPlayer) {
		$return = array('Weapon' => $this, 'TargetPlayer' => $targetPlayer, 'Hit' => true);
		return $this->doPlanetDamageToPlayer($return, $forces, $targetPlayer);
	}
}
