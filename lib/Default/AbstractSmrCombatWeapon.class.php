<?php declare(strict_types=1);

abstract class AbstractSmrCombatWeapon {
	/**
	 * Reduce the damage done to planets by this factor
	 */
	const PLANET_DAMAGE_MOD = 0.2;

	protected $gameTypeID;
	protected $name;
	protected $maxDamage;
	protected $shieldDamage;
	protected $armourDamage;
	protected $accuracy;
	protected $damageRollover;
	
	public function getBaseAccuracy() {
		return $this->accuracy;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function getMaxDamage() {
		return $this->maxDamage;
	}
	
	public function getShieldDamage() {
		return $this->shieldDamage;
	}
	
	public function getArmourDamage() {
		return $this->armourDamage;
	}
	
	public function isDamageRollover() {
		return $this->damageRollover;
	}
	
	public function canShootForces() {
		return true;
	}
	
	public function canShootPorts() {
		return true;
	}
	
	public function canShootPlanets() {
		return true;
	}
	
	public function canShootTraders() {
		return true;
	}
	
	public function getDamage() {
		return array('MaxDamage' => $this->getMaxDamage(), 'Shield' => $this->getShieldDamage(), 'Armour' => $this->getArmourDamage(), 'Rollover' => $this->isDamageRollover());
	}
	
	abstract public function &getModifiedDamageAgainstForces(AbstractSmrPlayer $weaponPlayer, SmrForce $forces);
	abstract public function &getModifiedDamageAgainstPort(AbstractSmrPlayer $weaponPlayer, SmrPort $port);
	abstract public function &getModifiedDamageAgainstPlanet(AbstractSmrPlayer $weaponPlayer, SmrPlanet $planet);
	abstract public function &getModifiedPortDamageAgainstPlayer(SmrPort $port, AbstractSmrPlayer $targetPlayer);
	abstract public function &getModifiedDamageAgainstPlayer(AbstractSmrPlayer $weaponPlayer, AbstractSmrPlayer $targetPlayer);
	abstract public function &getModifiedForceDamageAgainstPlayer(SmrForce $forces, AbstractSmrPlayer $targetPlayer);
	
	protected function &doPlayerDamageToForce(array &$return, AbstractSmrPlayer $weaponPlayer, SmrForce $forces) {
		$return['WeaponDamage'] =& $this->getModifiedDamageAgainstForces($weaponPlayer,$forces);
		$return['ActualDamage'] =& $forces->doWeaponDamage($return['WeaponDamage']);
		if($return['ActualDamage']['KillingShot']) {
			$return['KillResults'] =& $forces->killForcesByPlayer($weaponPlayer);
		}
		return $return;
	}
	
	protected function &doPlayerDamageToPlayer(array &$return, AbstractSmrPlayer $weaponPlayer, AbstractSmrPlayer $targetPlayer) {
		$return['WeaponDamage'] =& $this->getModifiedDamageAgainstPlayer($weaponPlayer,$targetPlayer);
		$return['ActualDamage'] =& $targetPlayer->getShip()->doWeaponDamage($return['WeaponDamage']);

		if($return['ActualDamage']['KillingShot']) {
			$return['KillResults'] =& $targetPlayer->killPlayerByPlayer($weaponPlayer);
		}
		return $return;
	}
	
	protected function &doPlayerDamageToPort(array &$return, AbstractSmrPlayer $weaponPlayer, SmrPort $port) {
		$return['WeaponDamage'] =& $this->getModifiedDamageAgainstPort($weaponPlayer,$port);
		$return['ActualDamage'] =& $port->doWeaponDamage($return['WeaponDamage']);
		if($return['ActualDamage']['KillingShot']) {
			$return['KillResults'] =& $port->killPortByPlayer($weaponPlayer);
		}
		return $return;
	}
	
	protected function &doPlayerDamageToPlanet(array &$return, AbstractSmrPlayer $weaponPlayer, SmrPlanet $planet, $delayed) {
		$return['WeaponDamage'] =& $this->getModifiedDamageAgainstPlanet($weaponPlayer,$planet);
		$return['ActualDamage'] =& $planet->doWeaponDamage($return['WeaponDamage'],$delayed);
		if($return['ActualDamage']['KillingShot']) {
			$return['KillResults'] =& $planet->killPlanetByPlayer($weaponPlayer);
		}
		return $return;
	}
	
	protected function &doPortDamageToPlayer(array &$return, SmrPort $port, AbstractSmrPlayer $targetPlayer) {
		$return['WeaponDamage'] =& $this->getModifiedPortDamageAgainstPlayer($port,$targetPlayer);
		$return['ActualDamage'] =& $targetPlayer->getShip()->doWeaponDamage($return['WeaponDamage']);

		if($return['ActualDamage']['KillingShot']) {
			$return['KillResults'] =& $targetPlayer->killPlayerByPort($port);
		}
		return $return;
	}
	
	protected function &doPlanetDamageToPlayer(array &$return, SmrPlanet $planet, AbstractSmrPlayer $targetPlayer) {
		$return['WeaponDamage'] =& $this->getModifiedPlanetDamageAgainstPlayer($planet,$targetPlayer);
		$return['ActualDamage'] =& $targetPlayer->getShip()->doWeaponDamage($return['WeaponDamage']);

		if($return['ActualDamage']['KillingShot']) {
			$return['KillResults'] =& $targetPlayer->killPlayerByPlanet($planet);
		}
		return $return;
	}
	
	protected function &doForceDamageToPlayer(array &$return, SmrForce $forces, AbstractSmrPlayer $targetPlayer) {
		$return['WeaponDamage'] =& $this->getModifiedForceDamageAgainstPlayer($forces,$targetPlayer);
		$return['ActualDamage'] =& $targetPlayer->getShip()->doWeaponDamage($return['WeaponDamage']);

		if($return['ActualDamage']['KillingShot']) {
			$return['KillResults'] =& $targetPlayer->killPlayerByForces($forces);
		}
		return $return;
	}
	
	abstract public function &shootForces(AbstractSmrPlayer $weaponPlayer, SmrForce $forces);
	abstract public function &shootPlayer(AbstractSmrPlayer $weaponPlayer, AbstractSmrPlayer $targetPlayer);
	abstract public function &shootPlayerAsForce(SmrForce $forces, AbstractSmrPlayer $targetPlayer);
}
