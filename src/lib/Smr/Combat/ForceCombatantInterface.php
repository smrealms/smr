<?php declare(strict_types=1);

namespace Smr\Combat;

/**
 * Combatant that receives Force-specific mine, combat drone, and scout drone damage.
 *
 * @template-contravariant TKiller of CombatantInterface = CombatantInterface
 * @extends CombatantInterface<TKiller, \Smr\Combat\Results\Damage\ForceTakenDamage>
 */
interface ForceCombatantInterface extends CombatantInterface {
}
