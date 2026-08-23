<?php declare(strict_types=1);

namespace SmrTest\lib\Combat\Weapon;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Smr\AbstractShip;
use Smr\Combat\Weapon\Weapon;
use Smr\Force;
use Smr\Planet;
use Smr\Player;
use Smr\Port;

#[CoversClass(Weapon::class)]
class WeaponTest extends TestCase {

	// Test Damage methods ----------------------------------------------------

	public function test_getModifiedDamageAgainstForces_returns_base_damage(): void {
		$weapon = $this->createWeapon();
		$expected = $this->baseDamage();
		$result = $weapon->getModifiedDamageAgainstForces(
			$this->createStub(Player::class),
			$this->createStub(Force::class),
		);

		self::assertSame($expected, $result);
	}

	public function test_getModifiedDamageAgainstPort_returns_base_damage(): void {
		$weapon = $this->createWeapon();
		$expected = $this->baseDamage();
		$result = $weapon->getModifiedDamageAgainstPort(
			$this->createStub(Player::class),
			$this->createStub(Port::class),
		);

		self::assertSame($expected, $result);
	}

	public function test_getModifiedDamageAgainstPlanet_reduces_base_damage(): void {
		$weapon = $this->createWeapon();
		$expected = ['Shield' => 3, 'Armour' => 2, 'Rollover' => false];
		$result = $weapon->getModifiedDamageAgainstPlanet(
			$this->createStub(Player::class),
			$this->createStub(Planet::class),
		);

		self::assertSame($expected, $result);
	}

	public function test_getModifiedDamageAgainstPlayer_returns_base_damage(): void {
		$weapon = $this->createWeapon();
		$expected = $this->baseDamage();
		$result = $weapon->getModifiedDamageAgainstPlayer(
			$this->createStub(Player::class),
			$this->createStub(Player::class),
		);

		self::assertSame($expected, $result);
	}

	public function test_getModifiedPortDamageAgainstPlayer_returns_base_damage(): void {
		$weapon = $this->createWeapon();
		$expected = $this->baseDamage();
		$result = $weapon->getModifiedPortDamageAgainstPlayer(
			$this->createStub(Port::class),
			$this->createStub(Player::class),
		);

		self::assertSame($expected, $result);
	}

	public function test_getModifiedPlanetDamageAgainstPlayer_returns_base_damage(): void {
		$weapon = $this->createWeapon();
		$expected = $this->baseDamage();
		$result = $weapon->getModifiedPlanetDamageAgainstPlayer(
			$this->createStub(Planet::class),
			$this->createStub(Player::class),
		);

		self::assertSame($expected, $result);
	}

	// Test Accuracy methods --------------------------------------------------

	#[TestWith([0, 61.2])]
	#[TestWith([10, 65.2])]
	public function test_getModifiedAccuracyAgainstForces_applies_player_level_modifier(int $level, float $expected): void {
		$weaponPlayer = $this->createPlayer($level);
		$force = $this->createStub(Force::class);

		$result = $this->createWeapon()->getModifiedAccuracyAgainstForces($weaponPlayer, $force);
		self::assertEqualsWithDelta($expected, $result, 0.0001);
	}

	#[TestWith([0, 0, 61.2])]
	#[TestWith([0, 10, 49.2])]
	#[TestWith([10, 0, 65.2])]
	#[TestWith([10, 10, 53.2])]
	public function test_getModifiedAccuracyAgainstPort_reduces_accuracy_for_port_level(
		int $weaponLevel,
		int $portLevel,
		float $expected,
	): void {
		$port = $this->createStub(Port::class);
		$port->method('getLevel')->willReturn($portLevel);
		$weaponPlayer = $this->createPlayer($weaponLevel);

		$result = $this->createWeapon()->getModifiedAccuracyAgainstPort($weaponPlayer, $port);
		self::assertEqualsWithDelta($expected, $result, 0.0001);
	}

	#[TestWith([0, 0.0, 61.2])]
	#[TestWith([0, 35.0, 55.2])]
	#[TestWith([10, 0.0, 65.2])]
	#[TestWith([10, 35.0, 59.2])]
	public function test_getModifiedAccuracyAgainstPlanet_reduces_accuracy_for_planet_level(
		int $weaponLevel,
		float $planetLevel,
		float $expected,
	): void {
		$planet = $this->createStub(Planet::class);
		$planet->method('getLevel')->willReturn($planetLevel);
		$weaponPlayer = $this->createPlayer($weaponLevel);

		$result = $this->createWeapon()->getModifiedAccuracyAgainstPlanet($weaponPlayer, $planet);
		self::assertEqualsWithDelta($expected, $result, 0.0001);
	}

