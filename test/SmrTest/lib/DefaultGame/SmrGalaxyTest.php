<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use Smr\Exceptions\GalaxyNotFound;
use SmrGalaxy;
use SmrSector;
use SmrTest\BaseIntegrationSpec;

/**
 * @covers SmrGalaxy
 */
class SmrGalaxyTest extends BaseIntegrationSpec {

	protected array $tablesToTruncate = ['game_galaxy'];

	protected function setUp(): void {
		// Start each test with an empty galaxy cache
		SmrGalaxy::clearCache();
	}

	public function test_getGalaxy_throws_if_galaxy_does_not_exist(): void {
		// Test that we raise an exception with the wrong Alliance name
		$this->expectException(GalaxyNotFound::class);
		$this->expectExceptionMessage('No such galaxy: 1-2');
		SmrGalaxy::getGalaxy(1, 2);
	}

	public function test_createGalaxy(): void {
		// Test arbitrary input
		$gameID = 42;
		$galaxyID = 3;

		$galaxy = SmrGalaxy::createGalaxy($gameID, $galaxyID);

		$this->assertSame($gameID, $galaxy->getGameID());
		$this->assertSame($galaxyID, $galaxy->getGalaxyID());
	}

	public function test_set_galaxy_dimensions(): void {
		$galaxy = SmrGalaxy::createGalaxy(1, 1);
		$galaxy->setWidth(3);
		$galaxy->setHeight(7);

		self::assertSame(3, $galaxy->getWidth());
		self::assertSame(7, $galaxy->getHeight());
		self::assertSame(21, $galaxy->getSize());
	}

	public function test_get_galaxy_sector_range(): void {
		// Create two sequential galaxies
		$galaxy1 = SmrGalaxy::createGalaxy(1, 1);
		$galaxy2 = SmrGalaxy::createGalaxy(1, 2);

		// Due to getStartSector calling getGameGalaxies, we need the galaxies
		// to be complete so that they can be saved/loaded from the database.
		foreach ([$galaxy1, $galaxy2] as $galaxy) {
			$galaxy->setWidth(3);
			$galaxy->setHeight(7);
			$galaxy->setName('A');
			$galaxy->setGalaxyType(SmrGalaxy::TYPE_NEUTRAL);
			$galaxy->setMaxForceTime(0);
		}

		// We need to save to database due to how getStartSector works
		SmrGalaxy::saveGalaxies();

		// Check the galaxy start and end sectors
		self::assertSame(1, $galaxy1->getStartSector());
		self::assertSame(21, $galaxy1->getEndSector());
		self::assertSame(22, $galaxy2->getStartSector());
		self::assertSame(42, $galaxy2->getEndSector());

		// While we have start sectors calculated, check contains
		self::assertFalse($galaxy1->contains(22));
		self::assertTrue($galaxy2->contains(22));
	}

	public function test_contains_when_passed_an_SmrSector(): void {
		$galaxyID = 3;
		$galaxy = SmrGalaxy::createGalaxy(1, $galaxyID);

		// Test a sector that should be in the galaxy
		$sector1 = $this->createPartialMock(SmrSector::class, ['getGalaxyID']);
		$sector1->method('getGalaxyID')->willReturn($galaxyID);
		self::assertTrue($galaxy->contains($sector1));

		// Test a sector that should NOT be in the galaxy
		$sector2 = $this->createPartialMock(SmrSector::class, ['getGalaxyID']);
		$sector2->method('getGalaxyID')->willReturn($galaxyID + 1);
		self::assertFalse($galaxy->contains($sector2));
	}

}
