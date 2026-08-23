<?php declare(strict_types=1);

namespace SmrTest\lib\Combat\Weapon;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Smr\AbstractShip;
use Smr\Combat\Weapon\Mines;
use Smr\Force;
use Smr\Player;
use Smr\Sector;

#[CoversClass(Mines::class)]
class MinesTest extends TestCase {

	public function test_getAmount(): void {
		$mines = new Mines(100);
		self::assertSame(100, $mines->getAmount());
	}

	public function test_getShieldDamage(): void {
		$mines = new Mines(100); // doesn't matter how many
		self::assertSame(20, $mines->getShieldDamage());
	}

	public function test_getArmourDamage(): void {
		$mines = new Mines(100); // doesn't matter how many
		self::assertSame(20, $mines->getShieldDamage());
	}

	public function test_getBaseAccuracy(): void {
		$mines = new Mines(100);
		self::assertSame(100, $mines->getBaseAccuracy());
	}

	#[TestWith([0, 0, false, 96.0])]
	#[TestWith([0, 10, false, 86.0])]
	#[TestWith([25, 10, false, 87.0])]
	#[TestWith([0, 10, true, 37.433674221733])]
	public function test_getModifiedForceAccuracyAgainstPlayer_reduces_accuracy_for_player_level_and_randomness(
		int $numberOfMines,
		int $level,
		bool $minesAreAttacker,
		float $expected,
	): void {
		$mines = new Mines(10);
		$sector = $this->createStub(Sector::class);
		$enemyForces = [];
		if ($numberOfMines > 0) {
			$enemyForce = $this->createStub(Force::class);
			$enemyForce->method('getMines')->willReturn($numberOfMines);
			$enemyForces[] = $enemyForce;
		}
		$sector->method('getEnemyForces')->willReturn($enemyForces);
		$sector->method('getNumberOfConnections')->willReturn(4);
		$forces = $this->createStub(Force::class);
		$forces->method('getSector')->willReturn($sector);
		$targetPlayer = $this->createStub(Player::class);
		$targetPlayer->method('getLevelID')->willReturn($level);
		srand(1);

		$result = $mines->getModifiedForceAccuracyAgainstPlayer($forces, $targetPlayer, $minesAreAttacker);
		self::assertEqualsWithDelta($expected, $result, 0.0001);
	}

	#[TestWith([false, false, 100])]
	#[TestWith([false, true, 75])]
	#[TestWith([true, false, 50])]
	#[TestWith([true, true, 40])]
	public function test_getModifiedForceDamageAgainstPlayer_applies_federal_and_dcs_modifiers(
		bool $isFederal,
		bool $hasDcs,
		int $damage,
	): void {
		$mines = $this->createMines(accuracy: 50);
		$force = $this->createStub(Force::class);
		$targetPlayer = $this->createTargetPlayer(isFederal: $isFederal, hasDcs: $hasDcs);

		$expected = ['Shield' => $damage, 'Armour' => $damage, 'Rollover' => false, 'Launched' => 5];
		$result = $mines->getModifiedForceDamageAgainstPlayer($force, $targetPlayer);
		self::assertSame($expected, $result);
	}

	public function test_shootPlayerAsForce_consumes_only_mines_needed_for_damage(): void {
		$mines = $this->createMines(accuracy: 50);
		$forces = $this->createStub(Force::class);
		$ship = $this->createStub(AbstractShip::class);
		// Five launched mines deal 100 shield damage; only 40 damage was taken.
		$ship->method('takeDamageFromMines')->willReturn(['TotalDamage' => 40, 'KillingShot' => false]);
		$targetPlayer = $this->createStub(Player::class);
		$targetPlayer->method('getShip')->willReturn($ship);

		$result = $mines->shootPlayerAsForce($forces, $targetPlayer);

		self::assertTrue($result['Hit']);
		assert(isset($result['WeaponDamage']['Launched'])); // needed for PHPStan
		self::assertSame(2, $result['WeaponDamage']['Launched']);
		// The two adjusted launches are subtracted from the original ten mines.
		self::assertSame(8, $mines->getAmount());
	}

	private function createMines(int $accuracy): Mines {
		return new class ($accuracy) extends Mines {

			public function __construct(private readonly int $fixedAccuracy) {
				parent::__construct(10);
			}

			public function getModifiedForceAccuracyAgainstPlayer(
				Force $forces,
				Player $targetPlayer,
				bool $minesAreAttacker,
			): float {
				return $this->fixedAccuracy;
			}

		};
	}

	private function createTargetPlayer(bool $isFederal, bool $hasDcs): Player {
		$ship = $this->createStub(AbstractShip::class);
		$ship->method('isFederal')->willReturn($isFederal);
		$ship->method('hasDCS')->willReturn($hasDcs);
		$player = $this->createStub(Player::class);
		$player->method('getShip')->willReturn($ship);
		return $player;
	}

}
