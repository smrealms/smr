<?php declare(strict_types=1);

namespace SmrTest\lib\Combat\Weapon;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Smr\AbstractShip;
use Smr\Combat\Weapon\ScoutDrones;
use Smr\Force;
use Smr\Player;

#[CoversClass(ScoutDrones::class)]
class ScoutDronesTest extends TestCase {

	public function test_getAmount(): void {
		$sds = new ScoutDrones(100);
		self::assertSame(100, $sds->getAmount());
	}

	public function test_getShieldDamage(): void {
		$sds = new ScoutDrones(100); // doesn't matter how many
		self::assertSame(20, $sds->getShieldDamage());
	}

	public function test_getArmourDamage(): void {
		$sds = new ScoutDrones(100); // doesn't matter how many
		self::assertSame(20, $sds->getShieldDamage());
	}

	public function test_getBaseAccuracy(): void {
		$sds = new ScoutDrones(100);
		self::assertSame(100, $sds->getBaseAccuracy());
	}

	#[TestWith([0, 96.0])]
	#[TestWith([10, 86.0])]
	public function test_getModifiedForceAccuracyAgainstPlayer_reduces_accuracy_for_player_level_and_randomness(
		int $level,
		float $expected,
	): void {
		$drones = $this->createDrones();
		$force = $this->createStub(Force::class);
		$targetPlayer = $this->createStub(Player::class);
		$targetPlayer->method('getLevelID')->willReturn($level);
		srand(1);

		$result = $drones->getModifiedForceAccuracyAgainstPlayer($force, $targetPlayer);
		self::assertSame($expected, $result);
	}

	public function test_getModifiedForceDamageAgainstPlayer_applies_launched_drone_damage(): void {
		$drones = $this->createDrones();
		$targetPlayer = $this->createStub(Player::class);
		$targetPlayer->method('getLevelID')->willReturn(0);
		$forces = $this->createStub(Force::class);
		srand(1);

		$expected = ['Shield' => 200, 'Armour' => 200, 'Rollover' => false, 'Launched' => 10];
		$result = $drones->getModifiedForceDamageAgainstPlayer($forces, $targetPlayer);

		self::assertSame($expected, $result);
	}

	public function test_shootPlayerAsForce_applies_damage_and_consumes_launched_drones(): void {
		$drones = $this->createDrones();
		$forces = $this->createStub(Force::class);
		$ship = $this->createStub(AbstractShip::class);
		$ship->method('takeDamage')
			->willReturn(['TotalDamage' => 200, 'KillingShot' => false]);
		$targetPlayer = $this->createStub(Player::class);
		$targetPlayer->method('getLevelID')->willReturn(0);
		$targetPlayer->method('getShip')->willReturn($ship);
		srand(1);

		$result = $drones->shootPlayerAsForce($forces, $targetPlayer);

		self::assertTrue($result['Hit']);
		self::assertSame(0, $drones->getAmount());
	}

	private function createDrones(): ScoutDrones {
		return new ScoutDrones(10);
	}

}
