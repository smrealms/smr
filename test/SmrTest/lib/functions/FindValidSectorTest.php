<?php declare(strict_types=1);

namespace SmrTest\lib\functions;

use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Smr\Exceptions\UserError;
use Smr\Sector;

#[CoversFunction('findValidSector')]
class FindValidSectorTest extends TestCase {

	/** @var array<int, Sector> */
	private array $mockSectors;

	public function setUp(): void {
		$sector1 = self::createMock(Sector::class);
		$sector1
			->method('getSectorID')
			->willReturn(1)
			->seal();

		$sector2 = self::createMock(Sector::class);
		$sector2
			->method('getSectorID')
			->willReturn(2)
			->seal();

		$this->mockSectors = [1 => $sector1, 2 => $sector2];
	}

	#[TestWith([1])]
	#[TestWith([2])]
	public function test_happy_path(int $sectorID): void {
		$result = findValidSector(
			$this->mockSectors,
			fn(Sector $sector): bool => $sector->getSectorID() === $sectorID,
		);
		self::assertSame($result, $this->mockSectors[$sectorID]);
	}

	public function test_no_match(): void {
		$this->expectException(UserError::class);
		$this->expectExceptionMessage('There are no eligible sectors meeting this condition!');
		findValidSector(
			$this->mockSectors,
			fn(Sector $sector): bool => $sector->getSectorID() === 3,
		);
	}

}
