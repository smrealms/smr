<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use ReflectionClassConstant;
use Smr\Container\DiContainer;
use Smr\Epoch;
use SmrEnhancedWeaponEvent;
use SmrLocation;
use SmrTest\BaseIntegrationSpec;

/**
 * @covers SmrEnhancedWeaponEvent
 */
class SmrEnhancedWeaponEventTest extends BaseIntegrationSpec {

	protected function tablesToTruncate(): array {
		return ['location_sells_special', 'location'];
	}

	public function test_integration(): void {
		// Get protected constant from class for more robust validation
		$prop = new ReflectionClassConstant(SmrEnhancedWeaponEvent::class, 'DURATION');
		$buffer = $prop->getValue();

		$gameID = 1;
		$sectorID = 2;
		$locTypePPL = 325; // Pulse of the Universe

		// We need to insert at least one location into the database, since
		// the class doesn't have a very modular design.
		$location = $this->createPartialMock(SmrLocation::class, ['getTypeID']);
		$location->method('getTypeID')->willReturn($locTypePPL);
		SmrLocation::addSectorLocation($gameID, $sectorID, $location);

		// Set an initial t=0
		$epoch = $this->createPartialMock(Epoch::class, ['getTime']);
		$epoch->method('getTime')->willReturn(0);
		DiContainer::getContainer()->set(Epoch::class, $epoch);

		// Create a random event (seed for reproducibility)
		srand(1);
		$event = SmrEnhancedWeaponEvent::getLatestEvent($gameID);
		self::assertSame($sectorID, $event->getSectorID());
		self::assertSame($buffer, $event->getExpireTime());
		self::assertSame(100.0, $event->getDurationRemainingPercent());

		// There is only one weapon it could have selected (PPL)
		$weapon = $event->getWeapon();
		self::assertSame(WEAPON_TYPE_PLANETARY_PULSE_LASER, $weapon->getWeaponTypeID());
		self::assertTrue($weapon->hasBonusDamage());
		self::assertFalse($weapon->hasBonusAccuracy());

		// If we try to create this event again, we get the same one back
		self::assertEquals($event, SmrEnhancedWeaponEvent::getLatestEvent($gameID));

		// Make sure we can get this event from the weapon shop
		$events = SmrEnhancedWeaponEvent::getShopEvents($gameID, $sectorID, $locTypePPL);
		self::assertEquals([$event], $events);

		// Advance to the very latest time that this event is valid
		$epoch = $this->createPartialMock(Epoch::class, ['getTime']);
		$epoch->method('getTime')->willReturn($buffer);
		DiContainer::getContainer()->set(Epoch::class, $epoch);
		self::assertSame(0.0, $event->getDurationRemainingPercent());

		// We should be able to create a 2nd event now, but since there is only
		// one valid configuration in this test, it just replaces it (simulating
		// the unlikely chance of selecting the same configuration).
		$event2 = SmrEnhancedWeaponEvent::getLatestEvent($gameID);
		self::assertNotEquals($event2, $event);
		self::assertSame(2 * $buffer, $event2->getExpireTime());
		$events2 = SmrEnhancedWeaponEvent::getShopEvents($gameID, $sectorID, $locTypePPL);
		self::assertEquals([$event2], $events2);
	}

}
