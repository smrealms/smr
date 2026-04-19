<?php declare(strict_types=1);

namespace Smr\Combat\Weapon;

use Smr\Force;
use Smr\Planet;
use Smr\Player;
use Smr\Port;

abstract class AbstractWeapon {

	/**
	 * Reduce the damage done to planets by this factor
	 */
	protected const float PLANET_DAMAGE_MOD = 0.2;

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
	 * @return WeaponDamageData
	 */
	public function getDamage(): array {
		return ['Shield' => $this->getShieldDamage(), 'Armour' => $this->getArmourDamage(), 'Rollover' => $this->isDamageRollover()];
	}

	/**
	 * @return WeaponDamageData
	 */
	abstract public function getModifiedDamageAgainstForces(Player $weaponPlayer, Force $forces): array;

	/**
	 * @return WeaponDamageData
	 */
	abstract public function getModifiedDamageAgainstPort(Player $weaponPlayer, Port $port): array;

	/**
	 * @return WeaponDamageData
	 */
	abstract public function getModifiedDamageAgainstPlanet(Player $weaponPlayer, Planet $planet): array;

	/**
	 * @return WeaponDamageData
	 */
	abstract public function getModifiedPortDamageAgainstPlayer(Port $port, Player $targetPlayer): array;

	/**
	 * @return WeaponDamageData
	 */
	abstract public function getModifiedDamageAgainstPlayer(Player $weaponPlayer, Player $targetPlayer): array;

	/**
	 * @return WeaponDamageData
	 */
	abstract public function getModifiedForceDamageAgainstPlayer(Force $forces, Player $targetPlayer): array;

	/**
	 * @return WeaponDamageData
	 */
	abstract public function getModifiedPlanetDamageAgainstPlayer(Planet $planet, Player $targetPlayer): array;

	/**
	 * @param array{Weapon: self, Target: \Smr\Force, Hit: bool} $return
	 * @return array{Weapon: self, Target: \Smr\Force, Hit: bool, WeaponDamage: WeaponDamageData, ActualDamage: ForceTakenDamageData, KillResults?: array{}}
	 */
	protected function doPlayerDamageToForce(array $return, Player $weaponPlayer, Force $forces): array {
		$return['WeaponDamage'] = $this->getModifiedDamageAgainstForces($weaponPlayer, $forces);
		$return['ActualDamage'] = $forces->takeDamage($return['WeaponDamage']);
		if ($return['ActualDamage']['KillingShot']) {
			$return['KillResults'] = $forces->killForcesByPlayer($weaponPlayer);
		}
		return $return;
	}

	/**
	 * @param array{Weapon: self, Target: \Smr\Player, Hit: bool} $return
	 * @return array{Weapon: self, Target: \Smr\Player, Hit: bool, WeaponDamage: WeaponDamageData, ActualDamage: TakenDamageData, KillResults?: array{DeadExp: int, KillerExp: int, KillerCredits: int}}
	 */
	protected function doPlayerDamageToPlayer(array $return, Player $weaponPlayer, Player $targetPlayer): array {
		$return['WeaponDamage'] = $this->getModifiedDamageAgainstPlayer($weaponPlayer, $targetPlayer);
		$return['ActualDamage'] = $targetPlayer->getShip()->takeDamage($return['WeaponDamage']);

		if ($return['ActualDamage']['KillingShot']) {
			$return['KillResults'] = $targetPlayer->killPlayerByPlayer($weaponPlayer);
		}
		return $return;
	}

	/**
	 * @param array{Weapon: self, Target: \Smr\Port, Hit: bool} $return
	 * @return array{Weapon: self, Target: \Smr\Port, Hit: bool, WeaponDamage: WeaponDamageData, ActualDamage: TakenDamageData, KillResults?: array{}}
	 */
	protected function doPlayerDamageToPort(array $return, Player $weaponPlayer, Port $port): array {
		$return['WeaponDamage'] = $this->getModifiedDamageAgainstPort($weaponPlayer, $port);
		$return['ActualDamage'] = $port->takeDamage($return['WeaponDamage']);
		if ($return['ActualDamage']['KillingShot']) {
			$return['KillResults'] = $port->killPortByPlayer($weaponPlayer);
		}
		return $return;
	}

