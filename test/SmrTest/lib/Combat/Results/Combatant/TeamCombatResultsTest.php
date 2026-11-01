<?php declare(strict_types=1);

namespace SmrTest\lib\Combat\Results\Combatant;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Smr\Combat\Results\Combatant\CombatantResult;
use Smr\Combat\Results\Combatant\TeamCombatResults;
use Smr\Combat\Results\Damage\NormalTakenDamage;
use Smr\Combat\Results\Damage\WeaponDamage;
use Smr\Combat\Results\Weapon\HitWeaponResult;
use Smr\Combat\Weapon\CombatDrones;
use Smr\Ship;

#[CoversClass(TeamCombatResults::class)]
class TeamCombatResultsTest extends TestCase {

	public function test_hasCombatant_compares_combat_ids(): void {
		$combatant = $this->createShip(42);
		$matchingCombatant = $this->createShip(42);
		$differentCombatant = $this->createShip(43);
		$teamResults = new TeamCombatResults(
			totalDamage: 0,
			traders: [
				CombatantResult::create(
					combatant: $combatant,
					weaponResults: [$this->createHit($combatant)],
				),
			],
		);

		self::assertTrue($teamResults->hasCombatant($matchingCombatant));
		self::assertFalse($teamResults->hasCombatant($differentCombatant));
	}

	public function test_getTotalDamageDealtTo_sums_damage_against_target(): void {
		$firstAttacker = $this->createShip(1);
		$secondAttacker = $this->createShip(2);
		$target = $this->createShip(3);
		$otherTarget = $this->createShip(4);
		// The target receives 7 damage from the first attacker and 5 from the second.
		$teamResults = new TeamCombatResults(
			totalDamage: 12,
			traders: [
				CombatantResult::create(
					combatant: $firstAttacker,
					weaponResults: [$this->createHit($target, damage: 7)],
				),
				CombatantResult::create(
					combatant: $secondAttacker,
					weaponResults: [$this->createHit($target, damage: 5)],
				),
			],
		);

		self::assertSame(12, $teamResults->getTotalDamageDealtTo($target));
		self::assertSame(0, $teamResults->getTotalDamageDealtTo($otherTarget));
	}

	private function createShip(int $combatID): Ship&Stub {
		$ship = $this->createStub(Ship::class);
		$ship->method('getCombatID')->willReturn($combatID);
		return $ship;
	}

	/**
	 * @return HitWeaponResult<Ship>
	 */
	private function createHit(Ship $target, int $damage = 0): HitWeaponResult {
		return new HitWeaponResult(
			weapon: new CombatDrones(1),
			target: $target,
			weaponDamage: new WeaponDamage(
				shieldDamage: $damage,
				armourDamage: 0,
				damageRollover: false,
			),
			actualDamage: new NormalTakenDamage(
				killingShot: false,
				targetAlreadyDead: false,
				shieldDamage: $damage,
				combatDroneDamage: 0,
				numCombatDrones: 0,
				hasCombatDrones: false,
				armourDamage: 0,
				totalDamage: $damage,
			),
			killResult: null,
		);
	}

}
