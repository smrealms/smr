<?php declare(strict_types=1);

abstract class AbstractSmrCombatWeapon {
	/**
	 * Reduce the damage done to planets by this factor
	 */
	const PLANET_DAMAGE_MOD = 0.2;

	protected string $name;
	protected int $shieldDamage;
	protected int $armourDamage;
	protected int $accuracy;
	protected bool $damageRollover;

	public function getBaseAccuracy() : int {
		return $this->accuracy;
	}

	public function getName() : string {
		return $this->name;
	}

	/**
	 * Return the max weapon damage possible in a single round.
	 */
	public function getMaxDamage() : int {
		return max($this->getShieldDamage(), $this->getArmourDamage());
	}

	public function getShieldDamage() : int {
		return $this->shieldDamage;
	}

	public function getArmourDamage() : int {
		return $this->armourDamage;
	}

	public function isDamageRollover() : bool {
		return $this->damageRollover;
	}

	public function canShootForces() : bool {
		return true;
	}

	public function canShootPorts() : bool {
		return true;
	}

	public function canShootPlanets() : bool {
		return true;
	}

	public function canShootTraders() : bool {
		return true;
	}

	public function getDamage() : array {
		return array('MaxDamage' => $this->getMaxDamage(), 'Shield' => $this->getShieldDamage(), 'Armour' => $this->getArmourDamage(), 'Rollover' => $this->isDamageRollover());
	}

	abstract public function getModifiedDamageAgainstForces(AbstractSmrPlayer $weaponPlayer, SmrForce $forces) : array;
	abstract public function getModifiedDamageAgainstPort(AbstractSmrPlayer $weaponPlayer, SmrPort $port) : array;
	abstract public function getModifiedDamageAgainstPlanet(AbstractSmrPlayer $weaponPlayer, SmrPlanet $planet) : array;
	abstract public function getModifiedPortDamageAgainstPlayer(SmrPort $port, AbstractSmrPlayer $targetPlayer) : array;
	abstract public function getModifiedDamageAgainstPlayer(AbstractSmrPlayer $weaponPlayer, AbstractSmrPlayer $targetPlayer) : array;
	abstract public function getModifiedForceDamageAgainstPlayer(SmrForce $forces, AbstractSmrPlayer $targetPlayer) : array;
	abstract public function getModifiedPlanetDamageAgainstPlayer(SmrPlanet $planet, AbstractSmrPlayer $targetPlayer) : array;

	protected function doPlayerDamageToForce(array $return, AbstractSmrPlayer $weaponPlayer, SmrForce $forces) : array {
		$return['WeaponDamage'] = $this->getModifiedDamageAgainstForces($weaponPlayer, $forces);
		$return['ActualDamage'] = $forces->doWeaponDamage($return['WeaponDamage']);
		if ($return['ActualDamage']['KillingShot']) {
			$return['KillResults'] = $forces->killForcesByPlayer($weaponPlayer);
		}
		return $return;
	}

	protected function doPlayerDamageToPlayer(array $return, AbstractSmrPlayer $weaponPlayer, AbstractSmrPlayer $targetPlayer) : array {
		$return['WeaponDamage'] = $this->getModifiedDamageAgainstPlayer($weaponPlayer, $targetPlayer);
		$return['ActualDamage'] = $targetPlayer->getShip()->doWeaponDamage($return['WeaponDamage']);

		if ($return['ActualDamage']['KillingShot']) {
			$return['KillResults'] = $targetPlayer->killPlayerByPlayer($weaponPlayer);
		}
		return $return;
	}

	protected function doPlayerDamageToPort(array $return, AbstractSmrPlayer $weaponPlayer, SmrPort $port) : array {
		$return['WeaponDamage'] = $this->getModifiedDamageAgainstPort($weaponPlayer, $port);
		$return['ActualDamage'] = $port->doWeaponDamage($return['WeaponDamage']);
		if ($return['ActualDamage']['KillingShot']) {
			$return['KillResults'] = $port->killPortByPlayer($weaponPlayer);
		}
		return $return;
	}

	protected function doPlayerDamageToPlanet(array $return, AbstractSmrPlayer $weaponPlayer, SmrPlanet $planet, bool $delayed) : array {
		$return['WeaponDamage'] = $this->getModifiedDamageAgainstPlanet($weaponPlayer, $planet);
		$return['ActualDamage'] = $planet->doWeaponDamage($return['WeaponDamage'], $delayed);
		if ($return['ActualDamage']['KillingShot']) {
			$return['KillResults'] = $planet->killPlanetByPlayer($weaponPlayer);
		}
		return $return;
	}

	protected function doPortDamageToPlayer(array $return, SmrPort $port, AbstractSmrPlayer $targetPlayer) : array {
		$return['WeaponDamage'] = $this->getModifiedPortDamageAgainstPlayer($port, $targetPlayer);
		$return['ActualDamage'] = $targetPlayer->getShip()->doWeaponDamage($return['WeaponDamage']);

		if ($return['ActualDamage']['KillingShot']) {
			$return['KillResults'] = $targetPlayer->killPlayerByPort($port);
		}
		return $return;
	}

	protected function doPlanetDamageToPlayer(array $return, SmrPlanet $planet, AbstractSmrPlayer $targetPlayer) : array {
		$return['WeaponDamage'] = $this->getModifiedPlanetDamageAgainstPlayer($planet, $targetPlayer);
		$return['ActualDamage'] = $targetPlayer->getShip()->doWeaponDamage($return['WeaponDamage']);

		if ($return['ActualDamage']['KillingShot']) {
			$return['KillResults'] = $targetPlayer->killPlayerByPlanet($planet);
		}
		return $return;
	}

	protected function doForceDamageToPlayer(array $return, SmrForce $forces, AbstractSmrPlayer $targetPlayer) : array {
		$return['WeaponDamage'] = $this->getModifiedForceDamageAgainstPlayer($forces, $targetPlayer);
		$return['ActualDamage'] = $targetPlayer->getShip()->doWeaponDamage($return['WeaponDamage']);

		if ($return['ActualDamage']['KillingShot']) {
			$return['KillResults'] = $targetPlayer->killPlayerByForces($forces);
		}
		return $return;
	}

	abstract public function shootForces(AbstractSmrPlayer $weaponPlayer, SmrForce $forces) : array;
	abstract public function shootPlayer(AbstractSmrPlayer $weaponPlayer, AbstractSmrPlayer $targetPlayer) : array;
	abstract public function shootPlayerAsForce(SmrForce $forces, AbstractSmrPlayer $targetPlayer) : array;
}
