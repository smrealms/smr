<?php declare(strict_types=1);

namespace SmrTest\lib\Combat\Weapon;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Smr\AbstractShip;
use Smr\Combat\Weapon\CombatDrones;
use Smr\Force;
use Smr\Planet;
use Smr\Player;
use Smr\Port;

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
		$player = $this->createStub(Player::class);
		$forces = $this->createStub(Force::class);
		srand(1);

		$result = $drones->getModifiedAccuracyAgainstForces($player, $forces);
		self::assertSame(51.0, $result);
	}

	public function test_getModifiedAccuracyAgainstPort_adds_random_accuracy(): void {
		$drones = $this->createDrones();
		$player = $this->createStub(Player::class);
		$port = $this->createStub(Port::class);
		srand(1);

		$result = $drones->getModifiedAccuracyAgainstPort($player, $port);
		self::assertSame(51.0, $result);
	}

	public function test_getModifiedAccuracyAgainstPlanet_adds_random_accuracy(): void {
		$drones = $this->createDrones();
		$player = $this->createStub(Player::class);
		$planet = $this->createStub(Planet::class);
		srand(1);

		$result = $drones->getModifiedAccuracyAgainstPlanet($player, $planet);
		self::assertSame(51.0, $result);
	}

	#[TestWith([0, 32.7477777778])]
	#[TestWith([10, 41.6366666667])]
	public function test_getModifiedAccuracyAgainstPlayer_applies_level_and_mr_modifiers(
		int $level,
		float $expected,
	): void {
		$drones = $this->createDrones();
		$weaponPlayer = $this->createWeaponPlayer($level);
		$targetPlayer = $this->createTargetPlayer(hasDcs: false, mr: 15);
		srand(1);

		$result = $drones->getModifiedAccuracyAgainstPlayer($weaponPlayer, $targetPlayer);
		self::assertEqualsWithDelta($expected, $result, 0.0001);
	}

	public function test_getModifiedForceAccuracyAgainstPlayer_adds_random_accuracy(): void {
		$drones = $this->createDrones();
		$forces = $this->createStub(Force::class);
		$targetPlayer = $this->createStub(Player::class);
		srand(1);

		$result = $drones->getModifiedForceAccuracyAgainstPlayer($forces, $targetPlayer);
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
		$forces = $this->createStub(Force::class);
		$forces->method('getMines')->willReturn(3);
		srand(1);

		$expected = ['Shield' => $damage, 'Armour' => $damage, 'Rollover' => true, 'Launched' => 6, 'Kamikaze' => $kamikaze];
		$result = $drones->getModifiedDamageAgainstForces($player, $forces);
		self::assertSame($expected, $result);
	}

	public function test_getModifiedDamageAgainstPort_applies_launched_drone_damage(): void {
		$drones = $this->createDrones();
		$player = $this->createStub(Player::class);
		$port = $this->createStub(Port::class);
		srand(1);

		$expected = ['Shield' => 12, 'Armour' => 12, 'Rollover' => true, 'Launched' => 6];
		$result = $drones->getModifiedDamageAgainstPort($player, $port);
		self::assertSame($expected, $result);
	}

	public function test_getModifiedDamageAgainstPlanet_reduces_damage_for_planet(): void {
		$drones = $this->createDrones();
		$player = $this->createStub(Player::class);
		$planet = $this->createStub(Planet::class);
		srand(1);

		$expected = ['Shield' => 3, 'Armour' => 3, 'Rollover' => true, 'Launched' => 6];
		$result = $drones->getModifiedDamageAgainstPlanet($player, $planet);
		self::assertSame($expected, $result);
	}

	#[TestWith([true, 7])]
	#[TestWith([false, 10])]
	public function test_getModifiedDamageAgainstPlayer_applies_dcs_modifier(bool $hasDcs, int $damage): void {
		$drones = $this->createDrones();
		$weaponPlayer = $this->createWeaponPlayer();
		$targetPlayer = $this->createTargetPlayer(hasDcs: $hasDcs);
		srand(1);

		$expected = ['Shield' => $damage, 'Armour' => $damage, 'Rollover' => true, 'Launched' => 5];
		$result = $drones->getModifiedDamageAgainstPlayer($weaponPlayer, $targetPlayer);
		self::assertSame($expected, $result);
	}

	#[TestWith([true, 9])]
	#[TestWith([false, 12])]
	public function test_getModifiedForceDamageAgainstPlayer_applies_dcs_modifier(bool $hasDcs, int $damage): void {
		$drones = $this->createDrones();
		$forces = $this->createStub(Force::class);
		$targetPlayer = $this->createTargetPlayer(hasDcs: $hasDcs);
		srand(1);

		$expected = ['Shield' => $damage, 'Armour' => $damage, 'Rollover' => true, 'Launched' => 6];
		$result = $drones->getModifiedForceDamageAgainstPlayer($forces, $targetPlayer);
		self::assertSame($expected, $result);
	}

	#[TestWith([true, 15])]
	#[TestWith([false, 20])]
	public function test_getModifiedPortDamageAgainstPlayer_applies_dcs_modifier(bool $hasDcs, int $damage): void {
		$drones = $this->createDrones();
		$port = $this->createStub(Port::class);
		$targetPlayer = $this->createTargetPlayer(hasDcs: $hasDcs);

		$expected = ['Shield' => $damage, 'Armour' => $damage, 'Rollover' => true, 'Launched' => 10];
		$result = $drones->getModifiedPortDamageAgainstPlayer($port, $targetPlayer);
		self::assertSame($expected, $result);
	}

	#[TestWith([true, 15])]
	#[TestWith([false, 20])]
	public function test_getModifiedPlanetDamageAgainstPlayer_applies_dcs_modifier(bool $hasDcs, int $damage): void {
		$drones = $this->createDrones();
		$planet = $this->createStub(Planet::class);
		$targetPlayer = $this->createTargetPlayer(hasDcs: $hasDcs);

		$expected = ['Shield' => $damage, 'Armour' => $damage, 'Rollover' => true, 'Launched' => 10];
		$result = $drones->getModifiedPlanetDamageAgainstPlayer($planet, $targetPlayer);
		self::assertSame($expected, $result);
	}

	// Test shoot methods -----------------------------------------------------

	public function test_shootForces_always_reports_hit_and_consumes_kamikaze_drones(): void {
		$drones = $this->createDrones();
		$player = $this->createStub(Player::class);
		$player->method('isCombatDronesKamikazeOnMines')->willReturn(true);
		$ship = $this->createMock(AbstractShip::class);
		// With kamikaze on mines, CD amount on ship will be decreased by kamikaze amount
		$ship->expects($this->once())->method('decreaseCDs')->with(3)->seal();
		$player->method('getShip')->willReturn($ship);
		$forces = $this->createStub(Force::class);
		$forces->method('getMines')->willReturn(3);
		$forces->method('takeDamage')->willReturn(['KillingShot' => false]);
		srand(1);

		$result = $drones->shootForces($player, $forces);

		self::assertTrue($result['Hit']);
		self::assertSame(['Shield' => 66, 'Armour' => 66, 'Rollover' => true, 'Launched' => 6, 'Kamikaze' => 3], $result['WeaponDamage']);
	}

	// Private helper functions -----------------------------------------------

	private function createDrones(): CombatDrones {
		return new CombatDrones(10);
	}

	private function createWeaponPlayer(int $level = 10): Player {
		$ship = $this->createStub(AbstractShip::class);
		$ship->method('getMR')->willReturn(0);
		$player = $this->createStub(Player::class);
		$player->method('getLevelID')->willReturn($level);
		$player->method('getShip')->willReturn($ship);
		return $player;
	}

	private function createTargetPlayer(bool $hasDcs, int $mr = 0): Player {
		$ship = $this->createStub(AbstractShip::class);
		$ship->method('hasDCS')->willReturn($hasDcs);
		$ship->method('getMR')->willReturn($mr);
		$player = $this->createStub(Player::class);
		$player->method('getLevelID')->willReturn(10);
		$player->method('getShip')->willReturn($ship);
		return $player;
	}

}
