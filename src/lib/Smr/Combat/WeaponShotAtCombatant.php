<?php declare(strict_types=1);

namespace Smr\Combat;

use Closure;
use Smr\Combat\Results\Kill\KillResultInterface;

/** @template-covariant TTarget of CombatantInterface */
class WeaponShotAtCombatant {

	/**
	 * Stores the weapon shot context until the weapon evaluates killing shot status.
	 * This ensures kill side effects occur only after damage is applied.
	 *
	 * @param Closure(): KillResultInterface $resolveKill
	 * @param TTarget $target
	 */
	public function __construct(
		public readonly CombatantInterface $shooter,
		public readonly CombatantInterface $target,
		private readonly Closure $resolveKill,
	) {}

	public function resolveKill(): KillResultInterface {
		return ($this->resolveKill)();
	}

}
