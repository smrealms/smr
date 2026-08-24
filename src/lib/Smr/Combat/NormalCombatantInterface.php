<?php declare(strict_types=1);

namespace Smr\Combat;

use Smr\Combat\Weapon\CombatDrones;

/**
 * Combatant that exchanges normal shield, drone, and armour weapon damage.
 *
 * @template-contravariant TKiller of CombatantInterface = CombatantInterface
 * @extends CombatantInterface<TKiller, \Smr\Combat\Results\Damage\NormalTakenDamage>
 */
interface NormalCombatantInterface extends CombatantInterface {

	/** @return array<int, \Smr\Combat\Weapon\Weapon> */
	public function getWeapons(): array;

	public function createCombatDrones(): CombatDrones;

	public function isDestroyed(): bool;

}
