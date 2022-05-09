<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use AbstractSmrShip;
use PHPUnit\Framework\TestCase;
use SmrForce;
use SmrTest\TestUtils;

/**
 * @covers SmrForce
 */
class SmrForceTest extends TestCase {

	private SmrForce $force;

	protected function setUp(): void {
		// Create an arbitrary empty force (we avoid using `getForce` for now
		// because of the call to the complicated function `tidyUpForces`).
		$this->force = TestUtils::constructPrivateClass(SmrForce::class, 1, 2, 3);
	}

	public function test_constructor_properties(): void {
		self::assertSame(1, $this->force->getGameID());
		self::assertSame(2, $this->force->getSectorID());
		self::assertSame(3, $this->force->getOwnerID());
	}

	/**
	 * @dataProvider provider_getBumpTurnCost
	 */
	public function test_getBumpTurnCost(int $mines, bool $hasDCS, int $expected): void {
		$this->force->setMines($mines);
		$ship = $this->createPartialMock(AbstractSmrShip::class, ['hasDCS', 'isFederal']);
		$ship->method('hasDCS')->willReturn($hasDCS);
		$ship->method('isFederal')->willReturn(false); // redundant with hasDCS
		self::assertSame($expected, $this->force->getBumpTurnCost($ship));
	}

	/**
	 * @return array<array{int, bool, int}>
	 */
	public function provider_getBumpTurnCost(): array {
		return [
			[0, false, 0],
			[0, true, 0],
			[9, false, 1],
			[9, true, 0],
			[24, false, 2],
			[24, true, 1],
			[25, false, 3],
			[25, true, 2],
		];
	}

	/**
	 * @dataProvider provider_getAttackTurnCost
	 */
	public function test_getAttackTurnCost(bool $hasDCS, int $expected): void {
		$ship = $this->createPartialMock(AbstractSmrShip::class, ['hasDCS', 'isFederal']);
		$ship->method('hasDCS')->willReturn($hasDCS);
		$ship->method('isFederal')->willReturn(false); // redundant with hasDCS
		self::assertSame($expected, $this->force->getAttackTurnCost($ship));
	}

	/**
	 * @return array<array{bool, int}>
	 */
	public function provider_getAttackTurnCost(): array {
		return [
			[false, 3],
			[true, 2],
		];
	}

	public function test_add_and_take_SDs(): void {
		self::assertSame(0, $this->force->getSDs());
		self::assertFalse($this->force->hasSDs());
		$this->force->addSDs(SmrForce::MAX_SDS);
		self::assertSame(SmrForce::MAX_SDS, $this->force->getSDs());
		self::assertTrue($this->force->hasSDs());
		self::assertTrue($this->force->hasMaxSDs());
		$this->force->takeSDs(1);
		self::assertSame(SmrForce::MAX_SDS - 1, $this->force->getSDs());
		self::assertTrue($this->force->hasSDs());
		self::assertFalse($this->force->hasMaxSDs());
	}

	public function test_add_and_take_CDs(): void {
		self::assertSame(0, $this->force->getCDs());
		self::assertFalse($this->force->hasCDs());
		$this->force->addCDs(SmrForce::MAX_CDS);
		self::assertSame(SmrForce::MAX_CDS, $this->force->getCDs());
		self::assertTrue($this->force->hasCDs());
		self::assertTrue($this->force->hasMaxCDs());
		$this->force->takeCDs(1);
		self::assertSame(SmrForce::MAX_CDS - 1, $this->force->getCDs());
		self::assertTrue($this->force->hasCDs());
		self::assertFalse($this->force->hasMaxCDs());
	}

	public function test_add_and_take_mines(): void {
		self::assertSame(0, $this->force->getMines());
		self::assertFalse($this->force->hasMines());
		$this->force->addMines(SmrForce::MAX_MINES);
		self::assertSame(SmrForce::MAX_MINES, $this->force->getMines());
		self::assertTrue($this->force->hasMines());
		self::assertTrue($this->force->hasMaxMines());
		$this->force->takeMines(1);
		self::assertSame(SmrForce::MAX_MINES - 1, $this->force->getMines());
		self::assertTrue($this->force->hasMines());
		self::assertFalse($this->force->hasMaxMines());
	}

}
