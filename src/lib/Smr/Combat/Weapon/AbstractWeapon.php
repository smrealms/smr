<?php declare(strict_types=1);

namespace Smr\Combat\Weapon;

use Smr\Combat\CombatantInterface;
use Smr\Combat\Results\Damage\WeaponDamage;
use Smr\Combat\Results\Weapon\HitWeaponResult;
use Smr\Combat\WeaponShotAtCombatant;

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

	public function canShootTarget(CombatantInterface $target): bool {
		return true;
	}

	public function getDamage(): WeaponDamage {
		return new WeaponDamage(
			shieldDamage: $this->getShieldDamage(),
			armourDamage: $this->getArmourDamage(),
			damageRollover: $this->isDamageRollover(),
		);
	}

	abstract public function getModifiedDamageAgainstTarget(CombatantInterface $shooter, CombatantInterface $target): WeaponDamage;

	/**
	 * Applies this weapon's modified damage and resolves a killing shot, if any.
	 *
	 * @template TTarget of CombatantInterface
	 * @param WeaponShotAtCombatant<TTarget> $shot
	 * @return HitWeaponResult<TTarget>
	 */
	protected function hitTarget(WeaponShotAtCombatant $shot): HitWeaponResult {
		$shooter = $shot->shooter;
		$target = $shot->target;
		$weaponDamage = $this->getModifiedDamageAgainstTarget($shooter, $target);
		$actualDamage = $target->takeDamage($weaponDamage);
		return new HitWeaponResult(
			weapon: $this,
			target: $target,
			weaponDamage: $weaponDamage,
			actualDamage: $actualDamage,
			killResult: $actualDamage->killingShot ? $shot->resolveKill() : null,
		);
	}

}
