<?php declare(strict_types=1);

namespace Smr\Combat\Results\Weapon;

use Smr\Combat\CombatantInterface;
use Smr\Combat\Results\Damage\TakenDamage;
use Smr\Combat\Results\Damage\WeaponDamage;
use Smr\Combat\Results\Kill\KillResultInterface;
use Smr\Combat\Weapon\AbstractWeapon;

/**
 * Represents a weapon hit a target, including the damage applied, taken, and an on-kill callback.
 *
 * @template-covariant TTarget of CombatantInterface
 */
final class HitWeaponResult extends WeaponResult {

	/**
	 * The target's CombatantInterface specialization determines actual damage type.
	 *
	 * @param TTarget $target
	 * @param template-type<TTarget, \Smr\Combat\CombatantInterface, 'TDamage'> $actualDamage
	 */
	public function __construct(
		AbstractWeapon $weapon,
		CombatantInterface $target,
		public readonly WeaponDamage $weaponDamage,
		public readonly TakenDamage $actualDamage,
		public readonly ?KillResultInterface $killResult,
	) {
		parent::__construct($weapon, $target);
	}

}
