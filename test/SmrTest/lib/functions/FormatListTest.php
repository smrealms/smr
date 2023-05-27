<?php declare(strict_types=1);

namespace SmrTest\lib\functions;

use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversFunction('format_list')]
class FormatListTest extends TestCase {

	#[TestWith(['', 0])]
	#[TestWith(['a', 1])]
	#[TestWith(['a and b', 2])]
	#[TestWith(['a, b and c', 3])]
	#[TestWith(['a, b, c and d', 4])]
	public function test_format_list(string $expected, int $length): void {
		$letters = array_slice(range('a', 'd'), 0, $length);
		self::assertSame($expected, format_list($letters));
	}

}
