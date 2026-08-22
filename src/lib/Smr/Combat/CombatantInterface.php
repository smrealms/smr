<?php declare(strict_types=1);

namespace Smr\Combat;

use Smr\Combat\Results\Damage\TakenDamage;
use Smr\Combat\Results\Damage\WeaponDamage;
use Smr\Combat\Results\Kill\KillResultInterface;

/**
 * Common combat capabilities shared by ships, ports, planets, and forces.
 *
 * TKiller restricts the combatant types that may destroy this combatant.
 * TDamage associates each combatant type with the damage result it receives.
 *
 * @template-contravariant TKiller of CombatantInterface = CombatantInterface
 * @template-covariant TDamage of TakenDamage = TakenDamage
 */
interface CombatantInterface {

	/** @return TDamage */
	public function takeDamage(WeaponDamage $damage): TakenDamage;

	public function getCombatName(): string;

	public function getCombatID(): int;

	/** @param TKiller $killer */
	public function killBy(CombatantInterface $killer): KillResultInterface;

	/**
	 * Factor to multiply drone damage by if target player has a DCS.
	 */
	public function reduceDamageDoneDCS(): float;

	public function decreaseCDs(int $number): void;

	public function hasCDs(): bool;

	public function getCDs(): int;

	public function getLevel(): float|int;

}
