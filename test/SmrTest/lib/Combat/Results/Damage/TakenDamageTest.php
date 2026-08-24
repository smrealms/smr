<?php declare(strict_types=1);

namespace SmrTest\lib\Combat\Results\Damage;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Smr\Combat\Results\Damage\ForceTakenDamage;
use Smr\Combat\Results\Damage\NormalTakenDamage;
use Smr\Combat\Results\Damage\TakenDamage;

#[CoversClass(TakenDamage::class)]
#[CoversClass(NormalTakenDamage::class)]
#[CoversClass(ForceTakenDamage::class)]
class TakenDamageTest extends TestCase {

	public function test_normalDamage_keeps_common_and_normal_damage_details(): void {
		$damage = new NormalTakenDamage(
			killingShot: true,
			targetAlreadyDead: false,
			shieldDamage: 2,
			combatDroneDamage: 3,
			numCombatDrones: 4,
			hasCombatDrones: true,
			armourDamage: 5,
			totalDamage: 10,
		);

		self::assertInstanceOf(TakenDamage::class, $damage);
		self::assertTrue($damage->killingShot);
		self::assertFalse($damage->targetAlreadyDead);
		self::assertSame(3, $damage->combatDroneDamage);
		self::assertSame(4, $damage->numCombatDrones);
		self::assertTrue($damage->hasCombatDrones);
		self::assertSame(10, $damage->totalDamage);
		self::assertSame(2, $damage->shieldDamage);
		self::assertSame(5, $damage->armourDamage);
	}

	public function test_forceDamage_keeps_common_and_force_damage_details(): void {
		$damage = new ForceTakenDamage(
			killingShot: false,
			targetAlreadyDead: true,
			minesDamage: 2,
			numMines: 3,
			hasMines: true,
			combatDroneDamage: 4,
			numCombatDrones: 5,
			hasCombatDrones: true,
			scoutDroneDamage: 6,
			numScoutDrones: 7,
			hasScoutDrones: true,
			totalDamage: 12,
		);

		self::assertInstanceOf(TakenDamage::class, $damage);
		self::assertFalse($damage->killingShot);
		self::assertTrue($damage->targetAlreadyDead);
		self::assertSame(2, $damage->minesDamage);
		self::assertSame(3, $damage->numMines);
		self::assertTrue($damage->hasMines);
		self::assertSame(4, $damage->combatDroneDamage);
		self::assertSame(5, $damage->numCombatDrones);
		self::assertTrue($damage->hasCombatDrones);
		self::assertSame(6, $damage->scoutDroneDamage);
		self::assertSame(7, $damage->numScoutDrones);
		self::assertTrue($damage->hasScoutDrones);
		self::assertSame(12, $damage->totalDamage);
	}

}
