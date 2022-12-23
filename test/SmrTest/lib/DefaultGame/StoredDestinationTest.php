<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use PHPUnit\Framework\TestCase;
use Smr\StoredDestination;

/**
 * @covers Smr\StoredDestination
 */
class StoredDestinationTest extends TestCase {

	/**
	 * @testWith ["foo", "#42 - foo"]
	 *           ["", "#42"]
	 */
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
