<?php declare(strict_types=1);

namespace SmrTest\lib;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Smr\StoredDestination;

#[CoversClass(StoredDestination::class)]
class StoredDestinationTest extends TestCase {

	#[TestWith(['foo', '#42 - foo'])]
	#[TestWith(['', '#42'])]
	public function test_getDisplayName(string $label, string $expected): void {
		$dest = new StoredDestination(
			sectorID: 42,
			label: $label,
			offsetLeft: 1,
			offsetTop: 1,
		);
		self::assertSame($expected, $dest->getDisplayName());
	}

}
