<?php declare(strict_types=1);

namespace Smr\Combat\Weapon;

use Exception;
use Smr\Combat\CombatantInterface;
use Smr\Combat\Results\Damage\WeaponDamage;
use Smr\Combat\Results\Weapon\HitWeaponResult;
use Smr\Combat\WeaponShotAtCombatant;
use Smr\Force;
use Smr\Ship;

class ScoutDrones extends AbstractWeapon {

	use ForcesTrait;

	public function __construct(int $numberOfSDs) {
		$this->amount = $numberOfSDs;
		$this->name = 'Scout Drones';
		$this->shieldDamage = 20;
		$this->armourDamage = 20;
		$this->accuracy = 100;
		$this->damageRollover = false;
	}

	public function getModifiedAccuracyAgainstTarget(CombatantInterface $shooter, CombatantInterface $target): float {
		$random = rand(1, 7) * rand(1, 7);
		$modifiedAccuracy = $this->getBaseAccuracy() - $target->getLevel() - $random;

		return max(0, min(100, $modifiedAccuracy));
	}

	public function getModifiedDamageAgainstTarget(
		CombatantInterface $shooter,
		CombatantInterface $target,
	): WeaponDamage {
		if (!$this->canShootTarget($target)) {
			return new WeaponDamage(
				shieldDamage: 0,
				armourDamage: 0,
				damageRollover: $this->isDamageRollover(),
			);
		}
		$baseDamage = $this->getDamage();
		$damage = new WeaponDamage(
			shieldDamage: $baseDamage->shieldDamage,
			armourDamage: $baseDamage->armourDamage,
			damageRollover: $baseDamage->damageRollover,
			launched: ICeil(
				$this->getAmount() * $this->getModifiedAccuracyAgainstTarget($shooter, $target) / 100,
			),
		);
		$damage->shieldDamage = ICeil($damage->launched * $damage->shieldDamage);
		$damage->armourDamage = ICeil($damage->launched * $damage->armourDamage);
		return $damage;
	}

	/**
	 * @template TTarget of CombatantInterface
	 * @param WeaponShotAtCombatant<TTarget> $shot
	 * @return HitWeaponResult<TTarget>
	 */
	public function shoot(WeaponShotAtCombatant $shot): HitWeaponResult {
		$shooter = $shot->shooter;
		$target = $shot->target;
		if (
			(!($shooter instanceof Force)) ||
			(!($target instanceof Ship))
		) {
			throw new Exception('ScoutDrones should not be used in this context');
		}

		$return = $this->hitTarget($shot);
		$this->amount -= $return->weaponDamage->launched; // kamikaze
		return $return;
	}

}
