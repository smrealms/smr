<?php declare(strict_types=1);

namespace Traits;

/**
 * Common interface used by the subset of AbstractSmrCombatWeapon classes
 * that are used for forces (mines, combat drones, and scout drones).
 */
trait CombatWeaponForce {

	protected string $name;
	protected int $shieldDamage;
	protected int $armourDamage;
	protected int $accuracy;
	protected int $amount;

	public function getBaseAccuracy() : int {
		return $this->accuracy;
	}

	public function getName() : string {
		return $this->name;
	}

	public function getShieldDamage() : int {
		return $this->shieldDamage;
	}

	public function getArmourDamage() : int {
		return $this->armourDamage;
	}

	/**
	 * Returns the amount of this type of force.
	 */
	public function getAmount() : int {
		return $this->amount;
	}

}
