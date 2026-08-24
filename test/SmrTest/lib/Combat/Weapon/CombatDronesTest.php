<?php declare(strict_types=1);

namespace SmrTest\lib\Combat\Weapon;

use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Smr\Combat\Results\Damage\ForceTakenDamage;
use Smr\Combat\Results\Damage\WeaponDamage;
use Smr\Combat\Results\Weapon\HitWeaponResult;
use Smr\Combat\Weapon\CombatDrones;
use Smr\Combat\WeaponShotAtCombatant;
use Smr\Force;
use Smr\Planet;
use Smr\Player;
use Smr\Port;
use Smr\Ship;

#[CoversClass(CombatDrones::class)]
class CombatDronesTest extends TestCase {

	public function test_getAmount(): void {
		$drones = new CombatDrones(100);
		self::assertSame(100, $drones->getAmount());
	}

	public function test_getShieldDamage(): void {
		// regular drones
		$drones = new CombatDrones(100); // doesn't matter how many
		self::assertSame(2, $drones->getShieldDamage());
		// port/planet drones
		$drones = new CombatDrones(100, true);
		self::assertSame(1, $drones->getShieldDamage());
	}

	public function test_getArmourDamage(): void {
		// regular drones
		$drones = new CombatDrones(100); // doesn't matter how many
		self::assertSame(2, $drones->getArmourDamage());
		// port/planet drones
		$drones = new CombatDrones(100, true);
		self::assertSame(1, $drones->getArmourDamage());
	}

	// Test Accuracy methods --------------------------------------------------

	public function test_getBaseAccuracy(): void {
		$result = $this->createDrones()->getBaseAccuracy();
		self::assertSame(3, $result);
	}

	public function test_getModifiedAccuracyAgainstForces_adds_random_accuracy(): void {
		$drones = $this->createDrones();
		$ship = $this->createShip();
		$forces = $this->createStub(Force::class);
		srand(1);

		$result = $drones->getModifiedAccuracyAgainstTarget($ship, $forces);
		self::assertSame(51.0, $result);
	}

	public function test_getModifiedAccuracyAgainstPort_adds_random_accuracy(): void {
		$drones = $this->createDrones();
		$ship = $this->createShip();
		$port = $this->createStub(Port::class);
		srand(1);

		$result = $drones->getModifiedAccuracyAgainstTarget($ship, $port);
		self::assertSame(51.0, $result);
	}

	public function test_getModifiedAccuracyAgainstPlanet_adds_random_accuracy(): void {
		$drones = $this->createDrones();
		$ship = $this->createShip();
		$planet = $this->createStub(Planet::class);
		srand(1);

		$result = $drones->getModifiedAccuracyAgainstTarget($ship, $planet);
		self::assertSame(51.0, $result);
	}

	#[TestWith([0, 32.7477777778])]
	#[TestWith([10, 41.6366666667])]
	public function test_getModifiedAccuracyAgainstPlayer_applies_level_and_mr_modifiers(
		int $level,
		float $expected,
	): void {
		$drones = $this->createDrones();
		$weaponShip = $this->createShip($level);
		$targetShip = $this->createShip(hasDcs: false, mr: 15);
		srand(1);

		$result = $drones->getModifiedAccuracyAgainstTarget($weaponShip, $targetShip);
		self::assertEqualsWithDelta($expected, $result, 0.0001);
	}

	public function test_getModifiedForceAccuracyAgainstPlayer_adds_random_accuracy(): void {
		$drones = $this->createDrones();
		$forces = $this->createStub(Force::class);
		$targetShip = $this->createShip();
		srand(1);

		$result = $drones->getModifiedAccuracyAgainstTarget($forces, $targetShip);
		self::assertSame(51.0, $result);
	}

	// Test Damage methods ----------------------------------------------------

	#[TestWith([true, 66, 3])]
	#[TestWith([false, 12, 0])]
	public function test_getModifiedDamageAgainstForces_applies_kamikaze_setting(
		bool $kamikazeOnMines,
		int $damage,
		int $kamikaze,
	): void {
		$drones = $this->createDrones();
		$player = $this->createStub(Player::class);
		$player->method('isCombatDronesKamikazeOnMines')->willReturn($kamikazeOnMines);
		$ship = $this->createShip();
		$ship->method('getPlayer')->willReturn($player);
		$forces = $this->createStub(Force::class);
		$forces->method('getMines')->willReturn(3);
		srand(1);

		$expected = new WeaponDamage(
			shieldDamage: $kamikazeOnMines ? 6 : $damage,
			armourDamage: $damage,
			damageRollover: true,
			launched: 6,
			kamikaze: $kamikaze,
		);
		$result = $drones->getModifiedDamageAgainstTarget($ship, $forces);
		self::assertEquals($expected, $result);
	}

	public function test_getModifiedDamageAgainstPort_applies_launched_drone_damage(): void {
		$drones = $this->createDrones();
		$ship = $this->createShip();
		$port = $this->createStub(Port::class);
		srand(1);

		$expected = $this->damage(amount: 12, launched: 6);
		$result = $drones->getModifiedDamageAgainstTarget($ship, $port);
		self::assertEquals($expected, $result);
	}