	#[TestWith([0, 0, 60.0])]
	#[TestWith([0, 20, 55.0])]
	#[TestWith([10, 0, 64.0])]
	#[TestWith([10, 20, 59.0])]
	public function test_getModifiedAccuracyAgainstPlayer_reduces_accuracy_for_level_and_mr(
		int $weaponLevel,
		int $targetLevel,
		float $expected,
	): void {
		$weaponPlayer = $this->createPlayer(level: $weaponLevel, mr: 0);
		$targetPlayer = $this->createPlayer(level: $targetLevel, mr: 15);

		$result = $this->createWeapon()->getModifiedAccuracyAgainstPlayer($weaponPlayer, $targetPlayer);
		self::assertEqualsWithDelta($expected, $result, 0.0001);
	}

	#[TestWith([0, 58.8])]
	#[TestWith([20, 48.8])]
	public function test_getModifiedPortAccuracyAgainstPlayer_reduces_accuracy_for_player_level(
		int $targetLevel,
		float $expected,
	): void {
		$port = $this->createStub(Port::class);
		$targetPlayer = $this->createPlayer($targetLevel);

		$result = $this->createWeapon()->getModifiedPortAccuracyAgainstPlayer($port, $targetPlayer);
		self::assertEqualsWithDelta($expected, $result, 0.0001);
	}

	#[TestWith([false, 0, 0.0, 4, 62.8])]
	#[TestWith([false, 0, 10.0, 4, 62.8])] // planet level does not change mounted weapon accuracy
	#[TestWith([false, 0, 10.0, 10, 68.8])] // accuracy bonus does change mounted weapon accuracy
	#[TestWith([false, 20, 10.0, 10, 58.8])] // target player level changes mounted weapon accuracy
	#[TestWith([true, 0, 0.0, 4, 58.8])]
	#[TestWith([true, 0, 10.0, 4, 63.8])] // planet level does change turret accuracy
	#[TestWith([true, 0, 10.0, 10, 63.8])] // accuracy bonus does not turret accuracy
	#[TestWith([true, 20, 10.0, 10, 53.8])] // target player level changes turret accuracy
	public function test_getModifiedPlanetAccuracyAgainstPlayer_reduces_modified_accuracy_for_player_level(
		bool $isTurret,
		int $playerLevel,
		float $planetLevel,
		int $accuracyBonus,
		float $expected,
	): void {
		$planet = $this->createStub(Planet::class);
		$planet->method('getLevel')->willReturn($planetLevel);
		$planet->method('getAccuracyBonus')->willReturn($accuracyBonus);
		$targetPlayer = $this->createPlayer($playerLevel);

		$result = $this->createWeapon(isTurret: $isTurret)
			->getModifiedPlanetAccuracyAgainstPlayer($planet, $targetPlayer);
		self::assertEqualsWithDelta($expected, $result, 0.0001);
	}

	// Private helper functions -----------------------------------------------

	private function createWeapon(bool $isTurret = false): Weapon {
		return new class ($isTurret) extends Weapon {

			public function __construct(private readonly bool $isTurret) {}

			public function getBaseAccuracy(): int {
				return 60;
			}

			public function getWeaponTypeID(): int {
				return $this->isTurret ? WEAPON_PLANET_TURRET : 0;
			}

			/** @return array{Shield: int, Armour: int, Rollover: bool} */
			public function getDamage(): array {
				return ['Shield' => 11, 'Armour' => 9, 'Rollover' => false];
			}

		};
	}

	/** @return array{Shield: int, Armour: int, Rollover: bool} */
	private function baseDamage(): array {
		return ['Shield' => 11, 'Armour' => 9, 'Rollover' => false];
	}

	private function createPlayer(int $level, int $mr = 0): Player {
		$ship = $this->createStub(AbstractShip::class);
		$ship->method('getMR')->willReturn($mr);
		$player = $this->createStub(Player::class);
		$player->method('getLevelID')->willReturn($level);
		$player->method('getShip')->willReturn($ship);
		return $player;
	}

}
