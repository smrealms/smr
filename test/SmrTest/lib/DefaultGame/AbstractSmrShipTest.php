<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use AbstractSmrShip;
use AbstractSmrPlayer;
use Smr\ShipClass;

/**
 * This test is expected to not make any changes to the database.
 * @covers AbstractSmrShip
 */
class AbstractSmrShipTest extends \PHPUnit\Framework\TestCase {

	private $player;

	protected function setUp() : void {
		// Create mock player that will be needed to create any ship
		$this->player = $this->createMock(AbstractSmrPlayer::class);
		$this->player
			->method('getAccountID')
			->willReturn(7);
		$this->player
			->method('getGameID')
			->willReturn(3);
		// Use Demonica because it's the only ship with all special hardware
		$this->player
			->method('getShipTypeID')
			->willReturn(SHIP_TYPE_DEMONICA);
	}

	public function test_base_ship_properties_are_set_correctly() {
		$ship = new AbstractSmrShip($this->player);
		self::assertSame('Demonica', $ship->getName());
		self::assertSame(SHIP_TYPE_DEMONICA, $ship->getShipTypeID());
		self::assertSame(ShipClass::HUNTER, $ship->getShipClassID());
		self::assertSame(6, $ship->getHardpoints());
		self::assertSame(10, $ship->getSpeed());
		self::assertSame(0, $ship->getCost());
	}

	public function test_cloak() {
		$ship = new AbstractSmrShip($this->player);

		// ships are initially uncloaked
		self::assertFalse($ship->isCloaked());

		// remain uncloaked when disabled without hardware
		$ship->decloak();
		self::assertFalse($ship->isCloaked());

		// add cloak hardware
		$ship->increaseHardware(HARDWARE_CLOAK, 1);
		self::assertFalse($ship->isCloaked());
		// enable
		$ship->enableCloak();
		self::assertTrue($ship->isCloaked());
		// disable
		$ship->decloak();
		self::assertFalse($ship->isCloaked());
	}

	public function test_cloak_throws_when_missing_hardware() {
		$ship = new AbstractSmrShip($this->player);
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Ship does not have the supported hardware!');
		$ship->enableCloak();
	}

	public function test_illusion_generator() {
		$ship = new AbstractSmrShip($this->player);

		// ship has no IG initially
		self::assertFalse($ship->getIllusionShip());

		// remain unset when disabled without hardware
		$ship->disableIllusion();
		self::assertFalse($ship->getIllusionShip());

		// add IG hardware
		$ship->increaseHardware(HARDWARE_ILLUSION, 1);
		self::assertFalse($ship->getIllusionShip());
		// enable
		$ship->setIllusion(SHIP_TYPE_THIEF, 12, 13);
		$expected = [
			'ID' => SHIP_TYPE_THIEF,
			'Attack' => 12,
			'Defense' => 13,
		];
		self::assertSame($expected, $ship->getIllusionShip());
		self::assertSame($expected['ID'], $ship->getIllusionShipID());
		self::assertSame($expected['Attack'], $ship->getIllusionAttack());
		self::assertSame($expected['Defense'], $ship->getIllusionDefense());
		self::assertSame('Thief', $ship->getIllusionShipName());
		// disable
		$ship->disableIllusion();
		self::assertFalse($ship->getIllusionShip());
	}

	public function test_illusion_throws_when_missing_hardware() {
		$ship = new AbstractSmrShip($this->player);
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Ship does not have the supported hardware!');
		$ship->setIllusion(SHIP_TYPE_THIEF, 12, 13);;
	}

	public function test_hardware() {
		$ship = new AbstractSmrShip($this->player);

		// shields
		self::assertSame(0, $ship->getShields());
		$ship->increaseShields(10);
		self::assertSame(10, $ship->getShields());
		$ship->increaseShields(5);
		self::assertSame(15, $ship->getShields());
		$ship->decreaseShields(10);
		self::assertSame(5, $ship->getShields());

		// armour
		self::assertSame(0, $ship->getArmour());
		$ship->increaseArmour(10);
		self::assertSame(10, $ship->getArmour());
		$ship->increaseArmour(5);
		self::assertSame(15, $ship->getArmour());
		$ship->decreaseArmour(10);
		self::assertSame(5, $ship->getArmour());

		// CDs
		self::assertSame(0, $ship->getCDs());
		$ship->increaseCDs(10);
		self::assertSame(10, $ship->getCDs());
		$ship->increaseCDs(5);
		self::assertSame(15, $ship->getCDs());
		$ship->decreaseCDs(10);
		self::assertSame(5, $ship->getCDs());

		// Mines
		self::assertSame(0, $ship->getMines());
		$ship->increaseMines(10);
		self::assertSame(10, $ship->getMines());
		$ship->increaseMines(5);
		self::assertSame(15, $ship->getMines());
		$ship->decreaseMines(10);
		self::assertSame(5, $ship->getMines());

		// SDs
		self::assertSame(0, $ship->getSDs());
		$ship->increaseSDs(10);
		self::assertSame(10, $ship->getSDs());
		$ship->increaseSDs(5);
		self::assertSame(15, $ship->getSDs());
		$ship->decreaseSDs(10);
		self::assertSame(5, $ship->getSDs());

		// Cloak
		self::assertTrue($ship->canHaveCloak());
		self::assertFalse($ship->hasCloak());
		$ship->increaseHardware(HARDWARE_CLOAK, 1);
		self::assertTrue($ship->hasCloak());

		// Illusion
		self::assertTrue($ship->canHaveIllusion());
		self::assertFalse($ship->hasIllusion());
		$ship->increaseHardware(HARDWARE_ILLUSION, 1);
		self::assertTrue($ship->hasIllusion());

		// Jump
		self::assertTrue($ship->canHaveJump());
		self::assertFalse($ship->hasJump());
		$ship->increaseHardware(HARDWARE_JUMP, 1);
		self::assertTrue($ship->hasJump());

		// Scanner
		self::assertTrue($ship->canHaveScanner());
		self::assertFalse($ship->hasScanner());
		$ship->increaseHardware(HARDWARE_SCANNER, 1);
		self::assertTrue($ship->hasScanner());

		// DCs
		self::assertTrue($ship->canHaveDCs());
		self::assertFalse($ship->hasDCs());
		$ship->increaseHardware(HARDWARE_DCS, 1);
		self::assertTrue($ship->hasDCs());
	}

}
