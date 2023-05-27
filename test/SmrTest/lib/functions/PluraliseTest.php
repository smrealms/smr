<?php declare(strict_types=1);

namespace SmrTest\lib\functions;

use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversFunction('pluralise')]
class PluraliseTest extends TestCase {

	#[TestWith([3, true, '3 tests'])]
	#[TestWith([1, true, '1 test'])]
	#[TestWith([0, true, '0 tests'])]
	#[TestWith([0.5, true, '0.5 tests'])]
	#[TestWith([3, false, 'tests'])]
	#[TestWith([1, false, 'test'])]
	public function test_pluralise(float|int $amount, bool $includeAmount, string $expect): void {
		$result = pluralise($amount, 'test', $includeAmount);
		self::assertSame($expect, $result);
	}

}