	/**
	 * @param array{Weapon: self, Target: \Smr\Planet, Hit: bool} $return
	 * @return array{Weapon: self, Target: \Smr\Planet, Hit: bool, WeaponDamage: WeaponDamageData, ActualDamage: TakenDamageData, KillResults?: array{}}
	 */
	protected function doPlayerDamageToPlanet(array $return, Player $weaponPlayer, Planet $planet): array {
		$return['WeaponDamage'] = $this->getModifiedDamageAgainstPlanet($weaponPlayer, $planet);
		$return['ActualDamage'] = $planet->takeDamage($return['WeaponDamage']);
		if ($return['ActualDamage']['KillingShot']) {
			$return['KillResults'] = $planet->killPlanetByPlayer($weaponPlayer);
		}
		return $return;
	}

	/**
	 * @param array{Weapon: self, Target: \Smr\Player, Hit: bool} $return
	 * @return array{Weapon: self, Target: \Smr\Player, Hit: bool, WeaponDamage: WeaponDamageData, ActualDamage: TakenDamageData, KillResults?: array{DeadExp: int, LostCredits: int}}
	 */
	protected function doPortDamageToPlayer(array $return, Port $port, Player $targetPlayer): array {
		$return['WeaponDamage'] = $this->getModifiedPortDamageAgainstPlayer($port, $targetPlayer);
		$return['ActualDamage'] = $targetPlayer->getShip()->takeDamage($return['WeaponDamage']);

		if ($return['ActualDamage']['KillingShot']) {
			$return['KillResults'] = $targetPlayer->killPlayerByPort($port);
		}
		return $return;
	}

	/**
	 * @param array{Weapon: self, Target: \Smr\Player, Hit: bool} $return
	 * @return array{Weapon: self, Target: \Smr\Player, Hit: bool, WeaponDamage: WeaponDamageData, ActualDamage: TakenDamageData, KillResults?: array{DeadExp: int, LostCredits: int}}
	 */
	protected function doPlanetDamageToPlayer(array $return, Planet $planet, Player $targetPlayer): array {
		$return['WeaponDamage'] = $this->getModifiedPlanetDamageAgainstPlayer($planet, $targetPlayer);
		$return['ActualDamage'] = $targetPlayer->getShip()->takeDamage($return['WeaponDamage']);

		if ($return['ActualDamage']['KillingShot']) {
			$return['KillResults'] = $targetPlayer->killPlayerByPlanet($planet);
		}
		return $return;
	}

	/**
	 * @param array{Weapon: self, Target: \Smr\Player, Hit: bool} $return
	 * @return array{Weapon: self, Target: \Smr\Player, Hit: bool, WeaponDamage: WeaponDamageData, ActualDamage: TakenDamageData, KillResults?: array{DeadExp: int, LostCredits: int}}
	 */
	protected function doForceDamageToPlayer(array $return, Force $forces, Player $targetPlayer): array {
		$return['WeaponDamage'] = $this->getModifiedForceDamageAgainstPlayer($forces, $targetPlayer);
		$return['ActualDamage'] = $targetPlayer->getShip()->takeDamage($return['WeaponDamage']);

		if ($return['ActualDamage']['KillingShot']) {
			$return['KillResults'] = $targetPlayer->killPlayerByForces($forces);
		}
		return $return;
	}

	/**
	 * @return array{Weapon: self, Target: \Smr\Force, Hit: bool, WeaponDamage?: WeaponDamageData, ActualDamage?: ForceTakenDamageData, KillResults?: array{}}
	 */
	abstract public function shootForces(Player $weaponPlayer, Force $forces): array;

	/**
	 * @return array{Weapon: self, Target: \Smr\Player, Hit: bool, WeaponDamage?: WeaponDamageData, ActualDamage?: TakenDamageData, KillResults?: array{DeadExp: int, KillerExp: int, KillerCredits: int}}
	 */
	abstract public function shootPlayer(Player $weaponPlayer, Player $targetPlayer): array;

	/**
	 * @return array{Weapon: self, Target: \Smr\Player, Hit: bool, WeaponDamage: WeaponDamageData, ActualDamage: TakenDamageData, KillResults?: array{DeadExp: int, LostCredits: int}}
	 */
	abstract public function shootPlayerAsForce(Force $forces, Player $targetPlayer): array;

}
