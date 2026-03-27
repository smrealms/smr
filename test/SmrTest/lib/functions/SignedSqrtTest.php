<?php declare(strict_types=1);

namespace SmrTest\lib\functions;

use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversFunction('signed_sqrt')]
class SignedSqrtTest extends TestCase {

	#[TestWith([4., 2.])]
	#[TestWith([0., 0.])]
	#[TestWith([-16., -4.])]
	public function test_signed_sqrt(float $input, float $expect): void {
		self::assertSame($expect, signed_sqrt($input));
		self::assertSame(-$expect, signed_sqrt(-$input));
	}

}