	public function test_getModifiedDamageAgainstPlanet_reduces_damage_for_planet(): void {
		$drones = $this->createDrones();
		$ship = $this->createShip();
		$planet = $this->createStub(Planet::class);
		srand(1);

		$expected = $this->damage(amount: 3, launched: 6);
		$result = $drones->getModifiedDamageAgainstTarget($ship, $planet);
		self::assertEquals($expected, $result);
	}

	#[TestWith([true, 7])]
	#[TestWith([false, 10])]
	public function test_getModifiedDamageAgainstPlayer_applies_dcs_modifier(bool $hasDcs, int $damage): void {
		$drones = $this->createDrones();
		$weaponShip = $this->createShip();
		$targetShip = $this->createShip(hasDcs: $hasDcs);
		srand(1);

		$expected = $this->damage(amount: $damage, launched: 5);
		$result = $drones->getModifiedDamageAgainstTarget($weaponShip, $targetShip);
		self::assertEquals($expected, $result);
	}

	#[TestWith([true, 9])]
	#[TestWith([false, 12])]
	public function test_getModifiedForceDamageAgainstPlayer_applies_dcs_modifier(bool $hasDcs, int $damage): void {
		$drones = $this->createDrones();
		$forces = $this->createStub(Force::class);
		$forces->method('reduceDamageDoneDCS')->willReturn(DCS_FORCE_DAMAGE_DECIMAL_PERCENT);
		$targetShip = $this->createShip(hasDcs: $hasDcs);
		srand(1);

		$expected = $this->damage(amount: $damage, launched: 6);
		$result = $drones->getModifiedDamageAgainstTarget($forces, $targetShip);
		self::assertEquals($expected, $result);
	}

	#[TestWith([true, 15])]
	#[TestWith([false, 20])]
	public function test_getModifiedPortDamageAgainstPlayer_applies_dcs_modifier(bool $hasDcs, int $damage): void {
		$drones = $this->createDrones();
		$port = $this->createStub(Port::class);
		$port->method('reduceDamageDoneDCS')->willReturn(DCS_PORT_DAMAGE_DECIMAL_PERCENT);
		$targetShip = $this->createShip(hasDcs: $hasDcs);

		$expected = $this->damage(amount: $damage, launched: 10);
		$result = $drones->getModifiedDamageAgainstTarget($port, $targetShip);
		self::assertEquals($expected, $result);
	}

	#[TestWith([true, 15])]
	#[TestWith([false, 20])]
	public function test_getModifiedPlanetDamageAgainstPlayer_applies_dcs_modifier(bool $hasDcs, int $damage): void {
		$drones = $this->createDrones();
		$planet = $this->createStub(Planet::class);
		$planet->method('reduceDamageDoneDCS')->willReturn(DCS_PLANET_DAMAGE_DECIMAL_PERCENT);
		$targetShip = $this->createShip(hasDcs: $hasDcs);

		$expected = $this->damage(amount: $damage, launched: 10);
		$result = $drones->getModifiedDamageAgainstTarget($planet, $targetShip);
		self::assertEquals($expected, $result);
	}

	public function test_shoot_always_reports_hit_and_consumes_kamikaze_drones(): void {
		$drones = $this->createDrones();
		$player = $this->createStub(Player::class);
		$player->method('isCombatDronesKamikazeOnMines')->willReturn(true);
		$ship = $this->createMock(Ship::class);
		$ship->expects($this->once())->method('getPlayer')->willReturn($player);
		// With kamikaze on mines, CD amount on ship will be decreased by kamikaze amount
		$ship->expects($this->once())->method('decreaseCDs')->with(3)->seal();
		$forces = $this->createStub(Force::class);
		$forces->method('getMines')->willReturn(3);
		$takenDamage = new ForceTakenDamage(false, false, 0, 0, false, 0, 0, false, 0, 0, false, 0);
		$forces->method('takeDamage')->willReturn($takenDamage);
		srand(1);

		$result = $drones->shoot(
			new WeaponShotAtCombatant($ship, $forces, static fn() => throw new LogicException()),
		);

		$expected = new WeaponDamage(
			shieldDamage: 6,
			armourDamage: 66,
			damageRollover: true,
			launched: 6,
			kamikaze: 3,
		);
		self::assertInstanceOf(HitWeaponResult::class, $result);
		self::assertEquals($takenDamage, $result->actualDamage);
		self::assertEquals($expected, $result->weaponDamage);
	}

	// Private helper functions -----------------------------------------------

	private function createDrones(): CombatDrones {
		return new CombatDrones(10);
	}

	private function damage(int $amount, int $launched): WeaponDamage {
		return new WeaponDamage(
			shieldDamage: $amount,
			armourDamage: $amount,
			damageRollover: true,
			launched: $launched,
			kamikaze: 0,
		);
	}

	private function createShip(int $level = 10, int $mr = 0, bool $hasDcs = false): Ship&Stub {
		$ship = $this->createStub(Ship::class);
		$ship->method('hasDCS')->willReturn($hasDcs);
		$ship->method('getLevel')->willReturn($level);
		$ship->method('getMR')->willReturn($mr);
		$ship->method('reduceDamageDoneDCS')->willReturn(DCS_PLAYER_DAMAGE_DECIMAL_PERCENT);
		return $ship;
	}

}
