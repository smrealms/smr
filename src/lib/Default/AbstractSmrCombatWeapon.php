<?php declare(strict_types=1);

abstract class AbstractSmrCombatWeapon {

	/**
	 * Reduce the damage done to planets by this factor
	 */
	protected const PLANET_DAMAGE_MOD = 0.2;

	protected bool $damageRollover;

	abstract public function getBaseAccuracy(): int;
	abstract public function getName(): string;
	abstract public function getShieldDamage(): int;
	abstract public function getArmourDamage(): int;

	public function isDamageRollover(): bool {
		return $this->damageRollover;
	}

	public function canShootForces(): bool {
		return true;
	}

	public function canShootPorts(): bool {
		return true;
	}

	public function canShootPlanets(): bool {
		return true;
	}

	public function canShootTraders(): bool {
		return true;
	}

	/**
	 * @return array<string, int|bool>
	 */
	public function getDamage(): array {
		return ['Shield' => $this->getShieldDamage(), 'Armour' => $this->getArmourDamage(), 'Rollover' => $this->isDamageRollover()];
	}

	/**
	 * @return array<string, int|bool>
	 */
	abstract public function getModifiedDamageAgainstForces(AbstractSmrPlayer $weaponPlayer, SmrForce $forces): array;

	/**
	 * @return array<string, int|bool>
	 */
	abstract public function getModifiedDamageAgainstPort(AbstractSmrPlayer $weaponPlayer, SmrPort $port): array;

	/**
	 * @return array<string, int|bool>
	 */
	abstract public function getModifiedDamageAgainstPlanet(AbstractSmrPlayer $weaponPlayer, SmrPlanet $planet): array;

	/**
	 * @return array<string, int|bool>
	 */
	abstract public function getModifiedPortDamageAgainstPlayer(AbstractSmrPort $port, AbstractSmrPlayer $targetPlayer): array;

	/**
	 * @return array<string, int|bool>
	 */
	abstract public function getModifiedDamageAgainstPlayer(AbstractSmrPlayer $weaponPlayer, AbstractSmrPlayer $targetPlayer): array;

	/**
	 * @return array<string, int|bool>
	 */
	abstract public function getModifiedForceDamageAgainstPlayer(SmrForce $forces, AbstractSmrPlayer $targetPlayer): array;

	/**
	 * @return array<string, int|bool>
	 */
	abstract public function getModifiedPlanetDamageAgainstPlayer(SmrPlanet $planet, AbstractSmrPlayer $targetPlayer): array;

	/**
	 * @param array<string, mixed> $return
	 * @return array<string, mixed>
	 */
	protected function doPlayerDamageToForce(array $return, AbstractSmrPlayer $weaponPlayer, SmrForce $forces): array {
		$return['WeaponDamage'] = $this->getModifiedDamageAgainstForces($weaponPlayer, $forces);
		$return['ActualDamage'] = $forces->takeDamage($return['WeaponDamage']);
		if ($return['ActualDamage']['KillingShot']) {
			$return['KillResults'] = $forces->killForcesByPlayer($weaponPlayer);
		}
		return $return;
	}

	/**
	 * @param array<string, mixed> $return
	 * @return array<string, mixed>
	 */
	protected function doPlayerDamageToPlayer(array $return, AbstractSmrPlayer $weaponPlayer, AbstractSmrPlayer $targetPlayer): array {
		$return['WeaponDamage'] = $this->getModifiedDamageAgainstPlayer($weaponPlayer, $targetPlayer);
		$return['ActualDamage'] = $targetPlayer->getShip()->takeDamage($return['WeaponDamage']);

		if ($return['ActualDamage']['KillingShot']) {
			$return['KillResults'] = $targetPlayer->killPlayerByPlayer($weaponPlayer);
		}
		return $return;
	}

	/**
	 * @param array<string, mixed> $return
	 * @return array<string, mixed>
	 */
	protected function doPlayerDamageToPort(array $return, AbstractSmrPlayer $weaponPlayer, SmrPort $port): array {
		$return['WeaponDamage'] = $this->getModifiedDamageAgainstPort($weaponPlayer, $port);
		$return['ActualDamage'] = $port->takeDamage($return['WeaponDamage']);
		if ($return['ActualDamage']['KillingShot']) {
			$return['KillResults'] = $port->killPortByPlayer($weaponPlayer);
		}
		return $return;
	}

	/**
	 * @param array<string, mixed> $return
	 * @return array<string, mixed>
	 */
	protected function doPlayerDamageToPlanet(array $return, AbstractSmrPlayer $weaponPlayer, SmrPlanet $planet): array {
		$return['WeaponDamage'] = $this->getModifiedDamageAgainstPlanet($weaponPlayer, $planet);
		$return['ActualDamage'] = $planet->takeDamage($return['WeaponDamage']);
		if ($return['ActualDamage']['KillingShot']) {
			$return['KillResults'] = $planet->killPlanetByPlayer($weaponPlayer);
		}
		return $return;
	}

	/**
	 * @param array<string, mixed> $return
	 * @return array<string, mixed>
	 */
	protected function doPortDamageToPlayer(array $return, AbstractSmrPort $port, AbstractSmrPlayer $targetPlayer): array {
		$return['WeaponDamage'] = $this->getModifiedPortDamageAgainstPlayer($port, $targetPlayer);
		$return['ActualDamage'] = $targetPlayer->getShip()->takeDamage($return['WeaponDamage']);

		if ($return['ActualDamage']['KillingShot']) {
			$return['KillResults'] = $targetPlayer->killPlayerByPort($port);
		}
		return $return;
	}

	/**
	 * @param array<string, mixed> $return
	 * @return array<string, mixed>
	 */
	protected function doPlanetDamageToPlayer(array $return, SmrPlanet $planet, AbstractSmrPlayer $targetPlayer): array {
		$return['WeaponDamage'] = $this->getModifiedPlanetDamageAgainstPlayer($planet, $targetPlayer);
		$return['ActualDamage'] = $targetPlayer->getShip()->takeDamage($return['WeaponDamage']);

		if ($return['ActualDamage']['KillingShot']) {
			$return['KillResults'] = $targetPlayer->killPlayerByPlanet($planet);
		}
		return $return;
	}

	/**
	 * @param array<string, mixed> $return
	 * @return array<string, mixed>
	 */
	protected function doForceDamageToPlayer(array $return, SmrForce $forces, AbstractSmrPlayer $targetPlayer): array {
		$return['WeaponDamage'] = $this->getModifiedForceDamageAgainstPlayer($forces, $targetPlayer);
		$return['ActualDamage'] = $targetPlayer->getShip()->takeDamage($return['WeaponDamage']);

		if ($return['ActualDamage']['KillingShot']) {
			$return['KillResults'] = $targetPlayer->killPlayerByForces($forces);
		}
		return $return;
	}

	/**
	 * @return array<string, mixed>
	 */
	abstract public function shootForces(AbstractSmrPlayer $weaponPlayer, SmrForce $forces): array;

	/**
	 * @return array<string, mixed>
	 */
	abstract public function shootPlayer(AbstractSmrPlayer $weaponPlayer, AbstractSmrPlayer $targetPlayer): array;

	/**
	 * @return array<string, mixed>
	 */
	abstract public function shootPlayerAsForce(SmrForce $forces, AbstractSmrPlayer $targetPlayer): array;

}
