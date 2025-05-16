<?php declare(strict_types=1);

namespace SmrTest\lib;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Smr\Sector;

#[CoversClass(Sector::class)]
class SectorTest extends TestCase {

	protected function setUp(): void {
		Sector::clearCache();
	}

	public function test_setLink(): void {
		// Construct a new sector
		$sector = Sector::createSector(1, 1);

		// Test that there are no links to begin with
		$dir = 'Up';
		self::assertSame([], $sector->getLinks());
		self::assertSame(0, $sector->getNumberOfLinks());
		self::assertSame(0, $sector->getLink($dir));
		self::assertFalse($sector->hasLink($dir));

		// When we add a single link
		$sector->setLink($dir, 2);

		// Then we should have updated the links
		self::assertSame([$dir => 2], $sector->getLinks());
		self::assertSame(1, $sector->getNumberOfLinks());
		self::assertSame(2, $sector->getLink($dir));
		self::assertTrue($sector->hasLink($dir));

		// When we remove the link
		$sector->setLink($dir, 0);

		// Then we should have updated the links
		self::assertSame([], $sector->getLinks());
		self::assertSame(0, $sector->getNumberOfLinks());
		self::assertSame(0, $sector->getLink($dir));
		self::assertFalse($sector->hasLink($dir));
	}

	public function test_setLinkSector(): void {
		// Construct two new sectors
		$sector1 = Sector::createSector(1, 1);
		$sector2 = Sector::createSector(1, 2);

		// Test that there is no link to start
		$dir = 'Left';
		$oppositeDir = Sector::oppositeDir($dir);
		self::assertFalse($sector1->hasLink($dir));
		self::assertFalse($sector2->hasLink($oppositeDir));

		// When we link the two sectors
		$sector1->setLinkSector($dir, $sector2);

		// Then both sectors should have updated links
		self::assertSame($sector2, $sector1->getLinkSector($dir));
		self::assertSame($sector1, $sector2->getLinkSector($oppositeDir));
		self::assertSame($sector2->getSectorID(), $sector1->getLink($dir));
		self::assertSame($sector1->getSectorID(), $sector2->getLink($oppositeDir));
	}

	public function test_cannot_link_to_self(): void {
		$sector = Sector::createSector(1, 1);
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Sector must not link to itself!');
		$sector->setLink('Up', $sector->getSectorID());
	}

	public function test_setWarp(): void {
		// Construct two new sectors
		$sector1 = Sector::createSector(1, 1);
		$sector2 = Sector::createSector(1, 2);

		// Test that there are no warps to begin with
		foreach ([$sector1, $sector2] as $sector) {
			self::assertFalse($sector->hasWarp());
			self::assertSame(0, $sector->getWarp());
			self::assertSame(0, $sector->getNumberOfConnections());
		}

		// When we add a warp between the two sectors
		$sector1->setWarp($sector2);

		// Then we should have updated warps
		foreach ([[$sector1, $sector2], [$sector2, $sector1]] as [$sectorA, $sectorB]) {
			self::assertTrue($sectorA->hasWarp());
			self::assertSame($sectorB->getSectorID(), $sectorA->getWarp());
			self::assertSame(1, $sectorA->getNumberOfConnections());
			self::assertSame($sectorB, $sectorA->getWarpSector());
		}

		// When we remove the warp
		$sector1->removeWarp();

		// Then we should have updated warps
		foreach ([$sector1, $sector2] as $sector) {
			self::assertFalse($sector->hasWarp());
			self::assertSame(0, $sector->getWarp());
			self::assertSame(0, $sector->getNumberOfConnections());
		}
	}

	public function test_cannot_warp_to_self(): void {
		$sector = Sector::createSector(1, 1);
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Sector must not warp to itself!');
		$sector->setWarp($sector);
	}

	public function test_cannot_have_multiple_warps(): void {
		$sector1 = Sector::createSector(1, 1);
		$sector2 = Sector::createSector(1, 2);
		$sector3 = Sector::createSector(1, 3);

		// When we already have 1 warp set
		$sector1->setWarp($sector2);

		// Then we cannot set a 2nd warp
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Sector 1 already has a warp (to 2)');
		$sector1->setWarp($sector3);
	}

	public function test_getSectorDirection(): void {
		$sector1 = Sector::createSector(1, 1);
		$sector2 = Sector::createSector(1, 2);

		// Test when we pass the same sector ID
		self::assertSame('Current', $sector1->getSectorDirection($sector1->getSectorID()));

		// Test when we pass an unconnected sector ID
		self::assertSame('None', $sector1->getSectorDirection($sector2->getSectorID()));

		// Test when we set a warp
		$sector1->setWarp($sector2);
		self::assertSame('Warp', $sector1->getSectorDirection($sector2->getSectorID()));
		$sector1->removeWarp();

		// Test when we set a link
		$dir = 'Down';
		$sector1->setLink($dir, $sector2->getSectorID());
		self::assertSame($dir, $sector1->getSectorDirection($sector2->getSectorID()));
	}

	public function test_createGalaxySectors(): void {
		// Create sectors
		$gameId = 3;
		$galaxyId = 4;
		$sectors = Sector::createGalaxySectors($gameId, $galaxyId, 5, 7);
		self::assertCount(3, $sectors);

		// Test that the galaxy cache is populated
		$sectors2 = Sector::getGalaxySectors($gameId, $galaxyId);
		self::assertSame($sectors2, $sectors);

		// Test that the individual sector cache is populated
		$sectors3 = [];
		foreach ([5, 6, 7] as $sectorId) {
			$sectors3[$sectorId] = Sector::getSector($gameId, $sectorId);
		}
		self::assertSame($sectors3, $sectors);
	}

}
