<?php declare(strict_types=1);

namespace SmrTest\lib\functions;

use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversFunction('format_time')]
class FormatTimeTest extends TestCase {

	#[TestWith([-60, '1 minute ago', '1m ago'])]
	#[TestWith([0, 'now', 'now'])]
	#[TestWith([59, 'less than 1 minute', '&lt;1m'])]
	#[TestWith([60, '1 minute', '1m'])]
	#[TestWith([120, '2 minutes', '2m'])]
	#[TestWith([1092131, '1 week, 5 days, 15 hours and 23 minutes', '1w, 5d, 15h and 23m'])]
	public function test_format_time(int $seconds, string $expectedLong, string $expectedShort): void {
		self::assertSame($expectedLong, format_time($seconds));
		self::assertSame($expectedShort, format_time($seconds, short: true));
	}

}
